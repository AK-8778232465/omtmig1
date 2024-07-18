<?php

namespace App\Http\Controllers;
use App\Models\Client;
use App\Models\user;
use App\Models\OrderCreation;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;
use DataTables;
use Illuminate\Support\Collection;


class ReportsController extends Controller
{

    public function Reports(Request $request)
    {
        $clients = Client::select('id','client_no', 'client_name')->where('is_active', 1)->where('is_approved', 1)->get();
        return view('app.reports.index',compact('clients'));
    }


    public function Productdropdown(Request $request)
    {
        $client_id = $request->client_id;
        if (!is_array($client_id)) {
            $client_id = [$client_id];
        }
        if (in_array('All', $client_id)) {
            $getProject = DB::table('stl_item_description')
                            ->select('id', 'client_id', 'process_name', 'project_code')
                            ->orderBy('project_code', 'asc')
                            ->get();
        } else {
            $getProject = DB::table('stl_item_description')
                            ->select('id', 'client_id', 'process_name', 'project_code')
                            ->whereIn('client_id', $client_id)
                            ->orderBy('project_code', 'asc')
                            ->get();
        }

        return response()->json($getProject); // Return JSON response
    }

    public function userwise_count(Request $request)
    {
        $user = Auth::user();
        $processIds = $this->getProcessIdsBasedOnUserRole($user);
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $client_id = $request->input('client_id');
        $project_id = $request->input('project_id');

        $statusCountsQuery = OrderCreation::query();
        $statusCountsQuery
            ->leftJoin('oms_users', 'oms_order_creations.assignee_user_id', '=', 'oms_users.id')
            ->leftJoin('stl_item_description', 'oms_order_creations.process_id', '=', 'stl_item_description.id')
            ->leftJoin('stl_client', 'stl_item_description.client_id', '=', 'stl_client.id')
            ->selectRaw('
                CONCAT(oms_users.emp_id, " (", oms_users.username, ")") as userinfo,
                SUM(CASE WHEN status_id = 1 THEN 1 ELSE 0 END) as `status_1`,
                SUM(CASE WHEN status_id = 2 THEN 1 ELSE 0 END) as `status_2`,
                SUM(CASE WHEN status_id = 3 THEN 1 ELSE 0 END) as `status_3`,
                SUM(CASE WHEN status_id = 4 THEN 1 ELSE 0 END) as `status_4`,
                SUM(CASE WHEN status_id = 5 THEN 1 ELSE 0 END) as `status_5`,
                SUM(CASE WHEN status_id = 13 THEN 1 ELSE 0 END) as `status_13`,
                SUM(CASE WHEN status_id = 14 THEN 1 ELSE 0 END) as `status_14`,
                COUNT(*) as `All`')
            ->whereNotNull('assignee_user_id')
            ->where('oms_order_creations.is_active', 1)
            ->where('stl_client.is_approved', 1)
            ->where('stl_item_description.is_approved', 1)
            ->whereDate('order_date', '>=', $fromDate)
            ->whereDate('order_date', '<=', $toDate)
            ->whereIn('oms_order_creations.process_id', $processIds)
            ->groupBy('oms_order_creations.assignee_user_id');

        if (!empty($project_id) && $project_id[0] !== 'All') {
            $statusCountsQuery->whereIn('oms_order_creations.process_id', $project_id);
        }

        if (!empty($client_id) && $client_id[0] !== 'All') {
            $statusCountsQuery->whereIn('stl_item_description.client_id', $client_id);
        }

        $statusCounts = $statusCountsQuery->get();

        $dataForDataTables = $statusCounts->map(function ($count) {
            return [
                'userinfo' => $count->userinfo,
                'status_1' => $count->status_1,
                'status_2' => $count->status_2,
                'status_3' => $count->status_3,
                'status_4' => $count->status_4,
                'status_5' => $count->status_5,
                'status_13' => $count->status_13,
                'status_14' => $count->status_14,
                'All' => $count->status_1 + $count->status_2 + $count->status_3 + $count->status_4 + $count->status_5 + $count->status_13 + $count->status_14,
            ];
        });

        return Datatables::of($dataForDataTables)->toJson();
    }


private function getProcessIdsBasedOnUserRole($user)
    {
        if (Auth::user()->hasRole('Super Admin')) {
            return DB::table('stl_item_description')->where('is_approved', 1)->where('is_active', 1)->pluck('id')->toArray();
        } else {
            $reportingUserIds = User::getAllLowerLevelUserIds(Auth::id());
            return DB::table('oms_user_service_mapping')->whereIn('user_id', $reportingUserIds)->where('is_active', 1)->pluck('service_id')->toArray();
        }
    }
}

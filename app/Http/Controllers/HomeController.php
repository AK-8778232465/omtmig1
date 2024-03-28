<?php

namespace App\Http\Controllers;

use DB;
use Hash;
use Session;
use App\Models\User;
use App\Models\Service;
use App\Models\Order;
use App\Models\Client;
use App\Models\County;
use App\Models\OrderCreation;
use App\Models\State;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Response;
use DataTables;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $user = User::where('id', Auth::id())->first();
        $processList=[];
        if ($user->is_active == 1) {
            session(['uid' => Auth::id()]);
            session(['user_type_id' => $user->user_type_id]);
            session(['company_id' => isset($user->company_id) ? $user->company_id : 0]);
            $reportingUserIds = User::getAllLowerLevelUserIds(Auth::id());
            if (Auth::user()->hasRole('Super Admin')) {
                $processIds = DB::table('stl_item_description')->where('is_approved', 1)->where('is_active', 1)->pluck('id')->toArray();
            } else {
                $processIds = DB::table('oms_user_service_mapping')->whereIn('user_id', $reportingUserIds)->where('is_active', 1)->pluck('service_id')->toArray();
            }
            $processList = DB::table('stl_item_description')->where('is_approved', 1)->where('is_active', 1)->whereIn('id', $processIds)->select('id', 'process_name', 'project_code')->get();

            $clients = Client::select('id','client_no', 'client_name')->where('is_active', 1)->where('is_approved', 1)->get();


        } else {
            Auth::logout();

            return redirect('/');
        }

        return view('app.dashboard.index', compact('processList','clients'));
    }

    public function profileupdate(Request $request)
    {
        $request->validate([
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->input('new_password'));
        $user->save();

        return redirect()->route('home')->with('success', 'Password updated successfully.');
    }

    public function profileEdit(Request $request)
    {
        return view('app.dashboard.profile_edit');
    }

    public function getCounty(Request $request)
    {
        $getCounty['county'] = County::select('id', 'stateId', 'county_name')->where('stateId', $request->state_id)->get();

        return response()->json($getCounty);
    }

    public function dashboard_count(Request $request)
    {
        $user = Auth::user();
        $processIds = $this->getProcessIdsBasedOnUserRole($user);
        $statusCountsQuery = OrderCreation::query();
        if ($request->project_id != 'All') {
            $statusCountsQuery->whereIn('process_id', $processIds)->where('process_id', $request->project_id);
        } else {
            $statusCountsQuery = $statusCountsQuery->whereIn('process_id', $processIds);
        }

        if (!in_array($user->user_type_id, [1, 2, 3, 4, 5, 9])) {
            if($user->user_type_id == 6) {
                $statusCountsQuery->where('assignee_user_id', $user->id);
            } elseif($user->user_type_id == 7) {
                $statusCountsQuery->where('assignee_qa_id', $user->id);
            } elseif($user->user_type_id == 8) {
                $statusCountsQuery->where(function ($query) use($user){
                    $query->where('assignee_user_id', $user->id)
                        ->orWhere('assignee_qa_id', $user->id);
                });
            }
        }

        $statusCounts = $statusCountsQuery->groupBy('status_id')
            ->selectRaw('count(*) as count, status_id')
            ->where('is_active', 1)
            ->pluck('count', 'status_id');

        if (in_array($user->user_type_id, [1, 2, 3, 4, 5, 9])) {
            $yetToAssignUser = $yetToAssignQa = 0;
            if ($request->project_id != 'All') {
                $yetToAssignUser = OrderCreation::where('assignee_user_id', null)->where('status_id', 1)->where('is_active', 1)->where('process_id', $request->project_id)->count();
                $yetToAssignQa = OrderCreation::where('assignee_qa_id', null)->where('status_id', 4)->where('is_active', 1)->where('process_id', $request->project_id)->count();
            } else {
                $yetToAssignUser = OrderCreation::where('assignee_user_id', null)->where('status_id', 1)->where('is_active', 1)->whereIn('process_id', $processIds)->count();
                $yetToAssignQa = OrderCreation::where('assignee_qa_id', null)->where('status_id', 4)->where('is_active', 1)->whereIn('process_id', $processIds)->count();
            }
            $statusCounts[1] = (!empty($statusCounts[1]) ? $statusCounts[1] : 0) - $yetToAssignUser;
            // $statusCounts[4] = (!empty($statusCounts[4]) ? $statusCounts[4] : 0) - $yetToAssignQa;
            $statusCounts[4] = (!empty($statusCounts[4]) ? $statusCounts[4] : 0);
            // $statusCounts[6] = $yetToAssignUser + $yetToAssignQa;
            $statusCounts[6] = $yetToAssignUser;
        } else {
            $statusCounts[6] = [0];
        }

        return response()->json(['StatusCounts' => $statusCounts]);
    }

    public function dashboard_datewise_count(Request $request)
    {
        $user = Auth::user();
        $processIds = $this->getProcessIdsBasedOnUserRole($user);
        $statusCountsQuery = OrderCreation::query();
        if ($request->project_id != 'All') {
            $statusCountsQuery->whereIn('process_id', $processIds)->where('process_id', $request->project_id);
        } else {
            $statusCountsQuery = $statusCountsQuery->whereIn('process_id', $processIds);
        }

        $statusCountsQuery->selectRaw('
                    DATE_FORMAT(order_date, "%m/%d/%Y") as order_date_formatted,
                    order_date,
                    SUM(CASE WHEN status_id = 1 THEN 1 ELSE 0 END) as `status_1`,
                    SUM(CASE WHEN status_id = 2 THEN 1 ELSE 0 END) as `status_2`,
                    SUM(CASE WHEN status_id = 3 THEN 1 ELSE 0 END) as `status_3`,
                    SUM(CASE WHEN status_id = 4 THEN 1 ELSE 0 END) as `status_4`,
                    SUM(CASE WHEN status_id = 5 THEN 1 ELSE 0 END) as `status_5`,
                    COUNT(*) as `status_6`
                ')->where('is_active', 1);

        if (!in_array($user->user_type_id, [1, 2, 3, 4, 5, 9])) {
            if($user->user_type_id == 6) {
                $statusCountsQuery->where('assignee_user_id', $user->id);
            } elseif($user->user_type_id == 7) {
                $statusCountsQuery->where('assignee_qa_id', $user->id);
            } elseif($user->user_type_id == 8) {
                $statusCountsQuery->where(function ($query) use($user){
                    $query->where('assignee_user_id', $user->id)
                        ->orWhere('assignee_qa_id', $user->id);
                });
            }
        }

        $statusCounts = $statusCountsQuery->groupBy('order_date')->get();

        $dataForDataTables = $statusCounts->map(function ($count) {
            return [
                'order_date' => $count->order_date_formatted,
                'order_date_unformatted' => $count->order_date,
                'status_1' => $count->status_1,
                'status_2' => $count->status_2,
                'status_3' => $count->status_3,
                'status_4' => $count->status_4,
                'status_5' => $count->status_5,
                'status_6' => $count->status_6,
            ];
        });

        return Datatables::of($dataForDataTables)->toJson();
    }

    public function dashboard_userwise_count(Request $request)
    {
        $user = Auth::user();
        $processIds = $this->getProcessIdsBasedOnUserRole($user);

        $statusCountsQuery = OrderCreation::query();
        $statusCountsQuery->whereNotNull('assignee_user_id');

        if ($request->project_id != 'All') {
            $statusCountsQuery->whereIn('process_id', $processIds)->where('process_id', $request->project_id);
        } else {
            $statusCountsQuery->whereIn('process_id', $processIds);
        }

        $statusCountsQuery->leftJoin('oms_users', 'oms_order_creations.assignee_user_id', '=', 'oms_users.id')
            ->selectRaw('
                CONCAT(oms_users.emp_id, " (", oms_users.username, ")") as userinfo,
                SUM(CASE WHEN status_id = 1 THEN 1 ELSE 0 END) as `status_1`,
                SUM(CASE WHEN status_id = 2 THEN 1 ELSE 0 END) as `status_2`,
                SUM(CASE WHEN status_id = 3 THEN 1 ELSE 0 END) as `status_3`,
                SUM(CASE WHEN status_id = 4 THEN 1 ELSE 0 END) as `status_4`,
                SUM(CASE WHEN status_id = 5 THEN 1 ELSE 0 END) as `status_5`,
                COUNT(*) as `status_6`
            ')
            ->where('oms_order_creations.is_active', 1)
            ->groupBy('oms_order_creations.assignee_user_id');

        $statusCounts = $statusCountsQuery->get();

        $dataForDataTables = $statusCounts->map(function ($count) {
            return [
                'userinfo' => $count->userinfo,
                'status_1' => $count->status_1,
                'status_2' => $count->status_2,
                'status_3' => $count->status_3,
                'status_4' => $count->status_4,
                'status_5' => $count->status_5,
                'status_6' => $count->status_6,
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



    public function revenue_detail(Request $request)
    {
            $user = Auth::user();
            $processIds = $this->getProcessIdsBasedOnUserRole($user);

            $fromDate = $request->input('from_date');
            $toDate = $request->input('to_date');
            $client_ids = $request->input('client_id');

            $query = DB::table('stl_item_description')
                ->select(
                    'oms_order_creations.process_id',
                    'stl_item_description.project_code',
                    'stl_item_description.process_name',
                    'stl_client.client_name',
                    'stl_client.client_no',
                    DB::raw('MAX(oms_order_creations.order_date) as order_date'),
                    DB::raw('COUNT(*) as num_orders_completed'),
                    DB::raw('SUM(stl_item_description.cost) as total_revenue')
                )
                ->join('oms_order_creations', 'stl_item_description.id', '=', 'oms_order_creations.process_id')
                ->join('stl_client', 'stl_item_description.client_id', '=', 'stl_client.id')
                ->where('stl_item_description.billing_type_id', 1)
                ->where('oms_order_creations.status_id', 5)
                ->where('oms_order_creations.is_active', 1)
                ->whereIn('oms_order_creations.process_id', $processIds)
                ->groupBy('stl_item_description.project_code')
                ->groupBy('stl_item_description.process_name')
                ->groupBy('oms_order_creations.process_id')
                ->groupBy('stl_client.client_name')
                ->groupBy('stl_client.client_no')
                ->groupBy('oms_order_creations.order_date')
                ->orderBy('stl_item_description.project_code');

            if ($fromDate && $toDate) {
                $query->whereBetween('oms_order_creations.order_date', [$fromDate, $toDate]);
            }

            if (!empty($client_ids) && $client_ids[0] !== 'All') {
                $query->whereIn('stl_client.id', $client_ids);
            }

            $revenueDetails = $query->get();
            $grandTotalRevenue = $revenueDetails->sum('total_revenue');

            $output = [];

            foreach ($revenueDetails as $revenueDetail) {
                $processId = $revenueDetail->process_id;

                if (isset($output[$processId])) {
                    $output[$processId]['No of orders completed'] += $revenueDetail->num_orders_completed;
                    $output[$processId]['Total'] += $revenueDetail->total_revenue;
                } else {
                    $output[$processId] = [
                        'id' => $processId,
                        'Client Name' => $revenueDetail->client_name,
                        'Client Code' => $revenueDetail->client_no,
                        'Date' => $revenueDetail->order_date,
                        'Process Name' => str_replace('&amp;', '&', $revenueDetail->process_name),
                        'Project Code' => $revenueDetail->project_code,
                        'No of orders completed' => $revenueDetail->num_orders_completed,
                        'Total' => $revenueDetail->total_revenue,
                    ];
                }
            }

            foreach ($output as &$item) {
                $item['Total'] = number_format($item['Total'], 5, '.', '');
                $item['Unit cost'] = $item['No of orders completed'] > 0 ? $item['Total'] / $item['No of orders completed'] : 0;
                $item['Unit cost'] = number_format($item['Unit cost'], 5, '.', ''); // Formatting unit cost to 5 decimal places
            }

            unset($item);

            return Datatables::of($output)->toJson();
    }


    public function revenue_detail_client(Request $request)
    {
        $user = Auth::user();
        $processIds = $this->getProcessIdsBasedOnUserRole($user);

        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');
        $client_ids = $request->input('client_id');

        $query = DB::table('stl_item_description')
            ->select(
                'stl_item_description.project_code',
                'stl_client.id',
                'stl_client.client_name',
                'stl_client.client_no',
                DB::raw('MAX(oms_order_creations.order_date) as order_date'),
                DB::raw('COUNT(DISTINCT oms_order_creations.order_id) as total_orders_completed'), // Counting distinct order IDs
                DB::raw('SUM(stl_item_description.cost) as total_revenue')
            )
            ->join('oms_order_creations', 'stl_item_description.id', '=', 'oms_order_creations.process_id')
            ->join('stl_client', 'stl_item_description.client_id', '=', 'stl_client.id')
            ->where('stl_item_description.billing_type_id', 1)
            ->where('oms_order_creations.status_id', 5)
            ->where('oms_order_creations.is_active', 1)
            ->whereIn('oms_order_creations.process_id', $processIds)
            ->groupBy('stl_item_description.project_code', 'stl_client.client_name', 'stl_client.client_no','stl_client.id');



        if ($fromDate && $toDate) {
            $query->whereBetween('oms_order_creations.order_date', [$fromDate, $toDate]);
        }

        if (!empty($client_ids) && $client_ids[0] !== 'All') {
            $query->whereIn('stl_client.id', $client_ids);
        }


        $revenueDetails = $query->get();
        $grandTotalRevenue = $revenueDetails->sum('total_revenue');

        $output = [];

        foreach ($revenueDetails as $revenueDetail) {
            $clientCode = $revenueDetail->client_no;


            if (isset($output[$clientCode])) {

                $output[$clientCode]['No of orders completed'] += $revenueDetail->total_orders_completed;
                $output[$clientCode]['Total'] += $revenueDetail->total_revenue;
            } else {

                $output[$clientCode] = [
                    'id' => $revenueDetail->id,
                    'Date' => $revenueDetail->order_date,
                    'Project Code' => $revenueDetail->project_code,
                    'Client Code' => $revenueDetail->client_no,
                    'Client Name' => $revenueDetail->client_name,
                    'No of orders completed' => $revenueDetail->total_orders_completed,
                    'Total' => $revenueDetail->total_revenue,
                ];
            }
        }

        foreach ($output as &$item) {
            $item['Total'] = number_format($item['Total'], 5, '.', '');
        }

        unset($item);

        return Datatables::of($output)->toJson();
    }

    public function order_detail(Request $request)
    {
        $user = Auth::user();
        $processIds = $this->getProcessIdsBasedOnUserRole($user);

        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $client_ids = $request->input('client_id');
        $process_id = $request->input('projectId');

        $query = DB::table('stl_item_description')
            ->select(
                'oms_order_creations.process_id',
                'stl_item_description.project_code',
                'stl_item_description.process_name',
                'stl_client.client_name',
                'stl_client.client_no',
                DB::raw('MAX(oms_order_creations.order_date) as order_date'),
                DB::raw('COUNT(*) as num_orders_completed'),
                DB::raw('SUM(stl_item_description.cost) as total_revenue')
            )
            ->join('oms_order_creations', 'stl_item_description.id', '=', 'oms_order_creations.process_id')
            ->join('stl_client', 'stl_item_description.client_id', '=', 'stl_client.id')
            ->where('stl_item_description.billing_type_id', 1)
            ->where('oms_order_creations.status_id', 5)
            ->where('oms_order_creations.is_active', 1)
            ->groupBy('stl_item_description.project_code')
            ->groupBy('stl_item_description.process_name')
            ->groupBy('oms_order_creations.process_id')
            ->groupBy('stl_client.client_name')
            ->groupBy('stl_client.client_no')
            ->groupBy('oms_order_creations.order_date')
            ->orderBy('stl_item_description.project_code');

        if ($fromDate && $toDate) {
            $query->whereBetween('oms_order_creations.order_date', [$fromDate, $toDate]);
        }

        if (!empty($client_ids) && $client_ids[0] !== 'All') {
            $query->whereIn('stl_client.id', $client_ids);
        }

        if (!empty($process_id)) {
            $query->where('oms_order_creations.process_id', $process_id);
        } else {
            $query->whereIn('oms_order_creations.process_id', $processIds);
        }


        $revenueDetails = $query->get();
        $grandTotalRevenue = $revenueDetails->sum('total_revenue');

        $output = [];

        foreach ($revenueDetails as $revenueDetail) {

            $processName = str_replace('&amp;', '&', $revenueDetail->process_name);

            $unitCost = $revenueDetail->num_orders_completed > 0 ? $revenueDetail->total_revenue / $revenueDetail->num_orders_completed : 0;

            $output[] = [
                'id' => $revenueDetail->process_id,
                'Client Name' => $revenueDetail->client_name,
                'Client Code' => $revenueDetail->client_no,
                'Date' => $revenueDetail->order_date,
                'Process Name' =>  $processName ,
                'Project Code' => $revenueDetail->project_code,
                'No of orders completed' => $revenueDetail->num_orders_completed,
                'Unit cost' => $unitCost,
                'Total' => $revenueDetail->total_revenue,
            ];
        }
        return Datatables::of($output)->toJson();
    }


    public function getTotalData(Request $request)
    {
        $user = Auth::user();
        $processIds = $this->getProcessIdsBasedOnUserRole($user);

        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $client_ids = $request->input('client_id');

        $query = DB::table('stl_item_description')
            ->select(
                'stl_item_description.project_code',
                DB::raw('MAX(oms_order_creations.order_date) as order_date'),
                DB::raw('COUNT(*) as num_orders_completed'),
                DB::raw('SUM(stl_item_description.cost) as total_revenue')
            )
            ->join('oms_order_creations', 'stl_item_description.id', '=', 'oms_order_creations.process_id')
            ->join('stl_client', 'stl_item_description.client_id', '=', 'stl_client.id')
            ->where('stl_item_description.billing_type_id', 1)
            ->where('oms_order_creations.status_id', 5)
            ->where('oms_order_creations.is_active', 1)
            ->whereIn('oms_order_creations.process_id', $processIds)
            ->groupBy('stl_client.client_name')
            ->groupBy('stl_client.client_no')
            ->groupBy('stl_item_description.project_code')
            ->groupBy('oms_order_creations.order_date')
            ->orderBy('stl_item_description.project_code');

        if ($fromDate && $toDate) {
            $query->whereBetween('oms_order_creations.order_date', [$fromDate, $toDate]);
        }

        if (!empty($client_ids) && $client_ids[0] !== 'All') {
            $query->whereIn('stl_client.id', $client_ids);
        }


        $revenueDetails = $query->get();

        $grandTotalRevenue = number_format((float)$revenueDetails->sum('total_revenue'), 5, '.', '');

        return response()->json(['GrandTotal' => $grandTotalRevenue]);
    }

}




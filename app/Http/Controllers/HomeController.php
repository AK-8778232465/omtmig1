<?php

namespace App\Http\Controllers;

use DB;
use Carbon\Carbon;
use Hash;
use Session;
use App\Models\User;
use App\Models\Service;
use App\Models\Order;
use App\Models\Client;
use App\Models\Process;
use App\Models\County;
use App\Models\OrderCreation;
use App\Models\State;
use App\Models\Status;
use App\Models\stl_item_description;
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

    public function dashboard_dropdown(Request $request)
    {
        $client_id = null;

        $getclient_id = $request->client_id;

        if (!is_array($getclient_id)) {
            $getclient_id = [$getclient_id];
        }

        if (in_array('All', $getclient_id)) {
            $getProject = DB::table('stl_item_description')
                            ->select('id', 'client_id', 'process_name', 'project_code')
                            ->get();
        } else {
            $getProject = DB::table('stl_item_description')
                            ->select('id', 'client_id', 'process_name', 'project_code')
                            ->whereIn('client_id', $getclient_id)
                            ->get();
        }

        return response()->json($getProject);
    }


    public function dashboard_count(Request $request)
    {
        $user = Auth::user();
        $processIds = $this->getProcessIdsBasedOnUserRole($user);

        // Define default request data values
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');
        $project_id = $request->input('project_id', ['All']); // Default to 'All'
        $client_id = $request->input('client_id', ['All']); // Default to 'All'



        // Ensure project_id and client_id are arrays
        if (!is_array($project_id)) {
            $project_id = explode(',', $project_id); // Convert string to array
        }

        if (!is_array($client_id)) {
            $client_id = explode(',', $client_id); // Convert string to array
        }

        $statusCountsQuery = OrderCreation::query();

        // Handle project_id and client_id cases
        if (in_array('All', $project_id) && !in_array('All', $client_id)) {
            // Case: Project_id is 'All' and client_id is not 'All'
            $statusCountsQuery->with('process', 'client')
                ->whereIn('process_id', $processIds)
                ->whereBetween('order_date', [$from_date, $to_date])
                ->whereHas('process', function ($query) use ($client_id) {
                    $query->whereIn('client_id', $client_id);
                });
        } else {
            if (!in_array('All', $project_id)) {
                // Case: project_id is specified (not 'All')
                $statusCountsQuery->whereIn('process_id', $processIds)
                    ->whereIn('process_id', $project_id)
                    ->whereBetween('order_date', [$from_date, $to_date]);
            } else {
                // Case: project_id is 'All'
                $statusCountsQuery->whereIn('process_id', $processIds)
                    ->whereBetween('order_date', [$from_date, $to_date]);
                    // return response()->json($statusCountsQuery->get());
            }
        }

        // Handle different user types
        if (!in_array($user->user_type_id, [1, 2, 3, 4, 5, 9])) {
            if ($user->user_type_id == 6) {
                $statusCountsQuery->where('assignee_user_id', $user->id);
            } elseif ($user->user_type_id == 7) {
                $statusCountsQuery->where('assignee_qa_id', $user->id);
            } elseif ($user->user_type_id == 8) {
                $statusCountsQuery->where(function ($query) use($user){
                    $query->where('assignee_user_id', $user->id)
                        ->orWhere('assignee_qa_id', $user->id);
                });
            }
        }

        // Calculate status counts
        $statusCounts = $statusCountsQuery->groupBy('status_id')
            ->selectRaw('count(*) as count, status_id')
            ->where('is_active', 1)
            ->pluck('count', 'status_id');
        // Additional conditions based on user type
        $yetToAssignUser = 0;
        $yetToAssignQa = 0;

        if (in_array($user->user_type_id, [1, 2, 3, 4, 5, 6, 7, 8, 9])) {
            // Handle additional query based on project_id and client_id
            if (in_array('All', $project_id) && !in_array('All', $client_id)) {
                // Case: Project_id is 'All' and client_id is not 'All'
                $yetToAssignUser = OrderCreation::with('process', 'client')
                    ->where('assignee_user_id', null)
                    ->where('status_id', 1)
                    ->where('is_active', 1)
                    ->whereBetween('order_date', [$from_date, $to_date])
                    ->whereHas('process', function ($query) use ($client_id) {
                        $query->whereIn('client_id', $client_id);
                    })
                    ->count();
            } elseif (!in_array('All', $project_id) && in_array('All', $client_id)) {
                // Case: project_id is specified and client_id is not 'All'
                $yetToAssignUser = OrderCreation::where('assignee_user_id', null)
                    ->where('status_id', 1)
                    ->where('is_active', 1)
                    ->whereIn('process_id', $project_id)
                    ->whereBetween('order_date', [$from_date, $to_date])
                    ->count();

                $yetToAssignQa = OrderCreation::where('assignee_qa_id', null)
                    ->where('status_id', 4)
                    ->where('is_active', 1)
                    ->whereIn('process_id', $project_id)
                    ->whereBetween('order_date', [$from_date, $to_date])
                    ->count();
            }elseif(!in_array('All', $project_id) && !in_array('All', $client_id)){
                $yetToAssignUser = OrderCreation::with('process', 'client')
                ->where('assignee_user_id', null)
                ->where('status_id', 1)
                ->where('is_active', 1)
                ->whereIn('process_id', $project_id)
                ->whereBetween('order_date', [$from_date, $to_date])
                ->whereHas('process', function ($query) use ($client_id) {
                    $query->whereIn('client_id', $client_id);
                })
                ->count();
            } else {
                // Case: project_id is 'All' and client_id is 'All'
                $yetToAssignUser = OrderCreation::where('assignee_user_id', null)
                    ->where('status_id', 1)
                    ->where('is_active', 1)
                    ->whereIn('process_id', $processIds)
                    ->whereBetween('order_date', [$from_date, $to_date])
                    ->count();

                $yetToAssignQa = OrderCreation::where('assignee_qa_id', null)
                    ->where('status_id', 4)
                    ->where('is_active', 1)
                    ->whereIn('process_id', $processIds)
                    ->whereBetween('order_date', [$from_date, $to_date])
                    ->count();
            }

            $statusCounts[1] = (!empty($statusCounts[1]) ? $statusCounts[1] : 0) - $yetToAssignUser;
            $statusCounts[4] = (!empty($statusCounts[4]) ? $statusCounts[4] : 0);
            $statusCounts[6] = $yetToAssignUser;
        } else {
            $statusCounts[6] = [0];
        }

        return response()->json([
            'StatusCounts' => $statusCounts,
        ]);
    }




    public function dashboard_datewise_count(Request $request)
{
    $user = Auth::user();

    $fromDate = $request->input('from_date');
    $toDate = $request->input('to_date');
    $client_id = $request->input('client_id');
    $project_id = $request->input('project_id');

    $processIds = $this->getProcessIdsBasedOnUserRole($user);

    $statusCountsQuery = OrderCreation::query()
        ->select('stl_client.client_name', 'stl_item_description.process_name', 'oms_order_creations.process_id', 'stl_item_description.project_code')
        ->leftJoin('stl_item_description', 'oms_order_creations.process_id', '=', 'stl_item_description.id')
        ->leftJoin('stl_client', 'stl_item_description.client_id', '=', 'stl_client.id')
        ->selectRaw('COUNT(CASE WHEN oms_order_creations.status_id = 1 AND oms_order_creations.assignee_user_id IS NOT NULL THEN 1 END) AS WIP')
        ->selectRaw('COUNT(CASE WHEN oms_order_creations.status_id = 2 THEN 2 END) AS Hold')
        ->selectRaw('COUNT(CASE WHEN oms_order_creations.status_id = 3 THEN 3 END) AS Cancelled')
        ->selectRaw('COUNT(CASE WHEN oms_order_creations.status_id = 4 THEN 4 END) AS Send_for_QC')
        ->selectRaw('COUNT(CASE WHEN oms_order_creations.status_id = 5 THEN 5 END) AS Completed')
        ->groupBy('stl_client.client_name', 'stl_item_description.process_name', 'oms_order_creations.process_id', 'stl_item_description.project_code');

    // Apply client_id condition
    if (!empty($client_id) && $client_id[0] !== 'All') {
        $statusCountsQuery->whereIn('stl_client.id', $client_id);
    }

    // Apply project_id condition
    if (!empty($project_id) && $project_id[0] !== 'All') {
        $statusCountsQuery->whereIn('oms_order_creations.process_id', $project_id);
    }

    // Apply date filtering
    if (!empty($fromDate) && !empty($toDate)) {
        $statusCountsQuery->where('order_date', '>=', $fromDate)->where('order_date', '<=', $toDate);
    } elseif (!empty($fromDate)) {
        $statusCountsQuery->where('order_date', '>=', $fromDate);
    } elseif (!empty($toDate)) {
        $statusCountsQuery->where('order_date', '<=', $toDate);
    }

    $dataForDataTables = $statusCountsQuery->get();

    $output = [];
    foreach ($dataForDataTables as $data) {
        // Initialize sum variable
        $sum = 0;
        // Add counts to sum conditionally
        if (isset($data->WIP)) $sum += $data->WIP;
        if (isset($data->Hold)) $sum += $data->Hold;
        if (isset($data->Cancelled)) $sum += $data->Cancelled;
        if (isset($data->Send_for_QC)) $sum += $data->Send_for_QC;
        if (isset($data->Completed)) $sum += $data->Completed;

        $output[] = [
            'client_name' => $data->client_name,
            'process_name' => $data->process_name,
            'project_code' => $data->project_code,
            'WIP' => $data->WIP,
            'Hold' => $data->Hold,
            'Cancelled' => $data->Cancelled,
            'Send for QC' => $data->Send_for_QC,
            'Completed' => $data->Completed,
            'All' => $sum, // Add the sum as 'All'
            // Add other fields as needed
        ];
    }

    return response()->json(['data' => $output]);
}

public function dashboard_userwise_count(Request $request)
{
    $user = Auth::user();
    $processIds = $this->getProcessIdsBasedOnUserRole($user);
    $fromDate = $request->input('from_date');
    $toDate = $request->input('to_date');
    $client_id = $request->input('client_id');
    $project_id = $request->input('project_id');

    $statusCountsQuery = OrderCreation::query();

    $statusCountsQuery
    ->whereNotNull('assignee_user_id')
    ->whereBetween('order_date', [$fromDate, $toDate])
    ->leftJoin('oms_users', 'oms_order_creations.assignee_user_id', '=', 'oms_users.id')
    ->leftJoin('stl_item_description', 'oms_order_creations.process_id', '=', 'stl_item_description.id')
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

    if (!empty($project_id) && $project_id[0] !== 'All') {
        $statusCountsQuery->whereIn('process_id', $project_id);
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
                $item['Total'] = number_format($item['Total'], 2, '.', '');
                $item['Unit cost'] = $item['No of orders completed'] > 0 ? $item['Total'] / $item['No of orders completed'] : 0;
                $item['Unit cost'] = number_format($item['Unit cost'], 2, '.', ''); // Formatting unit cost to 5 decimal places
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
            $item['Total'] = number_format($item['Total'], 2, '.', '');
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

        $grandTotalRevenue = number_format((float)$revenueDetails->sum('total_revenue'), 2, '.', '');

        return response()->json(['GrandTotal' => $grandTotalRevenue]);
    }



// public function revenue_detail_process_fte(Request $request)
// {
//     $user = Auth::user();
//     $processIds = $this->getProcessIdsBasedOnUserRole($user);


//     $fromDate = $request->input('ftefromDate');
//     $toDate = $request->input('ftetoDate');
//     $client_ids = $request->input('fteclient_id');

//     $query = DB::table('stl_item_description')
//         ->select(
//             'stl_item_description.id',
//             'stl_item_description.project_code',
//             'stl_item_description.process_name',
//             'stl_item_description.cost AS unit_cost',
//             'stl_item_description.no_of_resources',
//             'stl_client.client_no',
//             'stl_client.client_name',
//             'stl_item_description.effective_date'
//         )
//         ->join('stl_client', 'stl_item_description.client_id', '=', 'stl_client.id')
//         ->where('stl_item_description.is_active', 1)
//         ->where('stl_client.is_active', 1)
//         ->where('stl_item_description.billing_type_id', 2)
//         ->whereIn('stl_item_description.id', $processIds);
//         // ->where('stl_item_description.no_of_resources', '>', 0);

//     if (!empty($client_ids) && $client_ids[0] !== 'All') {
//         $query->whereIn('stl_client.id', $client_ids);
//     }

//     $revenueDetails = $query->get();

//     $output = [];

//     foreach ($revenueDetails as $revenueDetail) {
//         $projectCode = $revenueDetail->project_code;
//         $process_name = $revenueDetail->process_name;
//         $effectiveDate = Carbon::parse($revenueDetail->effective_date);

//         // $startDate = empty($fromDate) ? Carbon::parse($revenueDetail->effective_date) : Carbon::parse($fromDate);

//         $startDate = empty($fromDate) ? $effectiveDate : Carbon::parse($fromDate);
//         if ($startDate->lt($effectiveDate)) {
//             $startDate = $effectiveDate;
//         }
//         $endDate = empty($toDate) ? Carbon::today() : Carbon::parse($toDate)->endOfDay();

//         $cumulativeTotal = 0;
//         $monthlyRevenue = 0; // Track monthly revenue
//         $currentDate = $startDate->copy();

//         while ($currentDate <= $endDate) {
//             $daysInEffectiveMonth = $currentDate->daysInMonth;
//             $daysRemaining = $endDate->diffInDays($currentDate) + 1;

//             $perDayAmount = $revenueDetail->unit_cost / $daysInEffectiveMonth;
//             $invoiceAmount = $perDayAmount * $revenueDetail->no_of_resources;

//             // Check if it's the first day of the month
//             if ($currentDate->day == 1) {
//                 $monthlyRevenue = 0;
//             }

//             $monthlyRevenue += $invoiceAmount;
//             $cumulativeTotal += $invoiceAmount;

//             $output[$projectCode]['id'] = $revenueDetail->id;
//             $output[$projectCode][$projectCode."(".$process_name.")"][] = [
//                 'process_name' => $revenueDetail->process_name,
//                 'Unit cost' => $revenueDetail->unit_cost,
//                 'No of Resources' => $revenueDetail->no_of_resources,
//                 'per_day_amount' => number_format($perDayAmount, 2),
//                 'invoice_amount' => number_format($invoiceAmount, 2),
//                 'client_no' => $revenueDetail->client_no,
//                 'Eff Date' => $revenueDetail->effective_date,
//                 'Client' => $revenueDetail->client_name,
//                 'Date' => $currentDate->format('Y-m-d'),
//                 'days' => $daysRemaining,
//                 'unit_cost_divided_by' => $daysInEffectiveMonth,
//                 'Monthly Revenue' => number_format($monthlyRevenue, 2),
//             ];
//             $output[$projectCode]['total_revenue_generated_till_date'] = number_format($cumulativeTotal, 2);
//             $currentDate->addDay();
//         }
//     }
//     return Datatables::of($output)->toJson();
//     // return response()->json($output);
// }

// Manikandan







public function revenue_detail_process_fte(Request $request){

    $user = Auth::user();
    $processIds = $this->getProcessIdsBasedOnUserRole($user);
    $fromDate = $request->input('from_date');
    $toDate = $request->input('to_date');
    $client_ids = $request->input('client_id');
    $project_id = $request->input('project_id');

    $query = DB::table('service_audit AS sa')
        ->select(
            'sa.description_id AS service_id',
            'sa.description_id as id',
            'sa.process_name',
            'sid.project_code',
            'sc.client_name',
            'sa.cost AS unit_cost',
            'sa.no_of_resources',
            'sa.effective_date AS start_date',
            DB::raw('IFNULL((
                SELECT MIN(effective_date)
                FROM service_audit
                WHERE description_id = sa.description_id
                    AND process_name = sa.process_name
                    AND effective_date > sa.effective_date), CURDATE()) AS end_date'),
            DB::raw('DATEDIFF(IFNULL((
                SELECT MIN(effective_date)
                FROM service_audit
                WHERE description_id = sa.description_id
                    AND process_name = sa.process_name
                    AND effective_date > sa.effective_date), CURDATE()), sa.effective_date) AS days')
        )
        ->join('stl_item_description AS sid', 'sa.description_id', '=', 'sid.id')
        ->join('stl_client AS sc', 'sid.client_id', '=', 'sc.id')
        ->where('sa.is_active', 1)
        ->where('sid.is_active', 1)
        ->where('sid.billing_type_id', 2)
        ->whereIn('sa.description_id', $processIds)
        ->orderBy('sa.effective_date');

    if (!empty($client_ids) && $client_ids[0] !== 'All') {
        $query->whereIn('sc.id', $client_ids);
    }

    $auditRecords = $query->get();
    $output = [];

    foreach ($auditRecords as $key => $auditRecord) {
        // Determine start date
        $start_date = Carbon::parse($fromDate);
        if ($start_date->lessThan($auditRecord->start_date)) {
            $start_date = Carbon::parse($auditRecord->start_date);
        }

        // Determine end date
        $end_date = ($auditRecord->end_date === $toDate) ? Carbon::parse($auditRecord->end_date) : Carbon::parse($toDate);

        // Adjust end date if it's today
        if ($end_date->isToday()) {
            $end_date = Carbon::today();
        }

        $unit_cost = $auditRecord->unit_cost;
        $no_of_resources = $auditRecord->no_of_resources;

        // Calculate the difference in days between start_date and end_date
        $days = $end_date->diffInDays($start_date);

        // If it's not the last record, add 1 day to $days
        if ($key < count($auditRecords) - 1) {
            $days++;
        }

        // Calculate revenue based on days
        $revenue_selected = ($days / $start_date->daysInMonth) * $unit_cost * $no_of_resources;

        $output[] = [
            'id' => $auditRecord->service_id,
            'process_name' => $auditRecord->process_name,
            'project_code' => $auditRecord->project_code,
            'unit_cost' => $unit_cost,
            'no_of_resources' => $no_of_resources,
            'expected_revenue' => $no_of_resources * $unit_cost,
            'client_name' => $auditRecord->client_name,
            'start_date' => $start_date->format('m-d-Y'),
            'end_date' => $end_date->format('m-d-Y'),
            'days' => $days,
            'revenue_selected' => number_format($revenue_selected, 2, '.'),
        ];
    }


    return response()->json(['data' => $output]);
}



public function revenue_detail_processDetail_fte(Request $request)
{
    $user = Auth::user();
    $processIds = $this->getProcessIdsBasedOnUserRole($user);

    $fromDate = $request->input('from_date');
    $toDate = $request->input('to_date');
    $process_id =  $request->input('project_id');

    $query = DB::table('service_audit')
        ->select(
            'service_audit.id',
            'service_audit.description_id as id',
            'service_audit.process_name',
            'service_audit.unit_type_id',
            'service_audit.cost AS unit_cost',
            'service_audit.no_of_resources',
            'service_audit.effective_date'
        )
        ->join('stl_item_description', 'service_audit.description_id', '=', 'stl_item_description.id')
        ->where('service_audit.is_active', 1)
        ->where('stl_item_description.is_active', 1)
        ->where('stl_item_description.billing_type_id', 2)
        ->whereIn('service_audit.description_id', $processIds);

    if (!empty($process_id)) {
        $query->where('service_audit.description_id', $process_id);
    }

    $auditRecords = $query->get();

    // return response()->json($auditRecords, 200);

    $output = [];

    $cumulativeTotal = 0;
    $monthlyRevenue = 0; // Track monthly revenue
    $grandTotal = 0;

    foreach ($auditRecords as $key => $auditRecord) {
        $effective_date = Carbon::parse($auditRecord->effective_date);
        // Calculate end date based on next effective date or given end date
        $nextEffectiveDate = isset($auditRecords[$key + 1]) ? Carbon::parse($auditRecords[$key + 1]->effective_date)->subDay() : Carbon::today();
        $endDate = $nextEffectiveDate;
        $startDate = empty($fromDate) ? $effective_date : Carbon::parse($fromDate);
        // $startDate =  $effective_date ;
        if ($startDate->lt($effective_date)) {
            $startDate = $effective_date;
        }
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $daysInEffectiveMonth = $currentDate->daysInMonth;
            $daysRemaining = $endDate->diffInDays($currentDate) + 1;

            $perDayAmount = $auditRecord->unit_cost / $daysInEffectiveMonth;
            $invoiceAmount = $perDayAmount * $auditRecord->no_of_resources;

            if ($currentDate->day == 1) {
                $monthlyRevenue = 0;
            }

            $monthlyRevenue += $invoiceAmount;
            $cumulativeTotal += $invoiceAmount;
            $grandTotal += $invoiceAmount;

            if (empty($fromDate) || ($currentDate >= Carbon::parse($fromDate) && $currentDate <= Carbon::parse($toDate))) {
                $output[] = [
                    'process_name' => $auditRecord->process_name,
                    'Unit Cost' => $auditRecord->unit_cost,
                    'No of Resources' => $auditRecord->no_of_resources,
                    'per_day_amount' => number_format($perDayAmount, 2),
                    'invoice_amount' => number_format($invoiceAmount, 2),
                    'Eff Date' => $effective_date->format('Y-m-d'),
                    'Date' => $currentDate->format('Y-m-d'),
                    'days' => $daysRemaining,
                    'unit_cost_divided_by' => $daysInEffectiveMonth,
                    'Monthly Revenue' => number_format($monthlyRevenue, 2), // Monthly revenue
                    'Revenue Generated Till Date' => number_format($cumulativeTotal, 2)
                ];
                foreach ($output as &$row) {
                    $row['grand_total'] = number_format($grandTotal, 2);
                }
            }
            // Move to the next day
            $currentDate->addDay();
        }
    }

    // return response()->json($output);
    return Datatables::of($output)->toJson();
}

public function revenue_detail_process_total_fte(Request $request)
{
    $user = Auth::user();
    $processIds = $this->getProcessIdsBasedOnUserRole($user);

    $fromDate = $request->input('from_date');
    $toDate = $request->input('to_date');
    $process_id =  $request->input('project_id');

    $query = DB::table('service_audit')
        ->select(
            'service_audit.id',
            'service_audit.description_id as id',
            'service_audit.process_name',
            'service_audit.unit_type_id',
            'service_audit.cost AS unit_cost',
            'service_audit.no_of_resources',
            'service_audit.effective_date'
        )
        ->join('stl_item_description', 'service_audit.description_id', '=', 'stl_item_description.id')
        ->where('service_audit.is_active', 1)
        ->where('stl_item_description.is_active', 1)
        ->where('stl_item_description.billing_type_id', 2)
        ->where('service_audit.description_id', $process_id);

    $auditRecords = $query->get();

    // Initialize grand total
    $grandTotal = 0;

    $output = [];

    foreach ($auditRecords as $key => $auditRecord) {
        $effective_date = Carbon::parse($auditRecord->effective_date);
        // Calculate end date based on next effective date or given end date
        $nextEffectiveDate = isset($auditRecords[$key + 1]) ? Carbon::parse($auditRecords[$key + 1]->effective_date)->subDay() : Carbon::today();
        $endDate = $nextEffectiveDate;
        $startDate = empty($fromDate) ? $effective_date : Carbon::parse($fromDate);
        if ($startDate->lt($effective_date)) {
            $startDate = $effective_date;
        }
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $daysInEffectiveMonth = $currentDate->daysInMonth;
            $perDayAmount = $auditRecord->unit_cost / $daysInEffectiveMonth;
            $invoiceAmount = $perDayAmount * $auditRecord->no_of_resources;
            if (empty($fromDate) || ($currentDate >= Carbon::parse($fromDate) && $currentDate <= Carbon::parse($toDate))) {
             $grandTotal += $invoiceAmount;
            }
            $currentDate->addDay();
        }
        // Add the current total to output array
        $output[] = [
            'Total' => number_format($grandTotal, 2),
        ];
    }

    $totalSum = array_sum(array_column($output, 'Total'));

    if (count($output) > 1) {
        $output[] = [
            'Total Sum' => number_format($totalSum, 2),
        ];
    }else{
        $output[] = [
            'Total Sum' => number_format($grandTotal, 2),
        ];
    }

    return response()->json(['Total' => $output[count($output) - 1]['Total Sum']]);
}

public function revenue_detail_client_fte(Request $request){

    $user = Auth::user();
    $processIds = $this->getProcessIdsBasedOnUserRole($user);
    $fromDate = $request->input('from_date');
    $toDate = $request->input('to_date');
    $client_ids = $request->input('client_id');
    $project_id = $request->input('project_id');

    $query = DB::table('service_audit AS sa')
        ->select(
            'sa.description_id AS service_id',
            'sa.description_id as id',
            'sa.process_name',
            'sid.project_code',
            'sc.client_name',
            'sa.cost AS unit_cost',
            'sa.no_of_resources',
            'sa.effective_date AS start_date',
            DB::raw('IFNULL((
                SELECT MIN(effective_date)
                FROM service_audit
                WHERE description_id = sa.description_id
                    AND process_name = sa.process_name
                    AND effective_date > sa.effective_date), CURDATE()) AS end_date'),
            DB::raw('DATEDIFF(IFNULL((
                SELECT MIN(effective_date)
                FROM service_audit
                WHERE description_id = sa.description_id
                    AND process_name = sa.process_name
                    AND effective_date > sa.effective_date), CURDATE()), sa.effective_date) AS days')
        )
        ->join('stl_item_description AS sid', 'sa.description_id', '=', 'sid.id')
        ->join('stl_client AS sc', 'sid.client_id', '=', 'sc.id')
        ->where('sa.is_active', 1)
        ->where('sid.is_active', 1)
        ->where('sid.billing_type_id', 2)
        ->whereIn('sa.description_id', $processIds)
        ->orderBy('sa.effective_date');

    if (!empty($client_ids) && $client_ids[0] !== 'All') {
        $query->whereIn('sc.id', $client_ids);
    }

    $auditRecords = $query->get();
    $output = [];

    foreach ($auditRecords as $key => $auditRecord) {

        $start_date = Carbon::parse($fromDate);
        if ($start_date->lessThan($auditRecord->start_date)) {
            $start_date = Carbon::parse($auditRecord->start_date);
        }


        $end_date = ($auditRecord->end_date === $toDate) ? Carbon::parse($auditRecord->end_date) : Carbon::parse($toDate);


        if ($end_date->isToday()) {
            $end_date = Carbon::today();
        }

        $unit_cost = $auditRecord->unit_cost;
        $no_of_resources = $auditRecord->no_of_resources;


        $days = $end_date->diffInDays($start_date);


        if ($key < count($auditRecords) - 1) {
            $days++;
        }


        $revenue_selected = ($days / $start_date->daysInMonth) * $unit_cost * $no_of_resources;

        $output[] = [
            'id' => $auditRecord->service_id,
            'process_name' => $auditRecord->process_name,
            'project_code' => $auditRecord->project_code,
            'unit_cost' => $unit_cost,
            'no_of_resources' => $no_of_resources,
            'expected_revenue' => $no_of_resources * $unit_cost,
            'client_name' => $auditRecord->client_name,
            'start_date' => $start_date->format('m-d-Y'),
            'end_date' => $end_date->format('m-d-Y'),
            'days' => $days,
            'revenue_selected' => number_format($revenue_selected, 2, '.'),
        ];
    }

        $groupedOutput = [];
        foreach ($output as $record) {
    $clientName = $record['client_name'];
    $totalRevenue = str_replace(',', '', $record['revenue_selected']);

    if (!isset($groupedOutput[$clientName])) {
        $groupedOutput[$clientName] = [
            'total_revenue' => 0,
            'revenues_selected' => [],
        ];
    }

    $groupedOutput[$clientName]['total_revenue'] += floatval($totalRevenue);
    $groupedOutput[$clientName]['revenues_selected'][] = floatval($totalRevenue);
}

$finalOutput = [];

foreach ($groupedOutput as $clientName => $data) {
    $finalOutput[] = [
        'client_name' => $clientName,
        'total_revenue_selected' => number_format($data['total_revenue'], 2, '.'),
    ];
}

    return response()->json(['data' => $finalOutput]);

}
}

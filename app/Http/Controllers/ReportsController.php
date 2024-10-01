<?php

namespace App\Http\Controllers;
use App\Models\Client;
use App\Models\user;
use App\Models\State;
use App\Models\County;
use App\Models\City;
use App\Models\OrderCreation;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;
use DataTables;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;


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
        $client_id = $request->input('client_id');
        $project_id = $request->input('project_id');
        $selectedDateFilter = $request->input('selectedDateFilter');
        $fromDateRange = $request->input('fromDate_range');
        $toDateRange = $request->input('toDate_range');

        $fromDate = null;
        $toDate = null;
        if ($fromDateRange && $toDateRange) {
            $fromDate = Carbon::createFromFormat('Y-m-d', $fromDateRange)->toDateString();
            $toDate = Carbon::createFromFormat('Y-m-d', $toDateRange)->toDateString();
        } else {
            $datePattern = '/(\d{2}-\d{2}-\d{4})/';
            if (!empty($selectedDateFilter) && strpos($selectedDateFilter, 'to') !== false) {
                list($fromDateText, $toDateText) = explode('to', $selectedDateFilter);
                $fromDateText = trim($fromDateText);
                $toDateText = trim($toDateText);
                preg_match($datePattern, $fromDateText, $fromDateMatches);
                preg_match($datePattern, $toDateText, $toDateMatches);
                $fromDate = isset($fromDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $fromDateMatches[1])->toDateString() : null;
                $toDate = isset($toDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $toDateMatches[1])->toDateString() : null;
            } else {
                preg_match($datePattern, $selectedDateFilter, $dateMatches);
                $fromDate = isset($dateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $dateMatches[1])->toDateString() : null;
                $toDate = $fromDate;
            }
        }

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
    //new reports
    public function orderWise(Request $request)
    {
        $user = Auth::user();
        $processIds = $this->getProcessIdsBasedOnUserRole($user);
        $client_id = $request->input('client_id');
        $project_id = $request->input('project_id');
        $selectedDateFilter = $request->input('selectedDateFilter');
        $fromDateRange = $request->input('fromDate_range');
        $toDateRange = $request->input('toDate_range');

        $fromDate = null;
        $toDate = null;
        if ($fromDateRange && $toDateRange) {
            $fromDate = Carbon::createFromFormat('Y-m-d', $fromDateRange)->toDateString();
            $toDate = Carbon::createFromFormat('Y-m-d', $toDateRange)->toDateString();
        } else {
            $datePattern = '/(\d{2}-\d{2}-\d{4})/';
            if (!empty($selectedDateFilter) && strpos($selectedDateFilter, 'to') !== false) {
                list($fromDateText, $toDateText) = explode('to', $selectedDateFilter);
                $fromDateText = trim($fromDateText);
                $toDateText = trim($toDateText);
                preg_match($datePattern, $fromDateText, $fromDateMatches);
                preg_match($datePattern, $toDateText, $toDateMatches);
                $fromDate = isset($fromDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $fromDateMatches[1])->toDateString() : null;
                $toDate = isset($toDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $toDateMatches[1])->toDateString() : null;
            } else {
                preg_match($datePattern, $selectedDateFilter, $dateMatches);
                $fromDate = isset($dateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $dateMatches[1])->toDateString() : null;
                $toDate = $fromDate;
            }
        }

        $query = DB::table('oms_order_creations')
            ->leftJoin('stl_item_description', 'oms_order_creations.process_id', '=', 'stl_item_description.id')
            ->leftJoin('oms_state', 'oms_order_creations.state_id', '=', 'oms_state.id')
            ->leftJoin('county', 'oms_order_creations.county_id', '=', 'county.id')
            ->leftJoin('oms_status', 'oms_order_creations.status_id', '=', 'oms_status.id')
            ->leftJoin('stl_client', 'stl_item_description.client_id', '=', 'stl_client.id')
            ->leftJoin('stl_process', 'stl_item_description.process_id', '=', 'stl_process.id')
            ->leftJoin('oms_city', 'oms_order_creations.city_id', '=', 'oms_city.id')
            ->leftJoin('county_instructions', function($join) {
                $join->on('oms_order_creations.state_id', '=', 'county_instructions.state_id')
                     ->on('oms_order_creations.county_id', '=', 'county_instructions.county_id')
                     ->on(function($query) {
                         $query->on('oms_order_creations.city_id', '=', 'county_instructions.city_id')
                               ->orWhereNull('oms_order_creations.city_id')
                               ->whereNull('county_instructions.city_id');
                     })
                     ->on('stl_item_description.client_id', '=', 'county_instructions.client_id')
                     ->on('stl_item_description.lob_id', '=', 'county_instructions.lob_id');
            })
            ->leftJoin('order_status_history', function ($join) {
                $join->on('oms_order_creations.id', '=', 'order_status_history.order_id')
                    ->where('order_status_history.id', '=', DB::raw("(SELECT MAX(id) FROM order_status_history WHERE order_id = oms_order_creations.id)"));
            })
            ->select(
                'oms_order_creations.order_id as order_id',
                'oms_order_creations.order_date as order_date',
                'oms_order_creations.completion_date as completion_date',
                'stl_item_description.process_name as process',
                'oms_order_creations.city_id as city',
                'oms_state.short_code as short_code',
                'county.county_name as county_name',
                'oms_status.status as status',
                'county_instructions.json as county_instruction_json',
                'oms_order_creations.comment as status_comment'
            )
            ->whereNotNull('oms_order_creations.assignee_user_id')
            ->whereDate('oms_order_creations.order_date', '>=', $fromDate)
            ->whereDate('oms_order_creations.order_date', '<=', $toDate)
            ->whereIn('oms_order_creations.process_id', $processIds)
            ->where('oms_order_creations.is_active', 1)
            ->whereIn('oms_order_creations.status_id', [1, 2, 3, 4, 5, 13, 14])
            ->where('stl_item_description.is_approved', 1)
            ->where('stl_client.is_approved', 1);

        if (!empty($client_id) && $client_id[0] !== 'All') {
            $query->whereIn('stl_item_description.client_id', $client_id);
        }
        if (!empty($project_id) && $project_id[0] !== 'All') {
            $query->whereIn('oms_order_creations.process_id', $project_id);
        }
        $results = $query->orderBy('oms_order_creations.id', 'desc')->get();
        $results = $results->map(function($item) {
            $json = json_decode($item->county_instruction_json, true);
            $primarySource = $json['PRIMARY']['PRIMARY_SOURCE'] ?? null;
            return [
                'process' => $item->process,
                'order_date' => $item->order_date,
                'completion_date' => $item->completion_date,
                'order_id' => $item->order_id,
                'short_code' => $item->short_code,
                'county_name' => $item->county_name,
                'status' => $item->status,
                 'status_comment' => $item->status_comment,
                'primary_source' => $primarySource,
            ];
        });

        return Datatables::of($results)->toJson();
    }

    public function getGeoCounty(Request $request)
    {
        $getCounty['county'] = County::select('id', 'stateId', 'county_name')->where('stateId', $request->state_id)->get(); 
        return response()->json($getCounty);
    }
 
    public function getGeoCities(Request $request)
    {
        $cities = City::select('id', 'city')->where('county_id', $request->county_id)->get();
       
        return response()->json($cities);
    }

public function get_timetaken(Request $request)
    {
        $user = Auth::user();
        $processIds = $this->getProcessIdsBasedOnUserRole($user);
        $clientId = $request->input('client_id');
        $projectId = $request->input('project_id');
        $selectedDateFilter = $request->input('selectedDateFilter');
        $fromDateRange = $request->input('fromDate_range');
        $toDateRange = $request->input('toDate_range');
    
        $fromDate = null;
        $toDate = null;
    
        if ($fromDateRange && $toDateRange) {
            $fromDate = Carbon::createFromFormat('Y-m-d', $fromDateRange)->toDateString();
            $toDate = Carbon::createFromFormat('Y-m-d', $toDateRange)->toDateString();
        } else {
            $datePattern = '/(\d{2}-\d{2}-\d{4})/';
            if (!empty($selectedDateFilter) && strpos($selectedDateFilter, 'to') !== false) {
                list($fromDateText, $toDateText) = explode('to', $selectedDateFilter);
                $fromDateText = trim($fromDateText);
                $toDateText = trim($toDateText);
                preg_match($datePattern, $fromDateText, $fromDateMatches);
                preg_match($datePattern, $toDateText, $toDateMatches);
                $fromDate = isset($fromDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $fromDateMatches[1])->toDateString() : null;
                $toDate = isset($toDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $toDateMatches[1])->toDateString() : null;
            } else {
                preg_match($datePattern, $selectedDateFilter, $dateMatches);
                $fromDate = isset($dateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $dateMatches[1])->toDateString() : null;
                $toDate = $fromDate;
            }
        }
    
        $statusCountsQuery = OrderCreation::query()
            ->leftJoin('oms_users', 'oms_order_creations.assignee_user_id', '=', 'oms_users.id')
            ->leftJoin('stl_item_description', 'oms_order_creations.process_id', '=', 'stl_item_description.id')
            ->leftJoin('stl_client', 'stl_item_description.client_id', '=', 'stl_client.id')
            ->select(
                'oms_users.id as userid',
                'oms_users.emp_id as empid',
                'oms_users.username as username',
                'stl_item_description.process_name as process_name',
                'stl_item_description.project_code as project_code',
                'oms_order_creations.id as orderid'
            )
            ->whereNotNull('assignee_user_id')
            ->where('oms_order_creations.is_active', 1)
            ->where('stl_client.is_approved', 1)
            ->where('stl_item_description.is_approved', 1);
    
        if ($fromDate && $toDate) {
            $statusCountsQuery->whereDate('order_date', '>=', $fromDate)
                              ->whereDate('order_date', '<=', $toDate);
        }
    
        if (!empty($processIds)) {
            $statusCountsQuery->whereIn('oms_order_creations.process_id', $processIds);
        }
    
        if (!empty($projectId) && $projectId[0] !== 'All') {
            $statusCountsQuery->whereIn('oms_order_creations.process_id', $projectId);
        }
    
        if (!empty($clientId) && $clientId[0] !== 'All') {
            $statusCountsQuery->whereIn('stl_item_description.client_id', $clientId);
        }
    
       

        if(!empty($projectId) && $projectId[0] !== 'All'){
        $statusCounts = $statusCountsQuery->get();
            $statusCounts = $statusCounts->groupBy('process_name');
        }else{
            $statusCounts = $statusCountsQuery->get();
            $statusCounts = $statusCounts->groupBy('userid');
        }

        $dataForDataTables = $statusCounts->map(function ($orders, $userid) use($fromDate, $toDate, $projectId, $clientId){
        $completedCount = 0;
        if (!empty($projectId) && $projectId[0] !== 'All') {
    
            $completedCount = DB::table('oms_order_creations')
            ->whereDate('order_date', '>=', $fromDate)
            ->whereDate('order_date', '<=', $toDate)
            ->where('oms_order_creations.is_active', 1)
                ->where('status_id', 5)
                ->where('assignee_user_id', $userid)
                ->whereIn('oms_order_creations.process_id', $projectId)
                ->count();
        }elseif(!empty($clientId) && $clientId[0] !== 'All') {
            $completedCount = DB::table('oms_order_creations')
                ->leftJoin('oms_users', 'oms_order_creations.assignee_user_id', '=', 'oms_users.id')
                ->leftJoin('stl_item_description', 'oms_order_creations.process_id', '=', 'stl_item_description.id')
                ->leftJoin('stl_client', 'stl_item_description.client_id', '=', 'stl_client.id')
                ->whereDate('oms_order_creations.order_date', '>=', $fromDate)
                ->whereDate('oms_order_creations.order_date', '<=', $toDate)
                ->where('oms_order_creations.is_active', 1)
                ->where('oms_order_creations.status_id', 5)
                ->where('oms_order_creations.assignee_user_id', $userid)
                ->whereIn('stl_item_description.client_id', $clientId)
                ->count();
        }else{
            $completedCount = DB::table('oms_order_creations')
                ->whereDate('order_date', '>=', $fromDate)
                ->whereDate('order_date', '<=', $toDate)
                ->where('oms_order_creations.is_active', 1)
                ->where('status_id', 5)
                ->where('assignee_user_id', $userid)
                ->count();
        }
            $totalTimeTakenSeconds = 0;
            foreach ($orders as $order) {
            $orderStartTime = DB::table('order_status_history')
                    ->where('order_id', $order->orderid)
                    ->where('status_id', 1)
                    ->orderBy('created_at', 'asc')
                    ->first();
    
            $orderEndTime = DB::table('order_status_history')
                    ->where('order_id', $order->orderid)
                    ->where('status_id', 5)
                    ->orderBy('created_at', 'asc')
                    ->first();
    
                if ($orderStartTime && $orderEndTime) {
                
                $startTime = Carbon::parse($orderStartTime->created_at);
                $endTime = Carbon::parse($orderEndTime->created_at);
    
                $timeTakenSeconds = $endTime->diffInSeconds($startTime);
                $totalTimeTakenSeconds += $timeTakenSeconds;
                }
            }
    
        $totalTimeTakenHours = $this->formatSecondsToHours($totalTimeTakenSeconds);
        $avgTimeTakenSeconds = $completedCount > 0 ? ($totalTimeTakenSeconds / $completedCount) : 0;
        $avgTimeTakenHours = $this->formatSecondsToHours($avgTimeTakenSeconds);
    
    
            return [
                'emp_id' => $orders->first()->empid,
                'Users' => $orders->first()->username,
                'Product_Type' => $orders->first()->project_code . ' (' . $orders->first()->process_name . ')',
            'NO_OF_ASSIGNED_ORDERS' => $orders->count(),
                'NO_OF_COMPLETED_ORDERS' => $completedCount,
                'TOTAL_TIME_TAKEN_FOR_COMPLETED_ORDERS' => $totalTimeTakenHours,
                'AVG_TIME_TAKEN_FOR_COMPLETED_ORDERS' => $avgTimeTakenHours,
            'TOTAL_TIME_TAKEN_SECONDS' => $totalTimeTakenSeconds, 
            'AVG_TIME_TAKEN_SECONDS' => $avgTimeTakenSeconds, 
            ];
        });
    
        return Datatables::of($dataForDataTables)->toJson();

}
    
private function formatSecondsToHours($seconds)
{
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $seconds = $seconds % 60; 
    return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds); 
}
   
public function orderTimeTaken(Request $request) {
        $user = Auth::user();
        $processIds = $this->getProcessIdsBasedOnUserRole($user);
        $clientId = $request->input('client_id');
        $projectId = $request->input('project_id');
        $selectedDateFilter = $request->input('selectedDateFilter');
        $fromDateRange = $request->input('fromDate_range');
        $toDateRange = $request->input('toDate_range');
   
        $fromDate = null;
        $toDate = null;
   
        if ($fromDateRange && $toDateRange) {
            $fromDate = Carbon::createFromFormat('Y-m-d', $fromDateRange)->toDateString();
            $toDate = Carbon::createFromFormat('Y-m-d', $toDateRange)->toDateString();
        } else {
            $datePattern = '/(\d{2}-\d{2}-\d{4})/';
            if (!empty($selectedDateFilter) && strpos($selectedDateFilter, 'to') !== false) {
                list($fromDateText, $toDateText) = explode('to', $selectedDateFilter);
                $fromDateText = trim($fromDateText);
                $toDateText = trim($toDateText);
                preg_match($datePattern, $fromDateText, $fromDateMatches);
                preg_match($datePattern, $toDateText, $toDateMatches);
                $fromDate = isset($fromDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $fromDateMatches[1])->toDateString() : null;
                $toDate = isset($toDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $toDateMatches[1])->toDateString() : null;
            } else {
                preg_match($datePattern, $selectedDateFilter, $dateMatches);
                $fromDate = isset($dateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $dateMatches[1])->toDateString() : null;
                $toDate = $fromDate;
            }
        }
   
   
        $statusCountsQuery = OrderCreation::query()
            ->leftJoin('oms_users', 'oms_order_creations.assignee_user_id', '=', 'oms_users.id')
            ->leftJoin('stl_item_description', 'oms_order_creations.process_id', '=', 'stl_item_description.id')
            ->leftJoin('stl_client', 'stl_item_description.client_id', '=', 'stl_client.id')
            ->select(
                'oms_users.id as userid',
                'oms_users.emp_id as empid',
                'oms_users.username as username',
                'stl_item_description.process_name as process_name',
                'stl_item_description.project_code as project_code',
                'oms_order_creations.id as orderid'
            )
            ->whereNotNull('assignee_user_id')
            ->where('oms_order_creations.is_active', 1)
            ->where('stl_client.is_approved', 1)
            ->where('stl_item_description.is_approved', 1);
    
        if ($fromDate && $toDate) {
            $statusCountsQuery->whereDate('order_date', '>=', $fromDate)
                              ->whereDate('order_date', '<=', $toDate);
        }

        if (!empty($processIds)) {
            $statusCountsQuery->whereIn('oms_order_creations.process_id', $processIds);
        }
    
    
        if (!empty($projectId) && $projectId[0] !== 'All') {
            $statusCountsQuery->whereIn('oms_order_creations.process_id', $projectId);
        }
    
        if (!empty($clientId) && $clientId[0] !== 'All') {
            $statusCountsQuery->whereIn('stl_item_description.client_id', $clientId);
        }
    
        if(!empty($projectId) && $projectId[0] !== 'All'){
            $statusCounts = $statusCountsQuery->get();
            $statusCounts = $statusCounts->groupBy('process_name');
        }else{
    $statusCounts = $statusCountsQuery->get();
            $statusCounts = $statusCounts->groupBy('userid');
        }
    $statusOrder = [1, 13, 14, 4, 5];
    $dataForDataTables = $statusCounts->map(function ($orders, $userid) use ($fromDate, $toDate, $statusOrder) {
    
            $userDurations = [
            'Emp ID' => $orders->first()->empid,
            'Users' => $orders->first()->username,
            'Product_Type' => $orders->first()->project_code . ' (' . $orders->first()->process_name . ')',
            'Assigned Orders' => $orders->count(),
            'WIP' => ['count' => 0, 'time' => 0],
            'COVERSHEET PRP' => ['count' => 0, 'time' => 0],
            'CLARIFICATION' => ['count' => 0, 'time' => 0],
            'SEND FOR QC' => ['count' => 0, 'time' => 0],
            'COMPLETED' => ['count' => 0, 'time' => 0],
            ];
    
            foreach ($orders as $order) {
            $getFirstWipStatusHistory = DB::table('order_status_history')
                ->where('order_id', $order->orderid)
                ->where('status_id', 1) // Assuming 1 represents 'WIP'
                ->first();
    
            if ($getFirstWipStatusHistory) {
                $orderStatusHistory = DB::table('order_status_history')
                    ->where('order_id', $order->orderid)
                    ->whereIn('status_id', $statusOrder)
                ->orderBy('created_at', 'asc')
                    ->get();
    
                $statusorder = [
                    1 => 1, // WIP
                    2 => 13, // COVERSHEET PRP
                    3 => 14, // CLARIFICATION
                    4 => 4, // SEND FOR QC
                    5 => 5, // COMPLETED
                ];
    
                $statusDurations = [
                    1 => 'WIP',
                    2 => 'COVERSHEET PRP',
                    3 => 'CLARIFICATION',
                    4 => 'SEND FOR QC',
                    5 => 'COMPLETED',
                ];
    
                foreach ($statusorder as $currentStatus => $statusId) {
                    $currentStatusEntry = $orderStatusHistory->firstWhere('status_id', $statusId);
    
                    if ($currentStatusEntry) {

                        // Find the next status entry that has a value
                        $nextStatusEntry = null;
                        foreach ($statusorder as $nextStatus => $nextStatusId) {
                            if ($nextStatus > $currentStatus) {
                                $nextStatusEntry = $orderStatusHistory->firstWhere('status_id', $nextStatusId);
                                if ($nextStatusEntry) {
                                    $userDurations[$statusDurations[$currentStatus]]['count']++;
                                    break; // Stop if a valid next status entry is found
                                }
                            }
                        }
    
                        if ($nextStatusEntry) {
                            $timeDifference = Carbon::parse($nextStatusEntry->created_at)
                                                    ->diffInSeconds(Carbon::parse($currentStatusEntry->created_at));
                            
                            $userDurations[$statusDurations[$currentStatus]]['time'] += $timeDifference;
                        }
                    }
                        }
                    }
                }
    
        foreach ($userDurations as $status => &$data) {
            if (is_array($data)) {
                $hours = floor($data['time'] / 3600);
                $minutes = floor(($data['time'] % 3600) / 60);
                $seconds = $data['time'] % 60;
                $data['time'] = sprintf("%d:%02d:%02d", $hours, $minutes, $seconds);
            }
            }

            return $userDurations;
        })->values();
    
        return Datatables::of($dataForDataTables)->toJson();
}

private function getStatusLabel($statusId) {
$statusLabels = [
        1 => 'WIP',
    2 => 'COVERSHEET PRP',
    3 => 'CLARIFICATION',
        4 => 'SEND FOR QC',
    5 => 'COMPLETED',
];

return $statusLabels[$statusId] ?? 'UNKNOWN STATUS';
}


public function attendance_report(Request $request)
{
    $user = Auth::user();
    $processIds = $this->getProcessIdsBasedOnUserRole($user);
    $selectedDate = $request->input('selectDate');

    $statusCountsQuery = OrderCreation::query()
        ->leftJoin('oms_users', 'oms_order_creations.assignee_user_id', '=', 'oms_users.id')
        ->leftJoin('stl_item_description', 'oms_order_creations.process_id', '=', 'stl_item_description.id')
        ->leftJoin('stl_client', 'stl_item_description.client_id', '=', 'stl_client.id')
        ->select(
            'oms_users.id as userid',
            'oms_users.emp_id as empid',
            'oms_users.username as username',
            'stl_item_description.process_name as process_name',
            'stl_item_description.project_code as project_code',
            'oms_order_creations.id as orderid'
        )
        ->whereNotNull('assignee_user_id')
        ->where('oms_order_creations.is_active', 1)
        ->where('stl_client.is_approved', 1)
        ->where('stl_item_description.is_approved', 1);

    if ($selectedDate) {
        $statusCountsQuery->whereDate('order_date', '=', $selectedDate);
    }

    if (!empty($processIds)) {
        $statusCountsQuery->whereIn('oms_order_creations.process_id', $processIds);
    }

    $statusCounts = $statusCountsQuery->get();
    $statusOrder = [1, 13, 14, 4, 5];
    
    $dataForDataTables = $statusCounts->groupBy('userid')->map(function ($orders, $userid) use ($statusOrder) {

        $totalTimeSpent = 0;

        foreach ($orders as $order) {
            $orderStatusHistory = DB::table('order_status_history')
                ->where('order_id', $order->orderid)
                ->whereIn('status_id', $statusOrder)
                ->orderBy('created_at', 'asc')
                ->get();

            $statusorder = [
                1 => 1, // WIP
                2 => 13, // COVERSHEET PRP
                3 => 14, // CLARIFICATION
                4 => 4, // SEND FOR QC
                5 => 5, // COMPLETED
            ];

            foreach ($statusorder as $currentStatus => $statusId) {
                $currentStatusEntry = $orderStatusHistory->firstWhere('status_id', $statusId);

                if ($currentStatusEntry) {
                    $nextStatusEntry = null;
                    foreach ($statusorder as $nextStatus => $nextStatusId) {
                        if ($nextStatus > $currentStatus) {
                            $nextStatusEntry = $orderStatusHistory->firstWhere('status_id', $nextStatusId);
                            if ($nextStatusEntry) {
                                break; 
                            }
                        }
                    }

                    if ($nextStatusEntry) {
                        $timeDifference = Carbon::parse($nextStatusEntry->created_at)
                                                ->diffInSeconds(Carbon::parse($currentStatusEntry->created_at));
                        
                        $totalTimeSpent += $timeDifference;
                    }
                }
            }
        }

        $hours = floor($totalTimeSpent / 3600);
        $minutes = floor(($totalTimeSpent % 3600) / 60);
        $seconds = $totalTimeSpent % 60;

        return [
            'Emp ID' => $orders->first()->empid,
            'Emp Name' => $orders->first()->username,
            'Total Time Spent' => sprintf("%d:%02d:%02d", $hours, $minutes, $seconds)
    ];
    })->values();

    return Datatables::of($dataForDataTables)->toJson();
}

}

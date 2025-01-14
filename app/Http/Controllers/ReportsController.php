<?php

namespace App\Http\Controllers;
use App\Models\Client;
use App\Models\user;
use App\Models\State;
use App\Models\County;
use App\Models\City;
use App\Models\Role;
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
        $roles = Role::select('id', "name")->where('id', '!=', 1)->get();

        return view('app.reports.index',compact('clients', 'roles'));
    }


    public function get_lob(Request $request)
    {
        $client_id = $request->client_id;
        $product_id = $request->product_id;

        if (!is_array($client_id)) {
            $client_id = [$client_id];
        }
        if (in_array('All', $client_id)) {
            $getlob = DB::table('stl_lob')
                            ->select('id', 'name')
                            ->get();
        } else {
            $filteredLob = DB::table('stl_lob')->select('id', 'client_id', 'name')->get();
        $getlob = $filteredLob->filter(function($item) use ($client_id) {
            $clientIds = json_decode($item->client_id, true);
            // Check if there's any overlap between the request client_ids and the stored client_ids
            return !empty(array_intersect($client_id, $clientIds));
        });
        }

        if (in_array('All', $client_id)) {
            $getproduct = DB::table('stl_item_description')
                        ->select('id', 'process_name', 'project_code')
                        ->get();
        }else{
            $getproduct = DB::table('stl_item_description')
                        ->select('id', 'process_name', 'project_code')
                        ->whereIn('client_id', $client_id)
                        ->get();
        }


        return response()->json([
            'lob' => $getlob,
            'products' => $getproduct
        ]); 
    }


    public function get_process(Request $request)
    {
        $lob_id = $request->lob_id;
        $client_id = $request->client_id;

        if (!is_array($lob_id)) {
            $lob_id = [$lob_id];
        }
        if (in_array('Select Lob', $lob_id)) {
            $getprocess = DB::table('stl_process')
                            ->select('id', 'name')
                            ->get();
        } else {

            $getprocess = DB::table('stl_item_description')
            ->leftjoin('stl_process', 'stl_process.id', '=', 'stl_item_description.process_id')
            ->leftjoin('stl_client', 'stl_client.id', '=', 'stl_item_description.client_id')
                ->select('stl_process.id', 'stl_process.name')
                ->where('stl_process.lob_id', $lob_id)
                ->where('stl_item_description.lob_id', $lob_id)
                ->where('stl_item_description.client_id', $client_id)
                ->groupBy('stl_process.id')
                ->get();

        }

    $get_product = [];


    if($lob_id && $client_id){
        $get_product = DB::table('stl_item_description')
                    ->select('id', 'process_name', 'project_code')
                    ->where('client_id', $client_id)
                    ->where('lob_id', $lob_id)
                    ->get();
    }
    
        return response()->json([
            'process' => $getprocess,
            'products' => $get_product
        ]); 
    }



    
    public function get_product(Request $request)
    {
        $process_type_id = $request->process_type_id;
        $client_id = $request->client_id;
        $lob_id = $request->lob_id;

        if (!is_array($process_type_id)) {
            $process_type_id = [$process_type_id];
        }
        if (in_array('All', $process_type_id)) {
            $getprocess = DB::table('stl_item_description')
                            ->select('id', 'process_name', 'project_code')
                            ->get();
        } else {
            $getprocess = DB::table('stl_item_description')
                            ->select('id', 'process_name', 'project_code')
                            ->whereIn('process_id', $process_type_id)
                            ->where('client_id', $client_id)
                            ->where('lob_id', $lob_id)
                            ->get();

        }

        return response()->json($getprocess); 
    }

    public function userwise_count(Request $request)
    {
        $user = Auth::user();
        $processIds = $this->getProcessIdsBasedOnUserRole($user);
        $client_id = $request->input('client_id');
        $lob_id = $request->input('lob_id');
        $process_type_id = $request->input('process_type_id');
        $product_id = $request->input('product_id');
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
            ->leftJoin('stl_lob', 'oms_order_creations.lob_id', '=', 'stl_lob.id')
            ->leftJoin('stl_process', 'oms_order_creations.process_type_id', '=', 'stl_process.id')

            ->selectRaw('
                CONCAT(oms_users.emp_id, " (", oms_users.username, ")") as userinfo,
                SUM(CASE WHEN status_id = 1 THEN 1 ELSE 0 END) as `status_1`,
                SUM(CASE WHEN status_id = 2 THEN 1 ELSE 0 END) as `status_2`,
                SUM(CASE WHEN status_id = 3 THEN 1 ELSE 0 END) as `status_3`,
                SUM(CASE WHEN status_id = 4 THEN 1 ELSE 0 END) as `status_4`,
                SUM(CASE WHEN status_id = 5 THEN 1 ELSE 0 END) as `status_5`,
                SUM(CASE WHEN status_id = 13 THEN 1 ELSE 0 END) as `status_13`,
                SUM(CASE WHEN status_id = 14 THEN 1 ELSE 0 END) as `status_14`,
                SUM(CASE WHEN status_id = 16 THEN 1 ELSE 0 END) as `status_16`,
                SUM(CASE WHEN status_id = 17 THEN 1 ELSE 0 END) as `status_17`,
                SUM(CASE WHEN status_id = 15 THEN 1 ELSE 0 END) as `status_15`,
                SUM(CASE WHEN status_id = 18 THEN 1 ELSE 0 END) as `status_18`,
                SUM(CASE WHEN status_id = 20 THEN 1 ELSE 0 END) as `status_20`,
                COUNT(*) as `All`')
            ->whereNotNull('assignee_user_id')
            ->where('oms_order_creations.is_active', 1)
            ->where('stl_client.is_approved', 1)
            ->where('stl_item_description.is_approved', 1)
            ->whereDate('order_date', '>=', $fromDate)
            ->whereDate('order_date', '<=', $toDate)
            ->whereIn('oms_order_creations.process_id', $processIds)
            ->groupBy('oms_order_creations.assignee_user_id');

        if (!empty($product_id) && $product_id[0] !== 'All') {
            $statusCountsQuery->whereIn('oms_order_creations.process_id', $product_id);
        }

        if (!empty($client_id) && $client_id[0] !== 'All') {
            $statusCountsQuery->where('stl_item_description.client_id', $client_id);
        }

        if (!empty($process_type_id) && $process_type_id[0] !== 'All') {
            $statusCountsQuery->whereIn('oms_order_creations.process_type_id', $process_type_id);
        }



        if (!empty($lob_id) && $lob_id !== 'All') {
            $statusCountsQuery->where('oms_order_creations.lob_id', $lob_id);
        }

        $statusCounts = $statusCountsQuery->get();

        $dataForDataTables = $statusCounts->map(function ($count) {
            return [
                'userinfo' => $count->userinfo,
                'status_1' => $count->status_1,
                'status_2' => $count->status_2,
                'status_3' => $count->status_3,
                'status_16' => $count->status_16,
                'status_17' => $count->status_17,
                'status_4' => $count->status_4,
                'status_5' => $count->status_5,
                'status_13' => $count->status_13,
                'status_14' => $count->status_14,
                'status_15' => $count->status_15,
                'status_18' => $count->status_18,
                'status_20' => $count->status_20,
                'All' => $count->status_1 + $count->status_2 + $count->status_3 + $count->status_4 + $count->status_5 + $count->status_13 + $count->status_14 + $count->status_15 + $count->status_18 + $count->status_20,
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
        $lob_id = $request->input('lob_id');
        $process_type_id = $request->input('process_type_id');
        $product_id = $request->input('product_id');
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
            ->leftJoin('stl_process', 'oms_order_creations.process_type_id', '=', 'stl_process.id')
            ->leftJoin('stl_lob', 'oms_order_creations.lob_id', '=', 'stl_lob.id')
            ->leftJoin('oms_city', 'oms_order_creations.city_id', '=', 'oms_city.id')
            ->leftJoin('oms_users as assignee_user', 'oms_order_creations.assignee_user_id', '=', 'assignee_user.id')
            ->leftJoin('oms_users as status_update_qc', 'oms_order_creations.updated_qc', '=', 'status_update_qc.id')
            ->leftJoin('oms_users as assignee_qcer', 'oms_order_creations.assignee_qa_id', '=', 'assignee_qcer.id')
            ->leftJoin('oms_users as typist', 'oms_order_creations.typist_id', '=', 'typist.id')
            ->leftJoin('oms_users as typist_qc', 'oms_order_creations.typist_qc_id', '=', 'typist_qc.id')
            ->leftJoin('oms_tier', 'oms_order_creations.tier_id', '=', 'oms_tier.id')

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
            // ->leftJoin('order_status_history', function ($join) {
            //     $join->on('oms_order_creations.id', '=', 'order_status_history.order_id')
            //         ->where('order_status_history.id', '=', DB::raw("(SELECT MAX(id) FROM order_status_history WHERE order_id = oms_order_creations.id)"));
            // })
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
                'oms_order_creations.comment as status_comment',
                'stl_process.name as process_name',
                'assignee_user.emp_id as EmpId',
                'assignee_user.username as EmpName',
                'assignee_qcer.emp_id as qcer_EmpId',
                'assignee_qcer.username as qcer_EmpName',

                'typist.emp_id as typist_EmpId',
                'typist.username as typist_EmpName',

                'typist_qc.emp_id as typist_qc_EmpId',
                'typist_qc.username as typist_qc_EmpName',

                'status_update_qc.emp_id as qc_EmpId',
                'status_update_qc.username as qa_user',
                'oms_order_creations.qc_comment as qc_comment',
                'oms_order_creations.status_updated_time as status_updated_time',
                'oms_tier.Tier_id as tier_name',
                'stl_client.client_name as client_name',
                'stl_lob.name as lob_name',
                'stl_process.name as process_name',
            )
            // ->whereNotNull('oms_order_creations.assignee_user_id')
            ->where(function($query) {
                $query->where(function($subQuery) {
                    $subQuery->whereNull('oms_order_creations.typist_id')
                             ->whereNotNull('oms_order_creations.assignee_user_id');
                })
                ->orWhere(function($subQuery) {
                    $subQuery->whereNull('oms_order_creations.assignee_user_id')
                             ->whereNotNull('oms_order_creations.typist_id');
                });
            })
            ->whereDate('oms_order_creations.order_date', '>=', $fromDate)
            ->whereDate('oms_order_creations.order_date', '<=', $toDate)
            ->whereIn('oms_order_creations.process_id', $processIds)
            ->where('oms_order_creations.is_active', 1)
            ->whereIn('oms_order_creations.status_id', [1, 2, 3, 4, 5, 13, 14, 16, 17, 15, 18, 20])
            ->where('stl_item_description.is_approved', 1)
            ->where('stl_client.is_approved', 1);

        if (!empty($client_id) && $client_id[0] !== 'All') {
            $query->where('stl_item_description.client_id', $client_id);
        }

        if (!empty($product_id) && $product_id[0] !== 'All') {
            $query->whereIn('oms_order_creations.process_id', $product_id);
        }

        if (!empty($process_type_id) && $process_type_id[0] !== 'All') {
            $query->whereIn('oms_order_creations.process_type_id', $process_type_id);
        }

        if (!empty($lob_id) && $lob_id !== 'All') {
            $query->where('oms_order_creations.lob_id', $lob_id);
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
                'client_name' => $item->client_name,
                'lob_name' => $item->lob_name,
                'process_name' => $item->process_name,
                'short_code' => $item->short_code,
                'county_name' => $item->county_name,
                'status' => $item->status,
                'status_comment' => $item->status_comment,
                'primary_source' => $primarySource,
                'process_name' => $item->process_name,
                'tier' => $item->tier_name,
                'emp_id' => $item->EmpId,
                'emp_name' => $item->EmpName,
                'qa_user' => $item->qcer_EmpName,
                'qc_EmpId' => $item->qcer_EmpId,
                'qc_comment' => $item->qc_comment,
                'typist_emp_id' => $item->typist_EmpId,
                'typist_emp_name' => $item->typist_EmpName,
                'typist_qc_emp_id' => $item->typist_qc_EmpId,
                'typist_qc_emp_name' => $item->typist_qc_EmpName,
                'status_updated_time' => $item->status_updated_time
                
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
        $client_id = $request->input('client_id');
        $lob_id = $request->input('lob_id');
        $process_type_id = $request->input('process_type_id');
        $product_id = $request->input('product_id');
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
            ->leftJoin('stl_process', 'stl_item_description.process_id', '=', 'stl_process.id')
            ->leftJoin('stl_lob', 'oms_order_creations.lob_id', '=', 'stl_lob.id')
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
    
        if (!empty($product_id) && $product_id[0] !== 'All') {
            $statusCountsQuery->whereIn('oms_order_creations.process_id', $product_id);
        }
    
        if (!empty($client_id) && $client_id[0] !== 'All') {
            $statusCountsQuery->where('stl_item_description.client_id', $client_id);
        }    
    
if (!empty($process_type_id) && $process_type_id[0] !== 'All') {
            $statusCountsQuery->whereIn('oms_order_creations.process_type_id', $process_type_id);
        }

        if (!empty($lob_id) && $lob_id !== 'All') {
            $statusCountsQuery->where('oms_order_creations.lob_id', $lob_id);
        }
        $statusCounts = $statusCountsQuery->get();
    
        // Get all order IDs
        $orderIds = $statusCounts->pluck('orderid');
    
        // Pre-fetch order status history for all relevant orders
        $orderStatusHistory = DB::table('order_status_history')
            ->whereIn('order_id', $orderIds)
            ->whereIn('status_id', [1, 5]) // 1 = Start, 5 = End
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy('order_id');
    
        $dataForDataTables = $statusCounts->groupBy('userid')->map(function ($orders, $userid) use ($fromDate, $toDate, $product_id, $client_id, $orderStatusHistory) {
            $completedCount = 0;
    
            if (!empty($product_id) && $product_id[0] !== 'All') {
                $completedCount = DB::table('oms_order_creations')
                    ->whereDate('order_date', '>=', $fromDate)
                    ->whereDate('order_date', '<=', $toDate)
                    ->where('oms_order_creations.is_active', 1)
                    ->where('status_id', 5)
                    ->where('assignee_user_id', $userid)
                    ->whereIn('oms_order_creations.process_id', $product_id)
                    ->count();
            } elseif (!empty($client_id) && $client_id[0] !== 'All') {
                $completedCount = DB::table('oms_order_creations')
                    ->leftJoin('oms_users', 'oms_order_creations.assignee_user_id', '=', 'oms_users.id')
                    ->leftJoin('stl_item_description', 'oms_order_creations.process_id', '=', 'stl_item_description.id')
                    ->leftJoin('stl_client', 'stl_item_description.client_id', '=', 'stl_client.id')
                    ->whereDate('oms_order_creations.order_date', '>=', $fromDate)
                    ->whereDate('oms_order_creations.order_date', '<=', $toDate)
                    ->where('oms_order_creations.is_active', 1)
                    ->where('oms_order_creations.status_id', 5)
                    ->where('oms_order_creations.assignee_user_id', $userid)
                    ->where('stl_item_description.client_id', $client_id)
                    ->count();
            } else {
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
                $orderHistory = $orderStatusHistory->get($order->orderid, collect());
                $orderStartTime = $orderHistory->where('status_id', 1)->first();
                $orderEndTime = $orderHistory->where('status_id', 5)->first();
    
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
                'AVG_TIME_TAKEN_SECONDS' => $avgTimeTakenSeconds
            ];
        })->values();
    
        return Datatables::of($dataForDataTables)->toJson();

    }
    
    private function formatSecondsToHours($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds / 60) % 60);
        $seconds = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
   
public function orderTimeTaken(Request $request) {
        $user = Auth::user();
        $processIds = $this->getProcessIdsBasedOnUserRole($user);
        $client_id = $request->input('client_id');
        $lob_id = $request->input('lob_id');
        $process_type_id = $request->input('process_type_id');
        $product_id = $request->input('product_id');
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
            ->leftJoin('stl_process', 'stl_item_description.process_id', '=', 'stl_process.id')
            ->leftJoin('stl_lob', 'oms_order_creations.lob_id', '=', 'stl_lob.id')
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
    
    
        if (!empty($product_id) && $product_id[0] !== 'All') {
            $statusCountsQuery->whereIn('oms_order_creations.process_id', $product_id);
        }
    
        if (!empty($client_id) && $client_id[0] !== 'All') {
            $statusCountsQuery->where('stl_item_description.client_id', $client_id);
        }

        
        if (!empty($process_type_id) && $process_type_id[0] !== 'All') {
            $statusCountsQuery->whereIn('oms_order_creations.process_type_id', $process_type_id);
        }
    
       if (!empty($lob_id) && $lob_id !== 'All') {
            $statusCountsQuery->where('oms_order_creations.lob_id', $lob_id);
        }
    
        if(!empty($product_id) && $product_id[0] !== 'All'){
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


public function production_report(Request $request) {
    $user = Auth::user();
    $processIds = $this->getProcessIdsBasedOnUserRole($user);
    
    $clientId = $request->input('client_id');
    $lobId = $request->input('lob_id');
    $processTypeId = $request->input('process_type_id');
    $productId = $request->input('product_id');
    $selectedDateFilter = $request->input('selectedDateFilter');
    $fromDateRange = $request->input('fromDate_range');
    $toDateRange = $request->input('toDate_range');
    $draw = $request->input('draw');
    $start = $request->input('start');
    $length = $request->input('length');
    $searchValue = $request->input('search.value'); 
    
    // Date filtering logic
    list($fromDate, $toDate) = $this->getDateRange($selectedDateFilter, $fromDateRange, $toDateRange);

    $statusCountsQuery = DB::table('production_tracker')
        ->leftJoin('oms_order_creations as order_creation_main', 'production_tracker.order_id', '=', 'order_creation_main.id')
        ->leftJoin('oms_users as assignee_user', 'order_creation_main.assignee_user_id', '=', 'assignee_user.id')
        ->leftJoin('oms_users as qa_user', 'order_creation_main.assignee_qa_id', '=', 'qa_user.id')
        ->leftJoin('oms_users as typist_user', 'order_creation_main.typist_id', '=', 'typist_user.id')
        ->leftJoin('oms_users as typist_qc_user', 'order_creation_main.typist_qc_id', '=', 'typist_qc_user.id')
        ->leftJoin('stl_item_description', 'order_creation_main.process_id', '=', 'stl_item_description.id')
        ->leftJoin('stl_client', 'stl_item_description.client_id', '=', 'stl_client.id')
        ->leftJoin('stl_lob', 'order_creation_main.lob_id', '=', 'stl_lob.id')
        ->leftJoin('stl_process', 'order_creation_main.process_type_id', '=', 'stl_process.id')
        ->leftJoin('oms_state', 'order_creation_main.state_id', '=', 'oms_state.id')
        ->leftJoin('county', 'order_creation_main.county_id', '=', 'county.id')
        ->leftJoin('oms_status', 'order_creation_main.status_id', '=', 'oms_status.id')
        ->leftJoin('oms_vendor_information', 'production_tracker.accurate_client_id', '=', 'oms_vendor_information.id')
        ->leftJoin('oms_accurate_source', 'production_tracker.source', '=', 'oms_accurate_source.id')

        ->select(
            'order_creation_main.order_date as order_date',
            DB::raw("CONCAT(assignee_user.emp_id, '(', assignee_user.username, ')') as assignee_empid"),
            DB::raw("CONCAT(qa_user.emp_id, '(', qa_user.username, ')') as qa_empid"),
            DB::raw("CONCAT(typist_user.emp_id, '(', typist_user.username, ')') as typist_empid"),
            DB::raw("CONCAT(typist_qc_user.emp_id, '(', typist_qc_user.username, ')') as typist_qc_empid"),
            'stl_item_description.process_name as process_name',
            'oms_vendor_information.accurate_client_id as acc_client_id',
            'order_creation_main.order_id as order_num',
            'oms_state.short_code as short_code',
            'county.county_name as county_name',
            'production_tracker.portal_fee_cost as portal_fee_cost',
            'oms_accurate_source.source_name as source_name',
            'production_tracker.production_date as production_date',
            'production_tracker.copy_cost as copy_cost',
            'production_tracker.no_of_search_done as no_of_search_done',
            'production_tracker.no_of_documents_retrieved as no_of_documents_retrieved',
            'production_tracker.title_point_account as title_point_account',
            'production_tracker.purchase_link as purchase_link',
            'production_tracker.username as production_username',
            'production_tracker.password as password',
            'order_creation_main.completion_date as completion_date',
            'oms_status.status as status',
            'stl_item_description.tat_value as tat_value',
            'order_creation_main.comment as comment'
        )
        ->where('order_creation_main.is_active', 1)
        ->where('stl_item_description.is_approved', 1);

    if ($fromDate && $toDate) {
        $statusCountsQuery->whereBetween('order_creation_main.order_date', [$fromDate, $toDate]);
    }

    $this->applyFilters($statusCountsQuery, $processIds, $clientId, $lobId, $processTypeId, $productId, $searchValue);

    $this->applySorting($statusCountsQuery, $request);

    $totalRecords = $statusCountsQuery->count();
    $result = $statusCountsQuery->skip($start)->take($length)->get();

    return response()->json([
        'draw' => intval($draw),
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $totalRecords,
        'data' => $result
    ]);
}

private function getDateRange($selectedDateFilter, $fromDateRange, $toDateRange) {
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

    return [$fromDate, $toDate];
}

private function applyFilters($query, $processIds, $clientId, $lobId, $processTypeId, $productId, $searchValue) {
    if (!empty($processIds)) {
        $query->whereIn('order_creation_main.process_id', $processIds);
    }

    if (!empty($productId) && $productId[0] !== 'All') {
        $query->whereIn('order_creation_main.process_id', $productId);
    }

    if (!empty($clientId) && $clientId[0] !== 'All') {
        $query->where('stl_item_description.client_id', $clientId);
    }

    if (!empty($processTypeId) && $processTypeId[0] !== 'All') {
        $query->whereIn('order_creation_main.process_type_id', $processTypeId);
    }

    if (!empty($lobId) && $lobId !== 'All') {
        $query->where('order_creation_main.lob_id', $lobId);
    }

    if (!empty($searchValue)) {
        $query->where(function($q) use ($searchValue) {
            $q->where('assignee_user.emp_id', 'like', "%{$searchValue}%")
              ->orWhere('assignee_user.username', 'like', "%{$searchValue}%")
                  ->orWhere('qa_user.emp_id', 'like', "%{$searchValue}%")
              ->orWhere('qa_user.username', 'like', "%{$searchValue}%")
                  ->orWhere('typist_user.emp_id', 'like', "%{$searchValue}%")
              ->orWhere('typist_user.username', 'like', "%{$searchValue}%")
                  ->orWhere('typist_qc_user.emp_id', 'like', "%{$searchValue}%")
              ->orWhere('typist_qc_user.username', 'like', "%{$searchValue}%")
                  ->orWhere('stl_item_description.process_name', 'like', "%{$searchValue}%")
                  ->orWhere('order_creation_main.order_id', 'like', "%{$searchValue}%")
                  ->orWhere('oms_state.short_code', 'like', "%{$searchValue}%")
                  ->orWhere('county.county_name', 'like', "%{$searchValue}%")
                  ->orWhere('production_tracker.portal_fee_cost', 'like', "%{$searchValue}%")
                  ->orWhere('production_tracker.production_date', 'like', "%{$searchValue}%")
                  ->orWhere('oms_status.status', 'like', "%{$searchValue}%");
        });
    }
}

private function applySorting($query, Request $request) {
    $orderColumnIndex = $request->input('order.0.column'); 
    $orderDirection = $request->input('order.0.dir'); 

    $columns = [
        'order_creation_main.order_date',
        'assignee_user.emp_id',
        'qa_user.emp_id',
        'typist_user.emp_id',
        'typist_qc_user.emp_id',
        'assignee_user.username',
        'qa_user.username',
        'typist_user.username',
        'typist_qc_user.username',
        'stl_item_description.process_name',
        'oms_vendor_information.accurate_client_id',
        'order_creation_main.order_id',
        'oms_state.short_code',
        'county.county_name',
        'production_tracker.portal_fee_cost',
        'oms_accurate_source.source_name',
        'production_tracker.production_date',
        'production_tracker.copy_cost',
        'production_tracker.no_of_search_done',
        'production_tracker.no_of_documents_retrieved',
        'production_tracker.title_point_account',
        'production_tracker.purchase_link',
        'production_tracker.username',
        'production_tracker.password',
        'order_creation_main.completion_date',
        'oms_status.status',
        'stl_item_description.tat_value',
        'order_creation_main.comment'
    ];

    if (isset($columns[$orderColumnIndex])) {
        $query->orderBy($columns[$orderColumnIndex], $orderDirection);
    }
}


public function exportProductionReport(Request $request) {
    $user = Auth::user();
    $processIds = $this->getProcessIdsBasedOnUserRole($user);

    $client_id = $request->input('client_id') ?? null;
    $lob_id = $request->input('lob_id') ?? null;
    $process_type_id = $request->input('process_type_id') ?? null;
    $product_id = $request->input('product_id') ?? null;
    $selectedDateFilter = $request->input('selectedDateFilter') ?? null;
    $fromDateRange = $request->input('fromDate_range') ?? null;
    $toDateRange = $request->input('toDate_range') ?? null;
    $searchValue = $request->input('search.value') ?? null;
    

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

    $statusCountsQuery = DB::table('production_tracker')
        ->leftJoin('oms_order_creations as order_creation_main', 'production_tracker.order_id', '=', 'order_creation_main.id')
        ->leftJoin('oms_users as assignee_user', 'order_creation_main.assignee_user_id', '=', 'assignee_user.id')
        ->leftJoin('oms_users as qa_user', 'order_creation_main.assignee_qa_id', '=', 'qa_user.id')
        ->leftJoin('oms_users as typist_user', 'order_creation_main.typist_id', '=', 'typist_user.id')
        ->leftJoin('oms_users as typist_qc_user', 'order_creation_main.typist_qc_id', '=', 'typist_qc_user.id')
        ->leftJoin('stl_item_description', 'order_creation_main.process_id', '=', 'stl_item_description.id')
        ->leftJoin('stl_client', 'stl_item_description.client_id', '=', 'stl_client.id')
        ->leftJoin('stl_lob', 'order_creation_main.lob_id', '=', 'stl_lob.id')
        ->leftJoin('stl_process', 'order_creation_main.process_type_id', '=', 'stl_process.id')
        ->leftJoin('oms_state', 'order_creation_main.state_id', '=', 'oms_state.id')
        ->leftJoin('county', 'order_creation_main.county_id', '=', 'county.id')
        ->leftJoin('oms_status', 'order_creation_main.status_id', '=', 'oms_status.id')
        ->leftJoin('oms_vendor_information', 'production_tracker.accurate_client_id', '=', 'oms_vendor_information.id')
        ->leftJoin('oms_accurate_source', 'production_tracker.source', '=', 'oms_accurate_source.id')
        ->select(
            'order_creation_main.order_date as order_date',
            DB::raw("CONCAT(assignee_user.emp_id, '(', assignee_user.username, ')') as assignee_empid"),
            DB::raw("CONCAT(qa_user.emp_id, '(', qa_user.username, ')') as qa_empid"),
            DB::raw("CONCAT(typist_user.emp_id, '(', typist_user.username, ')') as typist_empid"),
            DB::raw("CONCAT(typist_qc_user.emp_id, '(', typist_qc_user.username, ')') as typist_qc_empid"),
            'stl_item_description.process_name as process_name',
            'oms_vendor_information.accurate_client_id as acc_client_id',
            'order_creation_main.order_id as order_num',
            'oms_state.short_code as short_code',
            'county.county_name as county_name',
            'production_tracker.portal_fee_cost as portal_fee_cost',
            'oms_accurate_source.source_name as source_name',
            'production_tracker.production_date as production_date',
            'production_tracker.copy_cost as copy_cost',
            'production_tracker.no_of_search_done as no_of_search_done',
            'production_tracker.no_of_documents_retrieved as no_of_documents_retrieved',
            'production_tracker.title_point_account as title_point_account',
            'production_tracker.purchase_link as purchase_link',
            'production_tracker.username as production_username',
            'production_tracker.password as password',
            'order_creation_main.completion_date as completion_date',
            'oms_status.status as status',
            'stl_item_description.tat_value as tat_value',
            'order_creation_main.comment as comment'
        )
        ->where('order_creation_main.is_active', 1)
        ->where(function ($statusCountsQuery) {
            $statusCountsQuery->where('stl_item_description.is_approved', 1)
                  ->orWhereNull('stl_item_description.is_approved');
        });
        

    if ($fromDate && $toDate) {
        $statusCountsQuery->whereBetween('order_creation_main.order_date', [$fromDate, $toDate]);
    }

    if (!empty($processIds)) {
        $statusCountsQuery->whereIn('order_creation_main.process_id', $processIds);
    }

    if (!empty($product_id) && $product_id[0] !== 'All') {
        $statusCountsQuery->whereIn('order_creation_main.process_id', $product_id);
    }

    if (!empty($client_id) && is_array($client_id) && $client_id[0] !== 'All') {
        $statusCountsQuery->whereIn('stl_item_description.client_id', $client_id);
    }
    
    if (!empty($process_type_id) && is_array($process_type_id) && $process_type_id[0] !== 'All') {
        $statusCountsQuery->whereIn('order_creation_main.process_type_id', $process_type_id);
    }
    
    if (!empty($lob_id) && is_array($lob_id) && $lob_id[0] !== 'All') {
        $statusCountsQuery->whereIn('order_creation_main.lob_id', $lob_id);
    }
    

    if (!empty($searchValue)) {
        $query->where(function($q) use ($searchValue) {
            $q->where('assignee_user.emp_id', 'like', "%{$searchValue}%")
              ->orWhere('assignee_user.username', 'like', "%{$searchValue}%")
                ->orWhere('qa_user.emp_id', 'like', "%{$searchValue}%")
              ->orWhere('qa_user.username', 'like', "%{$searchValue}%")
                ->orWhere('typist_user.emp_id', 'like', "%{$searchValue}%")
              ->orWhere('typist_user.username', 'like', "%{$searchValue}%")
                ->orWhere('typist_qc_user.emp_id', 'like', "%{$searchValue}%")
              ->orWhere('typist_qc_user.username', 'like', "%{$searchValue}%")
                ->orWhere('stl_item_description.process_name', 'like', "%{$searchValue}%")
                ->orWhere('order_creation_main.order_id', 'like', "%{$searchValue}%")
                ->orWhere('oms_state.short_code', 'like', "%{$searchValue}%")
                ->orWhere('county.county_name', 'like', "%{$searchValue}%")
                ->orWhere('production_tracker.portal_fee_cost', 'like', "%{$searchValue}%")
                ->orWhere('production_tracker.production_date', 'like', "%{$searchValue}%")
                ->orWhere('oms_status.status', 'like', "%{$searchValue}%");
        });
    }

    $result = $statusCountsQuery->get();

    return response()->json([
        'data' => $result
    ]);
}



private function getOrderInflowCounts($processIds, $from_date, $to_date)
{
    // Carry forward count for all clients
    $carry_forward = OrderCreation::whereIn('process_id', $processIds)
        ->where('is_active', 1)
        ->where('status_id', '!=', 3)
        ->where('status_id', '!=', 5)  // Exclude completed
        ->where('status_id', '!=', 20)  // Exclude completed
        ->whereDate('order_date', '<', $from_date)
        ->get()
        ->groupBy('client_id')
        ->map(function ($orders) {
            return $orders->count();
        });




    // Received count for all clients
    $received = OrderCreation::whereIn('process_id', $processIds)
        ->where('is_active', 1)
        ->where('status_id', '!=', 3)  // Exclude cancelled
        ->where('status_id', '!=', 5)  // Exclude completed
        ->where('status_id', '!=', 20)
        ->whereDate('order_date', '>=', $from_date)
        ->whereDate('order_date', '<=', $to_date)
        ->get()
        ->groupBy('client_id')
        ->map(function ($orders) {
            return $orders->count();
        });


    // Completed count for all clients
    $completed = OrderCreation::whereIn('process_id', $processIds)
        ->where('status_id', 5)
        ->where('is_active', 1)
        ->whereDate('completion_date', '>=', $from_date)
        ->whereDate('completion_date', '<=', $to_date)
        ->get()
        ->groupBy('client_id')
        ->map(function ($orders) {
            return $orders->count();
        });

    // Pending count for all clients
    $cancelled = OrderCreation::whereIn('process_id', $processIds)
        ->where('is_active', 1)
        ->where('status_id', 3)  // Status for cancelled orders
        ->whereBetween('order_date', [$from_date, $to_date])
        ->get()
        ->groupBy('client_id')
        ->map(function ($orders) {
            return $orders->count();
        });

    // Partially Cancelled count for all clients (Excludes from pending)
    $partially_cancelled = OrderCreation::whereIn('process_id', $processIds)
        ->where('is_active', 1)
        ->where('status_id', 20)  // Status for partially cancelled orders
        ->whereBetween('order_date', [$from_date, $to_date])
        ->get()
        ->groupBy('client_id')
        ->map(function ($orders) {
            return $orders->count();
        });

    return compact('carry_forward', 'received', 'completed', 'cancelled', 'partially_cancelled');
}

public function orderInflow_data(Request $request)
{
    $user = Auth::user();
    $processIds = $this->getProcessIdsBasedOnUserRole($user);

    $selectedDateFilter = $request->input('selectedDateFilter');
    $fromDateRange = $request->input('fromDate_range');
    $toDateRange = $request->input('toDate_range');
    $searchValue = $request->input('search_value');

    $from_date = null;
    $to_date = null;

    // Handle date range inputs
    if ($fromDateRange && $toDateRange) {
        $from_date = Carbon::createFromFormat('Y-m-d', $fromDateRange)->toDateString();
        $to_date = Carbon::createFromFormat('Y-m-d', $toDateRange)->toDateString();
    } else {
        $datePattern = '/(\d{2}-\d{2}-\d{4})/'; // mm-dd-yyyy pattern
        if (!empty($selectedDateFilter) && strpos($selectedDateFilter, 'to') !== false) {
            list($fromDateText, $toDateText) = explode('to', $selectedDateFilter);
            $fromDateText = trim($fromDateText);
            $toDateText = trim($toDateText);
            preg_match($datePattern, $fromDateText, $fromDateMatches);
            preg_match($datePattern, $toDateText, $toDateMatches);
            $from_date = isset($fromDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $fromDateMatches[1])->toDateString() : null;
            $to_date = isset($toDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $toDateMatches[1])->toDateString() : null;
        } else {
            preg_match($datePattern, $selectedDateFilter, $dateMatches);
            $from_date = isset($dateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $dateMatches[1])->toDateString() : null;
            $to_date = $from_date;
        }
    }

    $to_date = \Carbon\Carbon::parse($to_date)->endOfDay();

    // Get the order inflow counts using the helper function
    $orderCounts = $this->getOrderInflowCounts($processIds, $from_date, $to_date);

    // Prepare the response data
    $response = [];
    $clientIds = collect(array_merge(
        $orderCounts['carry_forward']->keys()->toArray(),
        $orderCounts['received']->keys()->toArray(),
        $orderCounts['completed']->keys()->toArray(),
        $orderCounts['cancelled']->keys()->toArray(),
        $orderCounts['partially_cancelled']->keys()->toArray()
    ))->unique();

    // Get client details (client_name)
    $clientNames = \DB::table('stl_client')->whereIn('id', $clientIds)->pluck('client_name', 'id');

    if (!empty($searchValue)) {
        $clientNames = $clientNames->filter(function ($clientName) use ($searchValue) {
            return stripos($clientName, $searchValue) !== false; // Case-insensitive search
        });
        $clientIds = $clientNames->keys(); // Update client ids to match the filtered names
    }

    // Pending count for all clients (Modified to exclude cancelled and partially cancelled orders from pending calculation)
    $totalRecords = $clientIds->count();

    // Pagination: take only the required records for the current page
    $start = $request->input('start', 0); // Starting record
    $length = $request->input('length', 10); // Number of records per page
    $clientIds = $clientIds->slice($start, $length);

    foreach ($clientIds as $clientId) {
        $carryForwardCount = $orderCounts['carry_forward']->get($clientId, 0);
        $receivedCount = $orderCounts['received']->get($clientId, 0);
        $completedCount = $orderCounts['completed']->get($clientId, 0);
        $cancelledCount = $orderCounts['cancelled']->get($clientId, 0);
        $partiallyCancelledCount = $orderCounts['partially_cancelled']->get($clientId, 0);

        // Calculate pending count
        $pendingCount = $carryForwardCount + $receivedCount ;

        $response[] = [
            'client_name' => $clientNames[$clientId] ?? 'N/A',  // Use client_name instead of client_id
            'carry_forward' => $carryForwardCount,
            'received' => $receivedCount,
            'completed' => $completedCount,
            'cancelled' => $cancelledCount,
            'partially_cancelled' => $partiallyCancelledCount,
            'pending' => $pendingCount,
        ];
    }

    return response()->json([
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $totalRecords,
        'data' => $response
    ]);
}

public function orderInflow_export(Request $request)
{
    $user = Auth::user();
    $processIds = $this->getProcessIdsBasedOnUserRole($user);

    $selectedDateFilter = $request->input('selectedDateFilter');
    $fromDateRange = $request->input('fromDate_range');
    $toDateRange = $request->input('toDate_range');

    $from_date = null;
    $to_date = null;

    // Handle date range inputs
    if ($fromDateRange && $toDateRange) {
        $from_date = Carbon::createFromFormat('Y-m-d', $fromDateRange)->toDateString();
        $to_date = Carbon::createFromFormat('Y-m-d', $toDateRange)->toDateString();
    } else {
        $datePattern = '/(\d{2}-\d{2}-\d{4})/'; // mm-dd-yyyy pattern
        if (!empty($selectedDateFilter) && strpos($selectedDateFilter, 'to') !== false) {
            list($fromDateText, $toDateText) = explode('to', $selectedDateFilter);
            $fromDateText = trim($fromDateText);
            $toDateText = trim($toDateText);
            preg_match($datePattern, $fromDateText, $fromDateMatches);
            preg_match($datePattern, $toDateText, $toDateMatches);
            $from_date = isset($fromDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $fromDateMatches[1])->toDateString() : null;
            $to_date = isset($toDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $toDateMatches[1])->toDateString() : null;
        } else {
            preg_match($datePattern, $selectedDateFilter, $dateMatches);
            $from_date = isset($dateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $dateMatches[1])->toDateString() : null;
            $to_date = $from_date;
        }
    }

    $to_date = \Carbon\Carbon::parse($to_date)->endOfDay();

    // Get the order inflow counts using the helper function
    $orderCounts = $this->getOrderInflowCounts($processIds, $from_date, $to_date);

    // Prepare the response data
    $response = [];
    $clientIds = collect(array_merge(
        $orderCounts['carry_forward']->keys()->toArray(),
        $orderCounts['received']->keys()->toArray(),
        $orderCounts['completed']->keys()->toArray(),
        $orderCounts['cancelled']->keys()->toArray(),
        $orderCounts['partially_cancelled']->keys()->toArray()
    ))->unique();

    // Get client details (client_name)
    $clientNames = \DB::table('stl_client')->whereIn('id', $clientIds)->pluck('client_name', 'id');

    // Total records
    $totalRecords = $clientIds->count();

    foreach ($clientIds as $clientId) {
        $carryForwardCount = $orderCounts['carry_forward']->get($clientId, 0);
        $receivedCount = $orderCounts['received']->get($clientId, 0);
        $completedCount = $orderCounts['completed']->get($clientId, 0);
        $cancelledCount = $orderCounts['cancelled']->get($clientId, 0);
        $partiallyCancelledCount = $orderCounts['partially_cancelled']->get($clientId, 0);

        // Pending count should exclude cancelled and partially cancelled orders
        $pendingCount = $carryForwardCount + $receivedCount;

        $response[] = [
            'client_name' => $clientNames[$clientId] ?? 'N/A',  // Use client_name instead of client_id
            'carry_forward' => $carryForwardCount,
            'received' => $receivedCount,
            'completed' => $completedCount,
            'cancelled' => $cancelledCount,  // Include cancelled count separately
            'partially_cancelled' => $partiallyCancelledCount,  // Include partially cancelled count separately
            'pending' => $pendingCount,
        ];
        }

    return response()->json([
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $totalRecords,
        'data' => $response
    ]);
}



public function getUsersByRole(Request $request)
{
    // Get the role_id from the request
    $roleId = $request->input('role_id');

    // Fetch users with matching user_type_id (assuming the relationship is on `oms_users`)
    $users = User::where('user_type_id', $roleId)->get(['id', 'username']); // Or any other fields you need

    // Return a response in JSON format
    return response()->json([
        'users' => $users
    ]);
}

public function getUserData(Request $request)
{
    // Validate incoming request to ensure user_id is provided
    $request->validate([
        'user_id' => 'required|exists:oms_users,id',
    ]);

    // Get the selected user_id
    $userId = $request->input('user_id');

    // Initialize the userLowerIds array with the given user_id
    $userLowerIds = [$userId];

    // This will hold the final list of all the user IDs in the hierarchy
    $allUserIds = [];

    // Flag to indicate if we need to continue fetching lower level users
    $continueFetching = true;

    while ($continueFetching) {
        // Get all users whose reporting_to is in the current list of userLowerIds
        $users = DB::table('oms_users')
            ->leftJoin('roles', 'oms_users.user_type_id', '=', 'roles.id')
            ->leftJoin('oms_users as reporting_user', 'oms_users.reporting_to', '=', 'reporting_user.id')
            ->select(
                'oms_users.id',
                'oms_users.emp_id',
                'oms_users.username',
                'roles.name as role',
                'reporting_user.username as reporting_to_username'
            )
            ->whereIn('oms_users.reporting_to', $userLowerIds)
            ->get();

        // If there are users found in this batch, add their IDs to the list
        if ($users->isNotEmpty()) {
            // Add these users' IDs to the allUserIds list
            foreach ($users as $user) {
                $allUserIds[] = $user->id;
            }

            // Update userLowerIds to the new list of IDs to continue fetching
            $userLowerIds = $users->pluck('id')->toArray();
        } else {
            // No more users to fetch, break the loop
            $continueFetching = false;
        }
    }

    // Get the final set of users by their collected IDs
    $userData = DB::table('oms_users')
        ->leftJoin('roles', 'oms_users.user_type_id', '=', 'roles.id')
        ->leftJoin('oms_users as reporting_user', 'oms_users.reporting_to', '=', 'reporting_user.id')
        ->select(
            'oms_users.id',
            'oms_users.emp_id',
            'oms_users.username',
            'roles.name as role',
            'reporting_user.username as reporting_to_username'
        )
        ->whereIn('oms_users.id', $allUserIds)
        ->get();

    // Check if user data exists

        return response()->json([
            'users' => $userData->map(function($user) {
                return [
                    'emp_id' => $user->emp_id ?? "",
                    'username' => $user->username ?? "",
                    'role' => $user->role ?? "",
                    'reporting_to_username' => $user->reporting_to_username ?? ""
                ];
            }),
        ]);

}

public function daily_completion(Request $request)
{
    $user = Auth::user();
    $processIds = $this->getProcessIdsBasedOnUserRole($user);

    $currentDate = Carbon::now();
    $firstDateOfCurrentMonth = Carbon::now()->startOfMonth();

    $client_id = $request->input('client_id');
    $lob_id = $request->input('lob_id');
    $process_type_id = $request->input('process_type_id');
    $product_id = $request->input('product_id');
    $selectedDateFilter = $request->input('selectedDateFilter');
    $fromDateRange = $request->input('fromDate_range');
    $toDateRange = $request->input('toDate_range');

    $from_date = null;
    $to_date = null;

    if ($fromDateRange && $toDateRange) {
        $from_date = Carbon::createFromFormat('Y-m-d', $fromDateRange)->toDateString();
        $to_date = Carbon::createFromFormat('Y-m-d', $toDateRange)->toDateString();
    } else {
        $datePattern = '/(\d{2}-\d{2}-\d{4})/';
        if (!empty($selectedDateFilter) && strpos($selectedDateFilter, 'to') !== false) {
            list($fromDateText, $toDateText) = explode('to', $selectedDateFilter);
            $fromDateText = trim($fromDateText);
            $toDateText = trim($toDateText);
            preg_match($datePattern, $fromDateText, $fromDateMatches);
            preg_match($datePattern, $toDateText, $toDateMatches);
            $from_date = isset($fromDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $fromDateMatches[1])->toDateString() : null;
            $to_date = isset($toDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $toDateMatches[1])->toDateString() : null;
        } else {
            preg_match($datePattern, $selectedDateFilter, $dateMatches);
            $from_date = isset($dateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $dateMatches[1])->toDateString() : null;
            $to_date = $from_date;
        }
    }

    // Fetch all possible statuses (same as original code)
    $statuses = DB::table('oms_status')->pluck('status', 'id')->toArray();

    // Query to get the status counts grouped by date, client_id, and order_id (same as original code)
    $statusCounts = DB::table('oms_order_creations')
    ->leftJoin('stl_item_description', 'oms_order_creations.process_id', '=', 'stl_item_description.id')
    ->leftJoin('stl_client', 'stl_item_description.client_id', '=', 'stl_client.id')
    ->join('oms_status', 'oms_order_creations.status_id', '=', 'oms_status.id')
    ->leftJoin('stl_process', 'oms_order_creations.process_type_id', '=', 'stl_process.id')
    ->leftJoin('stl_lob', 'oms_order_creations.lob_id', '=', 'stl_lob.id')

    ->whereDate('oms_order_creations.order_date', '>=', $from_date)  // Explicit 'from_date' condition
    ->whereDate('oms_order_creations.order_date', '<=', $to_date)
    ->where('oms_order_creations.is_active', 1)
    ->where('stl_item_description.is_approved', 1)
    ->where('stl_client.is_approved', 1)
    ->whereIn('oms_order_creations.process_id', $processIds)
    ->select(
            DB::raw('DATE_FORMAT(oms_order_creations.order_date, "%m-%d-%Y") as order_date'),
            'oms_order_creations.client_id',
        'stl_client.client_no',
        'stl_client.client_name',
            'oms_order_creations.id as order_id',
            'oms_status.id as status_id',
            'oms_status.status',
            'oms_order_creations.lob_id',
            'oms_order_creations.process_id',
            'oms_order_creations.process_type_id',
            'oms_order_creations.assignee_user_id'
        );

// Add conditional filters for project_id and client_id.
    if (!empty($product_id) && $product_id[0] !== 'All') {
        $statusCounts->whereIn('oms_order_creations.process_id', $product_id);
}

if (!empty($client_id) && $client_id[0] !== 'All') {
        $statusCounts->where('stl_item_description.client_id', $client_id);
}

if (!empty($lob_id) && $lob_id !== 'All') {
        $statusCounts->where('oms_order_creations.lob_id', $lob_id);
}

if (!empty($process_type_id) && $process_type_id[0] !== 'All') {
        $statusCounts->whereIn('oms_order_creations.process_type_id', $process_type_id);
}

// Retrieve the results after all conditions.
    $statusCounts = $statusCounts->get();

    // Initialize arrays for the response
    $statusCountMap = [];
    $orderReceivedMap = [];
    $pendingMap = [];
    $totalOrders = 0;

    $summaryCount = [
        'Yet to Assign' => 0,
        'WIP' => 0,
        'Coversheet Prep' => 0,
        'Doc Purchaser' => 0,
        'Clarification' => 0,
        'Ground Abstractor' => 0,
        'Send for QC' => 0,
        'Typing' => 0,
        'Typing QC' => 0,
        'Hold' => 0,
        'Completed' => 0,
        'Partially Cancelled' => 0,
        'Cancelled' => 0,
    ];

    // Process the status counts (same as original code)
    foreach ($statusCounts as $statusCount) {
        $dateKey = $statusCount->order_date;
        $clientKey = $statusCount->client_id;
        $statusName = $statusCount->status;
        $client_code = $statusCount->client_no . ' - ' . $statusCount->client_name;
        $status = $statusCount->status;

        // Add "Yet to Assign" or "WIP" based on conditions
        if ($statusCount->status_id == 1) {
            if ($statusCount->assignee_user_id === null) {
                $statusName = 'Yet to Assign';
            } else {
                $statusName = 'WIP';
            }
        }

        if (isset($summaryCount[$statusName])) {
            $summaryCount[$statusName] += 1;  // Increment the count for that status
            $totalOrders++;  // Increment total order count
        }

        // Initialize structure if not already present
        if (!isset($statusCountMap[$dateKey])) {
            $statusCountMap[$dateKey] = [];
        }
        if (!isset($statusCountMap[$dateKey][$clientKey])) {
            $statusCountMap[$dateKey][$clientKey] = [];
        }
        if (!isset($statusCountMap[$dateKey][$clientKey][$statusName])) {
            $statusCountMap[$dateKey][$clientKey][$statusName] = [
                'count' => 0,
                'lob_id' => $statusCount->lob_id,
                'process_id' => $statusCount->process_id,
                'process_type_id' => $statusCount->process_type_id,
                'client_code' => $client_code,
                'order_ids' => []
            ];
        }

        // Add the count and order_id
        $statusCountMap[$dateKey][$clientKey][$statusName]['count'] += 1;
        $statusCountMap[$dateKey][$clientKey][$statusName]['order_ids'][] = $statusCount->order_id;

        // Track "Order Received" count and order IDs
        if (!isset($orderReceivedMap[$dateKey])) {
            $orderReceivedMap[$dateKey] = ['count' => 0, 'order_ids' => []];
        }
        $orderReceivedMap[$dateKey]['count'] += 1;
        $orderReceivedMap[$dateKey]['order_ids'][] = $statusCount->order_id;

        // Track "Pending" count and order IDs (assumed logic: Order Received - Completed)
        $completedCount = $statusCountMap[$dateKey][$clientKey]['Completed']['count'] ?? 0;
        $cancelledCount = $statusCountMap[$dateKey][$clientKey]['Cancelled']['count'] ?? 0;
        $pendingMap[$dateKey] = [
            'count' => ($orderReceivedMap[$dateKey]['count'] - $completedCount - $cancelledCount),
            'order_ids' => array_diff($orderReceivedMap[$dateKey]['order_ids'], $statusCountMap[$dateKey][$clientKey]['Completed']['order_ids'] ?? [], $statusCountMap[$dateKey][$clientKey]['Cancelled']['order_ids'] ?? []),
        ];
    }
    $pendingCount = $totalOrders - $summaryCount['Completed'] - $summaryCount['Cancelled'];

    // Merge "Order Received" and "Pending" into "counts"
    foreach ($orderReceivedMap as $date => $data) {
        foreach ($data['order_ids'] as $orderId) {
            $statusCountMap[$date][$clientKey]['Order Received'] = [
                'count' => $data['count'],
                'order_ids' => $data['order_ids']
    ];
        }
    }

    foreach ($pendingMap as $date => $data) {
        foreach ($data['order_ids'] as $orderId) {
            $statusCountMap[$date][$clientKey]['Pending'] = [
                'count' => $data['count'],
                'order_ids' => $data['order_ids']
            ];
        }
    }

    // Prepare the final response with HTML links for counts (same as original code)
    $responseData = [
        'statuses' => $statuses,
        'counts' => $statusCountMap,
        'summaryCount' => $summaryCount,
        'totalOrders' => $totalOrders,
        'pendingCount' => $pendingCount
    ];

    // Add links to the counts in the responseData (same as original code)
    foreach ($responseData['counts'] as $date => $clients) {
        foreach ($clients as $clientId => $statusData) {
            foreach ($statusData as $statusName => $data) {
                // Wrap count in <a> tag with data attributes for order_ids
                $order_ids = implode(',', $data['order_ids']);
                $responseData['counts'][$date][$clientId][$statusName]['count_html'] =
                    '<a href="javascript:void(0);" data-order-ids="' . $order_ids . '" class="order-link">' .
                    $data['count'] . '</a>';
            }
        }
    }

    return response()->json($responseData);
}




public function fetch_order_details(Request $request)
{
    $page = $request->input('page', 1);
    $pageSize = $request->input('length', 10); // Page size for pagination
    $orderIds = $request->input('order_ids');
    $searchValue = $request->input('search_value');

    // Convert the string of IDs into an array if necessary
    if (is_string($orderIds)) {
        $orderIds = explode(',', $orderIds);
    }

    // Ensure that orderIds is an array before running the query
    $ordersQuery = DB::table('oms_order_creations')
    ->leftJoin('stl_client', 'oms_order_creations.client_id', '=', 'stl_client.id')
    ->join('oms_status', 'oms_order_creations.status_id', '=', 'oms_status.id')
    ->whereIn('oms_order_creations.id', $orderIds)
    ->select(
        DB::raw('DATE_FORMAT(oms_order_creations.order_date, "%m-%d-%Y") as order_date'),
        'oms_order_creations.order_id as order_id',
        'stl_client.client_name',
        DB::raw(
            'CASE
                WHEN oms_order_creations.assignee_user_id IS NULL AND oms_order_creations.status_id = 1 THEN "Yet to Assign"
                WHEN oms_order_creations.assignee_user_id IS NOT NULL AND oms_order_creations.status_id = 1 THEN "WIP"
                    ELSE oms_status.status
            END as status'
    )
        );

        if (!empty($searchValue)) {
            $ordersQuery->where(function ($query) use ($searchValue) {
                $query->where('oms_order_creations.order_id', 'like', '%' . $searchValue . '%')
                      ->orWhere('stl_client.client_name', 'like', '%' . $searchValue . '%')
                      ->orWhere('oms_status.status', 'like', '%' . $searchValue . '%');
            });
        }


    // Get the total number of records for pagination
    $totalRecords = $ordersQuery->count();

    if ($request->has('export') && $request->input('export') == true) {
        $orders = $ordersQuery->get();
    } else {
        $orders = $ordersQuery->skip(($page - 1) * $pageSize)->take($pageSize)->get();
    }

    return response()->json([
        'orders' => $orders,            // Paginated orders for the current page
        'recordsTotal' => $totalRecords, // Total number of records
        'recordsFiltered' => $totalRecords, // Filtered records (same as total in this case)
    ]);
    }







}

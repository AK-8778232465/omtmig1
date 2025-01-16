<?php

namespace App\Http\Controllers;

use App\Models\OrderCreation;
use App\Models\State;
use App\Models\Status;
use App\Models\County;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use DataTables;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use DateTime;
use DateTimeZone;



class OrderController extends Controller
{

    public function orders(Request $request)
    {
        $orderData = OrderCreation::leftJoin('stl_item_description', 'oms_order_creations.process_id', '=', 'stl_item_description.id')->get();

        $status_id = Status::pluck('id')->toArray();

        return view('app.orders.orderlist', compact('orderData', 'status_id'));
    }


    public function getStatusCount()
    {
        $user = Auth::user();
        $processIds = $this->getProcessIdsBasedOnUserRole($user);
        $statusCountsQuery = OrderCreation::query();
        $statusCountsQuery = $statusCountsQuery->with('process', 'client')
            ->whereIn('process_id', $processIds)
                ->where('is_active', 1) 
            ->whereHas('process', function ($query) {
                $query->where('stl_item_description.is_approved', 1);
            })
            ->whereHas('client', function ($query) {
                $query->where('stl_client.is_approved', 1);
            });

        if (!in_array($user->user_type_id, [1, 2, 3, 4, 5, 9])) {
            if ($user->user_type_id == 6) {
                    // For user_type_id = 6
                    $statusCountsQuery->where('assignee_user_id', $user->id)->whereNotIn('oms_order_creations.status_id', [16, 17]);
                } elseif ($user->user_type_id == 7) {
                    $statusCountsQuery->where('assignee_qa_id', $user->id)->whereNotIn('status_id', [1, 16, 17]);
                } elseif ($user->user_type_id == 8) {
                    $statusCountsQuery->where(function ($optionalquery) use ($user) {
                        $optionalquery->where(function ($subQuery) use ($user) {
                            $subQuery->where('oms_order_creations.assignee_user_id', $user->id)
                                ->orWhere('oms_order_creations.assignee_qa_id', $user->id);
                        })
                        ->orWhere(function ($subQuery) {
                            $subQuery->where('status_id', 4)
                                ->whereNull('assignee_qa_id');
                        });
                    })->whereNotIn('status_id', [15, 16, 17]);
                } elseif ($user->user_type_id == 10) {
                    $statusCountsQuery->where(function ($query) use ($user) {
                        $query->where('oms_order_creations.typist_id', $user->id);
                    })->whereNotIn('status_id', [1, 4, 13, 15, 17])
                      ->whereNotIn('oms_order_creations.process_type_id', [1, 3, 5, 15]);
                } elseif ($user->user_type_id == 11) {
                    $statusCountsQuery->where(function ($query) use ($user) {
                        $query->where('oms_order_creations.typist_qc_id', $user->id);
                    })->whereNotIn('status_id', [1, 4, 13, 15, 16])
                      ->whereNotIn('oms_order_creations.process_type_id', [1, 3, 5, 15]);
            }elseif ($user->user_type_id == 22) {
                    $statusCountsQuery->where(function ($subQuery) use ($user) {
                            $subQuery->where('typist_id', $user->id)
                                    ->orWhere('typist_qc_id', $user->id)
                                    ->orWhereNull('typist_id')
                                    ->orWhereNull('typist_qc_id');
                })
                        ->whereNotIn('oms_order_creations.status_id', [1, 4, 13, 15])
                        ->whereNotIn('oms_order_creations.process_type_id', [1, 3, 5, 15]);

            }
        }


        $statusCounts = $statusCountsQuery->groupBy('status_id')
            ->selectRaw('count(*) as count, status_id')
            ->where('is_active', 1)
            ->pluck('count', 'status_id');

        $yetToAssignUser = OrderCreation::with('process', 'client')
            ->where('assignee_user_id', null)
            ->where('status_id', 1)
            ->where('is_active', 1)
            ->whereIn('process_id', $processIds)
            ->whereHas('process', function ($query) {
                $query->where('stl_item_description.is_approved', 1);
            })
            ->whereHas('client', function ($query) {
                $query->where('stl_client.is_approved', 1);
            })
            ->count();

        $yetToAssignQaValue = 0;
        $yetToAssignTypistValue = 0;
        $yetToAssignTypistQaValue = 0;

        if(!in_array($user->user_type_id, [10, 11, 22])){
            $yetToAssignQaValue = OrderCreation::with('process', 'client')
            ->where('assignee_qa_id', null)
            ->where('status_id', 4)
            ->where('is_active', 1)
            ->whereIn('process_id', $processIds)
            ->whereHas('process', function ($query) {
                $query->where('stl_item_description.is_approved', 1);
            })
            ->whereHas('client', function ($query) {
                $query->where('stl_client.is_approved', 1);
            })
            ->count();
        }

    if(!in_array($user->user_type_id, [6, 7, 8])){
        if (!in_array($user->user_type_id, [11])) {
            $yetToAssignTypistValue = OrderCreation::with('process', 'client')
                ->where('typist_id', null)
                ->where('status_id', 16)
                ->where('is_active', 1)
                ->whereIn('process_id', $processIds)
                ->whereHas('process', function ($query) {
                    $query->where('stl_item_description.is_approved', 1);
                })
                ->whereHas('client', function ($query) {
                    $query->where('stl_client.is_approved', 1);
                })
                ->count();
        } 
        if (!in_array($user->user_type_id, [10])) {
            $yetToAssignTypistQaValue = OrderCreation::with('process', 'client')
                ->where('typist_qc_id', null)
                ->where('status_id', 17)
                ->where('is_active', 1)
                ->whereIn('process_id', $processIds)
                ->whereHas('process', function ($query) {
                    $query->where('stl_item_description.is_approved', 1);
                })
                ->whereHas('client', function ($query) {
                    $query->where('stl_client.is_approved', 1);
                })
                ->count();
        }
    }

            $yetToAssignCounts = [
                'yetToAssignQa' => isset($yetToAssignQaValue) ? $yetToAssignQaValue : 0,
                'yetToAssignTypist' => isset($yetToAssignTypistValue) ? $yetToAssignTypistValue : 0,
                'yetToAssignTypistQa' => isset($yetToAssignTypistQaValue) ? $yetToAssignTypistQaValue : 0,
            ];

        $user_coverSheet = OrderCreation::with('process', 'client')
            ->where('status_id', 13)
            ->where('is_active', 1)
            ->whereIn('process_id', $processIds)
            ->whereHas('process', function ($query) {
                $query->where('stl_item_description.is_approved', 1);
            })
            ->whereHas('client', function ($query) {
                $query->where('stl_client.is_approved', 1);
            })
        ->where('oms_order_creations.assignee_user_id', $user->id)
            ->count();

        $assign_coverSheet = OrderCreation::with('process', 'client')
            ->where('status_id', 13)
            ->where('is_active', 1)
            ->whereIn('process_id', $processIds)
            ->whereHas('process', function ($query) {
                $query->where('stl_item_description.is_approved', 1);
            })
            ->whereHas('client', function ($query) {
                $query->where('stl_client.is_approved', 1);
            })

            ->where('oms_order_creations.assignee_user_id', '!=', $user->id)
            ->where(function ($query) use ($user) {
                $query->whereNull('oms_order_creations.associate_id')
                    ->orWhere('oms_order_creations.associate_id', $user->id);
            })
            ->count();

        if (in_array($user->user_type_id, [1, 2, 3, 4, 5, 9, 23, 24])) {
            $statusCounts[1] = (!empty($statusCounts[1]) ? $statusCounts[1] : 0) - $yetToAssignUser;
                $statusCounts[4] = (!empty($statusCounts[4]) ? $statusCounts[4] : 0) - $yetToAssignQaValue;
                if (!empty($statusCounts[16]) && $statusCounts[16] != 0) {
                    $statusCounts[16] -= $yetToAssignTypistValue;
                }
                if (!empty($statusCounts[17]) && $statusCounts[17] != 0) {
                    $statusCounts[17] -= $yetToAssignTypistQaValue;
                }
            $statusCounts[6] = $yetToAssignUser;
            }
             else {
            $statusCounts[6] = in_array($user->user_type_id, [6, 8]) ? $yetToAssignUser : 0;
                
                // if(in_array($user->user_type_id, [8])){
                //     // $statusCounts[1] = (!empty($statusCounts[1]) ? $statusCounts[1] : 0) - $yetToAssignUser;
                // }
                if (!empty($statusCounts[4])) {
                    if (!in_array($user->user_type_id, [6, 7, 10, 11, 22])) {
                        $statusCounts[4] -= $yetToAssignQaValue;
                    }
                }

                if (in_array($user->user_type_id, [22])) {

                    if (!empty($statusCounts[16])) {
                        $statusCounts[16] -= $yetToAssignTypistValue;
                    }
                    if (!empty($statusCounts[17])) {
                        $statusCounts[17] -= $yetToAssignTypistQaValue;
                    }
                }
                
            $statusCounts[13] = $user_coverSheet;
        }

    $tatstatusCountsQuery = DB::table('oms_order_creations')
        ->leftJoin('stl_item_description', 'oms_order_creations.process_id', '=', 'stl_item_description.id')
        ->leftJoin('oms_state', 'oms_order_creations.state_id', '=', 'oms_state.id')
        ->leftJoin('county', 'oms_order_creations.county_id', '=', 'county.id')
        ->leftJoin('oms_status', 'oms_order_creations.status_id', '=', 'oms_status.id')
        ->leftJoin('stl_client', 'stl_item_description.client_id', '=', 'stl_client.id')
        ->leftJoin('oms_users as assignee_users', 'oms_order_creations.assignee_user_id', '=', 'assignee_users.id')
        ->leftJoin('oms_users as assignee_qas', 'oms_order_creations.assignee_qa_id', '=', 'assignee_qas.id')
        ->leftJoin('oms_users as typist_users', 'oms_order_creations.typist_id', '=', 'typist_users.id')
        ->leftJoin('oms_users as typist_qas', 'oms_order_creations.typist_qc_id', '=', 'typist_qas.id')
        ->leftJoin('oms_users as associate_names', 'oms_order_creations.associate_id', '=', 'associate_names.id')
        ->leftJoin('stl_lob', 'stl_item_description.lob_id', '=', 'stl_lob.id')
        ->leftJoin('oms_tier','oms_order_creations.tier_id', '=', 'oms_tier.id')
        ->leftJoin('stl_process', 'oms_order_creations.process_type_id', '=', 'stl_process.id')
        ->select(
            'oms_order_creations.id',
            'oms_order_creations.order_id as order_id',
            'oms_order_creations.status_id as status_id',
            'oms_order_creations.order_date as order_date',
            'stl_item_description.tat_value as tat_value',
            'oms_order_creations.assignee_user_id',
            'oms_order_creations.assignee_qa_id',
            'oms_order_creations.typist_id',
            'oms_order_creations.typist_qc_id',
            'oms_order_creations.associate_id',
        )
        ->whereIn('oms_order_creations.process_id', $processIds)
        ->where('oms_order_creations.is_active', 1)
        ->whereNotNull('oms_order_creations.assignee_user_id')
        ->where('stl_item_description.is_approved', 1)
        ->where('stl_client.is_approved', 1);

        if (!in_array($user->user_type_id, [1, 2, 3, 4, 5, 9])) {
            if ($user->user_type_id == 6) {
                $tatstatusCountsQuery->where('oms_order_creations.assignee_user_id', $user->id);
            } elseif($user->user_type_id == 7) {
                $tatstatusCountsQuery->where('oms_order_creations.assignee_qa_id', $user->id)
                ->whereNotIn('oms_order_creations.status_id', [1]);
            } elseif($user->user_type_id == 8) {
                $tatstatusCountsQuery->where(function ($query) use($user) {
                    $query->where('oms_order_creations.assignee_user_id', $user->id)
                        ->orWhere('oms_order_creations.assignee_qa_id', $user->id);
                });
            } elseif($user->user_type_id == 10){
                $tatstatusCountsQuery->where('oms_order_creations.typist_id', $user->id)
                ->whereNotIn('oms_order_creations.status_id', [1, 13, 4, 15, 17, 18, 20]);
            } elseif($user->user_type_id == 11){
                $tatstatusCountsQuery->where('oms_order_creations.typist_qc_id', $user->id)
                ->whereNotIn('oms_order_creations.status_id', [1, 13, 4, 15, 16, 18, 20]);
            } elseif ($user->user_type_id == 22) {
                $statusCountsQuery->where(function($query) use ($user) {
                    $query->where('oms_order_creations.typist_id', $user->id)
                          ->orWhere('oms_order_creations.typist_qc_id', $user->id);

                })
                ->whereIn('status_id', [2, 3, 5, 14, 16, 17, 18, 20]);

            }
        }

        $tatStatusCountsQuery = $tatstatusCountsQuery->get();

        function calculateTatValues($tatStatusCountsQuery)
        {
            $resultsByStatus = [];
        
            foreach ($tatStatusCountsQuery as $order) {
                if (!is_null($order->tat_value)) {
                    $tatValue = $order->tat_value; 
                    $statusId = $order->status_id; 
                    $tatHours = $tatValue / 4; 
        
                    $orderDate = new \DateTime($order->order_date, new \DateTimeZone('America/New_York'));
        
                    $currentDate = new \DateTime('now', new \DateTimeZone('America/New_York'));
                    $diff = $currentDate->diff($orderDate);
        
                    $hoursDifference = ($diff->days * 24) + $diff->h + ($diff->i / 60); 
        
                    if (!isset($resultsByStatus[$statusId])) {
                        $resultsByStatus[$statusId] = [
                            'orderReachthird' => 0,  
                            'orderReachfourth' => 0  
                        ];
                    }
                    if ($statusId == 5) {
                        continue;
                    }
                    if ($hoursDifference >= $tatHours * 3 && $hoursDifference < $tatHours * 4) {
                        $resultsByStatus[$statusId]['orderReachfourth'] += 1;
                    } 
                    elseif ($hoursDifference >= $tatHours * 2 && $hoursDifference < $tatHours * 3) {
                        $resultsByStatus[$statusId]['orderReachthird'] += 1;
                    }
                }
    }

            return $resultsByStatus;
    }

        $results = calculateTatValues($tatStatusCountsQuery);
        
        $totalThirdCount = array_sum(array_column($results, 'orderReachthird'));
        $totalFourthCount = array_sum(array_column($results, 'orderReachfourth'));

        return response()->json([
            'StatusCounts' => $statusCounts,
            'AssignCoverSheet' => $assign_coverSheet,
            'TatStatusResults' => $results,  
            'tat_status_All_third_count' => $totalThirdCount, 
            'tat_status_All_fourth_count' => $totalFourthCount,
            'yetToAssignCounts' => $yetToAssignCounts
        ]);
}

    public function getOrderData(Request $request)
    {
        $user = Auth::user();
        $processIds = $this->getProcessIdsBasedOnUserRole($user);
        $searchType = $request->input('searchType');
        $searchInputs = $request->input('searchInputs');
        $selectedDateFilter = $request->input('selectedDateFilter');
        $fromDateRange = $request->input('fromDate_range');
        $toDateRange = $request->input('toDate_range');
        $tat_zone_filter = $request->input('tat_zone_filter');
 

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
            ->leftJoin('oms_users as assignee_users', 'oms_order_creations.assignee_user_id', '=', 'assignee_users.id')
            ->leftJoin('oms_users as assignee_qas', 'oms_order_creations.assignee_qa_id', '=', 'assignee_qas.id')
            ->leftJoin('oms_users as typist_users', 'oms_order_creations.typist_id', '=', 'typist_users.id')
            ->leftJoin('oms_users as typist_qas', 'oms_order_creations.typist_qc_id', '=', 'typist_qas.id')
            ->leftJoin('oms_users as associate_names', 'oms_order_creations.associate_id', '=', 'associate_names.id')
            ->leftJoin('stl_lob', 'stl_item_description.lob_id', '=', 'stl_lob.id')
            ->leftJoin('oms_tier','oms_order_creations.tier_id', '=', 'oms_tier.id')
            ->leftJoin('stl_process', 'oms_order_creations.process_type_id', '=', 'stl_process.id')
            
            ->select(
                'oms_order_creations.id',
                'oms_order_creations.order_id as order_id',
                'oms_order_creations.status_id as status_id',
                'oms_order_creations.order_date as order_date',
                'oms_order_creations.process_type_id as process_type_id',
                'stl_item_description.project_code as project_code',
                'stl_item_description.qc_enabled as qc_enabled',
                'stl_item_description.tat_value as tat_value',
                'oms_state.short_code as short_code',
                'county.county_name as county_name',
                'assignee_users.username',
                'assignee_qas.username',
                'oms_order_creations.assignee_user_id',
                'oms_order_creations.assignee_qa_id',
                'oms_order_creations.typist_id',
                'oms_order_creations.typist_qc_id',
                // 'oms_order_creations.tax_bucket',
                'oms_order_creations.associate_id',
                'stl_lob.name as lob_name',
                'stl_process.name as process_name',
                'stl_client.client_name',
                'stl_client.id as client_id',
                'stl_item_description.process_name as process', 
                'oms_tier.Tier_id as tier_name',
                DB::raw('DATE_FORMAT(oms_order_creations.created_at, "%m/%d/%Y %H:%i:%s") as created_at'),
                DB::raw('CONCAT(assignee_users.emp_id, " (", assignee_users.username, ")") as assignee_user'),
                DB::raw('CONCAT(assignee_qas.emp_id, " (", assignee_qas.username, ")") as assignee_qa'),
                DB::raw('CONCAT(typist_users.emp_id, " (", typist_users.username, ")") as typist_user'),
                DB::raw('CONCAT(typist_qas.emp_id, " (", typist_qas.username, ")") as typist_qa'),
                DB::raw('CONCAT(associate_names.emp_id, " (", associate_names.username, ")") as associate_name')
            )
            ->where('oms_order_creations.is_active', 1)
            ->where('stl_item_description.is_approved', 1)
            ->where('stl_client.is_approved', 1);

            if ($fromDate) {
                $query->whereDate('oms_order_creations.order_date', '>=', $fromDate);
            }
        
            if ($toDate) {
                $query->whereDate('oms_order_creations.order_date', '<=', $toDate);
            }
            
            if (
                isset($request->status) &&
                in_array($request->status, [1, 2, 3, 4, 5, 13, 14, 15, 16, 17, 18, 20]) &&
                $request->status != 'All' &&
                $request->status != 6 &&
                $request->status != 7
            ) {
                if ($request->status == 1) {
                    if(in_array($user->user_type_id, [1, 2, 3, 4, 5, 9, 23, 24])) {
                        $query->where('oms_order_creations.status_id', $request->status)->whereNotNull('oms_order_creations.assignee_user_id');
                    } elseif(in_array($user->user_type_id, [6, 7])) {
                        $query->where('oms_order_creations.status_id', $request->status)->where('oms_order_creations.assignee_user_id', $user->id);
                    }else{
                        $query->where('oms_order_creations.status_id', $request->status)
                            ->where(function ($optionalquery) use ($user) {
                                $optionalquery->where('oms_order_creations.assignee_user_id', $user->id)
                                    ->orWhere('oms_order_creations.assignee_qa_id', $user->id);
                            });
                    }
                } elseif($request->status == 4) {
                    if(in_array($user->user_type_id, [1, 2, 3, 4, 5, 9, 23, 24])) {
                        $query->where('oms_order_creations.status_id', $request->status);
                    } else {
                        if($request->status != 13){
                        if(in_array($user->user_type_id, [6])) {
                                $query->where('oms_order_creations.status_id', $request->status)
                                            ->where(function ($query) use ($user) {
                                                $query->where('oms_order_creations.assignee_user_id', $user->id);
                                                        // ->orWhereNull('oms_order_creations.assignee_user_id');
                                            });

                        } elseif(in_array($user->user_type_id, [7])) {
                                $query->where('oms_order_creations.status_id', $request->status)
                                            ->where(function ($query) use ($user) {
                                                $query->where('oms_order_creations.assignee_qa_id', $user->id)
                                                        ->orWhereNull('oms_order_creations.assignee_qa_id');
                                            })->whereNotIn('status_id',[1, 13, 16, 17]);

                        } elseif(in_array($user->user_type_id, [8])) {
                            $query->where('oms_order_creations.status_id', $request->status)
                                    ->where(function ($query) use ($user) {
                                        $query->where(function ($subQuery) use ($user) {
                                            $subQuery->where('oms_order_creations.assignee_user_id', $user->id)
                                                    ->orWhereNull('oms_order_creations.assignee_user_id');
                                        })
                                        ->orWhere(function ($subQuery) use ($user) {
                                            $subQuery->where('oms_order_creations.assignee_qa_id', $user->id)
                                                    ->orWhereNull('oms_order_creations.assignee_qa_id');
                                        });
                            });

                            }
                            if(in_array($user->user_type_id, [10])){
                                $query->where('oms_order_creations.status_id', $request->status)
                                    ->where(function ($query) use ($user) {
                                        $query->where('oms_order_creations.typist_id', $user->id)
                                                ->orWhereNull('oms_order_creations.typist_id');
                                    })
                                      ->whereNotIn('status_id', [1, 4, 13, 15, 17])
                                      ->whereNotIn('oms_order_creations.process_type_id', [1, 3, 5, 15]);

                                    // $statusCountsQuery->where(function ($query) use ($user) {
                                    //     $query->where('oms_order_creations.typist_id', $user->id);
                                    // })->whereNotIn('status_id', [1, 13, 4, 15, 17, 20])
                                    //   ->whereNotIn('oms_order_creations.process_type_id', [1, 3, 5, 15]);
                            } else if(in_array($user->user_type_id, [11])){
                                $query->where('oms_order_creations.status_id', $request->status)
                                ->where(function ($query) use ($user) {
                                    $query->where('oms_order_creations.typist_qc_id', $user->id)
                                            ->orWhereNull('oms_order_creations.typist_qc_id');
                                })->whereNotIn('status_id', [1, 4, 13, 15, 16])
                                ->whereNotIn('oms_order_creations.process_type_id', [1, 3, 5, 15]);
                            }
                             else if (in_array($user->user_type_id, [22])) {
                            $query->where('oms_order_creations.status_id', $request->status)
                                ->where(function ($query) use ($user) {
                                    $query->where(function ($subQuery) use ($user) {
                                        $subQuery->where('oms_order_creations.typist_id', $user->id)
                                                 ->orWhereNull('oms_order_creations.typist_id');
                                    })
                                    ->orWhere(function ($subQuery) use ($user) {
                                        $subQuery->where('oms_order_creations.typist_qc_id', $user->id)
                                                 ->orWhereNull('oms_order_creations.typist_qc_id');
                            });
                                })->whereIn('oms_order_creations.status_id', [2, 3, 5, 14, 16, 17, 18])
                                ->whereNotIn('oms_order_creations.process_type_id', [1, 3, 5, 15]);
                            }
                        }
                    }
            } else if($request->status == 16){
                if(in_array($user->user_type_id, [1, 2, 3, 4, 5, 9, 23, 24])) {
                    $query->where('oms_order_creations.status_id', $request->status);
                } else {
                        if($request->status != 13){
                            if(in_array($user->user_type_id, [10])){
                                $query->where('oms_order_creations.status_id', $request->status)
                                    ->where(function ($query) use ($user) {
                                        $query->where('oms_order_creations.typist_id', $user->id)
                                                ->orWhereNull('oms_order_creations.typist_id');
                                    })
                                      ->whereNotIn('status_id', [1, 13, 4, 15, 17])
                                      ->whereNotIn('oms_order_creations.process_type_id', [1, 3, 5, 15]);

                            } else if(in_array($user->user_type_id, [11])){
                                $query->where('oms_order_creations.status_id', $request->status)
                                ->where(function ($query) use ($user) {
                                    $query->where('oms_order_creations.typist_qc_id', $user->id)
                                            ->orWhereNull('oms_order_creations.typist_qc_id');
                                })->whereNotIn('status_id', [1, 4, 13, 15, 16]);
                            }
                             else if (in_array($user->user_type_id, [22])) {
                            $query->where('oms_order_creations.status_id', $request->status)
                                ->where(function ($subQuery) use ($user) {
                                    $subQuery->where('typist_id', $user->id)
                                            ->orWhere('typist_qc_id', $user->id)
                                            ->orWhereNull('typist_id')
                                            ->orWhereNull('typist_qc_id');
                                })
                                ->whereNotIn('oms_order_creations.status_id', [1, 4, 13, 15])
                                ->whereNotIn('oms_order_creations.process_type_id', [1, 3, 5, 15]);
                        }
                    }
                    }
            } else if ($request->status == 17){
                if(in_array($user->user_type_id, [1, 2, 3, 4, 5, 9, 23, 24])) {
                    $query->where('oms_order_creations.status_id', $request->status);
                } else {
                    if($request->status != 13){
                        if(in_array($user->user_type_id, [10])){
                            $query->where('oms_order_creations.status_id', $request->status)
                                ->where(function ($query) use ($user) {
                                    $query->where('oms_order_creations.typist_id', $user->id)
                                            ->orWhereNull('oms_order_creations.typist_id');
                                })->whereNotIn('status_id', [1, 13, 4, 15, 16]);
                        } else if(in_array($user->user_type_id, [11])){
                            $query->where('oms_order_creations.status_id', $request->status)
                            ->where(function ($query) use ($user) {
                                $query->where('oms_order_creations.typist_qc_id', $user->id)
                                        ->orWhereNull('oms_order_creations.typist_qc_id');
                            })->whereNotIn('status_id', [1, 13, 4, 15, 16]);
                        }
                         else if (in_array($user->user_type_id, [22])) {
                            $query->where('oms_order_creations.status_id', $request->status)
                                ->where(function ($subQuery) use ($user) {
                                    $subQuery->where('typist_id', $user->id)
                                            ->orWhere('typist_qc_id', $user->id)
                                            ->orWhereNull('typist_id')
                                            ->orWhereNull('typist_qc_id');
                                })
                                ->whereNotIn('oms_order_creations.status_id', [1, 4, 13, 15])
                                ->whereNotIn('oms_order_creations.process_type_id', [1, 3, 5, 15]);


                        }
                    }
                }
            }
            else {
                    if(in_array($user->user_type_id, [1, 2, 3, 4, 5, 9, 23, 24])) {
                        // $query->where('oms_order_creations.status_id', $request->status)->whereNotNull('oms_order_creations.assignee_user_id');
                        $query->where(function ($query) {
                            $query->whereNotNull('oms_order_creations.assignee_user_id')
                                  ->orWhere(function ($query) {
                                      $query->whereNotNull('oms_order_creations.typist_id')
                                            ->whereNull('oms_order_creations.assignee_user_id');
                                  });
                        })->where('oms_order_creations.status_id', $request->status);
                        
                    } elseif(in_array($user->user_type_id, [6]) && $request->status != 13){
                        $query->where('oms_order_creations.status_id', $request->status)->where('oms_order_creations.assignee_user_id', $user->id);
                    }
                    elseif(in_array($user->user_type_id, [7]) && $request->status != 13) {
                        $query->where('oms_order_creations.status_id', $request->status)->Where('oms_order_creations.assignee_qa_id', $user->id);
                    }
                    elseif(in_array($user->user_type_id, [8]) && $request->status != 13) {
                        // $query->where('oms_order_creations.status_id', $request->status)
                        // ->where(function ($optionalquery) use($user) {
                        //     $optionalquery->where('oms_order_creations.assignee_user_id', $user->id)
                        //         ->orWhere('oms_order_creations.assignee_qa_id', $user->id);
                        // });
                        $query->where('oms_order_creations.status_id', $request->status)
                        ->where(function ($optionalquery) use ($user) {
                            $optionalquery->where(function ($subQuery) use ($user) {
                                $subQuery->where('oms_order_creations.assignee_user_id', $user->id)
                                    ->orWhere('oms_order_creations.assignee_qa_id', $user->id);
                            })
                            ->orWhere(function ($subQuery) {
                                $subQuery->where('status_id', 4)
                                    ->whereNull('assignee_qa_id');
                            });
                        })->whereNotIn('status_id', [15, 16, 17]);
                    }
                    elseif(in_array($user->user_type_id, [10]) && $request->status != 13) {
                        $query->where('oms_order_creations.status_id', $request->status)
                        ->where(function ($optionalquery) use($user) {
                            $optionalquery->where('oms_order_creations.typist_id', $user->id);
                               
                        });
                    } elseif(in_array($user->user_type_id, [11]) && $request->status != 13) {
                        $query->where('oms_order_creations.status_id', $request->status)
                        ->where(function ($optionalquery) use($user) {
                            $optionalquery->where('oms_order_creations.typist_qc_id', $user->id);
                        });
                    } elseif(in_array($user->user_type_id, [22]) && $request->status != 13) {
                        // $query->where('oms_order_creations.status_id', $request->status)
                        // ->where(function ($optionalquery) use($user) {
                        //     $optionalquery->where('oms_order_creations.typist_id', $user->id)
                        //         ->orWhere('oms_order_creations.typist_qc_id', $user->id);
                        // });

                        $query->where('oms_order_creations.status_id', $request->status)
                            ->where(function ($subQuery) use ($user) {
                            $subQuery->where('typist_id', $user->id)
                                    ->orWhere('typist_qc_id', $user->id)
                                    ->orWhereNull('typist_id')
                                    ->orWhereNull('typist_qc_id');
                        })
                        ->whereNotIn('oms_order_creations.status_id', [1, 4, 13, 15])
                        ->whereNotIn('oms_order_creations.process_type_id', [1, 3, 5, 15]);

                    }
                    else{
                        if($request->status != 13){
                        $query->where(function ($optionalquery) use($user) {
                            $optionalquery->where('oms_order_creations.assignee_user_id', $user->id)
                                ->orWhere('oms_order_creations.assignee_qa_id', $user->id);
                        });
                        } else{
                            $query->where('oms_order_creations.status_id', $request->status);
                            $query->where(function ($optionalquery) use($user) {
                            $optionalquery->whereNull('oms_order_creations.associate_id')
                                    ->orWhere(function($subquery) use($user) {
                                        $subquery->where('oms_order_creations.associate_id', $user->id)
                                                 ->orWhere('oms_order_creations.assignee_user_id', $user->id);
                                    });
                        });
                        }
                    }
                }
            } elseif ($request->status == 'All') {
                if(in_array($user->user_type_id, [6])) {
                    $query->where('oms_order_creations.assignee_user_id', $user->id)->whereNotIn('oms_order_creations.status_id', [16, 17]);
                } elseif(in_array($user->user_type_id, [7])) {
                $query->where(function ($optionalquery) use ($user) {
                    $optionalquery->where(function ($subQuery) use ($user) {
                        $subQuery->where('oms_order_creations.assignee_qa_id', $user->id);
                    })
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('status_id', 4)
                            ->whereNull('assignee_qa_id');
                    });
                })->whereNotIn('status_id', [1, 13, 16, 17]);
                } elseif(in_array($user->user_type_id, [8])) {
                    $query->where(function ($optionalquery) use ($user) {
                        $optionalquery->where(function ($subQuery) use ($user) {
                            $subQuery->where('oms_order_creations.assignee_user_id', $user->id)
                            ->orWhere('oms_order_creations.assignee_qa_id', $user->id);
                        })
                        ->orWhere(function ($subQuery) {
                            $subQuery->where('status_id', 4)
                                ->whereNull('assignee_qa_id');
                    });
                    })->whereNotIn('status_id', [15, 16, 17]);
                } elseif(in_array($user->user_type_id, [10])){
                    $query->where(function ($query) use ($user) {
                    $query->where('oms_order_creations.typist_id', $user->id)
                              ->orWhereNull('oms_order_creations.typist_id'); // Match typist_id or NULL
                    })->whereNotIn('status_id', [1, 4, 13, 15, 17]) // Exclude specific status_id
                      ->whereNotIn('oms_order_creations.process_type_id', [1, 3, 5, 15]); // Exclude specific process_type_id
                    
                }elseif(in_array($user->user_type_id, [11])){
                    $query->where(function($query) use ($user) {
                    $query->where('oms_order_creations.typist_qc_id', $user->id)
                              ->orWhereNull('oms_order_creations.typist_qc_id');
                    })
                    ->whereNotIn('status_id', [1, 4, 13, 15, 16])
                    ->whereNotIn('oms_order_creations.process_type_id', [1, 3, 5, 15]);
                    
                }elseif(in_array($user->user_type_id, [22])) {
                    $query->where(function ($subQuery) use ($user) {
                            $subQuery->where('typist_id', $user->id)
                                    ->orWhere('typist_qc_id', $user->id)
                                    ->orWhereNull('typist_id')
                                    ->orWhereNull('typist_qc_id');
                    })
                        ->whereNotIn('oms_order_creations.status_id', [1, 4, 13, 15])
                        ->whereNotIn('oms_order_creations.process_type_id', [1, 3, 5, 15]);
                }
                else {
                   // $query->whereNotNull('oms_order_creations.assignee_user_id')->orWhereIn('oms_order_creations.process_type_id',[2, 4, 6, 8, 9, 16]);
                   $query->where(function ($query) {
                    $query->whereNotNull('oms_order_creations.assignee_user_id')
                          ->orWhere(function ($query) {
                              $query->whereNotNull('oms_order_creations.typist_id')
                                    ->whereNull('oms_order_creations.assignee_user_id');
                          });
                });
                }
            } elseif ($request->status == 6) {
                if(in_array($user->user_type_id, [1, 2, 3, 4, 5, 6, 8, 9, 23, 24])) {
                    $query->whereNull('oms_order_creations.assignee_user_id')->where('oms_order_creations.status_id', 1);
                } else {
                    $query->whereNull('oms_order_creations.id');
                }
            } elseif ($request->status == 7) {
                if(in_array($user->user_type_id, [1, 2, 3, 4, 5, 7, 8, 9, 23, 24])) {
                    $query->whereNull('oms_order_creations.assignee_qa_id')->where('oms_order_creations.status_id', 4);
                } else {
                    $query->whereNull('oms_order_creations.id');
                }
            }

if (isset($request->sessionfilter) && $request->sessionfilter == 'true') {
        $user = Auth::user();
        $processIds = $this->getProcessIdsBasedOnUserRole($user);
        $fromDate = Session::get('fromDate');
        $toDate = Session::get('toDate');
        $project_id = Session::get('projectId');
        $client_id = Session::get('clientId');
    
        $project_id = !is_array($project_id) ? explode(',', $project_id) : $project_id;
        $client_id = !is_array($client_id) ? explode(',', $client_id) : $client_id;
    
        $carryOverAllStatusCounts = OrderCreation::query()->with('process', 'client')
                ->whereHas('process', function ($query) {
                    $query->where('stl_item_description.is_approved', 1);
                })
                ->whereHas('client', function ($query) {
                    $query->where('stl_client.is_approved', 1);
                });
        $currentCompletedCount = OrderCreation::query()->with('process', 'client')
                ->whereHas('process', function ($query) {
                    $query->where('stl_item_description.is_approved', 1);
                })
                ->whereHas('client', function ($query) {
                    $query->where('stl_client.is_approved', 1);
                });
        $getPreCompletedorderId = OrderCreation::query()->with('process', 'client')
                ->whereHas('process', function ($query) {
                    $query->where('stl_item_description.is_approved', 1);
                })
                ->whereHas('client', function ($query) {
                    $query->where('stl_client.is_approved', 1);
                });
        $getcurrentCompletedorderId = OrderCreation::query()->with('process', 'client')
                ->whereHas('process', function ($query) {
                    $query->where('stl_item_description.is_approved', 1);
                })
                ->whereHas('client', function ($query) {
                    $query->where('stl_client.is_approved', 1);
                });
        $currentOverAllStatusCounts = OrderCreation::query()->with('process', 'client')
                ->whereHas('process', function ($query) {
                    $query->where('stl_item_description.is_approved', 1);
                })
                ->whereHas('client', function ($query) {
                    $query->where('stl_client.is_approved', 1);
                });
    if(in_array($user->user_type_id, [1, 2, 3, 4, 5, 9, 23, 24])){
        if (in_array('All', $project_id) && !in_array('All', $client_id)) {
            $currentOverAllStatusCounts = OrderCreation::with('process', 'client')->select('id')
                ->where('status_id', '!=', 5)
                ->where('is_active', 1)
                ->whereNotNull('assignee_user_id')
                ->whereIn('process_id', $processIds)
                ->whereDate('order_date', '>=', $fromDate)
                ->whereDate('order_date', '<=', $toDate)
                ->whereHas('process', function ($query) use ($client_id) {
                    $query->whereIn('client_id', $client_id);
                });
    
            $carryOverAllStatusCounts = OrderCreation::with('process', 'client')->select('id')
                ->where('is_active', 1)
                ->whereIn('status_id', [1, 2, 4, 13, 14, 15, 16, 17, 18, 20])
                ->whereIn('process_id', $processIds)
                ->whereNotIn('status_id', [3, 5])
                ->whereNull('completion_date')
                ->whereDate('order_date', '<', $fromDate)
                ->whereHas('process', function ($query) use ($client_id) {
                    $query->whereIn('client_id', $client_id);
                });
    
            $getcurrentCompletedorderId = OrderCreation::with('process', 'client')->select('id')
                ->whereDate('completion_date', '>=', $fromDate)
                ->whereDate('completion_date', '<=', $toDate)
                ->whereIn('process_id', $processIds)
                ->where('status_id', 5)
                ->where('is_active', 1)
                ->whereHas('process', function ($query) use ($client_id) {
                    $query->whereIn('client_id', $client_id);
                });
    
            $getPreCompletedorderId = OrderCreation::with('process', 'client')
                ->select('id')
                ->whereDate('order_date', '<', $fromDate)
                ->whereIn('process_id', $processIds)
                ->where('status_id', 5)
                ->where('is_active', 1)
                ->whereHas('process', function ($query) use ($client_id) {
                    $query->whereIn('client_id', $client_id);
                });
    
        } else {
            if (!in_array('All', $project_id)) {
                // Case: project_id is specified (not 'All')
                $currentOverAllStatusCounts = OrderCreation::select('id')
                    ->whereIn('process_id', $project_id)
                    ->whereIn('process_id', $processIds)
                    ->whereNotNull('assignee_user_id')
                    ->where('status_id', '!=', 5)
                    ->where('is_active', 1)
                    ->whereDate('order_date', '>=', $fromDate)
                    ->whereDate('order_date', '<=', $toDate);
    
                $carryOverAllStatusCounts = OrderCreation::select('id')
                    ->where('is_active', 1)
                    ->whereIn('process_id', $project_id)
                    ->whereIn('process_id', $processIds)
                    ->whereIn('status_id', [1, 2, 4, 13, 14, 15, 16, 17, 18, 20])
                    ->whereNotIn('status_id', [3, 5])
                    ->whereNull('completion_date')
                    ->whereDate('order_date', '<', $fromDate);
    
                $getcurrentCompletedorderId = OrderCreation::select('id')
                    ->whereIn('process_id', $project_id)
                    ->whereIn('process_id', $processIds)
                    ->whereDate('completion_date', '>=', $fromDate)
                    ->whereDate('completion_date', '<=', $toDate)
                    ->where('status_id', 5)
                    ->where('is_active', 1);
    
                $getPreCompletedorderId = OrderCreation::select('id')
                    ->whereIn('process_id', $project_id)
                    ->whereIn('process_id', $processIds)
                    ->whereDate('order_date', '<', $fromDate)
                    ->where('status_id', 5)
                    ->where('is_active', 1);
    
            } else {
                $currentOverAllStatusCounts = OrderCreation::select('id')
                    ->whereDate('order_date', '>=', $fromDate)
                    ->whereDate('order_date', '<=', $toDate)
                    ->whereNotNull('assignee_user_id')
                    ->whereIn('process_id', $processIds)
                    ->where('status_id', '!=', 5)
                    ->where('is_active', 1);
    
                $carryOverAllStatusCounts = OrderCreation::select('id')
                    ->where('is_active', 1)
                    ->whereIn('process_id', $processIds)
                    ->whereIn('status_id', [1, 2, 4, 13, 14, 15, 16, 17, 18, 20])
                    ->whereNotIn('status_id', [3, 5])
                    ->whereNull('completion_date')
                    ->whereDate('order_date', '<', $fromDate);
    
                $getcurrentCompletedorderId = OrderCreation::select('id')
                    ->whereDate('completion_date', '>=', $fromDate)
                    ->whereDate('completion_date', '<=', $toDate)
                    ->whereIn('process_id', $processIds)
                    ->where('status_id', 5)
                    ->where('is_active', 1);
    
                $getPreCompletedorderId = OrderCreation::select('id')
                    ->whereDate('order_date', '<', $fromDate)
                    ->whereIn('process_id', $processIds)
                    ->where('status_id', 5)
                    ->where('is_active', 1);
            }
        }

    }elseif(!in_array($user->user_type_id, [1, 2, 3, 4, 5, 9])){
            if($user->user_type_id == 6){
                if (in_array('All', $project_id) && !in_array('All', $client_id)) {
                    $currentOverAllStatusCounts = OrderCreation::with('process', 'client')->select('id')
                        ->where('status_id', '!=', 5)
                        ->whereIn('process_id', $processIds)
                        ->whereNotNull('assignee_user_id')
                        ->where('is_active', 1)
                        ->where('assignee_user_id', $user->id)
                        ->whereDate('order_date', '>=', $fromDate)
                        ->whereDate('order_date', '<=', $toDate)
                        ->whereHas('process', function ($query) use ($client_id) {
                            $query->whereIn('client_id', $client_id);
                        });
            
                    $carryOverAllStatusCounts = OrderCreation::with('process', 'client')->select('id')
                        ->where('is_active', 1)
                        ->whereIn('status_id', [1, 2, 4, 13, 14, 15, 16, 17, 18, 20])
                        ->whereNotIn('status_id', [3, 5])
                        ->where('assignee_user_id', $user->id)
                        ->whereIn('process_id', $processIds)
                        ->whereNull('completion_date')
                        ->whereDate('order_date', '<', $fromDate)
                        ->whereHas('process', function ($query) use ($client_id) {
                            $query->whereIn('client_id', $client_id);
                        });
            
                    $getcurrentCompletedorderId = OrderCreation::with('process', 'client')->select('id')
                        ->whereDate('completion_date', '>=', $fromDate)
                        ->whereDate('completion_date', '<=', $toDate)
                        ->whereIn('process_id', $processIds)
                        ->where('assignee_user_id', $user->id)
                        ->where('status_id', 5)
                        ->where('is_active', 1)
                        ->whereHas('process', function ($query) use ($client_id) {
                            $query->whereIn('client_id', $client_id);
                        });
            
                    $getPreCompletedorderId = OrderCreation::with('process', 'client')
                        ->select('id')
                        ->whereDate('order_date', '<', $fromDate)
                        ->where('assignee_user_id', $user->id)
                        ->whereIn('process_id', $processIds)
                        ->where('status_id', 5)
                        ->where('is_active', 1)
                        ->whereHas('process', function ($query) use ($client_id) {
                            $query->whereIn('client_id', $client_id);
                        });
            
                } else {
                    if (!in_array('All', $project_id)) {
                        // Case: project_id is specified (not 'All')
                        $currentOverAllStatusCounts = OrderCreation::select('id')
                            ->whereIn('process_id', $project_id)
                            ->where('status_id', '!=', 5)
                            ->whereIn('process_id', $processIds)
                            ->where('assignee_user_id', $user->id)
                            ->where('is_active', 1)
                            ->whereDate('order_date', '>=', $fromDate)
                            ->whereDate('order_date', '<=', $toDate);
            
                        $carryOverAllStatusCounts = OrderCreation::select('id')
                            ->where('is_active', 1)
                            ->whereIn('process_id', $project_id)
                            ->whereIn('process_id', $processIds)
                            ->where('assignee_user_id', $user->id)
                            ->whereIn('status_id', [1, 2, 4, 13, 14, 15, 16, 17, 18, 20])
                            ->whereNotIn('status_id', [3, 5])
                            ->whereNull('completion_date')
                            ->whereDate('order_date', '<', $fromDate);
            
                        $getcurrentCompletedorderId = OrderCreation::select('id')
                            ->whereIn('process_id', $project_id)
                            ->whereIn('process_id', $processIds)
                            ->where('assignee_user_id', $user->id)
                            ->whereDate('completion_date', '>=', $fromDate)
                            ->whereDate('completion_date', '<=', $toDate)
                            ->where('status_id', 5)
                            ->where('is_active', 1);
            
                        $getPreCompletedorderId = OrderCreation::select('id')
                            ->whereIn('process_id', $project_id)
                            ->whereIn('process_id', $processIds)
                            ->where('assignee_user_id', $user->id)
                            ->whereDate('order_date', '<', $fromDate)
                            ->where('status_id', 5)
                            ->where('is_active', 1);
            
                    } else {
                        $currentOverAllStatusCounts = OrderCreation::select('id')
                            ->whereDate('order_date', '>=', $fromDate)
                            ->whereDate('order_date', '<=', $toDate)
                            ->whereIn('process_id', $processIds)
                            ->where('assignee_user_id', $user->id)
                            ->where('status_id', '!=', 5)
                            ->where('is_active', 1);
            
                        $carryOverAllStatusCounts = OrderCreation::select('id')
                            ->where('is_active', 1)
                            ->whereIn('process_id', $processIds)
                            ->where('assignee_user_id', $user->id)
                            ->whereIn('status_id', [1, 2, 4, 13, 14, 15, 16, 17, 18, 20])
                            ->whereNotIn('status_id', [3, 5])
                            ->whereNull('completion_date')
                            ->whereDate('order_date', '<', $fromDate);
            
                        $getcurrentCompletedorderId = OrderCreation::select('id')
                            ->whereDate('completion_date', '>=', $fromDate)
                            ->whereDate('completion_date', '<=', $toDate)
                            ->whereIn('process_id', $processIds)
                            ->where('assignee_user_id', $user->id)
                            ->where('status_id', 5)
                            ->where('is_active', 1);
            
                        $getPreCompletedorderId = OrderCreation::select('id')
                            ->whereDate('order_date', '<', $fromDate)
                            ->where('assignee_user_id', $user->id)
                            ->whereIn('process_id', $processIds)
                            ->where('status_id', 5)
                            ->where('is_active', 1);
                    }
                }
            }elseif($user->user_type_id == 7){
                if (in_array('All', $project_id) && !in_array('All', $client_id)) {
                    $currentOverAllStatusCounts = OrderCreation::with('process', 'client')->select('id')
                        ->where('status_id', '!=', 5)
                        ->whereNotIn('status_id', [1, 13])
                        ->where('is_active', 1)
                        ->where('assignee_qa_id', $user->id)
                        ->whereIn('process_id', $processIds)
                        ->whereDate('order_date', '>=', $fromDate)
                        ->whereDate('order_date', '<=', $toDate)
                        ->whereHas('process', function ($query) use ($client_id) {
                            $query->whereIn('client_id', $client_id);
                        });
            
                    $carryOverAllStatusCounts = OrderCreation::with('process', 'client')->select('id')
                        ->where('is_active', 1)
                        ->where('assignee_qa_id', $user->id)
                        ->whereIn('process_id', $processIds)
                        ->whereIn('status_id', [2, 4, 14])
                        ->whereNull('completion_date')
                        ->whereDate('order_date', '<', $fromDate)
                        ->whereHas('process', function ($query) use ($client_id) {
                            $query->whereIn('client_id', $client_id);
                        });
            
                    $getcurrentCompletedorderId = OrderCreation::with('process', 'client')->select('id')
                        ->whereDate('completion_date', '>=', $fromDate)
                        ->whereDate('completion_date', '<=', $toDate)
                        ->where('assignee_qa_id', $user->id)
                        ->whereIn('process_id', $processIds)
                        ->where('status_id', 5)
                        ->where('is_active', 1)
                        ->whereHas('process', function ($query) use ($client_id) {
                            $query->whereIn('client_id', $client_id);
                        });
            
                    $getPreCompletedorderId = OrderCreation::with('process', 'client')
                        ->select('id')
                        ->whereDate('order_date', '<', $fromDate)
                        ->where('assignee_qa_id', $user->id)
                        ->whereIn('process_id', $processIds)
                        ->where('status_id', 5)
                        ->where('is_active', 1)
                        ->whereHas('process', function ($query) use ($client_id) {
                            $query->whereIn('client_id', $client_id);
                        });
            
                } else {
                    if (!in_array('All', $project_id)) {
                        // Case: project_id is specified (not 'All')
                        $currentOverAllStatusCounts = OrderCreation::select('id')
                            ->whereIn('process_id', $project_id)
                            ->where('assignee_qa_id', $user->id)
                            ->whereIn('process_id', $processIds)
                            ->where('status_id', '!=', 5)
                            ->whereNotIn('status_id', [1, 13])
                            ->where('is_active', 1)
                            ->whereDate('order_date', '>=', $fromDate)
                            ->whereDate('order_date', '<=', $toDate);
            
                        $carryOverAllStatusCounts = OrderCreation::select('id')
                            ->where('is_active', 1)
                            ->whereIn('process_id', $project_id)
                            ->where('assignee_qa_id', $user->id)
                            ->whereIn('process_id', $processIds)
                            ->whereIn('status_id', [2, 4, 14])
                            ->whereNull('completion_date')
                            ->whereDate('order_date', '<', $fromDate);
            
                        $getcurrentCompletedorderId = OrderCreation::select('id')
                            ->whereIn('process_id', $project_id)
                            ->where('assignee_qa_id', $user->id)
                            ->whereIn('process_id', $processIds)
                            ->whereDate('completion_date', '>=', $fromDate)
                            ->whereDate('completion_date', '<=', $toDate)
                            ->where('status_id', 5)
                            ->where('is_active', 1);
            
                        $getPreCompletedorderId = OrderCreation::select('id')
                            ->whereIn('process_id', $project_id)
                            ->where('assignee_qa_id', $user->id)
                            ->whereIn('process_id', $processIds)
                            ->whereDate('order_date', '<', $fromDate)
                            ->where('status_id', 5)
                            ->where('is_active', 1);
            
                    } else {
                        $currentOverAllStatusCounts = OrderCreation::select('id')
                            ->whereDate('order_date', '>=', $fromDate)
                            ->whereDate('order_date', '<=', $toDate)
                            ->whereIn('process_id', $processIds)
                            ->where('assignee_qa_id', $user->id)
                            ->where('status_id', '!=', 5)
                            ->whereNotIn('status_id', [1, 13])
                            ->where('is_active', 1);
            
                        $carryOverAllStatusCounts = OrderCreation::select('id')
                            ->where('is_active', 1)
                            ->where('assignee_qa_id', $user->id)
                            ->whereIn('process_id', $processIds)
                            ->whereIn('status_id', [2, 4, 14])
                            ->whereNull('completion_date')
                            ->whereDate('order_date', '<', $fromDate);
            
                        $getcurrentCompletedorderId = OrderCreation::select('id')
                            ->whereDate('completion_date', '>=', $fromDate)
                            ->whereDate('completion_date', '<=', $toDate)
                            ->whereIn('process_id', $processIds)
                            ->where('assignee_qa_id', $user->id)
                            ->where('status_id', 5)
                            ->where('is_active', 1);
            
                        $getPreCompletedorderId = OrderCreation::select('id')
                            ->whereDate('order_date', '<', $fromDate)
                            ->where('assignee_qa_id', $user->id)
                            ->whereIn('process_id', $processIds)
                            ->where('status_id', 5)
                            ->where('is_active', 1);
                    }
                }

            }else if($user->user_type_id == 8){
                if (in_array('All', $project_id) && !in_array('All', $client_id)) {
                    $currentOverAllStatusCounts = OrderCreation::with('process', 'client')->select('id')
                        ->where('status_id', '!=', 5)
                        ->where(function ($query) use($user){
                            $query->where('assignee_user_id', $user->id)
                                ->orWhere('assignee_qa_id', $user->id);
                        })
                        ->where('is_active', 1)
                        ->whereDate('order_date', '>=', $fromDate)
                        ->whereDate('order_date', '<=', $toDate)
                        ->whereHas('process', function ($query) use ($client_id) {
                            $query->whereIn('client_id', $client_id);
                        });
            
                    $carryOverAllStatusCounts = OrderCreation::with('process', 'client')->select('id')
                        ->where('is_active', 1)
                        ->whereIn('status_id', [1, 2, 4, 13, 14, 15, 16, 17, 18, 20])
                        ->whereNotIn('status_id', [3,5])
                        ->where(function ($query) use($user){
                            $query->where('assignee_user_id', $user->id)
                                ->orWhere('assignee_qa_id', $user->id);
                        })
                        ->whereNull('completion_date')
                        ->whereDate('order_date', '<', $fromDate)
                        ->whereHas('process', function ($query) use ($client_id) {
                            $query->whereIn('client_id', $client_id);
                        });
            
                    $getcurrentCompletedorderId = OrderCreation::with('process', 'client')->select('id')
                        ->whereDate('completion_date', '>=', $fromDate)
                        ->whereDate('completion_date', '<=', $toDate)
                        ->where(function ($query) use($user){
                            $query->where('assignee_user_id', $user->id)
                                ->orWhere('assignee_qa_id', $user->id);
                        })
                        ->where('status_id', 5)
                        ->where('is_active', 1)
                        ->whereHas('process', function ($query) use ($client_id) {
                            $query->whereIn('client_id', $client_id);
                        });
            
                    $getPreCompletedorderId = OrderCreation::with('process', 'client')
                        ->select('id')
                        ->whereDate('order_date', '<', $fromDate)
                        ->where(function ($query) use($user){
                            $query->where('assignee_user_id', $user->id)
                                ->orWhere('assignee_qa_id', $user->id);
                        })
                        ->where('status_id', 5)
                        ->where('is_active', 1)
                        ->whereHas('process', function ($query) use ($client_id) {
                            $query->whereIn('client_id', $client_id);
                        });
            
                } else {
                    if (!in_array('All', $project_id)) {
                        // Case: project_id is specified (not 'All')
                        $currentOverAllStatusCounts = OrderCreation::select('id')
                            ->whereIn('process_id', $project_id)
                            ->where('status_id', '!=', 5)
                            ->where(function ($query) use($user){
                                $query->where('assignee_user_id', $user->id)
                                ->orWhere('assignee_qa_id', $user->id);
                        })
                            ->where('is_active', 1)
                            ->whereDate('order_date', '>=', $fromDate)
                            ->whereDate('order_date', '<=', $toDate);

                        $carryOverAllStatusCounts = OrderCreation::select('id')
                            ->where('is_active', 1)
                            ->whereIn('process_id', $project_id)
                            ->where(function ($query) use($user){
                                $query->where('assignee_user_id', $user->id)
                                ->orWhere('assignee_qa_id', $user->id);
                        })
                            ->whereIn('status_id', [1, 2, 4, 13, 14, 15, 16, 17, 18, 20])
                            ->whereNotIn('status_id', [3, 5])
                            ->whereNull('completion_date')
                            ->whereDate('order_date', '<', $fromDate);

                        $getcurrentCompletedorderId = OrderCreation::select('id')
                            ->whereIn('process_id', $project_id)
                            ->where(function ($query) use($user){
                                $query->where('assignee_user_id', $user->id)
                                ->orWhere('assignee_qa_id', $user->id);
                        })
                            ->whereDate('completion_date', '>=', $fromDate)
                            ->whereDate('completion_date', '<=', $toDate)
                            ->where('status_id', 5)
                            ->where('is_active', 1);
            
                        $getPreCompletedorderId = OrderCreation::select('id')
                            ->whereIn('process_id', $project_id)
                            ->where(function ($query) use($user){
                                $query->where('assignee_user_id', $user->id)
                                ->orWhere('assignee_qa_id', $user->id);
                        })
                            ->whereDate('order_date', '<', $fromDate)
                            ->where('status_id', 5)
                            ->where('is_active', 1);
            
                    } else {
                        $currentOverAllStatusCounts = OrderCreation::select('id')
                            ->whereDate('order_date', '>=', $fromDate)
                            ->whereDate('order_date', '<=', $toDate)
                            ->where(function ($query) use($user){
                                $query->where('assignee_user_id', $user->id)
                                ->orWhere('assignee_qa_id', $user->id);
                        })
                            ->where('status_id', '!=', 5)
                            ->where('is_active', 1);
            
                        $carryOverAllStatusCounts = OrderCreation::select('id')
                            ->where('is_active', 1)
                            ->where(function ($query) use($user){
                                $query->where('assignee_user_id', $user->id)
                                ->orWhere('assignee_qa_id', $user->id);
                        })
                            ->whereIn('status_id', [1, 2, 4, 13, 14, 15, 16, 17, 18, 20])
                            ->whereNotIn('status_id', [3, 5])
                            ->whereNull('completion_date')
                            ->whereDate('order_date', '<', $fromDate);

                        $getcurrentCompletedorderId = OrderCreation::select('id')
                            ->whereDate('completion_date', '>=', $fromDate)
                            ->whereDate('completion_date', '<=', $toDate)
                            ->where(function ($query) use($user){
                                $query->where('assignee_user_id', $user->id)
                                ->orWhere('assignee_qa_id', $user->id);
                        })
                            ->where('status_id', 5)
                            ->where('is_active', 1);

                        $getPreCompletedorderId = OrderCreation::select('id')
                            ->whereDate('order_date', '<', $fromDate)
                            ->where(function ($query) use($user){
                                $query->where('assignee_user_id', $user->id)
                                ->orWhere('assignee_qa_id', $user->id);
                        })
                            ->where('status_id', 5)
                            ->where('is_active', 1);
                    }
                }

            }else if($user->user_type_id == 22){
                if (in_array('All', $project_id) && !in_array('All', $client_id)) {
                    $currentOverAllStatusCounts = OrderCreation::with('process', 'client')->select('id')
                        ->where('status_id', '!=', 5)
                        ->where(function ($query) use($user){
                            $query->where('typist_id', $user->id)
                                ->orWhere('typist_qc_id', $user->id);
                        })
                        ->where('is_active', 1)
                        ->whereDate('order_date', '>=', $fromDate)
                        ->whereDate('order_date', '<=', $toDate)
                        ->whereHas('process', function ($query) use ($client_id) {
                            $query->whereIn('client_id', $client_id);
                        });

                    $carryOverAllStatusCounts = OrderCreation::with('process', 'client')->select('id')
                        ->where('is_active', 1)
                        ->whereIn('status_id', [1, 2, 4, 13, 14, 15, 16, 17, 18, 20])
                        ->whereNotIn('status_id', [3,5])
                        ->where(function ($query) use($user){
                            $query->where('typist_id', $user->id)
                                ->orWhere('typist_qc_id', $user->id);
                        })
                        ->whereNull('completion_date')
                        ->whereDate('order_date', '<', $fromDate)
                        ->whereHas('process', function ($query) use ($client_id) {
                            $query->whereIn('client_id', $client_id);
                        });

                    $getcurrentCompletedorderId = OrderCreation::with('process', 'client')->select('id')
                        ->whereDate('completion_date', '>=', $fromDate)
                        ->whereDate('completion_date', '<=', $toDate)
                        ->where(function ($query) use($user){
                            $query->where('typist_id', $user->id)
                                ->orWhere('typist_qc_id', $user->id);
                        })
                        ->where('status_id', 5)
                        ->where('is_active', 1)
                        ->whereHas('process', function ($query) use ($client_id) {
                            $query->whereIn('client_id', $client_id);
                        });

                    $getPreCompletedorderId = OrderCreation::with('process', 'client')
                        ->select('id')
                        ->whereDate('order_date', '<', $fromDate)
                        ->where(function ($query) use($user){
                            $query->where('typist_id', $user->id)
                                ->orWhere('typist_qc_id', $user->id);
                        })
                        ->where('status_id', 5)
                        ->where('is_active', 1)
                        ->whereHas('process', function ($query) use ($client_id) {
                            $query->whereIn('client_id', $client_id);
                        });

                } else {
                    if (!in_array('All', $project_id)) {
                        // Case: project_id is specified (not 'All')
                        $currentOverAllStatusCounts = OrderCreation::select('id')
                            ->whereIn('process_id', $project_id)
                            ->where('status_id', '!=', 5)
                            ->where(function ($query) use($user){
                                $query->where('typist_id', $user->id)
                                ->orWhere('typist_qc_id', $user->id);
                        })
                            ->where('is_active', 1)
                            ->whereDate('order_date', '>=', $fromDate)
                            ->whereDate('order_date', '<=', $toDate);

                        $carryOverAllStatusCounts = OrderCreation::select('id')
                            ->where('is_active', 1)
                            ->whereIn('process_id', $project_id)
                            ->where(function ($query) use($user){
                                $query->where('typist_id', $user->id)
                                ->orWhere('typist_qc_id', $user->id);
                        })
                            ->whereIn('status_id', [1, 2, 4, 13, 14, 15, 16, 17, 18, 20])
                            ->whereNotIn('status_id', [3, 5])
                            ->whereNull('completion_date')
                            ->whereDate('order_date', '<', $fromDate);

                        $getcurrentCompletedorderId = OrderCreation::select('id')
                            ->whereIn('process_id', $project_id)
                            ->where(function ($query) use($user){
                                $query->where('typist_id', $user->id)
                                ->orWhere('typist_qc_id', $user->id);
                        })
                            ->whereDate('completion_date', '>=', $fromDate)
                            ->whereDate('completion_date', '<=', $toDate)
                            ->where('status_id', 5)
                            ->where('is_active', 1);

                        $getPreCompletedorderId = OrderCreation::select('id')
                            ->whereIn('process_id', $project_id)
                            ->where(function ($query) use($user){
                                $query->where('typist_id', $user->id)
                                ->orWhere('typist_qc_id', $user->id);
                        })
                            ->whereDate('order_date', '<', $fromDate)
                            ->where('status_id', 5)
                            ->where('is_active', 1);

                    } else {
                        $currentOverAllStatusCounts = OrderCreation::select('id')
                            ->whereDate('order_date', '>=', $fromDate)
                            ->whereDate('order_date', '<=', $toDate)
                            ->where(function ($query) use($user){
                                $query->where('typist_id', $user->id)
                                ->orWhere('typist_qc_id', $user->id);
                        })
                            ->where('status_id', '!=', 5)
                            ->where('is_active', 1);

                        $carryOverAllStatusCounts = OrderCreation::select('id')
                            ->where('is_active', 1)
                            ->where(function ($query) use($user){
                                $query->where('typist_id', $user->id)
                                ->orWhere('typist_qc_id', $user->id);
                        })
                            ->whereIn('status_id', [1, 2, 4, 13, 14, 15, 16, 17, 18, 20])
                            ->whereNotIn('status_id', [3, 5])
                            ->whereNull('completion_date')
                            ->whereDate('order_date', '<', $fromDate);

                        $getcurrentCompletedorderId = OrderCreation::select('id')
                            ->whereDate('completion_date', '>=', $fromDate)
                            ->whereDate('completion_date', '<=', $toDate)
                            ->where(function ($query) use($user){
                                $query->where('typist_id', $user->id)
                                ->orWhere('typist_qc_id', $user->id);
                        })
                            ->where('status_id', 5)
                            ->where('is_active', 1);

                        $getPreCompletedorderId = OrderCreation::select('id')
                            ->whereDate('order_date', '<', $fromDate)
                            ->where(function ($query) use($user){
                                $query->where('typist_id', $user->id)
                                ->orWhere('typist_qc_id', $user->id);
                        })
                            ->where('status_id', 5)
                            ->where('is_active', 1);
                    }
                }

            }
    }
        
    
        // Get the IDs from the queries
        $getPreCompletedorderIdIds = $getPreCompletedorderId->pluck('id')->all();
        $getcurrentCompletedorderIdIds = $getcurrentCompletedorderId->pluck('id')->all();
    
        // Find common IDs
        $commonIds = array_intersect($getPreCompletedorderIdIds, $getcurrentCompletedorderIdIds);
    
        $preCompletedCount = OrderCreation::select('id')->whereIn('id', $commonIds);
    
        // Combine the queries using union
        $carryOverAllStatusCountsIds = $carryOverAllStatusCounts->union($preCompletedCount)->pluck('id')->toArray();
    
        // Get IDs from $currentOverAllStatusCounts
        $currentOverAllStatusCountsIds = $currentOverAllStatusCounts->union($getcurrentCompletedorderId)->pluck('id')->toArray();
    
        // Combine the final results
        $combinedIds = array_merge($carryOverAllStatusCountsIds, $currentOverAllStatusCountsIds);
    
        // Optionally, remove duplicate IDs if needed
        $combinedUniqueIds = array_unique($combinedIds);
    
        // Construct the final query
        $query = DB::table('oms_order_creations')
            ->leftJoin('stl_item_description', 'oms_order_creations.process_id', '=', 'stl_item_description.id')
            ->leftJoin('oms_state', 'oms_order_creations.state_id', '=', 'oms_state.id')
            ->leftJoin('county', 'oms_order_creations.county_id', '=', 'county.id')
            ->leftJoin('stl_client', 'stl_item_description.client_id', '=', 'stl_client.id')
            ->leftJoin('oms_status', 'oms_order_creations.status_id', '=', 'oms_status.id')
            ->leftJoin('oms_users as assignee_users', 'oms_order_creations.assignee_user_id', '=', 'assignee_users.id')
            ->leftJoin('oms_users as assignee_qas', 'oms_order_creations.assignee_qa_id', '=', 'assignee_qas.id')
            ->leftJoin('oms_users as typist_users', 'oms_order_creations.typist_id', '=', 'typist_users.id')
            ->leftJoin('oms_users as typist_qas', 'oms_order_creations.typist_qc_id', '=', 'typist_qas.id')
            ->leftJoin('oms_users as associate_names', 'oms_order_creations.associate_id', '=', 'associate_names.id')
            ->leftJoin('stl_lob', 'stl_item_description.lob_id', '=', 'stl_lob.id')
            ->leftJoin('oms_tier','oms_order_creations.tier_id', '=', 'oms_tier.id')
            ->leftJoin('stl_process', 'oms_order_creations.process_type_id', '=', 'stl_process.id')

            ->select(
                'oms_order_creations.id',
                'oms_order_creations.order_id as order_id',
                'oms_order_creations.status_id as status_id',
                'oms_order_creations.order_date as order_date',
                'oms_order_creations.process_type_id as process_type_id',
                'stl_item_description.project_code as project_code',
                'stl_item_description.qc_enabled as qc_enabled',
                'stl_item_description.tat_value as tat_value',
                'oms_state.short_code as short_code',
                'county.county_name as county_name',
                'oms_order_creations.assignee_user_id',
                'oms_order_creations.assignee_qa_id',
                'oms_order_creations.associate_id',
                'oms_order_creations.typist_id',
                'oms_order_creations.typist_qc_id',
                'stl_lob.name as lob_name',
                'stl_process.name as process_name',
                'stl_client.client_name',
                'stl_item_description.client_id',
                'stl_item_description.process_name as process', 
                'oms_tier.Tier_id as tier_name',
                DB::raw('DATE_FORMAT(oms_order_creations.created_at, "%m/%d/%Y %H:%i:%s") as created_at'),
                DB::raw('CONCAT(assignee_users.emp_id, " (", assignee_users.username, ")") as assignee_user'),
                DB::raw('CONCAT(assignee_qas.emp_id, " (", assignee_qas.username, ")") as assignee_qa'),
                DB::raw('CONCAT(typist_users.emp_id, " (", typist_users.username, ")") as typist_user'),
                DB::raw('CONCAT(typist_qas.emp_id, " (", typist_qas.username, ")") as typist_qa'),
                DB::raw('CONCAT(associate_names.emp_id, " (", associate_names.username, ")") as associate_name')
            )
            ->where('oms_order_creations.is_active', 1)
            ->where('stl_item_description.is_approved', 1)
            ->where('stl_client.is_approved', 1);

            $currentYet_to_assign = clone $query;

            $query->whereIn('oms_order_creations.id', $combinedUniqueIds);
            
        if ($request->status != 'All') {
            $query->where('oms_order_creations.status_id', $request->status);
        }

        if($request->status == 6){
            if(in_array('All', $project_id) && !in_array('All', $client_id)){
            $query = $currentYet_to_assign->whereNull('assignee_user_id')
                // ->whereNull('assignee_qa_id')
                ->whereIn('stl_item_description.client_id', $client_id)
            ->whereDate('order_date', '>=', $fromDate)
            ->whereDate('order_date', '<=', $toDate);
            }elseif(!in_array('All', $project_id)){
                $query = $currentYet_to_assign->whereNull('assignee_user_id')
                // ->whereNull('assignee_qa_id')
                ->whereIn('oms_order_creations.process_id', $project_id) 
                ->whereDate('order_date', '>=', $fromDate)
                ->whereDate('order_date', '<=', $toDate);
            }else{
                $query = $currentYet_to_assign->whereNull('assignee_user_id')
                // ->whereNull('assignee_qa_id')
                ->whereDate('order_date', '>=', $fromDate)
                ->whereDate('order_date', '<=', $toDate);
            }
           
        }
        if (Auth::user()->hasRole('Typist/Typist_Qcer')) {
            $query->where('oms_order_creations.status_id', '!=', 1);
        }
    }
    $query->whereIn('oms_order_creations.process_id', $processIds);

    // if ($request->status == 'tax'){
    //     $query->where('oms_order_creations.tax_bucket', 1);
    // }

    if ($searchType == 1 && !empty($searchInputs)) {

        $searchArray = explode(',', $searchInputs);
    
        $searchArray = array_map('trim', $searchArray);
    
        $query->where(function ($query) use ($searchArray) {
            foreach ($searchArray as $searchTerm) {
                $query->orWhere('oms_order_creations.order_id', 'like', '%' . $searchTerm . '%');
            }
        });
    }
    
   
    if ($searchType == 2 && !empty($searchInputs)) {
        $query->where('stl_item_description.project_code', 'like', '%' . $searchInputs . '%');
    }

    if ($searchType == 3 && !empty($searchInputs)) {
        $query->where('stl_client.client_name', 'like', '%' . $searchInputs . '%');
    }

    if ($searchType == 4 && !empty($searchInputs)) {
        $query->where('stl_lob.name', 'like', '%' . $searchInputs . '%');
    }

    if ($searchType == 5 && !empty($searchInputs)) {
        $query->where('stl_process.name', 'like', '%' . $searchInputs . '%');
    }
    if ($searchType == 6 && !empty($searchInputs)) {
        $query->where( 'stl_item_description.process_name', 'like', '%' . $searchInputs . '%');
    }

    if ($searchType == 7 && !empty($searchInputs)) {
        $query->where('oms_tier.Tier_id', 'like', '%' . $searchInputs . '%');
    }
    if ($searchType == 8 && !empty($searchInputs)) {
        $query->where('oms_state.short_code', 'like', '%' . $searchInputs . '%');
    }
    if ($searchType == 9 && !empty($searchInputs)) {
        $query->where('county.county_name', 'like', '%' . $searchInputs . '%');
    }
    if ($searchType == 10 && !empty($searchInputs)) {
        preg_match('/\((.*?)\)/', $searchInputs, $matches);
        $contentInsideParentheses = $matches[1] ?? $searchInputs;

        $query->where(function($q) use ($contentInsideParentheses) {
            $q->where('assignee_users.username', 'like', '%' . $contentInsideParentheses . '%')
              ->orWhere(DB::raw('CONCAT(assignee_users.emp_id, " (", assignee_users.username, ")")'), 'like', '%' . $contentInsideParentheses . '%');
        });
    }
    if ($searchType == 11 && !empty($searchInputs)) {
        preg_match('/\((.*?)\)/', $searchInputs, $matches);
        $contentInsideParentheses = $matches[1] ?? $searchInputs;

        $query->where(function($q) use ($contentInsideParentheses) {
            $q->where('assignee_qas.username', 'like', '%' . $contentInsideParentheses . '%')
              ->orWhere(DB::raw('CONCAT(assignee_qas.emp_id, " (", assignee_qas.username, ")")'), 'like', '%' . $contentInsideParentheses . '%');
        });
    }

    if ($searchType == 12 && !empty($searchInputs)) {
        preg_match('/\((.*?)\)/', $searchInputs, $matches);
        $contentInsideParentheses = $matches[1] ?? $searchInputs;

        $query->where(function($q) use ($contentInsideParentheses) {
            $q->where('typist_users.username', 'like', '%' . $contentInsideParentheses . '%')
              ->orWhere(DB::raw('CONCAT(typist_users.emp_id, " (", typist_users.username, ")")'), 'like', '%' . $contentInsideParentheses . '%');
        });
    }

    if ($searchType == 13 && !empty($searchInputs)) {
        preg_match('/\((.*?)\)/', $searchInputs, $matches);
        $contentInsideParentheses = $matches[1] ?? $searchInputs;

        $query->where(function($q) use ($contentInsideParentheses) {
            $q->where('typist_qas.username', 'like', '%' . $contentInsideParentheses . '%')
              ->orWhere(DB::raw('CONCAT(typist_qas.emp_id, " (", typist_qas.username, ")")'), 'like', '%' . $contentInsideParentheses . '%');
        });
    }


    if ($tat_zone_filter) {
        // Get the current time in America/New_York timezone
        $ny_time_zone = new DateTimeZone("America/New_York");
        $current_time = new DateTime("now", $ny_time_zone); // Current time in NY timezone

        switch ($tat_zone_filter) {
            case 1:
                // Brown color (out of TAT)
                $query->whereRaw('TIMESTAMPDIFF(HOUR, oms_order_creations.order_date, ?) > (stl_item_description.tat_value / 4) * 4', [$current_time->format('Y-m-d H:i:s')]);
                break;

            case 2:
                // Blue color (super rush)
                $query->whereRaw('TIMESTAMPDIFF(HOUR, oms_order_creations.order_date, ?) BETWEEN (stl_item_description.tat_value / 4) * 3 AND (stl_item_description.tat_value / 4) * 4', [$current_time->format('Y-m-d H:i:s')]);
                break;

            case 3:
                // Red color (rush)
                $query->whereRaw('TIMESTAMPDIFF(HOUR, oms_order_creations.order_date, ?) BETWEEN (stl_item_description.tat_value / 4) * 2 AND (stl_item_description.tat_value / 4) * 3', [$current_time->format('Y-m-d H:i:s')]);
                break;

            case 4:
                // Priority
                $query->whereRaw('TIMESTAMPDIFF(HOUR, oms_order_creations.order_date, ?) BETWEEN (stl_item_description.tat_value / 4) * 1 AND (stl_item_description.tat_value / 4) * 2', [$current_time->format('Y-m-d H:i:s')]);
                break;

            case 5:
                // Non-Priority
                $query->whereRaw('TIMESTAMPDIFF(HOUR, oms_order_creations.order_date, ?) <= (stl_item_description.tat_value / 4) * 1', [$current_time->format('Y-m-d H:i:s')]);
                break;
        }
    }


        return DataTables::of($query)
        ->addColumn('checkbox', function ($order) use ($user){
            if(in_array($user->user_type_id, [6, 7, 8])) {
                return '<span class="px-2 py-2 rounded text-white assign-me ml-2" id="assign_me_' . ($order->id ?? '') . '">Assign</span>';
            } else {
                return '<input class="checkbox-table check-one mx-2" data-id="' . ($order->client_id ?? '') . '" type="checkbox" value="' . ($order->id ?? '') . '" id="logs' . ($order->id ?? '') . '" name="orders[]">';
            }
        })
        ->addColumn('action', function ($order) {
            return '<td><div class="row mb-0"><div class="edit_order col-6" style="cursor: pointer;" data-id="' . ($order->id ?? '') . '"><img class="menuicon tbl_editbtn" src="/assets/images/edit.svg" />&nbsp;</div><div class="col-6"><span class="dripicons-trash delete_order text-danger" style="font-size:14px; cursor: pointer;" data-id="' . ($order->id ?? '') . '"></span></div></div></td>';
        })
        ->orderColumn('order_id', function($query, $order) {
            $query->orderBy('oms_order_creations.order_id', $order);
        })
        ->orderColumn('lob_name', function($query, $order) {
            $query->orderBy('stl_lob.name', $order);
        })
        ->orderColumn('process_name', function($query, $order) {
            $query->orderBy('stl_process.name', $order);
        })
        ->orderColumn('order_date', function($query, $order) {
            $query->orderBy('oms_order_creations.order_date', $order);
        })
        ->filterColumn('lob_name', function($order, $keyword) {
            $sql = "stl_lob.name  like ?";
            $order->whereRaw($sql, ["%{$keyword}%"]);
        })
        ->filterColumn('process_name', function($order, $keyword) {
            $sql = "stl_process.name  like ?";
            $order->whereRaw($sql, ["%{$keyword}%"]);
        })
        ->filterColumn('project_code', function($order, $keyword) {
            $sql = "stl_item_description.project_code  like ?";
            $order->whereRaw($sql, ["%{$keyword}%"]);
        })
        ->filterColumn('order_id', function($order, $keyword) {
            $keywords = explode(',', $keyword);
            $placeholders = [];
            foreach ($keywords as $key => $value) {
                $placeholders[] = '?';
            }
            $sql = "oms_order_creations.order_id IN (" . implode(',', $placeholders) . ")";
            
            $order->whereRaw($sql, $keywords);
        })
        ->filterColumn('short_code', function($order, $keyword) {
            $sql = "oms_state.short_code  like ?";
            $order->whereRaw($sql, ["%{$keyword}%"]);
        })
        ->filterColumn('county_name', function($order, $keyword) {
            $sql = "county.county_name  like ?";
            $order->whereRaw($sql, ["%{$keyword}%"]);
        })
        ->filterColumn('assignee_user', function($order, $keyword) {
            $sql = 'CONCAT(assignee_users.emp_id, " (", assignee_users.username, ")")  like ?';
            $order->whereRaw($sql, ["%{$keyword}%"]);
        })
        //new  'stl_client.client_name',
        ->filterColumn('client_name', function($query, $keyword) {
            $query->whereRaw('stl_client.client_name like ?', ["%{$keyword}%"]);
        })
        ->filterColumn('process', function($query, $keyword) {
            $query->whereRaw('stl_item_description.process_name like ?', ["%{$keyword}%"]);
        })
        ->filterColumn('tier_name', function($query, $keyword) {
            $query->whereRaw('oms_tier.Tier_id like ?', ["%{$keyword}%"]);
        })
        
        ->filterColumn('assignee_qa', function($order, $keyword) {
            $sql = 'CONCAT(assignee_qas.emp_id, " (", assignee_qas.username, ")")  like ?';
            $order->whereRaw($sql, ["%{$keyword}%"]);
        })
        ->filterColumn('associate_name', function($order, $keyword) {
            $sql = 'CONCAT(associate_names.emp_id, " (", associate_names.username, ")")  like ?';
            $order->whereRaw($sql, ["%{$keyword}%"]);
        })
        ->filterColumn('typist_user', function($query, $keyword) {
            $sql = 'CONCAT(typist_users.emp_id, " (", typist_users.username, ")")  like ?';
            $order->whereRaw($sql, ["%{$keyword}%"]);        })
        ->filterColumn('typist_qa', function($query, $keyword) {
            $sql = 'CONCAT(typist_qas.emp_id, " (", typist_qas.username, ")")  like ?';
            $order->whereRaw($sql, ["%{$keyword}%"]);
        })
        ->addColumn('status', function ($order) use ($request) {
                if (in_array($order->client_id, [82, 84, 85, 86, 87, 89, 91, 88, 2, 13, 90, 92, 100, 99]))
                {
                                $statusMapping = [];
                    if (Auth::user()->hasRole('Typist') && in_array($order->process_type_id, [12, 7])) {
                                $statusMapping = [
                                                1 => 'WIP',
                                    14 => 'Clarification',
                                    4 => 'Send for QC',
                                    16 => 'Typing',
                                    17 => 'Typing QC',
                                    18 => 'Ground Abstractor',
                                    2 => 'Hold',
                                    5 => 'Completed',
                                    20 => 'Partially Cancelled',
                                    3 => 'Cancelled',
                                ];
                                            if(in_array($order->client_id, [82]) && in_array($order->process_type_id, [7])){
                                                $statusMapping[15] = 'Doc Purchase';
                                            }
                    } elseif (Auth::user()->hasRole('Typist/Qcer')  && in_array($order->process_type_id, [12, 7])) {
                            $statusMapping = [
                                            1 => 'WIP',
                                14 => 'Clarification',
                                4 => 'Send for QC', 
                                16 => 'Typing',
                                17 => 'Typing QC',
                                18 => 'Ground Abstractor',
                                2 => 'Hold',
                                5 => 'Completed',
                                20 => 'Partially Cancelled',
                                3 => 'Cancelled',
                            ];
                                        if (in_array($order->status_id, [16, 17])) {
                                            unset($statusMapping[4]);
                                        }
                                        if(in_array($order->client_id, [82]) && in_array($order->process_type_id, [7])){
                                            $statusMapping[15] = 'Doc Purchase';
                                        }

                            }elseif ((Auth::user()->hasRole('Typist') || Auth::user()->hasRole('Typist/Qcer'))  && in_array($order->process_type_id, [2, 4, 6, 8, 9, 16])) {
                                $statusMapping = [
                                    14 => 'Clarification',
                                    18 => 'Ground Abstractor',
                                    2 => 'Hold',
                                    5 => 'Completed',
                                    20 => 'Partially Cancelled',
                                    3 => 'Cancelled',
                                    16 => 'Typing',
                                    17 => 'Typing QC',
                                ];

                                if(in_array($order->client_id, [82]) && in_array($order->process_type_id, [7, 8, 9])){
                                    $statusMapping[15] = 'Doc Purchase';
                                }

                }elseif (Auth::user()->hasRole('Typist/Typist_Qcer')) {
                    $statusMapping = [
                        
                        14 => 'Clarification',
                        4 => 'Send for QC',
                                16 => 'Typing',
                        17 => 'Typing QC',                        
                        18 => 'Ground Abstractor',
                                2 => 'Hold',
                                5 => 'Completed',
                        20 => 'Partially Cancelled',
                                3 => 'Cancelled',
                            ];
                                if (in_array($order->status_id, [16, 17])) {
                                    unset($statusMapping[4]);
                            }
                            if(in_array($order->client_id, [82]) && in_array($order->process_type_id, [7])){
                                $statusMapping[15] = 'Doc Purchase';
                            }
                            if(in_array($order->process_type_id, [12, 7])){
                                $statusMapping[1] = 'WIP';
                                $statusMapping[4] = 'Send for QC';
                            }

                            if (Auth::user()->hasRole('Typist/Typist_Qcer') && in_array($order->process_type_id, [2, 4, 6, 8, 9, 16])) {
                                unset($statusMapping[4]);
                                $statusMapping[15] = 'Doc Purchase';
                            }
                            
                        }
        
                    elseif((!$order->typist_id  && $order->status_id == 16 )||($order->typist_id && $order->status_id == 16 ) || (!$order->typist_id  && $order->status_id == 17 ) || ($order->typist_id && $order->status_id == 17 )){
                                $statusMapping = [];
                                $statusMapping = [
                                    14 => 'Clarification',
                                    16 => 'Typing',
                                    17 => 'Typing QC',
                                    18 => 'Ground Abstractor',
                                    2 => 'Hold',
                                    5 => 'Completed',
                                    20 => 'Partially Cancelled',
                                    3 => 'Cancelled',
                                ];
                                if(in_array($order->client_id, [82]) || in_array($order->process_type_id, [7])){
                                    $statusMapping[15] = 'Doc Purchase';
                                }
                                if(in_array($order->process_type_id, [12, 7])){
                                    $statusMapping[1] = 'WIP';
                                    $statusMapping[4] = 'Send for QC';
                                }
                                
                 }
                elseif (Auth::user()->hasRole('Process') || Auth::user()->hasRole('Qcer') || Auth::user()->hasRole('PM/TL') || Auth::user()->hasRole('Business Head') || Auth::user()->hasRole('AVP') || Auth::user()->hasRole('VP')) {
                    $statusMapping = [
                        1 => 'WIP',
                        15 => 'Doc Purchase',
                        14 => 'Clarification',
                        4 => 'Send for QC',
                        16 => 'Typing',
                        17 => 'Typing QC',
                        18 => 'Ground Abstractor',
                        2 => 'Hold',
                        5 => 'Completed',
                        20 => 'Partially Cancelled',
                        3 => 'Cancelled',
                    ];
                        if (!in_array($order->process_type_id, [12, 7, 8, 9]) || in_array($order->client_id, [84, 85, 86, 13, 2, 87, 88, 89, 90, 91, 92])) {
                        unset($statusMapping[15]);
                    }
                            
                            if(Auth::user()->hasRole('PM/TL') && in_array($order->client_id, [82]) && in_array($order->process_type_id, [7, 8, 9])){
                                $statusMapping[15] = 'Doc Purchase';
                            }

                        if (!in_array($order->process_type_id, [12, 7])) {
                            unset($statusMapping[16]);
                            unset($statusMapping[17]);
                        }
                    
                            if (in_array($order->status_id, [16, 17]) && in_array($order->process_type_id, [2, 4, 6, 8, 9, 16])) {
                                unset($statusMapping[1]);
                                unset($statusMapping[4]);
                            }
                            if ((Auth::user()->hasRole('PM/TL') || Auth::user()->hasRole('Business Head') || Auth::user()->hasRole('AVP') || Auth::user()->hasRole('VP') || Auth::user()->hasRole('SPOC')) && in_array($order->process_type_id, [2, 4, 6, 8, 9, 16]) && in_array($order->status_id, [14])) {
                                unset($statusMapping[1]);
                                unset($statusMapping[4]);
                                unset($statusMapping[13]);
                                $statusMapping[16] = 'Typing';
                                $statusMapping[17] = 'Typing QC';
                            }
            
                } else {
                                $statusMapping = [
                                    1 => 'WIP',
                                    15 => 'Doc Purchase',
                                    14 => 'Clarification',
                                    4 => 'Send for QC',
                                    16 => 'Typing',
                                    17 => 'Typing QC',
                                    18 => 'Ground Abstractor',
                                    2 => 'Hold',
                                    5 => 'Completed',
                                    20 => 'Partially Cancelled',
                                    3 => 'Cancelled',
                                ];
                            if (!in_array($order->process_type_id, [12, 7, 8, 9]) || in_array($order->client_id, [84, 85, 86])) {
                            unset($statusMapping[15]);
                        }
                            if (!in_array($order->process_type_id, [12, 7])) {
                                unset($statusMapping[16]);
                                unset($statusMapping[17]);
                            }

                        if (in_array($order->status_id, [16, 17]) && in_array($order->process_type_id, [2, 4, 6, 8, 9, 16])) {
                            unset($statusMapping[1]);
                            unset($statusMapping[4]);
                        }
                        
                }
            
                        }else{
                    if($order->assignee_qa_id) {
                        if (Auth::user()->hasRole('Qcer') || Auth::user()->hasRole('PM/TL')){
                            $statusMapping = [];
                                $statusMapping = [
                                    1 => 'WIP',
                                    13 => 'Coversheet Prep',
                                    14 => 'Clarification',
                                    18 => 'Ground Abstractor',
                                    4 => 'Send for QC',
                                    2 => 'Hold',
                                    5 => 'Completed',
                                    20 => 'Partially Cancelled',
                                    3 => 'Cancelled',
                                ];
                                if (in_array($order->status_id, [16, 17])) {
                                    unset($statusMapping[1]);
                                    unset($statusMapping[4]);
                                }

                                if ((Auth::user()->hasRole('PM/TL')) && in_array($order->process_type_id, [2, 4, 6, 8, 9, 16]) && in_array($order->status_id, [14])) {
                                    unset($statusMapping[1]);
                                    unset($statusMapping[4]);
                                    unset($statusMapping[13]);
                                    $statusMapping[16] = 'Typing';
                                    $statusMapping[17] = 'Typing QC';
                                }
                        }elseif($order->assignee_qa_id && Auth::user()->hasRole('Process') && $order->status_id == 1 ){
                            $statusMapping = [];
                            $statusMapping = [
                                1 => 'WIP',
                                13 => 'Coversheet Prep',
                                14 => 'Clarification',
                                18 => 'Ground Abstractor',
                                4 => 'Send for QC',
                                2 => 'Hold',
                                5 => 'Completed',
                                20 => 'Partially Cancelled',
                                3 => 'Cancelled',
                            ];
                            if (in_array($order->status_id, [16, 17])) {
                                unset($statusMapping[1]);
                                unset($statusMapping[4]);
                            }
                        }else{
                            $statusMapping = [];
                                $statusMapping = [
                                    1 => 'WIP',
                                    13 =>'Coversheet Prep',
                                    14 => 'Clarification',
                                    18 => 'Ground Abstractor',
                                    4 => 'Send for QC',
                                    2 => 'Hold',
                                    5 => 'Completed',
                                    20 => 'Partially Cancelled',
                                    3 => 'Cancelled',
                                ];
                                if (in_array($order->status_id, [16, 17])) {
                                    unset($statusMapping[1]);
                                    unset($statusMapping[4]);
                                }
                                if ((Auth::user()->hasRole('PM/TL') || Auth::user()->hasRole('Business Head') || Auth::user()->hasRole('AVP') || Auth::user()->hasRole('VP') || Auth::user()->hasRole('SPOC')) && in_array($order->process_type_id, [2, 4, 6, 8, 9, 16]) && in_array($order->status_id, [14])) {
                                    unset($statusMapping[1]);
                                    unset($statusMapping[4]);
                                    unset($statusMapping[13]);
                                    $statusMapping[16] = 'Typing';
                                    $statusMapping[17] = 'Typing QC';
                                }
                        }
                            } else {

                            if (!$order->assignee_qa_id && Auth::user()->hasRole('PM/TL'))
                                {
                            $statusMapping = [];
                            $statusMapping = [
                                1 => 'WIP',
                                13 => 'Coversheet Prep',
                                14 => 'Clarification',
                                18 => 'Ground Abstractor',
                                4 => 'Send for QC',
                                2 => 'Hold',
                                5 => 'Completed',
                                20 => 'Partially Cancelled',
                                3 => 'Cancelled',
                            ];
                                        if (in_array($order->status_id, [16, 17])) {
                                            unset($statusMapping[1]);
                                            unset($statusMapping[4]);
                                        }
                                        if (in_array($order->status_id, [16, 17]) && in_array($order->process_type_id, [2, 4, 6, 8, 9, 16]) && Auth::user()->hasRole('PM/TL')) {
                                            $statusMapping[16] = 'Typing';
                                            $statusMapping[17] = 'Typing QC';
                                            unset($statusMapping[13]);
                                        }
                                        if ((Auth::user()->hasRole('PM/TL')) && in_array($order->process_type_id, [2, 4, 6, 8, 9, 16]) && in_array($order->status_id, [14])) {
                                            unset($statusMapping[1]);
                                            unset($statusMapping[4]);
                                            unset($statusMapping[13]);
                                            $statusMapping[16] = 'Typing';
                                            $statusMapping[17] = 'Typing QC';
                                        }
                        }elseif((!$order->assignee_qa_id && Auth::user()->hasRole('Process') && $order->status_id == 1 )||(!$order->assignee_qa_id && Auth::user()->hasRole('Process') && $order->status_id == 3 )){
                            $statusMapping = [];
                            $statusMapping = [
                                1 => 'WIP',
                                13 => 'Coversheet Prep',
                                14 => 'Clarification',
                                4 => 'Send for QC',
                                18 => 'Ground Abstractor',
                                2 => 'Hold',
                                5 => 'Completed',
                                20 => 'Partially Cancelled',
                                3 => 'Cancelled',
                            ];
                                    if (in_array($order->status_id, [16, 17])) {
                                        unset($statusMapping[1]);
                                        unset($statusMapping[4]);
                                    }
                            }elseif((!$order->typist_id  && $order->status_id == 16 )||($order->typist_id && $order->status_id == 16 )|| (!$order->typist_id  && $order->status_id == 17 )||($order->typist_id && $order->status_id == 17 )){
                                    $statusMapping = [];
                                    $statusMapping = [
                                        13 => 'Coversheet Prep',
                                        14 => 'Clarification',
                                        16 => 'Typing',
                                        17 => 'Typing QC',
                                        18 => 'Ground Abstractor',
                                        2 => 'Hold',
                                        5 => 'Completed',
                                        20 => 'Partially Cancelled',
                                        3 => 'Cancelled',
                                    ];

                                    if (in_array($order->status_id, [16, 17])) {
                                        unset($statusMapping[13]);
                                    }
                                    if ((Auth::user()->hasRole('PM/TL')) && in_array($order->process_type_id, [2, 4, 6, 8, 9, 16]) && in_array($order->status_id, [14])) {
                                        unset($statusMapping[1]);
                                        unset($statusMapping[4]);
                                        unset($statusMapping[13]);
                                        $statusMapping[16] = 'Typing';
                                        $statusMapping[17] = 'Typing QC';
                                    }
                                }
                            else{
                            $statusMapping = [];
                            $statusMapping = [
                                1 => 'WIP',
                                13 => 'Coversheet Prep',
                                14 => 'Clarification',
                                4 => 'Send for QC',
                                18 => 'Ground Abstractor',
                                2 => 'Hold',
                                5 => 'Completed',
                                20 => 'Partially Cancelled',
                                3 => 'Cancelled',

                            ];
                            if (in_array($order->client_id, [2,13])) {
                                unset($statusMapping[13]);
                            }
                                    if (in_array($order->status_id, [16, 17])) {
                                        unset($statusMapping[1]);
                                        unset($statusMapping[4]);
                                    }

                                    if ((Auth::user()->hasRole('Typist/Qcer') || Auth::user()->hasRole('Typist') || Auth::user()->hasRole('Typist/Typist_Qcer')) && in_array($order->process_type_id, [2, 4, 6, 8, 9, 16])) {
                                        unset($statusMapping[1]);
                                        unset($statusMapping[13]);
                                        unset($statusMapping[4]);

                                        $statusMapping[16] = 'Typing';  // Ensure 16 is set to 'Typing'
                                        $statusMapping[17] = 'Typing QC';  // Set 17 to a desired status name (replace with actual status name)

                                    }
                                    if ((Auth::user()->hasRole('PM/TL') || Auth::user()->hasRole('Business Head') || Auth::user()->hasRole('AVP') || Auth::user()->hasRole('VP') || Auth::user()->hasRole('SPOC')) && in_array($order->process_type_id, [2, 4, 6, 8, 9, 16]) && in_array($order->status_id, [14])) {
                                        unset($statusMapping[1]);
                                        unset($statusMapping[4]);
                                        unset($statusMapping[13]);
                                        $statusMapping[16] = 'Typing';
                                        $statusMapping[17] = 'Typing QC';
                                    }
                        }
                        }
                    }

        $user = Auth::user();

        $disabled = "";
        $makedisable = "";

        if($user->user_type_id == 8){
            if (($order->status_id == 1 && $user->user_type_id == 8 && $order->assignee_qa_id != Auth::id()) ||
             ($order->status_id == 1 && $user->user_type_id == 8 && $order->assignee_user_id == Auth::id() && $order->assignee_qa_id == Auth::id())) {
                $disabled = '';
                $makedisable = '';
            // } elseif (($order->status_id == 4 && $user->user_type_id == 8 && $order->assignee_user_id != Auth::id() && $order->assignee_qa_id == Auth::id()) ||
            //               ($order->status_id == 4 && $user->user_type_id == 8 && $order->assignee_user_id == Auth::id() && $order->assignee_qa_id ==  Auth::id()) ||
            //                ($order->status_id == 4 && $user->user_type_id == 8 && $order->assignee_user_id == Auth::id() && $order->assignee_qa_id == null)) {
            //     $disabled = '';
            //     $makedisable = '';
            } elseif ($order->status_id == 4 && $user->user_type_id == 8){
                $disabled = '';
                $makedisable = '';
            }elseif (($order->status_id == 14)){
                $disabled = '';
                $makedisable = '';
            }
            else {
                $disabled = 'readonly';
                $makedisable = 'select-disabled';
            }
        }

        if($order->status_id == 16 && ($order->typist_id == null ||$order->typist_id == "null" )){
             $disabled = 'readonly';
            $makedisable = 'select-disabled';
        }

        if($order->status_id == 17 && ($order->typist_qc_id == null ||$order->typist_qc_id == "null" )){
                $disabled = 'readonly';
                $makedisable = 'select-disabled';
            }
       if($order->status_id == 4 && $user->user_type_id == 7 && ($order->assignee_qa_id == null ||$order->assignee_qa_id == "null" )){
        $disabled = 'readonly';
       $makedisable = 'select-disabled';
        }

        return '<select style="width:100%" class="status-dropdown ' . $makedisable . ' form-control" data-row-id="' . $order->id . '" ' . $disabled . '>' .
            collect($statusMapping)->map(function ($value, $key) use ($order) {
                return '<option value="' . $key . '" ' . ($key == $order->status_id ? 'selected' : '') . '>' . $value . '</option>';
            })->join('') .
            '</select>';
        })

        ->addColumn('order_date', function ($order) {
            return $order->order_date ? date('m/d/Y H:i:s', strtotime($order->order_date)) : '';
        })

        ->addColumn('lob_name', function ($order) {
            return $order->lob_name ? $order->lob_name : '';
        })

        ->addColumn('process_name', function ($order) {
            return $order->process_name ? $order->process_name : '';
        })
        ->addColumn('order_id', function ($order) {
            $user = Auth::user();
            $orderId = '';
        
            $currentDateTime = Carbon::now('America/New_York');
            $tatValueInHours = $order->tat_value / 4; 
            $orderDate = $order->order_date;        
            $elapsedHours = $currentDateTime->diffInHours($orderDate); 
            if($order->status_id == 5){
                $className = 'goto-order5'; 
            } else {
            if ($elapsedHours < $tatValueInHours) {
                $className = 'goto-order1'; 
            } elseif ($elapsedHours < ($tatValueInHours*2)) {
                $className = 'goto-order2'; 
            } elseif ($elapsedHours < ($tatValueInHours*3)) {
                $className = 'goto-order3'; 
            } elseif ($elapsedHours < ($tatValueInHours*4)) {
                $className = 'goto-order4'; 
            } else {
                $className = 'goto-order6'; 
            }
            }
        
            if ($user->user_type_id == 8) {
                if (($order->status_id == 1 && $order->assignee_qa_id != Auth::id()) ||
                    ($order->status_id == 1 && $order->assignee_user_id == Auth::id() && $order->assignee_qa_id == Auth::id()) ||
                    ($order->status_id == 14 && $order->assignee_user_id == Auth::id() ||
                    ($order->status_id == 14 && $order->assignee_qa_id == Auth::id()))){
                    $orderId = $order->id ?? '';
                // } elseif (($order->status_id == 4 && $order->assignee_user_id != Auth::id() && $order->assignee_qa_id == Auth::id()) ||
                //             ($order->status_id == 4 && $order->assignee_user_id == Auth::id() && $order->assignee_qa_id == Auth::id()) || 
                //             ($order->status_id == 4 && $order->assignee_user_id == Auth::id() && $order->assignee_qa_id == null)) {
                //     $orderId = $order->id ?? '';
                } elseif ($order->status_id == 4){
                    $orderId = $order->id ?? '';
                } elseif (($order->status_id == 13 && $order->associate_id != null) ||($order->status_id == 13 && $order->associate_id == null)
                ) {
                        $orderId = $order->id ?? '';
                    } 
                else {
                    $orderId = '';
                }
            }
            else if ($order->status_id == 1 && $order->assignee_user_id == null){
                $orderId =  '';
            }
            else {
                $orderId = $order->id ?? '';
            }

            return '<span class="px-2 py-1 rounded text-white goto-order ' . $className . ' ml-2" id="goto_' . $orderId . '">' . $order->order_id . '</span>';
        })
        ->rawColumns(['checkbox', 'action', 'status', 'order_id'])
        ->toJson();
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

    public function orders_status(Request $request)
    {
        $orderData = OrderCreation::leftJoin('stl_item_description', 'oms_order_creations.process_id', '=', 'stl_item_description.id')->get();
        $status_id = Status::pluck('id')->toArray();

        // $processList = DB::table('stl_item_description')->where('is_approved', 1)->where('is_active', 1)->select('id', 'process_name', 'project_code')->get();
        $user = User::where('id', Auth::id())->first();

        $processList=[];
        session(['user_type_id' => $user->user_type_id]);
        $reportingUserIds = User::getAllLowerLevelUserIds(Auth::id());
        if (Auth::user()->hasRole('Super Admin')) {
            $processIds = DB::table('stl_item_description')->where('is_approved', 1)->where('is_active', 1)->pluck('id')->toArray();
        } else {
            $processIds = DB::table('oms_user_service_mapping')->whereIn('user_id', $reportingUserIds)->where('is_active', 1)->pluck('service_id')->toArray();
        }
        $processList = DB::table('stl_item_description')->where('is_approved', 1)->where('is_active', 1)->whereIn('id', $processIds)->select('id', 'process_name', 'project_code')->get();


        $stateList = State::select('id', 'short_code')->get();
        $processors = User::select('id', 'username', 'emp_id', 'user_type_id')->where('is_active', 1)->whereIn('user_type_id', [6, 8, 9])->orderBy('emp_id')->get();
        $qcers = User::select('id', 'username', 'emp_id', 'user_type_id')->where('is_active', 1)->whereIn('user_type_id', [7, 8])->orderBy('emp_id')->get();
        $typists = User::select('id', 'username', 'emp_id', 'user_type_id')->where('is_active', 1)->whereIn('user_type_id', [10, 22])->orderBy('emp_id')->get();
        $typists_qcs = User::select('id', 'username', 'emp_id', 'user_type_id')->where('is_active', 1)->whereIn('user_type_id', [11, 22])->orderBy('emp_id')->get();
        $status_changes = Status::select('id', 'status')->where('id', '!=', 19)->get();

        $statusList = Status::select('id', 'status')->get();
        $countyList = County::select('id', 'county_name')->get();

        $defaultStatus = Status::where('status', 'WIP')->first();

        $selectedStatus = $request->input('status', $defaultStatus->id);

        return view('app.orders.orders_status', compact('processList', 'stateList', 'statusList', 'processors', 'qcers', 'countyList', 'selectedStatus', 'typists', 'typists_qcs', 'status_changes'));
    }

    public function assignment_update(Request $request)
    {
        $input = $request->all();
        $orderId = $request->input('orders');
        $validatedData = $request->validate([
            'type_id' => 'required',
          
            'orders' => 'required',
        ]);


        $orders = DB::table('oms_order_creations')->where('id', $orderId)->select('client_id', 'process_type_id')->get();

    if ($orders) {
        foreach ($orders as $order) {
            $process_typeId = $order->process_type_id;
        }
    } else {
        // Handle the case where no orders were found (optional)
        return response()->json(['message' => 'Orders not found'], 404);
    }

        if (count($request->input('orders')) > 0) {
            $orderIds = $input['orders'];
 
            if ($input['type_id'] == 7) {
                OrderCreation::whereIn('id', $orderIds)
                    ->whereNull('assignee_qa_id')
                    ->update(['assignee_qa_id' => $input['user_id']]);
            }
 
            if (in_array($input['type_id'], [13, 1, 6, 14, 4, 2, 3, 16, 17])) {
                if ($input['user_id'] != null) {
                    OrderCreation::whereIn('id', $orderIds)
                        ->update(['assignee_user_id' => $input['user_id']]);
                } elseif ($input['qcer_id'] != null) {
                    OrderCreation::whereIn('id', $orderIds)
                        ->update(['assignee_qa_id' => $input['qcer_id']]);
                } elseif ($input['cover_prep_id'] != null) {
                    OrderCreation::whereIn('id', $orderIds)
                        ->update(['associate_id' => $input['cover_prep_id']]);
                }elseif ($input['typist_id'] != null) {
                    OrderCreation::whereIn('id', $orderIds)
                        ->update(['typist_id' => !in_array($process_typeId, [1, 3, 5, 15, 18]) ? $input['typist_id'] : null]);
                }elseif ($input['typist_qc_id'] != null) {
                    OrderCreation::whereIn('id', $orderIds)
                        ->update(['typist_qc_id' => !in_array($process_typeId, [1, 3, 5, 15, 18]) ? $input['typist_qc_id'] : null]);
                }

            }
 
            if (in_array($input['type_id'], [13, 1, 6, 14, 4, 2, 3, 16, 17]) && $input['user_id'] != null && $input['qcer_id'] != null && $input['cover_prep_id'] != null && $input['typist_id'] != null && $input['typist_qc_id'] != null) {
                OrderCreation::whereIn('id', $orderIds)
                    ->update([
                        'assignee_user_id' => $input['user_id'],
                        'assignee_qa_id' => $input['qcer_id'],
                        'associate_id' => $input['cover_prep_id'],
                    'typist_id' => !in_array($process_typeId, [1, 3, 5, 15, 18]) ? $input['typist_id'] : null,
                    'typist_qc_id' => !in_array($process_typeId, [1, 3, 5, 15, 18]) ? $input['typist_qc_id'] : null,
                    ]);
            }
 
            if (in_array($input['type_id'], [13, 1, 6, 14, 4, 2, 3, 16, 17])) {
                if ($input['user_id'] != null && $input['qcer_id'] != null) {
                    OrderCreation::whereIn('id', $orderIds)
                        ->update([
                            'assignee_user_id' => $input['user_id'],
                            'assignee_qa_id' => $input['qcer_id'],
                            'associate_id' => $input['cover_prep_id'],
                            'typist_id' => !in_array($process_typeId, [1, 3, 5, 15, 18]) ? $input['typist_id'] : null,
                            'typist_qc_id' => !in_array($process_typeId, [1, 3, 5, 15, 18]) ? $input['typist_qc_id'] : null,

                        ]);
            }

                if ($input['user_id'] != null && $input['qcer_id'] != null && $input['cover_prep_id'] != null && $input['typist_id'] != null)  {
                    OrderCreation::whereIn('id', $orderIds)
                        ->update([
                            'assignee_user_id' => $input['user_id'],
                            'assignee_qa_id' => $input['qcer_id'],
                            'associate_id' => $input['cover_prep_id'],
                            'typist_id' => !in_array($process_typeId, [1, 3, 5, 15, 18]) ? $input['typist_id'] : null,
                            'typist_qc_id' => !in_array($process_typeId, [1, 3, 5, 15, 18]) ? $input['typist_qc_id'] : null,

                        ]);
                }


                if ($input['user_id'] != null && $input['qcer_id'] != null && $input['cover_prep_id'] != null && $input['typist_qc_id'] != null)  {
                    OrderCreation::whereIn('id', $orderIds)
                        ->update([
                            'assignee_user_id' => $input['user_id'],
                            'assignee_qa_id' => $input['qcer_id'],
                            'associate_id' => $input['cover_prep_id'],
                            'typist_id' => !in_array($process_typeId, [1, 3, 5, 15, 18]) ? $input['typist_id'] : null,
                            'typist_qc_id' => !in_array($process_typeId, [1, 3, 5, 15, 18]) ? $input['typist_qc_id'] : null,

                        ]);
                }


                if ($input['qcer_id'] != null && $input['cover_prep_id'] != null && $input['typist_id'] != null)  {
                    OrderCreation::whereIn('id', $orderIds)
                        ->update([
                            'assignee_user_id' => $input['user_id'],
                            'assignee_qa_id' => $input['qcer_id'],
                            'associate_id' => $input['cover_prep_id'],
                            'typist_id' => !in_array($process_typeId, [1, 3, 5, 15, 18]) ? $input['typist_id'] : null,
                            'typist_qc_id' => !in_array($process_typeId, [1, 3, 5, 15, 18]) ? $input['typist_qc_id'] : null,

                        ]);
                }

                if ($input['qcer_id'] != null && $input['cover_prep_id'] != null && $input['typist_qc_id'] != null)  {
                    OrderCreation::whereIn('id', $orderIds)
                        ->update([
                            'assignee_user_id' => $input['user_id'],
                            'assignee_qa_id' => $input['qcer_id'],
                            'associate_id' => $input['cover_prep_id'],
                            'typist_id' => !in_array($process_typeId, [1, 3, 5, 15, 18]) ? $input['typist_id'] : null,
                            'typist_qc_id' => !in_array($process_typeId, [1, 3, 5, 15, 18]) ? $input['typist_qc_id'] : null,

                        ]);
                }

                if ($input['user_id'] != null && $input['cover_prep_id'] != null && $input['typist_id'] != null)  {
                    OrderCreation::whereIn('id', $orderIds)
                        ->update([
                            'assignee_user_id' => $input['user_id'],
                            'assignee_qa_id' => $input['qcer_id'],
                            'associate_id' => $input['cover_prep_id'],
                            'typist_id' => !in_array($process_typeId, [1, 3, 5, 15, 18]) ? $input['typist_id'] : null,
                            'typist_qc_id' => !in_array($process_typeId, [1, 3, 5, 15, 18]) ? $input['typist_qc_id'] : null,

                        ]);
                }
                if ($input['qcer_id'] != null && $input['cover_prep_id'] != null && $input['typist_id'] != null)  {
                    OrderCreation::whereIn('id', $orderIds)
                        ->update([
                            'assignee_user_id' => $input['user_id'],
                            'assignee_qa_id' => $input['qcer_id'],
                            'associate_id' => $input['cover_prep_id'],
                            'typist_id' => !in_array($process_typeId, [1, 3, 5, 15, 18]) ? $input['typist_id'] : null,
                            'typist_qc_id' => !in_array($process_typeId, [1, 3, 5, 15, 18]) ? $input['typist_qc_id'] : null,

                        ]);
                }

                if ($input['user_id'] != null && $input['cover_prep_id'] != null && $input['typist_qc_id'] != null)  {
                    OrderCreation::whereIn('id', $orderIds)
                        ->update([
                            'assignee_user_id' => $input['user_id'],
                            'assignee_qa_id' => $input['qcer_id'],
                            'associate_id' => $input['cover_prep_id'],
                            'typist_id' => !in_array($process_typeId, [1, 3, 5, 15, 18]) ? $input['typist_id'] : null,
                            'typist_qc_id' => !in_array($process_typeId, [1, 3, 5, 15, 18]) ? $input['typist_qc_id'] : null,

                        ]);
                }

                if ($input['qcer_id'] != null && $input['cover_prep_id'] != null && $input['typist_qc_id'] != null)  {
                    OrderCreation::whereIn('id', $orderIds)
                        ->update([
                            'assignee_user_id' => $input['user_id'],
                            'assignee_qa_id' => $input['qcer_id'],
                            'associate_id' => $input['cover_prep_id'],
                            'typist_id' => !in_array($process_typeId, [1, 3, 5, 15, 18]) ? $input['typist_id'] : null,
                            'typist_qc_id' => !in_array($process_typeId, [1, 3, 5, 15, 18]) ? $input['typist_qc_id'] : null,

                        ]);
                }
                    
                if ($input['cover_prep_id'] != null && $input['typist_id'] != null) {
                    OrderCreation::whereIn('id', $orderIds)
                        ->update([
                            'assignee_user_id' => $input['user_id'],
                            'typist_id' => !in_array($process_typeId, [1, 3, 5, 15, 18]) ? $input['typist_id'] : null,
                        ]);
                }


                if ($input['cover_prep_id'] != null && $input['typist_qc_id'] != null) {
                    OrderCreation::whereIn('id', $orderIds)
                        ->update([
                            'assignee_user_id' => $input['user_id'],
                            'typist_qc_id' => !in_array($process_typeId, [1, 3, 5, 15, 18]) ? $input['typist_qc_id'] : null,
                        ]);
                }


                
                if ($input['user_id'] != null && $input['typist_id'] != null) {
                    OrderCreation::whereIn('id', $orderIds)
                        ->update([
                            'associate_id' => $input['cover_prep_id'],
                            'typist_id' => !in_array($process_typeId, [1, 3, 5, 15, 18]) ? $input['typist_id'] : null,
                        ]);
                }

                if ($input['user_id'] != null && $input['typist_qc_id'] != null) {
                    OrderCreation::whereIn('id', $orderIds)
                        ->update([
                            'associate_id' => $input['cover_prep_id'],
                            'typist_qc_id' => !in_array($process_typeId, [1, 3, 5, 15, 18]) ? $input['typist_qc_id'] : null,
                        ]);
                }



                if ($input['qcer_id'] != null && $input['typist_id'] != null) {
                    OrderCreation::whereIn('id', $orderIds)
                        ->update([
                            'assignee_qa_id' => $input['qcer_id'],
                                'typist_id' => !in_array($process_typeId, [1, 3, 5, 15, 18]) ? $input['typist_id'] : null,
                        ]);
                }


                if ($input['qcer_id'] != null && $input['typist_qc_id'] != null) {
                    OrderCreation::whereIn('id', $orderIds)
                        ->update([
                            'assignee_qa_id' => $input['qcer_id'],
                                'typist_qc_id' => !in_array($process_typeId, [1, 3, 5, 15, 18]) ? $input['typist_qc_id'] : null,
                        ]);
                }


                if ($input['typist_qc_id'] != null && $input['typist_id'] != null) {
                    OrderCreation::whereIn('id', $orderIds)
                        ->update([
                            'typist_id' => !in_array($process_typeId, [1, 3, 5, 15, 18]) ? $input['typist_id'] : null,
                            'typist_qc_id' => !in_array($process_typeId, [1, 3, 5, 15, 18]) ? $input['typist_qc_id'] : null,
                        ]);
                }

                if ($input['cover_prep_id'] != null && $input['qcer_id'] != null) {
                    OrderCreation::whereIn('id', $orderIds)
                        ->update([
                            'assignee_qa_id' => $input['qcer_id'],
                            'associate_id' => $input['cover_prep_id']
                        ]);
            }

                if ($input['cover_prep_id'] != null && $input['user_id'] != null) {
                    OrderCreation::whereIn('id', $orderIds)
                        ->update([
                            'assignee_user_id' => $input['user_id'],
                            'associate_id' => $input['cover_prep_id']
                        ]);
                }
            }
            return response()->json(['data' => 'success', 'msg' => 'Order Assigned Successfully']);
        }
    }

    public function update_order_status(Request $request)
    {
        $input = $request->all();

        $validatedData = $request->validate([
            'rowId' => 'required',
            'selectedStatus' => 'required|integer',
        ]);

        $orderId = $input['rowId'];
        $statusId = $input['selectedStatus'];

        $updateData = ['status_id' => $statusId];

        if ($statusId == 5) {
            $updateData['completion_date'] = Carbon::now()->setTimezone('America/New_York');
        } else {
            $updateData['completion_date'] = null;
        }

        $update_data = OrderCreation::select('state_id', 'county_id', 'tier_id', 'city_id')
        ->where('id', $orderId)
        ->first();

        if (is_null($update_data->state_id) || is_null($update_data->county_id)) {
            return response()->json([
                'error' => 'State and County are required to Fill'
            ]);
        }



        $update_status = OrderCreation::where('id', $orderId)->update($updateData);

        if ($update_status) {
            OrderCreation::where('id', $orderId)->update(['status_updated_time' => Carbon::now()]);
        }

        if ($update_status) {
            $update_data = OrderCreation::select('tier_id', 'state_id', 'county_id', 'city_id')
                ->where('id', $orderId)
                ->first();
    
            if ($update_data) {
                OrderCreation::where('id', $orderId)
                    ->update([
                        'tier_id' => $update_data->tier_id,
                        'state_id' => $update_data->state_id,
                        'county_id' => $update_data->county_id,
                        'city_id' => $update_data->city_id,
                    ]);
    
                $gethistorydata = DB::table('order_status_history')
                    ->select('comment', 'checked_array')
                    ->where('order_id', $orderId)
                    ->orderBy('id','desc')->first();
    
                DB::table('order_status_history')->insert([
                    'order_id' => $orderId,
                    'status_id' => $statusId,
                    'comment' => null,
                    'current_status_id' => $request->currentValue,
                    'checked_array' => $gethistorydata ? $gethistorydata->checked_array : null,
                    'created_at' => Carbon::now()->setTimezone('America/New_York'),
                    'created_by' => Auth::id(),
                ]);
    
                return response()->json(['success' => 'Status updated successfully']);
            } else {
                return response()->json(['error' => 'Order not found']);
            }
        } else {
            return response()->json(['error' => 'Failed to update the status']);
    }
    }
    

    public function redirectwithfilter(Request $request)
    {   $user = Auth::user();
        $processIds = $this->getProcessIdsBasedOnUserRole($user);
        $projectId = $request->input('projectId');
        $clientId = $request->input('clientId');
        // $fromDate = $request->input('fromDate');
        // $toDate = $request->input('toDate');
        $selectedDateFilter = $request->input('selectedDateFilter');

        // $fromDateRange = Session::get('fromDate');
        // $toDateRange = Session::get('toDate');
        $fromDateRange = $request->input('fromDate');
        $toDateRange = $request->input('toDate');
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

        session()->forget(['projectId', 'clientId', 'fromDate', 'toDate', 'dashboardfilters','user','processIds']);
        // Store values in session
        session(['user' => $user ?? '']);
        session(['processIds' => $processIds ?? '']);
        session(['projectId' => $projectId ?? '']);
        session(['clientId' => $clientId ?? '']);
        session(['fromDate' => $fromDate ?? '']);
        session(['toDate' => $toDate ?? '']);
        session(['dashboardfilters' => true]);

        return response()->json(['success' => 'Filter values stored in session.']);
    }




public function status_change(Request $request)
{
    $statusId = $request->input('status_id');
    $orderIds = $request->input('orders');

    // Check if both orderIds and statusId are provided
    if (!$orderIds || !$statusId) {
        return response()->json(['success' => false, 'message' => 'Invalid data provided.']);
    }

    // List of process_type_ids to exclude when status_id is 15
    $excludedProcessTypes1 = [1, 3, 5, 15, 18];
    $excludedProcessTypes2 = [2, 4, 6, 8, 9, 16];
    $excludedProcessTypes3 = [7, 12, 17];

    // Get the order details
    $orderDetails = OrderCreation::whereIn('id', $orderIds)
        ->select('id', 'order_id', 'process_type_id', 'client_id', 'status_id')
        ->get();

    // Initialize a flag for validation
    $validationFailed = false;
    $errorMessage = '';

    // Check if all orders have the same client_id and process_type_id
    $firstOrder = $orderDetails->first(); // Get the first order
    foreach ($orderDetails as $order) {
        if ($order->client_id !== $firstOrder->client_id) {
            $validationFailed = true;
            $errorMessage = 'Please select orders with the same client.';
            break;
        }
        // if ($order->process_type_id !== $firstOrder->process_type_id) {
        //     $validationFailed = true;
        //     $errorMessage = 'Please select orders with the same Client, Lob, Process';
        //     break;
        // }
    }

    // Loop through the order details for other validation checks
    if (!$validationFailed) {
        foreach ($orderDetails as $order) {
            // If client_id is 16, allow statusId 13 for excluded process types
            if ($order->client_id == 16) {
                // No restriction on status_id 13 for client_id 16
                if (in_array($order->process_type_id, $excludedProcessTypes1) && in_array($statusId, [15, 16, 17])) {
                    $validationFailed = true;
                    $errorMessage = "Please check your client and process to update the status";
                    break;
                } else if (in_array($order->process_type_id, $excludedProcessTypes2) && in_array($statusId, [1, 4, 15])) {
                    $validationFailed = true;
                    $errorMessage = "Please check your client and process to update the status";
                    break;
                } else if (in_array($order->process_type_id, $excludedProcessTypes3) && in_array($statusId, [13, 7, 12, 17, 15])) {
                    $validationFailed = true;
                    $errorMessage = "Please check your client and process to update the status";
                    break;
                }
            } else if($order->client_id == 82){
                if (in_array($order->process_type_id, $excludedProcessTypes2) && in_array($statusId, [1, 4, 13])) {
                    $validationFailed = true;
                    $errorMessage = "Please check your client and process to update the status";
                    break;
                } else if (in_array($order->process_type_id, $excludedProcessTypes3) && in_array($statusId, [13, 7, 12, 17])) {
                    $validationFailed = true;
                    $errorMessage = "Please check your client and process to update the status";
                    break;
                }
            }else {
                if (in_array($order->process_type_id, $excludedProcessTypes1) && in_array($statusId, [13, 15, 16, 17])) {
                    $validationFailed = true;
                    $errorMessage = "Please check your client and process to update the status";
                    break;
                } else if (in_array($order->process_type_id, $excludedProcessTypes2) && in_array($statusId, [13, 1, 4, 15])) {
                    $validationFailed = true;
                    $errorMessage = "Please check your client and process to update the status";
                    break;
                } else if (in_array($order->process_type_id, $excludedProcessTypes3) && in_array($statusId, [13,15])) {
                    $validationFailed = true;
                    $errorMessage = "Please check your client and process to update the status";
                    break;
                }
            }
        }
    }

    // If validation fails, return error response
    if ($validationFailed) {
        return response()->json([
            'success' => false,
            'message' => $errorMessage
        ]);
    }

    // If validation passes, proceed with the status update
    $update_status = OrderCreation::whereIn('id', $orderIds)
        ->update(['status_id' => $statusId]);

    if ($update_status) {
        return response()->json(['success' => true, 'message' => 'Status has been updated.']);
    } else {
        return response()->json(['success' => false, 'message' => 'Failed to update status.']);
    }
}
 
public function self_user_assign(Request $request)
{
    // Validate input
    $validatedData = $request->validate([
        'type_id' => 'required',
        'orders' => 'required',
    ]);

    // Update the order's assignee
    $orderId = $request->input('orders');
    $updateResult = DB::table('oms_order_creations')
        ->where('id', $orderId)
        ->update([
            'assignee_user_id' => auth()->id(), // Assuming self-assignment
        ]);

    // Check update result and return appropriate response
    if ($updateResult) {
        return response()->json(['message' => 'Order assigned successfully.'], 200);
    } else {
        return response()->json(['message' => 'Failed to assign order.'], 400);
    }
}


}

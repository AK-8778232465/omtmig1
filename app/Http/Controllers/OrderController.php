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
            ->whereHas('process', function ($query) {
                $query->where('stl_item_description.is_approved', 1);
            })
            ->whereHas('client', function ($query) {
                $query->where('stl_client.is_approved', 1);
            });

        if (!in_array($user->user_type_id, [1, 2, 3, 4, 5, 9])) {
            if ($user->user_type_id == 6) {
                $statusCountsQuery->where('assignee_user_id', $user->id);
            } elseif($user->user_type_id == 7) {
                $statusCountsQuery->where('assignee_qa_id', $user->id)
                ->whereNotIn('status_id', [1]);
            } elseif($user->user_type_id == 8) {
                $statusCountsQuery->where(function ($query) use($user) {
                    $query->where('assignee_user_id', $user->id)
                        ->orWhere('assignee_qa_id', $user->id);
                });
            } else{

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
        $yetToAssignQa = OrderCreation::with('process', 'client')
            ->where('assignee_qa_id')
            ->where('assignee_user_id')
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
            ->Where('oms_order_creations.assignee_user_id', $user->id)
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

        if (in_array($user->user_type_id, [1, 2, 3, 4, 5, 9])) {
            $statusCounts[1] = (!empty($statusCounts[1]) ? $statusCounts[1] : 0) - $yetToAssignUser;
            $statusCounts[4] = (!empty($statusCounts[4]) ? $statusCounts[4] : 0) - $yetToAssignQa;
            $statusCounts[6] = $yetToAssignUser;
            $statusCounts[7] = $yetToAssignQa;
        } else {
            $statusCounts[6] = in_array($user->user_type_id, [6, 8]) ? $yetToAssignUser : 0;
            $statusCounts[7] = in_array($user->user_type_id, [7, 8]) ? $yetToAssignQa : 0;
            $statusCounts[13] = $user_coverSheet;
        }

        return response()->json(['StatusCounts' => $statusCounts, 'AssignCoverSheet' => $assign_coverSheet]);
    }


    public function getOrderData(Request $request)
    {
        $user = Auth::user();
        $processIds = $this->getProcessIdsBasedOnUserRole($user);

        $query = DB::table('oms_order_creations')
            ->leftJoin('stl_item_description', 'oms_order_creations.process_id', '=', 'stl_item_description.id')
            ->leftJoin('oms_state', 'oms_order_creations.state_id', '=', 'oms_state.id')
            ->leftJoin('county', 'oms_order_creations.county_id', '=', 'county.id')
            ->leftJoin('oms_status', 'oms_order_creations.status_id', '=', 'oms_status.id')
            ->leftJoin('stl_client', 'stl_item_description.client_id', '=', 'stl_client.id')
            ->leftJoin('oms_users as assignee_users', 'oms_order_creations.assignee_user_id', '=', 'assignee_users.id')
            ->leftJoin('oms_users as assignee_qas', 'oms_order_creations.assignee_qa_id', '=', 'assignee_qas.id')
            ->leftJoin('oms_users as associate_names', 'oms_order_creations.associate_id', '=', 'associate_names.id')
            ->leftJoin('stl_lob', 'stl_item_description.lob_id', '=', 'stl_lob.id')
            ->leftJoin('stl_process', 'stl_item_description.process_id', '=', 'stl_process.id')
            ->select(
                'oms_order_creations.id',
                'oms_order_creations.order_id as order_id',
                'oms_order_creations.status_id as status_id',
                'oms_order_creations.order_date as order_date',
                'stl_item_description.project_code as project_code',
                'stl_item_description.qc_enabled as qc_enabled',
                'stl_item_description.tat_value as tat_value',
                'oms_state.short_code as short_code',
                'county.county_name as county_name',
                'oms_order_creations.assignee_user_id',
                'oms_order_creations.assignee_qa_id',
                'oms_order_creations.associate_id',
                'stl_lob.name as lob_name',
                'stl_process.name as process_name',
                DB::raw('CONCAT(assignee_users.emp_id, " (", assignee_users.username, ")") as assignee_user'),
                DB::raw('CONCAT(assignee_qas.emp_id, " (", assignee_qas.username, ")") as assignee_qa'),
                DB::raw('CONCAT(associate_names.emp_id, " (", associate_names.username, ")") as associate_name')
            )
            ->where('oms_order_creations.is_active', 1)
            ->where('stl_item_description.is_approved', 1)
            ->where('stl_client.is_approved', 1);

            if (
                isset($request->status) &&
                in_array($request->status, [1, 2, 3, 4, 5, 13, 14]) &&
                $request->status != 'All' &&
                $request->status != 6 &&
                $request->status != 7
            ) {
                if ($request->status == 1) {
                    if(in_array($user->user_type_id, [1, 2, 3, 4, 5, 9])) {
                        $query->where('oms_order_creations.status_id', $request->status)->whereNotNull('oms_order_creations.assignee_user_id');
                    } else {
                        $query->where('oms_order_creations.status_id', $request->status)->where('oms_order_creations.assignee_user_id', $user->id);
                    }
                } elseif($request->status == 4) {
                    if(in_array($user->user_type_id, [1, 2, 3, 4, 5, 9])) {
                        $query->where('oms_order_creations.status_id', $request->status)->whereNotNull('oms_order_creations.assignee_user_id');
                    } else {
                        if($request->status != 13){
                        if(in_array($user->user_type_id, [6])) {
                            $query->where('oms_order_creations.status_id', $request->status)->where('oms_order_creations.assignee_user_id', $user->id);

                        } elseif(in_array($user->user_type_id, [7])) {
                            $query->where('oms_order_creations.status_id', $request->status)->where('oms_order_creations.assignee_qa_id', $user->id);

                        } elseif(in_array($user->user_type_id, [8])) {
                            $query->where('oms_order_creations.status_id', $request->status)
                            ->where(function ($optionalquery) use ($user) {
                                $optionalquery->where('oms_order_creations.assignee_user_id', $user->id)
                                    ->orWhere('oms_order_creations.assignee_qa_id', $user->id);
                            });

                        }
                    }
                    }


            }else {
                    if(in_array($user->user_type_id, [1, 2, 3, 4, 5, 9])) {
                        $query->where('oms_order_creations.status_id', $request->status)->whereNotNull('oms_order_creations.assignee_user_id');
                    } elseif(in_array($user->user_type_id, [6]) && $request->status != 13){
                        $query->where('oms_order_creations.status_id', $request->status)->where('oms_order_creations.assignee_user_id', $user->id);
                    }
                    elseif(in_array($user->user_type_id, [7]) && $request->status != 13) {
                        $query->where('oms_order_creations.status_id', $request->status)->Where('oms_order_creations.assignee_qa_id', $user->id);
                    }
                    elseif(in_array($user->user_type_id, [8]) && $request->status != 13) {
                        $query->where('oms_order_creations.status_id', $request->status)
                        ->where(function ($optionalquery) use($user) {
                            $optionalquery->where('oms_order_creations.assignee_user_id', $user->id)
                                ->orWhere('oms_order_creations.assignee_qa_id', $user->id);
                        });

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
                    $query->where('oms_order_creations.assignee_user_id', $user->id);
                } elseif(in_array($user->user_type_id, [7])) {
                    $query->where('oms_order_creations.assignee_qa_id', $user->id)
                    ->whereNotIn('status_id', [1, 13]);
                } elseif(in_array($user->user_type_id, [8])) {
                    $query->where(function ($optionalquery) use($user) {
                        $optionalquery->where('oms_order_creations.assignee_user_id', $user->id)
                            ->orWhere('oms_order_creations.assignee_qa_id', $user->id);
                    });
                } else {
                    $query->whereNotNull('oms_order_creations.assignee_user_id');
                }
            } elseif ($request->status == 6) {
                if(in_array($user->user_type_id, [1, 2, 3, 4, 5, 6, 8, 9])) {
                    $query->whereNull('oms_order_creations.assignee_user_id')->where('oms_order_creations.status_id', 1);
                } else {
                    $query->whereNull('oms_order_creations.id');
                }
            } elseif ($request->status == 7) {
                if(in_array($user->user_type_id, [1, 2, 3, 4, 5, 7, 8, 9])) {
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
    
        // Ensure project_id and client_id are arrays
        $project_id = !is_array($project_id) ? explode(',', $project_id) : $project_id;
        $client_id = !is_array($client_id) ? explode(',', $client_id) : $client_id;
    
        // Initialize queries
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
    if(in_array($user->user_type_id, [1, 2, 3, 4, 5, 9])){
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
                ->whereIn('status_id', [1, 2, 4, 13, 14])
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
                    ->whereIn('status_id', [1, 2, 4, 13, 14])
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
                    ->whereIn('status_id', [1, 2, 4, 13, 14])
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
                        ->whereIn('status_id', [1, 2, 4, 13, 14])
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
                            ->whereIn('status_id', [1, 2, 4, 13, 14])
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
                            ->whereIn('status_id', [1, 2, 4, 13, 14])
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
                        ->whereIn('status_id', [1, 2, 4, 13, 14])
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
                            ->whereIn('status_id', [1, 2, 4, 13, 14])
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
                            ->whereIn('status_id', [1, 2, 4, 13, 14])
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
            ->leftJoin('oms_users as associate_names', 'oms_order_creations.associate_id', '=', 'associate_names.id')
            ->leftJoin('stl_lob', 'stl_item_description.lob_id', '=', 'stl_lob.id')
            ->leftJoin('stl_process', 'stl_item_description.process_id', '=', 'stl_process.id')
            ->select(
                'oms_order_creations.id',
                'oms_order_creations.order_id as order_id',
                'oms_order_creations.status_id as status_id',
                'oms_order_creations.order_date as order_date',
                'stl_item_description.project_code as project_code',
                'stl_item_description.qc_enabled as qc_enabled',
                'stl_item_description.tat_value as tat_value',
                'oms_state.short_code as short_code',
                'county.county_name as county_name',
                'oms_order_creations.assignee_user_id',
                'oms_order_creations.assignee_qa_id',
                'oms_order_creations.associate_id',
                'stl_lob.name as lob_name',
                'stl_process.name as process_name',
                DB::raw('CONCAT(assignee_users.emp_id, " (", assignee_users.username, ")") as assignee_user'),
                DB::raw('CONCAT(assignee_qas.emp_id, " (", assignee_qas.username, ")") as assignee_qa'),
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
                ->whereNull('assignee_qa_id')
                ->whereIn('stl_item_description.client_id', $client_id)
            ->whereDate('order_date', '>=', $fromDate)
            ->whereDate('order_date', '<=', $toDate);
            }elseif(!in_array('All', $project_id)){
                $query = $currentYet_to_assign->whereNull('assignee_user_id')
                ->whereNull('assignee_qa_id')
                ->whereIn('oms_order_creations.process_id', $project_id) 
                ->whereDate('order_date', '>=', $fromDate)
                ->whereDate('order_date', '<=', $toDate);
            }else{
                $query = $currentYet_to_assign->whereNull('assignee_user_id')
                ->whereNull('assignee_qa_id')
                ->whereDate('order_date', '>=', $fromDate)
                ->whereDate('order_date', '<=', $toDate);
            }
           
        }
    }
    $query->whereIn('oms_order_creations.process_id', $processIds);

        return DataTables::of($query)
        ->addColumn('checkbox', function ($order) use ($user){
            if(in_array($user->user_type_id, [6, 7, 8])) {
                return '<span class="px-2 py-2 rounded text-white assign-me ml-2" id="assign_me_' . ($order->id ?? '') . '">Assign</span>';
            } else {
                return '<input class="checkbox-table check-one mx-2" data-id="' . ($order->process_id ?? '') . '" type="checkbox" value="' . ($order->id ?? '') . '" id="logs' . ($order->id ?? '') . '" name="orders[]">';
            }
        })
        ->addColumn('action', function ($order) {
            return '<td><div class="row mb-0"><div class="edit_order col-6" style="cursor: pointer;" data-id="' . ($order->id ?? '') . '"><img class="menuicon tbl_editbtn" src="/assets/images/edit.svg" />&nbsp;</div><div class="col-6"><span class="dripicons-trash delete_order text-danger" style="font-size:14px; cursor: pointer;" data-id="' . ($order->id ?? '') . '"></span></div></div></td>';
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
            $sql = "oms_order_creations.order_id  like ?";
            $order->whereRaw($sql, ["%{$keyword}%"]);
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
        ->filterColumn('assignee_qa', function($order, $keyword) {
            $sql = 'CONCAT(assignee_qas.emp_id, " (", assignee_qas.username, ")")  like ?';
            $order->whereRaw($sql, ["%{$keyword}%"]);
        })
        ->filterColumn('associate_name', function($order, $keyword) {
            $sql = 'CONCAT(associate_names.emp_id, " (", associate_names.username, ")")  like ?';
            $order->whereRaw($sql, ["%{$keyword}%"]);
        })
        ->addColumn('status', function ($order) use ($request) {
                    if($order->assignee_qa_id) {
                        if (Auth::user()->hasRole('Qcer') || Auth::user()->hasRole('PM/TL')){
                            $statusMapping = [];
                                $statusMapping = [
                                    1 => 'WIP',
                                    13 => 'Coversheet Prep',
                                    14 => 'Clarification',
                                    4 => 'Send for QC',
                                    2 => 'Hold',
                                    5 => 'Completed',
                                    3 => 'Cancelled',
                                ];
                        }elseif($order->assignee_qa_id && Auth::user()->hasRole('Process') && $order->status_id == 1 ){
                            $statusMapping = [];
                            $statusMapping = [
                                1 => 'WIP',
                                13 => 'Coversheet Prep',
                                14 => 'Clarification',
                                4 => 'Send for QC',
                                2 => 'Hold',
                                5 => 'Completed',
                                3 => 'Cancelled',
                            ];
                        }else{
                            $statusMapping = [];
                                $statusMapping = [
                                    1 => 'WIP',
                                    13 =>'Coversheet Prep',
                                    14 => 'Clarification',
                                    4 => 'Send for QC',
                                    2 => 'Hold',
                                    5 => 'Completed',
                                    3 => 'Cancelled',
                                ];
                        }

                    } else {
                        if (!$order->assignee_qa_id && Auth::user()->hasRole('PM/TL')){
                            $statusMapping = [];
                            $statusMapping = [
                                1 => 'WIP',
                                13 => 'Coversheet Prep',
                                14 => 'Clarification',
                                4 => 'Send for QC',
                                2 => 'Hold',
                                5 => 'Completed',
                                3 => 'Cancelled',
                            ];
                        }elseif((!$order->assignee_qa_id && Auth::user()->hasRole('Process') && $order->status_id == 1 )||(!$order->assignee_qa_id && Auth::user()->hasRole('Process') && $order->status_id == 3 )){
                            $statusMapping = [];
                            $statusMapping = [
                                1 => 'WIP',
                                13 => 'Coversheet Prep',
                                14 => 'Clarification',
                                4 => 'Send for QC',
                                2 => 'Hold',
                                5 => 'Completed',
                                3 => 'Cancelled',
                            ];
                        }else{
                            $statusMapping = [];
                            $statusMapping = [
                                1 => 'WIP',
                                13 => 'Coversheet Prep',
                                14 => 'Clarification',
                                4 => 'Send for QC',
                                2 => 'Hold',
                                5 => 'Completed',
                                3 => 'Cancelled',

                            ];
                        }

                    }


            return '<select style="width:100%" class="status-dropdown form-control" data-row-id="' . $order->id . '">' .
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
            return '<span class="px-2 py-1 rounded text-white goto-order ml-2" id="goto_' . ($order->id ?? '') . '">'.$order->order_id.'</span>';
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
        $statusList = Status::select('id', 'status')->get();
        $countyList = County::select('id', 'county_name')->get();

        // Set the default status based on your requirements, for example, 'WIP'
        $defaultStatus = Status::where('status', 'WIP')->first();

        // Get the selected status from the request or use the default status
        $selectedStatus = $request->input('status', $defaultStatus->id);

        return view('app.orders.orders_status', compact('processList', 'stateList', 'statusList', 'processors', 'qcers', 'countyList', 'selectedStatus'));
    }

    public function assignment_update(Request $request)
    {
        $input = $request->all();
        $validatedData = $request->validate([
            'type_id' => 'required',
          
            'orders' => 'required',
        ]);

        if (count($request->input('orders')) > 0) {
            $orderIds = $input['orders'];
 
            if ($input['type_id'] == 7) {
                OrderCreation::whereIn('id', $orderIds)
                    ->whereNull('assignee_qa_id')
                    ->update(['assignee_qa_id' => $input['user_id']]);
            }
 
            if (in_array($input['type_id'], [13, 1, 6, 14, 4, 2, 3])) {
                if ($input['user_id'] != null) {
                    OrderCreation::whereIn('id', $orderIds)
                        ->update(['assignee_user_id' => $input['user_id']]);
                } elseif ($input['qcer_id'] != null) {
                    OrderCreation::whereIn('id', $orderIds)
                        ->update(['assignee_qa_id' => $input['qcer_id']]);
                } elseif ($input['cover_prep_id'] != null) {
                    OrderCreation::whereIn('id', $orderIds)
                        ->update(['associate_id' => $input['cover_prep_id']]);
                }
            }
 
            if (in_array($input['type_id'], [13, 1, 6, 14, 4, 2, 3]) && $input['user_id'] != null && $input['qcer_id'] != null && $input['cover_prep_id'] != null) {
                OrderCreation::whereIn('id', $orderIds)
                    ->update([
                        'assignee_user_id' => $input['user_id'],
                        'assignee_qa_id' => $input['qcer_id'],
                        'associate_id' => $input['cover_prep_id']
                    ]);
            }
 
            if (in_array($input['type_id'], [13, 1, 6, 14, 4, 2, 3])) {
                if ($input['user_id'] != null && $input['qcer_id'] != null) {
                    OrderCreation::whereIn('id', $orderIds)
                        ->update([
                            'assignee_user_id' => $input['user_id'],
                            'assignee_qa_id' => $input['qcer_id'],
                            'associate_id' => $input['cover_prep_id']
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
            $updateData['completion_date'] = Carbon::now();
        } else {
            $updateData['completion_date'] = null;
        }

        $update_status = OrderCreation::where('id', $orderId)->update($updateData);

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
                    'comment' => $gethistorydata ? $gethistorydata->comment : null,
                    'checked_array' => $gethistorydata ? $gethistorydata->checked_array : null,
                    'created_at' => now(),
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
        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');

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
}

<?php

namespace App\Http\Controllers;

use App\Models\State;
use App\Models\Status;
use App\Models\County;
use App\Models\User;
use App\Models\Process;
use App\Models\Product;
use App\Models\OrderCreation;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use DataTables;

class OrderFormController extends Controller
{
    public function index(Request $request, $orderId = null)
    {
        $user = Auth::user();
        $processIds = $this->getProcessIdsBasedOnUserRole($user);

        $query = DB::table('oms_order_creations')
            ->leftJoin('stl_item_description', 'oms_order_creations.process_id', '=', 'stl_item_description.id')
            ->leftJoin('oms_state', 'oms_order_creations.state_id', '=', 'oms_state.id')
            ->leftJoin('county', 'oms_order_creations.county_id', '=', 'county.id')
            ->leftJoin('oms_status', 'oms_order_creations.status_id', '=', 'oms_status.id')
            ->leftJoin('oms_users as assignee_users', 'oms_order_creations.assignee_user_id', '=', 'assignee_users.id')
            ->leftJoin('oms_users as assignee_qas', 'oms_order_creations.assignee_qa_id', '=', 'assignee_qas.id')
            ->select(
                'oms_order_creations.id',
                'oms_order_creations.order_id as order_id',
                'oms_order_creations.status_id as status_id',
                'oms_order_creations.county_id as county_id',
                'oms_order_creations.process_id as process_id',
                'oms_order_creations.order_date as order_date',
                'stl_item_description.project_code as project_code',
                'stl_item_description.process_name as process_name',
                'stl_item_description.qc_enabled as qc_enabled',
                'oms_state.short_code as short_code',
                'county.county_name as county_name',
                'oms_order_creations.assignee_user_id',
                'oms_order_creations.assignee_qa_id',
                DB::raw('CONCAT(assignee_users.emp_id, " (", assignee_users.username, ")") as assignee_user'),
                DB::raw('CONCAT(assignee_qas.emp_id, " (", assignee_qas.username, ")") as assignee_qa')
            )
            ->where('oms_order_creations.is_active', 1)->where('oms_order_creations.id', $orderId);

            $query->whereIn('oms_order_creations.process_id', $processIds);



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
                        if(in_array($user->user_type_id, [6])) {
                            $query->where('oms_order_creations.status_id', $request->status)->where('oms_order_creations.assignee_user_id', $user->id);

                        } elseif(in_array($user->user_type_id, [7])) {
                            $query->where('oms_order_creations.status_id', $request->status)->where('oms_order_creations.assignee_qa_id', $user->id);

                        } elseif(in_array($user->user_type_id, [8])) {
                            $query->where(function ($optionalquery) use($user) {
                                $optionalquery->where('oms_order_creations.assignee_user_id', $user->id)
                                    ->orWhere('oms_order_creations.assignee_qa_id', $user->id);
                            });

                        }
                    }
                } else {
                    if(in_array($user->user_type_id, [1, 2, 3, 4, 5, 9])) {
                        $query->where('oms_order_creations.status_id', $request->status)->whereNotNull('oms_order_creations.assignee_user_id');
                    } elseif(in_array($user->user_type_id, [6])){
                        $query->where('oms_order_creations.status_id', $request->status)->where('oms_order_creations.assignee_user_id', $user->id);
                    }
                    else if(in_array($user->user_type_id, [7])) {
                        $query->where('oms_order_creations.status_id', $request->status)->Where('oms_order_creations.assignee_qa_id', $user->id);
                    }else{
                        $query->where(function ($optionalquery) use($user) {
                            $optionalquery->where('oms_order_creations.assignee_user_id', $user->id)
                                ->orWhere('oms_order_creations.assignee_qa_id', $user->id);
                        });
                    }
                }
            } elseif ($request->status == 'All') {
                if(in_array($user->user_type_id, [6])) {
                    $query->where('oms_order_creations.assignee_user_id', $user->id);
                } elseif(in_array($user->user_type_id, [7])) {
                    $query->where('oms_order_creations.assignee_qa_id', $user->id);
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

        $orderData = $query->first();

        if(!empty($orderData)) {
            $countyData = DB::table('county_instructions')->where('county_id', $orderData->county_id)->where('process_id', $orderData->process_id)->first();
            $countyInfo = Null;
            $checklist = Null;
            if(!empty($countyData->json)) {
                $countyInfo = json_decode($countyData->json, true);
            }
            if(!empty($countyData->checklist_array)) {
                $conditionIds = [explode(',', $countyData->checklist_array)];
                $checklist = DB::table('checklist')->whereIn('id', explode(',', $countyData->checklist_array))->get();
            }

            $orderHistory = DB::table('order_status_history')->where('order_id', $orderId)->orderBy('created_at', 'desc')->first();

            $query = DB::table('oms_order_creations')
            ->leftJoin('stl_item_description', 'oms_order_creations.process_id', '=', 'stl_item_description.id')
            ->leftJoin('oms_products', 'stl_item_description.client_id', '=', 'oms_products.client_id')
            ->where('oms_order_creations.id',$orderId)->pluck('oms_products.lob_id')
            ->toArray();
            $lobData = DB::table('stl_lob')->whereIn('id',array_unique($query))->get();
            $productData = DB::table('oms_products')->get();

            return view('app.orders.orderform', compact('orderData', 'countyInfo', 'checklist', 'orderHistory','lobData','productData'));
        } else {
            return redirect('/orders_status');
        }
    }


    public function getProduct_dropdown(Request $request)
    {
        $getProduct['product'] = Product::select('id', 'lob_id','client_id', 'product_name')->where('lob_id', $request->getlob_id)->get();

        return response()->json($getProduct);
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

    public function orderSubmit(Request $request) {
        if(!empty($request->orderId) && !empty($request->orderStatus)) {
            $orderId = $request->orderId;
            $update_status = OrderCreation::find($orderId)
            ->update([
                'status_id' => $request->orderStatus,
                'tier_id' => $request->tierId,
                'product_id' => $request->productId
            ]);
            if($update_status) {
                DB::table('order_status_history')->insert([
                    'order_id' => $orderId,
                    'status_id' => $request->orderStatus,
                    'comment' => $request->orderComment,
                    'checked_array' => $request->checklistItems,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                return response()->json(['success' => 'Order Status Updated Successfully']);;
            } else {
                return response()->json(['error' => 'Something went wrong']);
            }
        }
    }

    public function coversheet_prep(Request $request, $orderId = null){
        
        $user = Auth::user();
        $processIds = $this->getProcessIdsBasedOnUserRole($user);

        $query = DB::table('oms_order_creations')
            ->leftJoin('stl_item_description', 'oms_order_creations.process_id', '=', 'stl_item_description.id')
            ->leftJoin('oms_state', 'oms_order_creations.state_id', '=', 'oms_state.id')
            ->leftJoin('county', 'oms_order_creations.county_id', '=', 'county.id')
            ->leftJoin('oms_status', 'oms_order_creations.status_id', '=', 'oms_status.id')
            ->leftJoin('oms_users as assignee_users', 'oms_order_creations.assignee_user_id', '=', 'assignee_users.id')
            ->leftJoin('oms_users as assignee_qas', 'oms_order_creations.assignee_qa_id', '=', 'assignee_qas.id')
            ->select(
                'oms_order_creations.id',
                'oms_order_creations.order_id as order_id',
                'oms_order_creations.status_id as status_id',
                'oms_order_creations.county_id as county_id',
                'oms_order_creations.process_id as process_id',
                'oms_order_creations.order_date as order_date',
                'stl_item_description.project_code as project_code',
                'stl_item_description.process_name as process_name',
                'stl_item_description.qc_enabled as qc_enabled',
                'oms_state.short_code as short_code',
                'county.county_name as county_name',
                'oms_order_creations.assignee_user_id',
                'oms_order_creations.assignee_qa_id',
                DB::raw('CONCAT(assignee_users.emp_id, " (", assignee_users.username, ")") as assignee_user'),
                DB::raw('CONCAT(assignee_qas.emp_id, " (", assignee_qas.username, ")") as assignee_qa')
            )
            ->where('oms_order_creations.is_active', 1)->where('oms_order_creations.id', $orderId);

            $query->whereIn('oms_order_creations.process_id', $processIds);



            if (
                isset($request->status) &&
                in_array($request->status, [1, 2, 3, 4, 5, 13, 14]) &&
                $request->status != 'All' &&
                $request->status != 6 &&
                $request->status != 7
            ) {
                if ($request->status == 1) {
                    if(in_array($user->user_type_id, [1, 2, 3, 4, 5, 9, 13, 14])) {
                        $query->where('oms_order_creations.status_id', $request->status)->whereNotNull('oms_order_creations.assignee_user_id');
                    } else {
                        $query->where('oms_order_creations.status_id', $request->status)->where('oms_order_creations.assignee_user_id', $user->id);
                    }
                } elseif($request->status == 4) {
                    if(in_array($user->user_type_id, [1, 2, 3, 4, 5, 9, 13, 14])) {
                        $query->where('oms_order_creations.status_id', $request->status)->whereNotNull('oms_order_creations.assignee_user_id');
                    } else {
                        if(in_array($user->user_type_id, [6])) {
                            $query->where('oms_order_creations.status_id', $request->status)->where('oms_order_creations.assignee_user_id', $user->id);

                        } elseif(in_array($user->user_type_id, [7])) {
                            $query->where('oms_order_creations.status_id', $request->status)->where('oms_order_creations.assignee_qa_id', $user->id);

                        } elseif(in_array($user->user_type_id, [8])) {
                            $query->where(function ($optionalquery) use($user) {
                                $optionalquery->where('oms_order_creations.assignee_user_id', $user->id)
                                    ->orWhere('oms_order_creations.assignee_qa_id', $user->id);
                            });

                        }
                    }
                } else {
                    if(in_array($user->user_type_id, [1, 2, 3, 4, 5, 9, 13, 14])) {
                        $query->where('oms_order_creations.status_id', $request->status)->whereNotNull('oms_order_creations.assignee_user_id');
                    } elseif(in_array($user->user_type_id, [6])){
                        $query->where('oms_order_creations.status_id', $request->status)->where('oms_order_creations.assignee_user_id', $user->id);
                    }
                    else if(in_array($user->user_type_id, [7])) {
                        $query->where('oms_order_creations.status_id', $request->status)->Where('oms_order_creations.assignee_qa_id', $user->id);
                    }else{
                        $query->where(function ($optionalquery) use($user) {
                            $optionalquery->where('oms_order_creations.assignee_user_id', $user->id)
                                ->orWhere('oms_order_creations.assignee_qa_id', $user->id);
                        });
                    }
                }
            } elseif ($request->status == 'All') {
                if(in_array($user->user_type_id, [6])) {
                    $query->where('oms_order_creations.assignee_user_id', $user->id);
                } elseif(in_array($user->user_type_id, [7])) {
                    $query->where('oms_order_creations.assignee_qa_id', $user->id);
                } elseif(in_array($user->user_type_id, [8])) {
                    $query->where(function ($optionalquery) use($user) {
                        $optionalquery->where('oms_order_creations.assignee_user_id', $user->id)
                            ->orWhere('oms_order_creations.assignee_qa_id', $user->id);
                    });
                } else {
                    $query->whereNotNull('oms_order_creations.assignee_user_id');
                }
            } elseif ($request->status == 6) {
                if(in_array($user->user_type_id, [1, 2, 3, 4, 5, 6, 8, 9, 13, 14])) {
                    $query->whereNull('oms_order_creations.assignee_user_id')->where('oms_order_creations.status_id', 1);
                } else {
                    $query->whereNull('oms_order_creations.id');
                }
            } elseif ($request->status == 7) {
                if(in_array($user->user_type_id, [1, 2, 3, 4, 5, 7, 8, 9, 13, 14])) {
                    $query->whereNull('oms_order_creations.assignee_qa_id')->where('oms_order_creations.status_id', 4);
                } else {
                    $query->whereNull('oms_order_creations.id');
                }
            }

        $orderData = $query->first();

        return view('app.orders.coversheet_prep', compact('orderData'));
    }
}

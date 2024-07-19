<?php

namespace App\Http\Controllers;
use App\Models\Lob;
use App\Models\State;
use App\Models\Status;
use App\Models\County;
use App\Models\User;
use App\Models\Process;
use App\Models\Tier;
use App\Models\City;
use App\Models\Product;
use App\Models\PrimarySource;
use App\Models\OrderCreation;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use DataTables;
use Carbon\Carbon;

class OrderFormController extends Controller
{
    public function index(Request $request, $orderId = null)
    {
        $user = Auth::user();
        $processIds = $this->getProcessIdsBasedOnUserRole($user);
        $query = DB::table('oms_order_creations')
        ->leftJoin('stl_item_description', 'oms_order_creations.process_id', '=', 'stl_item_description.id')
        ->leftJoin('stl_client', 'stl_item_description.client_id', '=', 'stl_client.id')
        ->leftJoin('oms_state', 'oms_order_creations.state_id', '=', 'oms_state.id')
        ->leftJoin('county', 'oms_order_creations.county_id', '=', 'county.id')
        ->leftJoin('oms_status', 'oms_order_creations.status_id', '=', 'oms_status.id')
        ->leftJoin('oms_users as assignee_users', 'oms_order_creations.assignee_user_id', '=', 'assignee_users.id')
        ->leftJoin('oms_users as assignee_qas', 'oms_order_creations.assignee_qa_id', '=', 'assignee_qas.id')
        ->leftJoin('stl_lob', 'stl_item_description.lob_id', '=', 'stl_lob.id')
        ->leftJoin('stl_process', 'stl_item_description.process_id', '=', 'stl_process.id')
        ->leftJoin('oms_city','oms_order_creations.city_id','=','oms_city.id')
        ->select(
            'oms_order_creations.id',
            'oms_order_creations.order_id as order_id',
            'oms_order_creations.status_id as status_id',
            'oms_order_creations.county_id as county_id',
            'oms_order_creations.process_id as process_id',
            ///
                'oms_order_creations.city_id as city_id',
            'oms_city.city as city',
            ////
            'oms_order_creations.tier_id as tier_id',
            'oms_order_creations.order_date as order_date',
            'oms_order_creations.state_id as property_state', // Add this line
            'oms_order_creations.county_id as property_county', // Add this line
            'stl_item_description.project_code as project_code',
            'stl_item_description.process_name as process_name',
            'stl_item_description.qc_enabled as qc_enabled',
            'oms_state.short_code as short_code',
            'county.county_name as county_name',
            'oms_order_creations.assignee_user_id',
            'oms_order_creations.tier_id',
            'oms_order_creations.assignee_qa_id',
            'stl_item_description.lob_id as lob_id', // Add this line
            'stl_lob.name as lob_name', // Select the lob name
            'stl_client.client_name',
            'stl_item_description.client_id as client_id',
            'stl_process.name as process_type',
            DB::raw('CONCAT(assignee_users.emp_id, " (", assignee_users.username, ")") as assignee_user'),
            DB::raw('CONCAT(assignee_qas.emp_id, " (", assignee_qas.username, ")") as assignee_qa'),
            'stl_item_description.process_name' // Adding product_name from 'oms_products' table
        )
        ->where('oms_order_creations.is_active', 1)
        ->where('oms_order_creations.id', $orderId);

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
            if($orderData->client_id != 16) {
                return view('app.orders.comingsoon');
            }
            $countyData = Null;
            $countyInfo = Null;
            $checklist = Null;


            if ($orderData->city_id) {
                if ($orderData->city_id) {
                    $countyData = DB::table('county_instructions')
                                ->where('city_id', $orderData->city_id)
                                ->where('county_id', $orderData->county_id)
                                ->where('state_id', $orderData->property_state)
                                ->whereNotNull('county_id')
                                ->where('lob_id', $orderData->lob_id)
                                ->first();
                        }
                 }else {
                     $countyData = DB::table('county_instructions')
                         ->where('county_id', $orderData->county_id)
                         ->where('state_id', $orderData->property_state)
                         ->whereNotNull('county_id')
                         ->where('lob_id', $orderData->lob_id)
                         ->first();
                     }

                if (!empty($countyData)) {
                    $countyDetailjson = json_decode($countyData->json, true);
                    $countyDetail = $this->removePlaceholders($countyDetailjson);

                    $commonData = DB::table('county_instructions')
                        ->whereNull('city_id')
                        ->where('county_id', $orderData->county_id)
                        ->where('state_id', $orderData->property_state)
                        ->whereNotNull('county_id')
                        ->where('lob_id', $orderData->lob_id)
                        ->first();
                    
                if ($commonData) {
                    $commonjson = json_decode($commonData->json, true);
                    $commonDetail = $this->removePlaceholders($commonjson);
                } else {
                    $commonDetail = [];
                }
                    
                function mergeWithDefaults($countyDetail, $commonDetail) {
                    foreach ($commonDetail as $key => $value) {
                        if (is_array($value)) {
                                
                            $countyDetail[$key] = mergeWithDefaults(
                                isset($countyDetail[$key]) ? $countyDetail[$key] : [],
                                $value
                            );
                        } else {
                            
                            if (!isset($countyDetail[$key]) || $countyDetail[$key] === null || $countyDetail[$key] === '') {
                                $countyDetail[$key] = $value;
                            }
                        }
                    }
                    return $countyDetail;
                }
                    
                $countyInfo = mergeWithDefaults($countyDetail, $commonDetail);
                    
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

        if(!empty($orderData->process_id)) {
            $checklist_conditions_with_product = DB::table('checklist')
                ->where('checklist.state_id', $orderData->property_state)
                ->where('checklist.process_id', $orderData->process_id)
                ->where('checklist.is_special', 1)
                ->where('checklist.lob_id', $orderData->lob_id)
                ->get();
        }

        $checklist_conditions_with_null = DB::table('checklist')
            ->where('checklist.state_id', $orderData->property_state)
            ->where('checklist.is_special', 1)
            ->where('checklist.lob_id', $orderData->lob_id)
            ->whereNull('checklist.process_id')
            ->get();

        if(!empty($orderData->process_id)) {
            $checklist_conditions = $checklist_conditions_with_product->merge($checklist_conditions_with_null);
        } else {
            $checklist_conditions = $checklist_conditions_with_null;
        }

        if(!empty($orderData->process_id)) {
            $checklist_conditions_with_product_2 = DB::table('checklist')
                ->where('checklist.state_id', $orderData->property_state)
                ->where('checklist.process_id', $orderData->process_id)
                ->where('checklist.is_special', 0)
                ->where('checklist.lob_id', $orderData->lob_id)
                ->get();
        }

        $checklist_conditions_with_null_2 = DB::table('checklist')
            ->where('checklist.state_id', $orderData->property_state)
            ->where('checklist.is_special', 0)
            ->where('checklist.lob_id', $orderData->lob_id)
            ->whereNull('checklist.process_id')
            ->get();

        if(!empty($orderData->process_id)) {
            $checklist_conditions_2 = $checklist_conditions_with_product_2->merge($checklist_conditions_with_null_2);
        } else {
            $checklist_conditions_2 = $checklist_conditions_with_null_2;
        }

            $stateList = State::select('id', 'short_code')->get();
            if(isset($orderData->property_state)){
                $countyList = County::select('id','county_name')->where('stateId',$orderData->property_state)->get();
            }
            else{
                $countyList = County::select('id', 'county_name')->where('id', 0)->get();
            }

    
            $cityList = City::select('id','city')->where('county_id',$orderData->property_county)->get();

            $productList = product::select('id','product_name')->get();

            $tierList = Tier::select('id','tier_id')->get();
   
        $lobList = DB::table('stl_lob')->select('id', 'name')->get(); 

            $primarySource = PrimarySource::select('id','source_name')->get();

            $instructionId = !empty($countyData->id) ? $countyData->id : '' ;

            if(in_array($user->user_type_id, [6,7,8]) && (Auth::id() == $orderData->assignee_user_id || Auth::id() == $orderData->assignee_qa_id)) {
            return view('app.orders.orderform', compact('orderData', 'lobList','countyList','cityList','tierList','productList','countyInfo', 'checklist_conditions_2', 'orderHistory','checklist_conditions','stateList','primarySource','instructionId'));
        } else if(in_array($user->user_type_id, [1,2,3,4,5,9])) {
            return view('app.orders.orderform', compact('orderData', 'lobList','countyList','cityList','tierList','productList','countyInfo', 'checklist_conditions_2', 'orderHistory','checklist_conditions','stateList','primarySource','instructionId'));
        } else {
            return redirect('/orders_status');
        }
        } else {
            return redirect('/orders_status');
        }
}


    private function removePlaceholders(array $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->removePlaceholders($value);
            } elseif (is_string($value)) {
                $data[$key] = str_replace('-', '', $value);
            }
        }
        return $data;
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
            $statusId = $request->orderStatus;


            if ($statusId == 5) {
            $update_status = OrderCreation::find($orderId)
            ->update([
                'status_id' => $request->orderStatus,
                'tier_id' => $request->tierId,
                'state_id' => $request->stateId,
                'county_id' => $request->countyId,
                'city_id' => $request->cityId,
                'completion_date' => Carbon::now(),
            ]);
            } else {
                $update_status = OrderCreation::find($orderId)
                ->update([
                    'status_id' => $request->orderStatus,
                    'tier_id' => $request->tierId,
                    'state_id' => $request->stateId,
                    'county_id' => $request->countyId,
                    'city_id' => $request->cityId,
                    'completion_date' => null,
                ]);
            }
            if($update_status) {
                DB::table('order_status_history')->insert([
                    'order_id' => $orderId,
                    'status_id' => $request->orderStatus,
                    'comment' => $request->orderComment,
                    'checked_array' => $request->checklistItems,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                DB::table('order_status_history')->where('order_id',$orderId)->update([
                    'comment' => $request->orderComment,
                ]);

                $getPrimaryName = DB::table('oms_primary_source')->where('id', $request->primarySource)->value('source_name');

                if(!empty($request->instructionId)) {
                $countyInstructionId = DB::table('county_instructions')
                    ->where('id', $request->instructionId)
                    ->first();

                $jsonData = json_decode($countyInstructionId->json, true);

                if (isset($jsonData['PRIMARY'])) {
                    $jsonData['PRIMARY']['PRIMARY_SOURCE'] = $getPrimaryName;
                }

                $updatedJsonData = json_encode($jsonData);

                DB::table('county_instructions')
                    ->where('id', $request->instructionId) 
                    ->update(['json' => $updatedJsonData]);
                }

                if($request->submit_type == 2) {
                    return response()->json(['redirect' => $request->orderId]);
                }
                return response()->json(['success' => 'Order Status Updated Successfully', 'redirect' => 'orders']);
            } else {
                return response()->json(['error' => 'Something went wrong']);
            }
        } else {
            if(!empty($request->orderId)){
                $orderId = $request->orderId;
                $statusId = $request->orderStatus;

            if ($statusId == 5) {
                $update_status = OrderCreation::find($orderId)
                ->update([
                    'tier_id' => $request->tierId,
                    'state_id' => $request->stateId,
                    'county_id' => $request->countyId,
                    'city_id' => $request->cityId,
                    'completion_date' => Carbon::now(),
                ]);
            }else{
                    $update_status = OrderCreation::find($orderId)
                    ->update([
                        'tier_id' => $request->tierId,
                        'state_id' => $request->stateId,
                        'county_id' => $request->countyId,
                        'city_id' => $request->cityId,
                        'completion_date' => null,
                ]);
            }
                DB::table('order_status_history')->where('order_id',$orderId)->update([
                    'comment' => $request->orderComment,
                ]);

            $getPrimaryName = DB::table('oms_primary_source')->where('id', $request->primarySource)->value('source_name');
            if(!empty($request->instructionId)) {
            $countyInstructionId = DB::table('county_instructions')
                ->where('id', $request->instructionId)
                ->first();

            $jsonData = json_decode($countyInstructionId->json, true);

            if (isset($jsonData['PRIMARY'])) {
                $jsonData['PRIMARY']['PRIMARY_SOURCE'] = $getPrimaryName;
            }

            $updatedJsonData = json_encode($jsonData);

            DB::table('county_instructions')
                ->where('id', $request->instructionId) 
                ->update(['json' => $updatedJsonData]);
            }

                if($request->submit_type == 2) {
                    return response()->json(['redirect' => $request->orderId]);
                }
                return response()->json(['success' => 'Order Status Updated Successfully', 'redirect' => 'orders']);
            } else {
                return response()->json(['error' => 'Something went wrong']);
            }
        }
    }


    public function coversheet_prep(Request $request, $orderId = null){
        $user = Auth::user();
        $orderData = OrderCreation::find($orderId);

        if (($user->user_type_id === 6 || $user->user_type_id === 8) && empty($orderData->associate_id)) {
            $orderData->update(['associate_id' => $user->id]);
        } elseif (in_array($user->user_type_id, [1, 2, 3, 4, 5, 9])) {

        } elseif (in_array($user->id, [$orderData->assignee_user_id, $orderData->assignee_qa_id, $orderData->associate_id])) {

        } else {
            return redirect('/orders_status');
        }

        return view('app.orders.coversheet_prep', compact('orderData'));
    }

    public function coversheet_submit(Request $request)
    {
        if(!empty($request->orderId) && !empty($request->orderStatus)) {
            $orderId = $request->orderId;
            $statusId = $request->orderStatus;

            if($statusId == 5){
                $update_status = OrderCreation::find($orderId)
            ->update([
                'status_id' => $request->orderStatus,
                'completion_date' => Carbon::now(),
            ]);
            }else{
            $update_status = OrderCreation::find($orderId)
            ->update([
                'status_id' => $request->orderStatus,
                'completion_date' => null,
            ]);
            }

            return response()->json(['success' => 'Order Status Updated Successfully']);
        } else {
            return response()->json(['error' => 'Something went wrong']);
        }
    }

}

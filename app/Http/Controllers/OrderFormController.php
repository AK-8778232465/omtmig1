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
use Stevebauman\Location\Facades\Location;
use Illuminate\Support\Facades\Http;
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
        ->leftJoin('oms_users as typist_users', 'oms_order_creations.typist_id', '=', 'typist_users.id')
        ->leftJoin('oms_users as typist_qas', 'oms_order_creations.typist_qc_id', '=', 'typist_qas.id')
        ->leftJoin('stl_lob', 'stl_item_description.lob_id', '=', 'stl_lob.id')
        ->leftJoin('stl_process', 'oms_order_creations.process_type_id', '=', 'stl_process.id')
        ->leftJoin('oms_city','oms_order_creations.city_id','=','oms_city.id')
        ->select(
            'oms_order_creations.id',
            'oms_order_creations.order_id as order_id',
            'oms_order_creations.status_id as status_id',
            'oms_order_creations.county_id as county_id',
            'oms_order_creations.process_id as process_id',
            'oms_order_creations.completion_date as completion_date',
            ///
                'oms_order_creations.city_id as city_id',
            'oms_city.city as city',
            ////
            'oms_order_creations.tier_id as tier_id',
            'oms_order_creations.order_date as order_date',
            'oms_order_creations.state_id as property_state', 
            'oms_order_creations.county_id as property_county', 
            'stl_item_description.project_code as project_code',
            'stl_item_description.process_name as process_name',
            'stl_item_description.qc_enabled as qc_enabled',
            'oms_state.short_code as short_code',
            'oms_state.state_name as state_name', 
            'county.county_name as county_name', 
            'county.county_name as county_name',
            'oms_order_creations.assignee_user_id',
            'oms_order_creations.tier_id',
            'oms_order_creations.assignee_qa_id',
            'oms_order_creations.typist_id',
            'oms_order_creations.typist_qc_id',
            'stl_item_description.lob_id as lob_id',
            'stl_lob.name as lob_name',
            'stl_client.client_name',
            'stl_item_description.client_id as client_id',
            'stl_process.name as process_type',
            'stl_process.id as stl_process_id',
            'stl_item_description.tat_value as tat_value',
            'oms_order_creations.accurate_read_id as read_value',
            DB::raw('CONCAT(assignee_users.emp_id, " (", assignee_users.username, ")") as assignee_user'),
            DB::raw('CONCAT(assignee_qas.emp_id, " (", assignee_qas.username, ")") as assignee_qa'),
            DB::raw('CONCAT(typist_users.emp_id, " (", typist_users.username, ")") as typist_user'),
            DB::raw('CONCAT(typist_qas.emp_id, " (", typist_qas.username, ")") as typist_qa'),
            'stl_item_description.process_name'
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
                    if(in_array($user->user_type_id, [1, 2, 3, 4, 5, 9, 10, 11])) {
                        $query->where('oms_order_creations.status_id', $request->status)->whereNotNull('oms_order_creations.assignee_user_id');
                    } else {
                        $query->where('oms_order_creations.status_id', $request->status)->where('oms_order_creations.assignee_user_id', $user->id);
                    }
                } elseif($request->status == 4) {
                    if(in_array($user->user_type_id, [1, 2, 3, 4, 5, 9, 10, 11])) {
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
                    if(in_array($user->user_type_id, [1, 2, 3, 4, 5, 9, 10, 11])) {
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
                if(in_array($user->user_type_id, [1, 2, 3, 4, 5, 6, 8, 9, 10, 11])) {
                    $query->whereNull('oms_order_creations.assignee_user_id')->where('oms_order_creations.status_id', 1);
                } else {
                    $query->whereNull('oms_order_creations.id');
                }
            } elseif ($request->status == 7) {
                if(in_array($user->user_type_id, [1, 2, 3, 4, 5, 7, 8, 9, 10, 11])) {
                    $query->whereNull('oms_order_creations.assignee_qa_id')->where('oms_order_creations.status_id', 4);
                } else {
                    $query->whereNull('oms_order_creations.id');
                }
            }

        $orderData = $query->first();

        if(!empty($orderData)) {
            $excludedClients = [16, 82, 84, 85, 86, 87, 88, 89, 90, 91, 92];
            if (!in_array($orderData->client_id, $excludedClients)) {
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
                         ->whereNull('city_id')
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



            $orderHistory = DB::table('order_status_history')->where('order_id', $orderId)->orderBy('id', 'desc')->first();

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

            $tierList = Tier::select('id', 'tier_id')
                ->whereRaw('JSON_CONTAINS(stl_process_id, \'["' . $orderData->stl_process_id . '"]\')')
                ->get();
   
        $lobList = DB::table('stl_lob')->select('id', 'name')->get(); 

            $primarySource = PrimarySource::select('id','source_name')->get();

            $clientIdList = DB::table('oms_vendor_information')
                ->select('id', 'accurate_client_id')->where('product_id', $orderData->process_id)->orderBy('accurate_client_id')  
                ->get();

            $userinput = DB::table('production_tracker')
                ->where('order_id', $orderData->id)->first();

                $vendorequirements = DB::table('county_instructions')
                ->where('county_id', $orderData->county_id)
                ->where('state_id', $orderData->property_state)
                ->where('city_id', $orderData->city_id)
                ->whereNotNull('county_id')
                ->where('lob_id', $orderData->lob_id)
                ->first();
    
                if ($vendorequirements) {
                    $vendorequirements = json_decode($vendorequirements->json, true);
                } else {
                    $vendorequirements = [];
                }

            $instructionId = !empty($countyData->id) ? $countyData->id : '' ;

            $orderstatusInfo = DB::table('order_status_history')
                ->leftJoin('oms_users', 'order_status_history.created_by', '=', 'oms_users.id')
                ->leftJoin('oms_status', 'order_status_history.current_status_id', '=', 'oms_status.id')
                ->select(
                    'order_status_history.comment',
                    'oms_status.status',
                    'oms_users.emp_id',
                    'oms_users.username',
                    'order_status_history.created_at'
                )
                ->where('order_status_history.order_id', $orderData->id)
                ->whereNotNull('order_status_history.comment')
                ->orderBy('order_status_history.id', 'desc')
                ->get();
                
                $sourcedetails = DB::table('oms_accurate_source')->get();

                if ($orderData->lob_id == 8) {
                    $famsTypingInfo = DB::table('oms_fams_typing_info')
                        ->where(function ($query) use ($orderData) {
                            $query->where('lob_id', $orderData->lob_id)
                                ->where(function ($query) use ($orderData) {
                                    $query->where('state_id', $orderData->property_state)
                                            ->orWhereNull('state_id');
                                });
                        })
                        ->where(function ($query) use ($orderData) {
                            $query->where('product_id', $orderData->process_id)
                                ->orWhereNull('product_id');
                        })
                        ->get();
                    
                    if ($famsTypingInfo->isEmpty()) {
                        $famsTypingInfo = DB::table('oms_fams_typing_info')
                            ->where('lob_id', $orderData->lob_id)
                            ->whereNull('state_id')
                            ->whereNull('product_id')
                            ->get();
                    }
                    $avr_dr_required = (object) ['required' => null]; 
                
                    if ($famsTypingInfo->contains('area', 'Parcel ID and Legal requirements')) {
                        $avr_dr_required = DB::table('oms_avr_dr_required_info')
                            ->select('required')
                            ->where('state', $orderData->state_name)
                            ->where('county', $orderData->county_name)
                            ->first();
                    }
                
                    $famsTypingInfo->transform(function ($item) use ($avr_dr_required) {
                        $item->required = $avr_dr_required->required ?? '';
                
                        if ($item->area == "Parcel ID and Legal requirements") {
                            if ($item->required == 1) {
                                $item->comments = "Parcel Required";
                            } elseif ($item->required == 2) {
                                $item->comments = "Legal Required";
                            }
                        }
                
                        return $item;
                    });
                
                } else {
                    $famsTypingInfo = DB::table('oms_fams_typing_info')
                            ->where(function ($query) use ($orderData) {
                                $query->where('lob_id', $orderData->lob_id)
                                    ->where(function ($query) use ($orderData) {
                                        $query->where('state_id', $orderData->property_state)
                                            ->orWhereNull('state_id');
                                    });
                            })
                            ->where(function ($query) use ($orderData) {
                                $query->where('product_id', $orderData->process_id)
                                    ->orWhereNull('product_id');
                            })
                            ->get()
                            ->reject(function ($item) use ($orderData) {
                                $productIds = [117, 118, 119, 120, 129, 130];
                                return $item->lob_id == 6 &&
                                   in_array($orderData->process_id, $productIds) &&
                                    $orderData->property_state == 37 &&
                                   $item->comments == 'If only docket available in SP, we need to add Actual Copy to follow note';
                            });
                    
                    if ($famsTypingInfo->isEmpty()) {
                        $famsTypingInfo = DB::table('oms_fams_typing_info')
                            ->where('lob_id', $orderData->lob_id)
                                ->whereNull('state_id')
                            ->whereNull('product_id')
                            ->get();
                    }
                }
                
                $getjsonDetails = DB::table('taxes')
                ->where('order_id', $orderData->id)
                ->pluck('json')
                ->toArray();
            
            // Check if $getjsonDetails is empty, and if so, set each entry as null
            if (empty($getjsonDetails)) {
                $getjsonDetails = [
                    'tax_form' => null,
                    'selected_type' => null,
                    'search_data' => null,
                    'order_id' => null,
                    'type_dd' => null,
                    'fiscal_year' => null,
                    'tax_id_number' => null,
                    'tax_described_number' => null,
                    'tax_state_number' => null,
                    'taxing_entity_dd' => null,
                    'phone_number' => null,
                    'street_address1' => null,
                    'street_address2' => null,
                    'zip_number' => null,
                    'override_id' => null,
                    'first_estimate_id' => null,
                    'second_estimate_id' => null,
                    'third_estimate_id' => null,
                    'fourth_estimate_id' => null,
                    'first_partially_paid_amount' => null,
                    'second_partially_paid_amount' => null,
                    'third_partially_paid_amount' => null,
                    'fourth_partially_paid_amount' => null,
                    'city_number' => null,
                    'state_dd' => null,
                    'total_annual_tax' => null,
                    'payment_frequency_dd' => null,
                    'land_data' => null,
                    'improvements' => null,
                    'exemption_mortgage' => null,
                    'exemption_homeowner' => null,
                    'exemption_homestead' => null,
                    'exemption_additional' => null,
                    'others' => null,
                    'net_value' => null,
                    'first_installment_amount' => null,
                    'first_installment_texes_out' => null,
                    'first_installment_discount_expires' => null,
                    'first_installment_tax_due' => null,
                    'first_installment_tax_delinquent' => null,
                    'first_installment_good_through' => null,
                    'first_installment_tax_paid' => null,
                    'second_installment_amount' => null,
                    'second_installment_texes_out' => null,
                    'second_installment_discount_expires' => null,
                    'second_installment_tax_due' => null,
                    'second_installment_tax_delinquent' => null,
                    'second_installment_good_through' => null,
                    'second_installment_tax_paid' => null,
                    'third_installment_amount' => null,
                    'third_installment_texes_out' => null,
                    'third_installment_discount_expires' => null,
                    'third_installment_tax_due' => null,
                    'third_installment_tax_delinquent' => null,
                    'third_installment_good_through' => null,
                    'third_installment_tax_paid' => null,
                    'fourth_installment_amount' => null,
                    'fourth_installment_texes_out' => null,
                    'fourth_installment_discount_expires' => null,
                    'fourth_installment_tax_due' => null,
                    'fourth_installment_tax_delinquent' => null,
                    'fourth_installment_good_through' => null,
                    'fourth_installment_tax_paid' => null,
                    'notes' => null
                ];
            } else {
                // Decode each JSON entry as usual if data exists
                $getjsonDetails = array_map(function ($item) {
                    $decodedItem = json_decode($item, true);
                    return is_array($decodedItem) ? $decodedItem : json_decode($decodedItem, true);
                }, $getjsonDetails);
            }
            
            if(in_array($user->user_type_id, [6,7,8]) && (Auth::id() == $orderData->assignee_user_id || Auth::id() == $orderData->assignee_qa_id)) {
            return view('app.orders.orderform', compact('orderData','vendorequirements', 'lobList','countyList','cityList','tierList','productList','countyInfo', 'checklist_conditions_2', 'orderHistory','checklist_conditions','stateList','primarySource','instructionId','clientIdList','userinput','orderstatusInfo','sourcedetails','famsTypingInfo','getjsonDetails'));
        } else if(in_array($user->user_type_id, [1, 2, 3, 4, 5, 9, 10, 11])) {
            return view('app.orders.orderform', compact('orderData','vendorequirements', 'lobList','countyList','cityList','tierList','productList','countyInfo', 'checklist_conditions_2', 'orderHistory','checklist_conditions','stateList','primarySource','instructionId','clientIdList','userinput','orderstatusInfo','sourcedetails','famsTypingInfo','getjsonDetails'));
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

        if (!empty($request->getID) && ($request->getID == 82)) {
            $readId = $request->readed_value;
            $id = $request->orderId;
            $updated = DB::table('oms_order_creations')
            ->where('id', $id)
            ->update(['accurate_read_id' => $readId]);

            $getdata = DB::table('production_tracker')->where('order_id', $request->orderId)->first();
            
            if ($getdata) {
                DB::table('production_tracker')->where('order_id', $request->orderId)
                    ->update([
                        'accurate_client_id' => $request->accurateClientId,
                        'portal_fee_cost' => $request->portalfeecost,
                        'source' => $request->source,
                        'copy_cost' => $request->copyCost,
                        'no_of_search_done' => $request->noOfSearch,
                        'no_of_documents_retrieved' => $request->documentRetrive,
                        'title_point_account' => $request->titlePointAccount,
                        'purchase_link' => $request->purchase_link,
                        'username' => $request->username,
                        'password' => $request->password,
                        'file_path' => $request->file_path,
                        'updated_by' => Auth::id(),
                        'updated_at' => Carbon::now(),
                    ]);
            } else {
                DB::table('production_tracker')->insert([
                    'order_id' => $request->orderId,
                    'accurate_client_id' => $request->accurateClientId,
                    'portal_fee_cost' => $request->portalfeecost,
                    'source' => $request->source,
                    'copy_cost' => $request->copyCost,
                    'no_of_search_done' => $request->noOfSearch,
                    'no_of_documents_retrieved' => $request->documentRetrive,
                    'title_point_account' => $request->titlePointAccount,
                    'purchase_link' => $request->purchase_link,
                    'username' => $request->username,
                    'password' => $request->password,
                    'file_path' => $request->file_path,
                    'created_by' => Auth::id(),
                    'created_at' => Carbon::now(),
                ]);
            }
        }


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
                'completion_date' => Carbon::now()->setTimezone('America/New_York'),
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
                    'current_status_id' => $request->currentStatusId,
                    'comment' => $request->orderComment,
                    'checked_array' => $request->checklistItems,
                    'created_at' => Carbon::now()->setTimezone('America/New_York'),
                    'created_by' => Auth::id(),
                ]);

                    DB::table('oms_order_creations')
                    ->where('id', $orderId)
                    ->update([
                        'comment' => $request->orderComment,
                    ]);

                if($request->currentStatusId == 4){
                    DB::table('oms_order_creations')
                    ->where('id', $orderId)
                    ->update([
                        'qc_comment' => $request->orderComment,
                        'updated_qc' => Auth::id(),
                    ]);
                }

                DB::table('oms_order_creations')
                    ->where('id', $orderId)
                    ->update(['status_updated_time' => Carbon::now()
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
                    'completion_date' => Carbon::now()->setTimezone('America/New_York'),
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
                'completion_date' => Carbon::now()->setTimezone('America/New_York'),
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


    public function updateClickTime(Request $request)
    {
    $request->validate([
        'order_id' => 'required|integer',
        'status' => 'required|integer',
    ]);

    $orderId = $request->input('order_id');
    $statusId = $request->input('status');

    if ($statusId == 1) {
        $order = DB::table('oms_order_creations')
            ->where('id', $orderId)
            ->where('status_id', 1)
            ->where('assignee_user_id', Auth::id())
            ->first();

        if ($order) {
            $existingHistory = DB::table('order_status_history')->where('order_id', $orderId)->first();
            if (!$existingHistory) {
                DB::table('order_status_history')->insert([
                    'order_id' => $orderId,
                    'status_id' => $statusId,
                    'comment' => null,
                    'checked_array' => null,
                    'created_at' => Carbon::now()->setTimezone('America/New_York'),
                    'created_by' => Auth::id(),
                ]);
                return response()->json(['message' => 'Time duration updated successfully.']);
            } else {
                return response()->json(['message' => 'Order status history already exists.'], 409);
            }
        } else {
            return response()->json(['message' => 'Failed to update time duration.'], 500);
        }
    } else {
        return response()->json(['message' => 'Invalid status ID.'], 400);
    }
    }

    public function getaccurateClientId(Request $request){
        $getclientid = $request->client_id;
        $getproductid = $request->product_id;
        $orderId = $request->order_id;
    
        $getUserInputdetails = DB::table('production_tracker')
            ->where('order_id', $orderId)
            ->where('accurate_client_id', $getclientid)
            ->first();
    
        if(!empty($getclientid) && !empty($getproductid)){
            $vendorDetail = DB::table('oms_vendor_information')
                ->where('id', $getclientid)
                ->where('product_id', $getproductid)
                ->first();
        } else {
            return response()->json(['message' => 'Product Is Not Available']);
        }
    
        return response()->json([
            'vendorDetail' => $vendorDetail,
            'getUserInputdetails' => $getUserInputdetails
        ]);
    }

    public function taxform_submit(Request $request)
    {
        // Define an array to map old field names to new field names
        $fieldMapping = [
            'tax_status' => 'tax_form',
            'get_data' => 'selected_type',
            'search_input' => 'search_data',
            'order_id' => 'order_id',
            'type_id' => 'type_dd',
            'fiscal_yr_id' => 'fiscal_year',
            'tax_id' => 'tax_id_number',
            'tax_described_id' => 'tax_described_number',
            'tax_state_id' => 'tax_state_number',
            'taxing_id' => 'taxing_entity_dd',
            'phone_num' => 'phone_number',
            'street_address1' => 'street_address1',
            'street_address2' => 'street_address2',
            'zip_id' => 'zip_number',
            'override_id' => 'override_id',
            'first_estimate_id' => 'first_estimate_id',
            'second_estimate_id' => 'second_estimate_id',
            'third_estimate_id' => 'third_estimate_id',
            'fourth_estimate_id' => 'fourth_estimate_id',
            'first_partially_paid_amount' => 'first_partially_paid_amount',
            'second_partially_paid_amount' => 'second_partially_paid_amount',
            'third_partially_paid_amount' => 'third_partially_paid_amount',
            'fourth_partially_paid_amount' => 'fourth_partially_paid_amount',
            'city_id' => 'city_number',
            'state' => 'state_dd',
            'total_annual_tax' => 'total_annual_tax',
            'payment_frequency' => 'payment_frequency_dd',
            'land' => 'land_data',
            'improvement' => 'improvements',
            'exemption_mortgage' => 'exemption_mortgage',
            'exemption_homeowner' => 'exemption_homeowner',
            'exemption_homestead' => 'exemption_homestead',
            'exemption_additional' => 'exemption_additional',
            'others' => 'others',
            'net_value' => 'net_value',
            'first_amount_id' => 'first_installment_amount',
            'first_texes_out_id' => 'first_installment_texes_out',
            'first_discount_expires_id' => 'first_installment_discount_expires',
            'first_tax_due_id' => 'first_installment_tax_due',
            'first_tax_delinquent_id' => 'first_installment_tax_delinquent',
            'first_good_through_id' => 'first_installment_good_through',
            'first_tax_paid_id' => 'first_installment_tax_paid',
            'second_amount_id' => 'second_installment_amount',
            'second_texes_out_id' => 'second_installment_texes_out',
            'second_discount_expires_id' => 'second_installment_discount_expires',
            'second_tax_due_id' => 'second_installment_tax_due',
            'second_tax_delinquent_id' => 'second_installment_tax_delinquent',
            'second_good_through_id' => 'second_installment_good_through',
            'second_tax_paid_id' => 'second_installment_tax_paid',
            'third_amount_id' => 'third_installment_amount',
            'third_texes_out_id' => 'third_installment_texes_out',
            'third_discount_expires_id' => 'third_installment_discount_expires',
            'third_tax_due_id' => 'third_installment_tax_due',
            'third_tax_delinquent_id' => 'third_installment_tax_delinquent',
            'third_good_through_id' => 'third_installment_good_through',
            'third_tax_paid_id' => 'third_installment_tax_paid',
            'fourth_amount_id' => 'fourth_installment_amount',
            'fourth_texes_out_id' => 'fourth_installment_texes_out',
            'fourth_discount_expires_id' => 'fourth_installment_discount_expires',
            'fourth_tax_due_id' => 'fourth_installment_tax_due',
            'fourth_tax_delinquent_id' => 'fourth_installment_tax_delinquent',
            'fourth_good_through_id' => 'fourth_installment_good_through',
            'fourth_tax_paid_id' => 'fourth_installment_tax_paid',
            'exampleFormControlTextarea1' => 'notes'
        ];
    
        // Initialize an array for the renamed fields
        $renamedData = [];
    
        // Loop through the field mapping to rename the fields
        foreach ($fieldMapping as $oldField => $newField) {
            $renamedData[$newField] = $request->input($oldField);
        }
    
        // Encode the renamed data as JSON
        $jsonData = json_encode($renamedData);
    
        // Check if the order_id already exists
        $existingRecord = DB::table('taxes')->where('order_id', $renamedData['order_id'])->first();
    
        if ($existingRecord) {
            // Update existing record
            $updated = DB::table('taxes')
                ->where('order_id', $renamedData['order_id'])
                ->update([
                    'json' => $jsonData,
                    'updated_by' => Auth::id(),
                    'updated_at' => Carbon::now(),
                ]);
    
            $status = $updated ? 'success' : 'error';
            $message = $updated ? 'Data updated successfully' : 'Failed to update data';
        } else {
            // Insert new record
            $inserted = DB::table('taxes')->insert([
                'order_id' => $renamedData['order_id'],
                'json' => $jsonData,
                'updated_by' => Auth::id(),
                'updated_at' => Carbon::now(),
            ]);
    
            $status = $inserted ? 'success' : 'error';
            $message = $inserted ? 'Data inserted successfully' : 'Failed to insert data';
        }
    
        // Prepare the response with the renamed fields
        $responseData = [
            'status' => $status,
            'data' => $renamedData,
            'message' => $message,
        ];
    
        // Return the response as JSON
        return response()->json($responseData, $status === 'success' ? 201 : 500);
    }    

    
}
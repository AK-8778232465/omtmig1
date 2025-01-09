<?php

namespace App\Http\Controllers;

use App\Jobs\RetryFtcOrder;
use App\Models\Lob;
use App\Models\State;
use App\Models\Status;
use App\Models\County;
use App\Models\User;
use App\Models\Process;
use App\Models\Tier;
use App\Models\City;
use App\Models\OmsAttachmentHistory;
use App\Models\Product;
use App\Models\PrimarySource;
use App\Models\OrderCreation;
use App\Models\TaxAttachmentFile;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stevebauman\Location\Facades\Location;
use Illuminate\Support\Facades\Http;
use Session;
use DataTables;
use Carbon\Carbon;
use FPDF;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade as PDF;
use Intervention\Image\Facades\Image;
use PhpOffice\PhpWord\IOFactory;
use setasign\Fpdi\Fpdi;
use App\Models\SupportingDocs;
use App\Http\Traits\FastTaxAPI;
use App\Http\Traits\Retryftc;


class OrderFormController extends Controller
{
    use FastTaxAPI;
    use Retryftc;

    protected $taxCertPDFController;


    public function __construct(TaxCertPDFController $taxCertPDFController)
    {
        $this->taxCertPDFController = $taxCertPDFController;

    }

    public function index(Request $request, $orderId = null)
    {
      
        $pathInfo = $request->getPathInfo();
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
        ->leftJoin('oms_users as tax_user', 'oms_order_creations.tax_user_id', '=', 'tax_user.id')
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
            'oms_order_creations.tax_user_id',
            'oms_order_creations.tax_bucket',
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
            DB::raw('CONCAT(tax_user.emp_id, " (", tax_user.username, ")") as tax_user_name'),
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
                $request->status != 7 && 
                $request->status != 'tax'
            ) {
                if ($request->status == 1) {
                    if(in_array($user->user_type_id, [1, 2, 3, 4, 5, 9, 10, 11, 23, 24])) {
                        $query->where('oms_order_creations.status_id', $request->status)->whereNotNull('oms_order_creations.assignee_user_id');
                    } else {
                        $query->where('oms_order_creations.status_id', $request->status)->where('oms_order_creations.assignee_user_id', $user->id);
                    }
                } elseif($request->status == 4) {
                    if(in_array($user->user_type_id, [1, 2, 3, 4, 5, 9, 10, 11, 23, 24])) {
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

                        }elseif(in_array($user->user_type_id, [22])) {
                            $query->where(function ($optionalquery) use($user) {
                                $optionalquery->where('oms_order_creations.typist_id', $user->id)
                                    ->orWhere('oms_order_creations.typist_qc_id', $user->id);
                            });

                        }
                    }
                } else {
                    if(in_array($user->user_type_id, [1, 2, 3, 4, 5, 9, 10, 11, 23, 24])) {
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
                }elseif(in_array($user->user_type_id, [22])) {
                    $query->where(function ($optionalquery) use($user) {
                        $optionalquery->where('oms_order_creations.typist_id', $user->id)
                            ->orWhere('oms_order_creations.typist_qc_id', $user->id);
                    });

                } else {
                    $query->whereNotNull('oms_order_creations.assignee_user_id');
                    
                }
            } elseif ($request->status == 6) {
                if(in_array($user->user_type_id, [1, 2, 3, 4, 5, 6, 8, 9, 10, 11, 23, 24])) {
                    $query->whereNull('oms_order_creations.assignee_user_id')->where('oms_order_creations.status_id', 1);
                } else {
                    $query->whereNull('oms_order_creations.id');
                }
            } elseif ($request->status == 7) {
                if(in_array($user->user_type_id, [1, 2, 3, 4, 5, 7, 8, 9, 10, 11, 23, 24])) {
                    $query->whereNull('oms_order_creations.assignee_qa_id')->where('oms_order_creations.status_id', 4);
                } else {
                    $query->whereNull('oms_order_creations.id');
                }

            }

            if (str_ends_with($pathInfo, '/tax')) { // Check if the path ends with "/tax"
                if (in_array($user->user_type_id, [25])) {
                    $query->where('oms_order_creations.tax_user_id', $user->id)
                          ->orWhere('oms_order_creations.tax_bucket', 1);
                } else {
                    $query->where('oms_order_creations.tax_bucket', 1);
                }
            }
        $orderData = $query->first();

        if(!empty($orderData)) {
            $excludedClients = [16, 82, 84, 85, 86, 87, 88, 89, 90, 91, 92, 13, 2, 93, 94, 95, 96, 97, 98, 99, 100];
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
                            ->select('id', 'accurate_client_id')
                            ->whereRaw('JSON_CONTAINS(product_id, ?)', [json_encode((string)$orderData->process_id)])
                            ->orderBy('accurate_client_id')
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
                

                try {
                    $State = $orderData->short_code; // Access the 'state_name' column from the result
                    $County = $orderData->county_name; // Access the 'county_name' column
                    $CountyId = $orderData->client_id; // Access the 'county_id' column
              
                    // Check if the values are not null and proceed
                    if ($State !== null && $County !== null && $CountyId !== null) {
                        $data = [
                            "state" => $State,
                            "countyname" => $County,
                            "countyId" => $CountyId
                        ];
                
                        $ftcResponse = $this->getFtcData('CountyStatus.php', $data);
             
                        // Check the FTC response
                        if (isset($ftcResponse['Status']) && in_array(strtolower($ftcResponse['Status']),['automated','manual'])) {
                       
                            DB::table('oms_order_creations')
                                ->where('id', $orderId)
                                ->update([
                                    'tax_search_status' => $ftcResponse, // Ensure the JSON data is saved as a string
                                    'status_updated_time' => Carbon::now(),
                                ]);
                        } 
                    } 
                } catch (\Exception $e) {
                    // Log the error and return a generic error message
                    \Log::error('Error processing FTC data: ' . $e->getMessage(), [
                        'exception' => $e,
                        'orderData' => $orderData
                    ]);
                
                }
                


                $getjsonDetails = DB::table('taxes')
                    ->where('order_id', $orderData->id)
                    ->pluck('json')
                    ->toArray();

                $gettaxesDetails = DB::table('taxes')
                ->where('order_id', $orderData->id)
                ->select('submit_btn')
                ->first();

                // return response()->json(['submit_btn' => $gettaxesDetails]);

            // Check if $getjsonDetails is empty, and if so, set each entry as null
            if (empty($getjsonDetails)) {
                $getjsonDetails = [
                    'order_id' => null,
                    'type_dd' => null,
                    'fiscal_year' => null,
                    'tax_id_number' => null,
                    'extracted_parcel' => null,
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
                    'totalValue' => null,
                    'first_partially_paid_amount' => null,
                    'second_partially_paid_amount' => null,
                    'third_partially_paid_amount' => null,
                    'fourth_partially_paid_amount' => null,
                    'firstInstStatus'=>null,
                    'second_paid_id' => null,
                    'third_paid_id' => null,
                    'fourth_paid_id' => null,
                    'first_due_id' => null,
                    'second_due_id' => null,
                    'third_due_id' => null,
                    'fourth_due_id' => null,
                    'firstDeliqDate_flag' => null,
                    'second_delinquent_id' => null,
                    'third_delinquent_id' => null,
                    'fourth_delinquent_id' => null,
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
                    'notes' => null,
                    'hidebutton' => 1,
                ];
            } else {
                $getjsonDetails = array_map(function ($item) {
                    $decodedItem = json_decode($item, true);
                    return is_array($decodedItem) ? $decodedItem : json_decode($decodedItem, true);
                }, $getjsonDetails);
            }
            $taxType = DB::table('oms_tax_type')->get();
            $taxEntity = DB::table('oms_tax_entity')->get();
            $taxPaymentFrequency = DB::table('oms_tax_payment_frequency')->get();

            $getTaxJson = DB::table('oms_order_creations')
                ->where('id', $orderData->id)
                ->pluck('tax_search_status')
                ->first();

            $inpuTaxJson = DB::table('oms_order_creations')
                ->where('id', $orderData->id)
                ->pluck('tax_json')
                ->first();

            $getApi = DB::table('oms_order_creations')
                ->where('id', $orderData->id)
                ->select('api_data')
                ->first();    

            $getTaxJson = json_decode($getTaxJson, true);
            $inpuTaxJson = json_decode($inpuTaxJson, true);

            $getTaxBucket = DB::table('oms_order_creations')
                ->where('id', $orderData->id)
                ->get();

            $orderTaxInfo = DB::table('oms_tax_comments_histroy')
                ->leftJoin('oms_users', 'oms_tax_comments_histroy.updated_by', '=', 'oms_users.id')
                ->select(
                    'oms_tax_comments_histroy.comment',
                    'oms_users.emp_id',
                    'oms_users.username',
                    'oms_tax_comments_histroy.updated_at'
                )
                ->where('oms_tax_comments_histroy.order_id', $orderData->id)
                ->whereNotNull('oms_tax_comments_histroy.comment')
                ->orderBy('oms_tax_comments_histroy.id', 'desc')
                ->get();

                $taxInfo = DB::table('taxes')->where('order_id', $orderData->id)->first();

                if ($taxInfo && isset($taxInfo->json)) {
                    $jsonString = json_decode($taxInfo->json, true);

                    if (json_last_error() === JSON_ERROR_NONE) {
                        $formattedJson = json_encode($jsonString, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                        $input_json = strip_tags(html_entity_decode($formattedJson));
                    } else {
                        $input_json = 'Invalid JSON';
                    }
                } else {
                    $input_json = 'Json Not Available';
                }

                // return response()->json($orderData);

            if(in_array($user->user_type_id, [6,7,8]) && (Auth::id() == $orderData->assignee_user_id || Auth::id() == $orderData->assignee_qa_id)) {
            return view('app.orders.orderform', compact('orderData','vendorequirements', 'lobList','countyList','cityList','tierList','productList','countyInfo', 'checklist_conditions_2', 'orderHistory','checklist_conditions',
                                                        'stateList','primarySource','instructionId','clientIdList','userinput','orderstatusInfo',
                                                        'sourcedetails','famsTypingInfo','getjsonDetails','taxType','taxEntity','taxPaymentFrequency','getTaxJson', 'getTaxBucket','orderTaxInfo','getApi','inpuTaxJson','gettaxesDetails','input_json'));
        } else if(in_array($user->user_type_id, [1, 2, 3, 4, 5, 9, 10, 11, 23, 24, 25])) {
            return view('app.orders.orderform', compact('orderData','vendorequirements', 'lobList','countyList','cityList','tierList','productList','countyInfo',
                                                        'checklist_conditions_2', 'orderHistory','checklist_conditions','stateList','primarySource','instructionId',
                                                        'clientIdList','userinput','orderstatusInfo','sourcedetails','famsTypingInfo','getjsonDetails','taxType','taxEntity','taxPaymentFrequency','getTaxJson', 'getTaxBucket','orderTaxInfo','getApi','inpuTaxJson', 'gettaxesDetails','input_json'));
        }else if(in_array($user->user_type_id, [22]) && (Auth::id() == $orderData->typist_id || Auth::id() == $orderData->typist_qc_id) && $orderData->status_id !=4 && $orderData->status_id != 5) {
            return view('app.orders.orderform', compact('orderData','vendorequirements', 'lobList','countyList','cityList','tierList','productList','countyInfo',
                                                        'checklist_conditions_2', 'orderHistory','checklist_conditions','stateList','primarySource','instructionId',
                                                        'clientIdList','userinput','orderstatusInfo','sourcedetails','famsTypingInfo','getjsonDetails','taxType','taxEntity','taxPaymentFrequency','getTaxJson', 'getTaxBucket','orderTaxInfo','getApi','inpuTaxJson', 'gettaxesDetails','input_json'));
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
                'status' => 'required',
            ]);

            $user = Auth::user();
            $orderId = $request->input('order_id');
            $statusId = $request->input('status');

            // Map status ID to fields
            $fieldMap = [
                4 => 'assignee_qa_id',
                16 => 'typist_id',
                17 => 'typist_qc_id'
            ];

            if($statusId == "tax" && in_array($user->user_type_id, [25])){
                $order = DB::table('oms_order_creations')
                    ->where('id', $orderId)
                    ->whereNull('tax_user_id')
                    ->where('tax_bucket', 1)
                    ->first();
                if ($order) {
                    DB::table('oms_order_creations')->where('id', $orderId)->update([
                        'tax_user_id' => $user->id,
                    ]);
                    return response()->json(['message' => 'Order updated successfully.']);
                }
            }

            // Check for allowed statuses and user types
            if (in_array($statusId, [4, 16, 17]) && in_array($user->user_type_id, [7, 8, 9, 10, 11, 22])) {
                $field = $fieldMap[$statusId];

                $order = DB::table('oms_order_creations')
                    ->where('id', $orderId)
                    ->where('status_id', $statusId)
                    ->whereNull($field)
                    ->first();

                if ($order) {
                    DB::table('oms_order_creations')->where('id', $orderId)->update([
                        $field => $user->id,
                    ]);
                    return response()->json(['message' => 'Order updated successfully.']);
                }
            }

    // If status ID is 1
    if ($statusId == 1) {
        $order = $this->findOrder($orderId, 1, $user->id);

        if ($order) {
            $historyExists = DB::table('order_status_history')
                ->where('order_id', $orderId)
                ->doesntExist();

            if ($historyExists) {
                DB::table('order_status_history')->insert([
                    'order_id' => $orderId,
                    'status_id' => $statusId,
                    'comment' => null,
                    'checked_array' => null,
                    'created_at' => Carbon::now()->setTimezone('America/New_York'),
                    'created_by' => $user->id,
                ]);

                return response()->json(['message' => 'Time duration updated successfully.']);
            } else {
                return response()->json(['message' => 'Order status history already exists.'], 409);
            }
        } else {
            return response()->json(['message' => 'Order not found.'], 404);
        }
    }

    return response()->json(['message' => 'Invalid status ID or unauthorized action.'], 400);
}

private function findOrder($orderId, $statusId, $userId = null)
{
    return DB::table('oms_order_creations')
        ->where('id', $orderId)
        ->where('status_id', $statusId)
        ->when($userId, fn($query) => $query->where('assignee_user_id', $userId))
        ->first();
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
                ->whereRaw('JSON_CONTAINS(product_id, ?)', [json_encode((string)$getproductid)])
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
        $submitBtnValue = $request->input('submit_btn');

        $fieldMapping = [
            'extracted_parcel' => $request['parcel'],
            'order_id' => $request['order_id'],
            'type_id' => $request['type_id'],
            'taxYear' => $request['fiscal_yr_id'],
            'taxAccountId' => $request['tax_id'],
            'taxing_entity_dd'=> $request['taxing_entity_dd'],
            'tax_described_id' => $request['tax_described_id'],
            'tax_state_id' => $request['tax_state_id'],
            'taxing_id' => $request['taxing_id'],
            'phone_number' => $request['phone_num'],
            'streetAddress1' => $request['street_address1'],
            'street_address2' => $request['street_address2'],
            'zip_id' => $request['zip_id'],
            'override_id' => $request['override_id'],
            'first_estimate_id' => $request['first_estimate_id'],
            'second_estimate_id' => $request['second_estimate_id'],
            'third_estimate_id' => $request['third_estimate_id'],
            'fourth_estimate_id' => $request['fourth_estimate_id'],
            'first_partially_paid_amount' => $request['first_partially_paid_amount'],
            'second_partially_paid_amount' => $request['second_partially_paid_amount'],
            'third_partially_paid_amount' => $request['third_partially_paid_amount'],
            'fourth_partially_paid_amount' => $request['fourth_partially_paid_amount'],
            'firstInstStatus' => $request['first_paid_id'],
            'secondInstStatus' => $request['second_paid_id'],
            'thirdInstStatus' => $request['third_paid_id'],
            'fourthInstStatus' => $request['fourth_paid_id'],
            'firstDeliqDate_flag' => $request['first_delinquent_id'],
            'secondDeliqDate_Eflag' => $request['second_delinquent_id'],
            'thirdDeliqDate_Eflag' => $request['third_delinquent_id'],
            'fourthDeliqDate_Eflag' => $request['fourth_delinquent_id'],
            'city_id' => $request['city_id'],
            'state' => $request['state'],
            'totalValue' => $request['total_annual_tax'],
            'numberOfInst' => $request['payment_frequency'],
            'landValue' => $request['land'],
            'improvementValue' => $request['improvement'],
            'exemption_mortgage' => $request['exemption_mortgage'],
            'homeownerExemption' => $request['exemption_homeowner'],
            'exemption_homestead' => $request['exemption_homestead'],
            'veteranExemption' => $request['exemption_additional'],
            'otherExemption' => $request['others'],
            'netTaxable' => $request['net_value'],
            'firstInstBilledAmt' => $request['first_amount_id'],
            'first_texes_out_id' => $request['first_texes_out_id'],
            'first_discount_expires_id' => $request['first_discount_expires_id'],
            'firstInstDueDate' => $request['first_tax_due_id'],
            'firstDeliqDate' => $request['first_tax_delinquent_id'],
            // 'first_good_through_id' => 'first_installment_good_through',
            'firstInstPaidDate' => $request['first_tax_paid_id'],
            'secondInstBilledAmt' => $request['second_amount_id'],
            'second_texes_out_id' => $request['second_texes_out_id'],
            'second_discount_expires_id' => $request['second_discount_expires_id'],
            'secondInstDueDate' => $request['second_tax_due_id'],
            'secondDeliqDate' => $request['second_tax_delinquent_id'],
            // 'second_good_through_id' => 'second_installment_good_through',
            'secondInstPaidDate' => $request['second_tax_paid_id'],
            'thirdInstBilledAmt' => $request['third_amount_id'],
            'third_texes_out_id' => $request['third_texes_out_id'],
            'third_discount_expires_id' => $request['third_discount_expires_id'],
            'thirdInstDueDate' => $request['third_tax_due_id'],
            'thirdDeliqDate' => $request['third_tax_delinquent_id'],
            // 'third_good_through_id' => 'third_installment_good_through',
            'thirdInstPaidDate' => $request['third_tax_paid_id'],
            'fourthInstBilledAmt' => $request['fourth_amount_id'],
            'fourth_texes_out_id' => $request['fourth_texes_out_id'],
            'fourth_discount_expires_id' => $request['fourth_discount_expires_id'],
            'fourthInstDueDate' => $request['fourth_tax_due_id'],
            'fourthDeliqDate' => $request['fourth_tax_delinquent_id'],
            // 'fourth_good_through_id' => 'fourth_installment_good_through',
            'fourthInstPaidDate' => $request['fourth_tax_paid_id'],
            'exampleFormControlTextarea1' => $request['exampleFormControlTextarea1'],
            'taxing_entity_dd' => $request['taxing_id']
            
        ];

        // Encode the renamed data as JSON
        $jsonData = json_encode($fieldMapping);
     
    $data =  $jsonData;
    // foreach ($data as $data) {
        $taxCert = [];
        $data = json_decode($jsonData, true);
       

            $exemptionAmount = (string)(number_format($data['homeownerExemption'], 2, '.', '') + number_format($data['otherExemption'], 2, '.', ''));
            $improvementValue = !empty($data['improvementValue']) ? number_format($data['improvementValue'], 2, '.', '') : (!empty($data['buildingValue']) ? number_format($data['buildingValue'], 2, '.', '') : "0.00");

            $paymentData = [];

            if ($data['numberOfInst'] == 1) {
                $paymentData = [];
                $paymentData[] = [
                    "tax_year" => $data['taxYear'],
                    "tax_type" => "COUNTY",
                    "tax_period" => "ANNUAL",
                    "status" => (strtolower($data['firstInstStatus']) == "paid") ? "PAID" : "DUE",
                    "tax_amount" => (string)(!empty((float)$data['firstInstBilledAmt']) ? (float)$data['firstInstBilledAmt'] : "0.00"),
                    "due_date" => !empty($data['firstInstDueDate']) ? date('Y-m-d', strtotime($data['firstInstDueDate'])) : "",
                    "delinquent_date" => !empty($data['firstDeliqDate']) ? date('Y-m-d', strtotime($data['firstDeliqDate'])) : "",
                    "paid_date" => !empty($data['firstInstPaidDate']) ? date('Y-m-d', strtotime($data['firstInstPaidDate'])) : ""
                ];
            }

            if ($data['numberOfInst'] == 2) {
                $paymentData[] = [
                    "tax_year" => $data['taxYear'],
                    "tax_type" => "COUNTY",
                    "tax_period" => "1ST INSTALLMENT",
                    "status" => (strtolower($data['firstInstStatus']) == "paid") ? "PAID" : "DUE",
                    "tax_amount" => (string)(!empty((float)$data['firstInstBilledAmt']) ? (float)$data['firstInstBilledAmt'] : "0.00"),
                    "due_date" => !empty($data['firstInstDueDate']) ? date('Y-m-d', strtotime($data['firstInstDueDate'])) : "",
                    "delinquent_date" => !empty($data['firstDeliqDate']) ? date('Y-m-d', strtotime($data['firstDeliqDate'])) : "",
                    "paid_date" => !empty($data['firstInstPaidDate']) ? date('Y-m-d', strtotime($data['firstInstPaidDate'])) : ""
                ];

                $paymentData[] = [
                    "tax_year" => $data['taxYear'],
                    "tax_type" => "COUNTY",
                    "tax_period" => "2ND INSTALLMENT",
                    "status" => (strtolower($data['secondInstStatus']) == "paid") ? "PAID" : "DUE",
                    "tax_amount" => (string)(!empty((float)$data['secondInstBilledAmt']) ? (float)$data['secondInstBilledAmt'] : "0.00"),
                    "due_date" => !empty($data['secondInstDueDate']) ? date('Y-m-d', strtotime($data['secondInstDueDate'])) : "",
                    "delinquent_date" => !empty($data['secondDeliqDate']) ? date('Y-m-d', strtotime($data['secondDeliqDate'])) : "",
                    "paid_date" => !empty($data['secondInstPaidDate']) ? date('Y-m-d', strtotime($data['secondInstPaidDate'])) : ""
                ];
            }

            if ($data['numberOfInst'] == 3) {
                $paymentData[] = [
                    "tax_year" => $data['taxYear'],
                    "tax_type" => "COUNTY",
                    "tax_period" => "1ST INSTALLMENT",
                    "status" => (strtolower($data['firstInstStatus']) == "paid") ? "PAID" : "DUE",
                    "tax_amount" => (string)(!empty((float)$data['firstInstBilledAmt']) ? (float)$data['firstInstBilledAmt'] : "0.00"),
                    "due_date" => !empty($data['firstInstDueDate']) ? date('Y-m-d', strtotime($data['firstInstDueDate'])) : "",
                    "delinquent_date" => !empty($data['firstDeliqDate']) ? date('Y-m-d', strtotime($data['firstDeliqDate'])) : "",
                    "paid_date" => !empty($data['firstInstPaidDate']) ? date('Y-m-d', strtotime($data['firstInstPaidDate'])) : ""
                ];

                $paymentData[] = [
                    "tax_year" => $data['taxYear'],
                    "tax_type" => "COUNTY",
                    "tax_period" => "2ND INSTALLMENT",
                    "status" => (strtolower($data['secondInstStatus']) == "paid") ? "PAID" : "DUE",
                    "tax_amount" => (string)(!empty((float)$data['secondInstBilledAmt']) ? (float)$data['secondInstBilledAmt'] : "0.00"),
                    "due_date" => !empty($data['secondInstDueDate']) ? date('Y-m-d', strtotime($data['secondInstDueDate'])) : "",
                    "delinquent_date" => !empty($data['secondDeliqDate']) ? date('Y-m-d', strtotime($data['secondDeliqDate'])) : "",
                    "paid_date" => !empty($data['secondInstPaidDate']) ? date('Y-m-d', strtotime($data['secondInstPaidDate'])) : ""
                ];

                $paymentData[] = [
                    "tax_year" => $data['taxYear'],
                    "tax_type" => "COUNTY",
                    "tax_period" => "3RD INSTALLMENT",
                    "status" => (strtolower($data['thirdInstStatus']) == "paid") ? "PAID" : "DUE",
                    "tax_amount" => (string)(!empty((float)$data['thirdInstBilledAmt']) ? (float)$data['thirdInstBilledAmt'] : "0.00"),
                    "due_date" => !empty($data['thirdInstDueDate']) ? date('Y-m-d', strtotime($data['thirdInstDueDate'])) : "",
                    "delinquent_date" => !empty($data['thirdDeliqDate']) ? date('Y-m-d', strtotime($data['thirdDeliqDate'])) : "",
                    "paid_date" => !empty($data['thirdInstPaidDate']) ? date('Y-m-d', strtotime($data['thirdInstPaidDate'])) : ""
                ];
            }

            if ($data['numberOfInst'] == 4) {
                $paymentData[] = [
                    "tax_year" => $data['taxYear'],
                    "tax_type" => "COUNTY",
                    "tax_period" => "1ST QUARTER",
                    "status" => (strtolower($data['firstInstStatus']) == "paid") ? "PAID" : "DUE",
                    "tax_amount" => (string)(!empty((float)$data['firstInstBilledAmt']) ? (float)$data['firstInstBilledAmt'] : "0.00"),
                    "due_date" => !empty($data['firstInstDueDate']) ? date('Y-m-d', strtotime($data['firstInstDueDate'])) : "",
                    "delinquent_date" => !empty($data['firstDeliqDate']) ? date('Y-m-d', strtotime($data['firstDeliqDate'])) : "",
                    "paid_date" => !empty($data['firstInstPaidDate']) ? date('Y-m-d', strtotime($data['firstInstPaidDate'])) : ""
                ];

                $paymentData[] = [
                    "tax_year" => $data['taxYear'],
                    "tax_type" => "COUNTY",
                    "tax_period" => "2ND QUARTER",
                    "status" => (strtolower($data['secondInstStatus']) == "paid") ? "PAID" : "DUE",
                    "tax_amount" => (string)(!empty((float)$data['secondInstBilledAmt']) ? (float)$data['secondInstBilledAmt'] : "0.00"),
                    "due_date" => !empty($data['secondInstDueDate']) ? date('Y-m-d', strtotime($data['secondInstDueDate'])) : "",
                    "delinquent_date" => !empty($data['secondDeliqDate']) ? date('Y-m-d', strtotime($data['secondDeliqDate'])) : "",
                    "paid_date" => !empty($data['secondInstPaidDate']) ? date('Y-m-d', strtotime($data['secondInstPaidDate'])) : ""
                ];

                $paymentData[] = [
                    "tax_year" => $data['taxYear'],
                    "tax_type" => "COUNTY",
                    "tax_period" => "3RD QUARTER",
                    "status" => (strtolower($data['thirdInstStatus']) == "paid") ? "PAID" : "DUE",
                    "tax_amount" => (string)(!empty((float)$data['thirdInstBilledAmt']) ? (float)$data['thirdInstBilledAmt'] : "0.00"),
                    "due_date" => !empty($data['thirdInstDueDate']) ? date('Y-m-d', strtotime($data['thirdInstDueDate'])) : "",
                    "delinquent_date" => !empty($data['thirdDeliqDate']) ? date('Y-m-d', strtotime($data['thirdDeliqDate'])) : "",
                    "paid_date" => !empty($data['thirdInstPaidDate']) ? date('Y-m-d', strtotime($data['thirdInstPaidDate'])) : ""
                ];

                $paymentData[] = [
                    "tax_year" => $data['taxYear'],
                    "tax_type" => "COUNTY",
                    "tax_period" => "4TH QUARTER",
                    "status" => (strtolower($data['fourthInstStatus']) == "paid") ? "PAID" : "DUE",
                    "tax_amount" => (string)(!empty((float)$data['fourthInstBilledAmt']) ? (float)$data['fourthInstBilledAmt'] : "0.00"),
                    "due_date" => !empty($data['fourthInstDueDate']) ? date('Y-m-d', strtotime($data['fourthInstDueDate'])) : "",
                    "delinquent_date" => !empty($data['fourthDeliqDate']) ? date('Y-m-d', strtotime($data['fourthDeliqDate'])) : "",
                    "paid_date" => !empty($data['fourthInstPaidDate']) ? date('Y-m-d', strtotime($data['fourthInstPaidDate'])) : ""
                ];
            }
            
            
            $taxCert[] = [
                'fieldlist' => [
                    "assessed_land_value" => (string)number_format((float)$data['landValue'], 2, '.', ''),
                    "assessed_total_value" => (string)number_format((float)$data['totalValue'], 2, '.', ''),
                    "exemption_amount" => number_format($exemptionAmount, 2, '.', ''),
                    "township" => !empty($data['township']) ? $data['township'] : '',
                    "exemption_percentage" => '',
                    "assessed_improvement_value" => $improvementValue,
                    "is_tax_sale" => "Off",
                    "tax_parcel_id" => $data['extracted_parcel'],
                    "account_number" => $data['taxAccountId'],
                    // "is_prior_tax_paid" => ($data['delinquentTotalTax'] == 0 || $data['delinquentTotalTax'] == "0.00" || $data['delinquentTotalTax'] == "") ? "Yes" : "No",
                    "is_other_exemption" => ($data['otherExemption'] == 0 || $data['otherExemption'] == "0.00" || $data['otherExemption'] == "") ? "Off" : "On",
                    "is_homestead_exemption" => ($data['homeownerExemption'] == 0 || $data['homeownerExemption'] == "0.00" || $data['homeownerExemption'] == "") ? "Off" : "On",
                    // "legal_description" => $data['legalDescription'],
                    "notes" => $data['exampleFormControlTextarea1'],
                ],
                'payment_info' => $paymentData
            ];

           
        // }
        $responseDataJson = [
            'tax_cert' => $taxCert,
        ];
        $DataJson = json_encode($responseDataJson);
        $existingRecord = DB::table('taxes')->where('order_id', $fieldMapping['order_id'])->first();
    
        if ($existingRecord) {
            // Update existing record
            $updated = DB::table('taxes')
                ->where('order_id', $fieldMapping['order_id'])
                ->update([
                    'json' => $jsonData,
                    'tax_cert' => $responseDataJson,
                    'submit_btn' => $submitBtnValue,
                    'updated_by' => Auth::id(),
                    'updated_at' => Carbon::now(),
                ]);
            $tax_bucket_update = DB::table('oms_order_creations')->where('id', $fieldMapping['order_id'])->update([
                'tax_bucket' => null,
                ]);

            $status = $updated ? 'success' : 'error';
            $message = $updated ? 'Data updated successfully' : 'Failed to update data';
        } else {
            // Insert new record
            $inserted = DB::table('taxes')->insert([
                'order_id' => $fieldMapping['order_id'],
                'json' => $jsonData,
                'submit_btn' => $submitBtnValue,
                'updated_by' => Auth::id(),
                'updated_at' => Carbon::now(),
            ]);

            $tax_bucket_update = DB::table('oms_order_creations')->where('id', $fieldMapping['order_id'])->update([
                'tax_bucket' => null,
            ]);

            $status = $inserted ? 'success' : 'error';
            $message = $inserted ? 'Data inserted successfully' : 'Failed to insert data';
        }
    
            DB::table('oms_tax_comments_histroy')
                    ->insert([
                        'order_id'=> $request['order_id'],
                        'comment' => $request['exampleFormControlTextarea1'],
                        'updated_by' => Auth::id(),
                        'updated_at' => Carbon::now('America/New_York'),
                    ]);

            $redirect = DB::table('taxes')->select('submit_btn')->where('order_id', $request['order_id'])->get();
            // Prepare the response with the renamed fields
        $responseData = [
            'status' => $status,
            'data' => $jsonData,
            'message' => $message,
            'redirect' => $redirect,
        ];

            if($status === 'success'){
                $this->taxCertPDFController->fillPDF($request['order_id'],$DataJson);
            }
    
        return response()->json($responseData, $status === 'success' ? 201 : 500);
    }    

    public function moveToTaxStatus(Request $request)
    {
        if ($request->has(['tax_status', 'get_data', 'search_input'])) {

            $jsondata = json_encode([
                'tax_status' => $request->tax_status,
                'get_data' => $request->get_data,
                'search_input' => $request->search_input,
            ]);

            DB::table('oms_order_creations')
                ->where('id', $request->orderId)
                ->update([
                    'tax_json' => $jsondata,
                    'tax_bucket' => 1,
                    'api_data' => $request->search_input
                ]);

            return response()->json(['message' => 'Order status updated successfully'], 200);
        }

        return response()->json(['error' => 'Missing required fields'], 400);
    }


    public function storeFile(Request $request)
    {
        
        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();

        $pathfileName = uniqid() . '-' . $file->getClientOriginalName();
    
       
        $directoryPath = 'texAttachments'; // Directory for storing files
        $disk = 'public'; // Specify the disk (e.g., 'public', 'local', 's3')
    
        // Check if the directory exists, if not, create it
        if (!Storage::disk($disk)->exists($directoryPath)) {
            Storage::disk($disk)->makeDirectory($directoryPath);
        }
    
        // Store the file with the unique name
        $filePath = $file->storeAs($directoryPath, $pathfileName, $disk);
    
        // Store the file metadata in the OmsAttachmentHistory model
        OmsAttachmentHistory::create([
            'order_id' => $request->input('order_id'),
            'updated_by' => Auth::id(),
            'action' => 'Uploaded',
            'file_path' => $filePath,
            'file_name' => $fileName,
            'is_delete' => 1,
            'updated_at' => now(),
        ]);
    
        // Return the file path and name as a JSON response
        return response()->json([
            'filePath' => $filePath,
            'fileName' => $fileName
        ]);
    }
    

        public function getFiles(Request $request)
        {
            $orderId = $request->input('order_id');
        
            // Retrieve files from TaxAttachmentFile
            $attachments = TaxAttachmentFile::where('order_id', $orderId)->get()->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'name' => $attachment->file_name, // Assuming file_name exists in TaxAttachmentFile
                    'path' => Storage::url($attachment->file_path), // Generate the file's public URL
                    'source' => 'TaxAttachmentFile',
                ];
            });
        
            // Retrieve files from SupportingDocs
            $supportingDocs = SupportingDocs::where('order_id', $orderId)->get()->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'name' => $doc->file_name, // Assuming file_name exists in SupportingDocs
                    'path' => Storage::url($doc->file_path), // Generate the file's public URL
                    'source' => 'SupportingDocs',
                ];
            });
        
            // Merge the collections if both are non-empty, otherwise return whichever is non-empty
            if ($attachments->isNotEmpty() && $supportingDocs->isNotEmpty()) {
                $files = $attachments->merge($supportingDocs);
            } elseif ($attachments->isNotEmpty()) {
                $files = $attachments;
            } elseif ($supportingDocs->isNotEmpty()) {
                $files = $supportingDocs;
            } else {
                $files = collect(); // Return an empty collection if both are empty
            }
        
            return response()->json($files);
        }

        public function getCertFiles(Request $request)
        {
            $orderId = $request->input('order_id');
            $directory = "public/taxcert/{$orderId}";
            $files = [];
        
            // Check if directory exists
            if (Storage::exists($directory)) {
                // Get all files in the directory
                $storedFiles = Storage::files($directory);
        
                foreach ($storedFiles as $filePath) {
                    $fileName = basename($filePath);
                    $fileUrl = Storage::url($filePath); // Get public URL if storage is linked
                    $files[] = [
                        'name' => $fileName,
                        'path' => $fileUrl
                    ];
                }
            }
        
            return response()->json($files);
        }
        
        
            public function deleteFile(Request $request)
            {
                $fileId = $request->input('file_id');
            
                try {
                    // Find the file by ID, or fail if not found
                    $file = TaxAttachmentFile::findOrFail($fileId);
            
                    // Check if the physical file exists in storage, and delete it if so
                    $filePath = str_replace('storage/', '', $file->file_path);
                    if (\Storage::disk('public')->exists($filePath)) {
                        \Storage::disk('public')->delete($filePath);
                    }
                    
                    // Delete the file entry from the database
                    $file->delete();
                OmsAttachmentHistory::create([
                        'order_id' => $file->order_id,
                        'updated_by' => Auth::id(),
                        'action' => 'Deleted',
                        'file_name' => $file->file_name,
                        'updated_at' => now(),
                    ]);
                    return response()->json([
                        'status' => 'success',
                        'message' => 'File deleted successfully.'
                    ]);
                } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                    // Handle case where the file ID does not exist
                    return response()->json([
                        'status' => 'error',
                        'message' => 'File not found.'
                    ], 404);
                } catch (\Exception $e) {
                    // Handle general errors
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Failed to delete the file. Please try again.',
                        'error' => $e->getMessage() // Optional: include the error message for debugging
                    ], 500);
                }
            }
            public function attachmentHistoryData(Request $request)
            {
                
                        $query = OmsAttachmentHistory::select('id', 'order_id', 'file_name', 'updated_by', 'file_path','action', DB::raw("DATE_FORMAT(updated_at, '%m/%d/%Y') as updated_at"))
                        ->with('user:id,username') // Include only `id` and `username` from `stl_user`
                        ->orderByDesc('updated_at'); // Order by `updated_at` in descending order

                        if ($request->has('order_id') && !empty($request->order_id)) {
                        $query->where('order_id', $request->order_id);
                        }

                        $data = $query->get();
                return response()->json(['data' => $data]);
            }

            public function submitFtcOrder(Request $request)
            {
                $type = $request->type;
                $orderId = $request->orderId;
                $search_value = $request->search_value;

                if ($request->has(['tax_status', 'type', 'search_value'])) {

                    $jsondata = json_encode([
                        'tax_status' => $request->tax_status,
                        'get_data' => $request->type,
                        'search_input' => $request->search_value,
                    ]);
        
                    DB::table('oms_order_creations')
                        ->where('id', $request->orderId)
                        ->update([
                            'tax_json' => $jsondata,
                            // 'tax_bucket' => 1,
                            'api_data' => $request->search_value
                        ]);
                }
            
                // Retrieve order data from the database
                $orderData = DB::table('oms_order_creations')
                    ->leftJoin('oms_state', 'oms_order_creations.state_id', '=', 'oms_state.id')
                    ->leftJoin('county', 'oms_order_creations.county_id', '=', 'county.id')
                    ->select('oms_order_creations.*', 'oms_state.short_code as short_code', 'county.county_name as county_name')
                    ->where('oms_order_creations.id', $orderId)
                    ->first();
                if (isset($orderData->id)) {
                    // Prepare the data to be sent to FTC
                    $data = [
                        "state" => $orderData->short_code,  
                        "zip" => "",
                        "countyname" => $orderData->county_name,
                        "county" => $orderData->county_id,
                        "order_no" => $orderData->order_id,
                        "APN" => ($type == 1) ? $search_value : '',
                        "property_address" => ($type == 1) ? '' : $search_value,
                        "city" => "",
                        "owner_name" => "",
                    ];

                    $ftcOrderData = DB::table('ftc_order_data')
                    ->where('order_id', $orderData->id)
                    ->first();
                
                if ($ftcOrderData) {
                    // Update the existing record
                    DB::table('ftc_order_data')
                        ->where('order_id', $orderData->id)
                        ->update([
                            'request_data' => json_encode($data),
                            'created_by' => Auth::id(),
                            'updated_at' => Carbon::now(),
                        ]);
                
                    $ftc_log_id = $ftcOrderData->id; // Use the existing record's ID
                } else {
                    // Insert a new record and get its ID
                    $ftc_log_id = DB::table('ftc_order_data')->insertGetId([
                        'order_id' => $orderData->id,
                        'request_data' => json_encode($data),
                        'created_by' => Auth::id(),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
                    // Call the FTC API
                    $ftcResponse = $this->getFtcData('ftc/CreateOrderFTC.php', $data);
                    // Check if FTC response is successful and contains OrderId
                    if (($ftcResponse['Status'] ?? '') === "Success" && !empty($ftcResponse['OrderId'])) {
                        // Update the FTC log with the response 
                        DB::table('ftc_order_data')
                            ->where('id', $ftc_log_id)
                            ->update([
                                'ftc_order_id' => $ftcResponse['OrderId'],
                                'updated_at' => Carbon::now(),
                            ]);
                            $orderId = $orderData->id;
                            $this->processFtcOrder($orderId);

                            $ftc_status = DB::table('ftc_order_data')->select('ftc_status')
                                    ->where('ftc_order_id', $ftcResponse['OrderId'])->get();

                            // RetryFtcOrder::dispatch($orderId);
                            return $ftc_status;
                    } else {
                        // In case of failure, log the error or provide a failure message
                        DB::table('ftc_order_data')
                            ->where('id', $ftc_log_id)
                            ->update([
                                'ftc_status' => 'Failed',
                                'updated_at' => Carbon::now(),
                            ]);
            
                        // Return a failure response
                        return response()->json(['message' => 'FTC order creation failed.'], 400);
                    }
                } else {
                    return response()->json(['message' => 'Order not found.'], 404);
                }
            }
            
 // Fetch attachment history data
 public function getAttachmentHistory(Request $request)
 {

    $query = OmsAttachmentHistory::select('id', 'order_id', 'file_name', 'updated_by', 'file_path', 'is_delete','action', DB::raw("DATE_FORMAT(updated_at, '%m/%d/%Y %H:%i:%s') as updated_at"))
    ->with('user:id,username') 
    ->orderByDesc('updated_at'); 
   $order_id = $request->order_id;

    $query->where('order_id', $order_id);
    $data = $query->get();
return response()->json(['data' => $data]);
 }

 // Delete file
 public function deleteAttachment(Request $request)
 {
     $attachment = OmsAttachmentHistory::find($request->id);

     if (!$attachment || $attachment->is_delete != 1) {
         return response()->json(['success' => false, 'message' => 'File cannot be deleted.'], 403);
     }

     // Delete file from storage
     if (Storage::exists($attachment->file_path)) {
         Storage::delete($attachment->file_path);
     }
     OmsAttachmentHistory::create([
        'order_id' => $attachment->order_id,
        'updated_by' => Auth::id(),
        'action' => 'Deleted',
        'file_name' => $attachment->file_name,
        'updated_at' => now(),
    ]);

    //  $attachment->delete();
     DB::table('oms_attachment_history')
     ->where('id', $request->id)
     ->update([
         'file_path' => null ,
         'is_delete' => null,
     ]);


     return response()->json(['success' => true, 'message' => 'File deleted successfully.']);
 }

 
 public function updateCounty(Request $request)
 {
     $request->validate([
         'getclient' => 'required|integer',
         'county_id' => 'required|integer',
         'order_id' => 'required_if:getclient,82|integer',
     ]);
 
     if ($request->getclient == 82) {
         $updated = DB::table('oms_order_creations')
             ->where('id', $request->order_id)
             ->update(['county_id' => $request->county_id]);
 
         return response()->json([
             'message' => $updated ? 'County updated successfully.' : 'Order not found or no changes made.'
         ], $updated ? 200 : 404);
     }
 
     return response()->json(['message' => 'Invalid client ID.'], 400);
 }
 


            
}

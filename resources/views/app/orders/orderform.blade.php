@extends('layouts.app')
@section('title', config('app.name') . ' | Orders')
@section('content')
<style>

.card-body #timer ul li {
  display: inline-block;
  font-size: 0.5em;
  list-style-type: none;
  padding: 1em;
}

.card-body #timer ul li span {
  display: block;
  font-size: 1rem;
}

.adjust-colon {
position: relative;
    top: -15px;
}

.timer-green {
    color: #41B680;
    font-size: 14px;      
}

.timer-red {
    color: red;
    font-size: 14px;
}

#headline.timer-red{
    color: white;  
    background-color: red !important; 
}


#headline.timer-green{
    color: white; 
    background-color: #41B680 !important; 
}

</style>
<div class="col-lg-12 mt-2">
    <div class="card">
        <div class="card-body">
            <div class="p-0">
                <div class="d-flex justify-content-center">
                    <h5 class="border bg-info rounded font-weight-bold fs-4 text-uppercase border-grey px-2 py-1">{{$orderData->client_name}} - {{$orderData->process_type}}</h5>
                </div>
                <div class="row">
                    <div class="col-md-3 mt-5">
                        <div class="d-flex">
                            <h5 class="font-weight-bold">LOB:</h5> 
                            <div style="margin-left: 10px;">
                                <h5 class="border bg-light rounded font-weight-bold fs-3 text-uppercase  p-1 mt-1">{{ $orderData->lob_id ? ($lobList->where('id', $orderData->lob_id)->first()->name ?? '-') : '' }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7"></div>
                    <div class="col-md-2">
                        <div class="card-body">
                            <div id="timer" class="text-center bg-light rounded font-weight-bold">
                            <h5 id="headline" class="border bg-white rounded font-weight-bold fs-4 text-uppercase border-grey px-2 py-1">Timer</h5>
                                <ul class="p-0 m-0">
                                <li><span id="days"></span> Days</li>
                                    <li><span class="adjust-colon">:</span></li>
                                <li><span id="hours"></span> Hours</li>
                                    <li><span class="adjust-colon">:</span></li>
                                <li><span id="minutes"></span> Minutes</li>
                                    <li><span class="adjust-colon">:</span></li>
                                <li><span id="seconds"></span> Seconds</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <h6 class="font-weight-bold">Order Information :</h6>
                <div class="card shadow shadow-md rounded showdow-grey mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <div class="font-weight-bold">Order No</div>
                                <div>{{ $orderData->order_id }}</div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="font-weight-bold">Product</div>
                                <div>{{($orderData->process_name) ? $orderData->process_name : '-' }}</div>
                                <input type="hidden" id="accurate_product_id" value="{{($orderData->process_id)}}">
                            </div>
                            @if($orderData->client_id == 16)
                            <div class="col-md-3 mb-2">
                                <div class="font-weight-bold">Tier</div>
                                <select name="tier_id" id="tier_id" class="form-control">
                                    <option value="">Select Tier</option>
                                    @foreach($tierList as $tier)
                                        <option value="{{ $tier->id }}" {{ $orderData->tier_id == $tier->id ? 'selected' : '' }}>
                                            {{ $tier->tier_id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            <div class="col-md-3 mb-2">
                                <div class="font-weight-bold">Order Rec Date and Time</div>
                                <div>
                                    {{ $orderData->order_date ? (($formattedDate = date('m/d/Y H:i', strtotime($orderData->order_date))) !== false ? $formattedDate : '-') : '-' }} ({{('EST')}}) 
                                </div>
 
                            </div>
                        </div>
                        <div class="row mt-1">
                            <div class="col-md-3 mb-2">
                                <div class="font-weight-bold">Emp Id</div>
                                <div>{!! isset($orderData->assignee_user) ? trim(explode('(', $orderData->assignee_user)[0]) : '-' !!}</div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="font-weight-bold">Emp Name</div>
                                <div>{!! isset($orderData->assignee_user) ? trim(explode(')', explode('(', $orderData->assignee_user)[1])[0]) : '-' !!}</div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="font-weight-bold">State</div>
                                <select class="form-control select2dropdown" style="width:100%" name="property_state" id="property_state" aria-hidden="true">
                                    <option value="">Select State</option>
                                    @foreach ($stateList as $state)
                                        <option value="{{ $state->id }}" {{ $orderData->property_state == $state->id ? 'selected' : '' }}>
                                            {{ $state->short_code }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="font-weight-bold">County</div>
                                <select class="form-control select2dropdown" style="width:100%" name="property_county" id="property_county" aria-hidden="true">
                                    <option value="">Select County</option>
                                    @foreach ($countyList as $county)
                                        <option value="{{ $county->id }}" {{ $orderData->property_county == $county->id ? 'selected' : '' }}>
                                            {{ $county->county_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                      
                        <div class="row">
                        @if($orderData->client_id == 16)
                            <div class="col-md-3 mb-2">
                                <div class="font-weight-bold">Municipality</div>
                                <select id="city" name="city" class="form-control select2dropdown" data-parsley-required="true">
                                    <option value="">Select Municipality</option>
                                    @foreach($cityList as $city)
                                        <option value="{{ $city->id }}" {{ ($orderData->city_id == $city->id) ? 'selected' : '' }}>
                                            {{ $city->city }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        @if($orderData->client_id == 82)
                            <div class="col-md-3 mt-0 mb-2">
                                <div class="font-weight-bold">User Name</div>
                                <div>{!! !empty($orderData->assignee_user) ? $orderData->assignee_user : '-' !!}</div>
                                </div>
                            <div class="col-md-3 mt-0 mb-2">
                                <div class="font-weight-bold">Qcer Name</div>
                                <div>{!! !empty($orderData->assignee_qa) ? $orderData->assignee_qa : '-' !!}</div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="font-weight-bold">Typist Name</div>
                                <div>{!! !empty($orderData->typist_user) ? $orderData->typist_user : '-' !!}</div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="font-weight-bold">Typist Qcer Name</div>
                                <div>{!! !empty($orderData->typist_qa) ? $orderData->typist_qa : '-' !!}</div>
                            </div>
                        @endif
                        <!-- // -->
                        </div>
                    </div>
                </div>
                @if($orderData->client_id == 82)
                <h6 class="font-weight-bold">User Inputs :</h6>
                <div class="card shadow shadow-md rounded showdow-grey mb-4">
                <input type="hidden" id="getID" value="{{($orderData->client_id)}}">
                <input type="hidden" id="order_id" value="{{($orderData->id)}}">
                    <div class="card-body">
                        <div class="row">
                        <div class="col-md-3 mb-2">
                            <div class="font-weight-bold">Client ID</div>
                            <select class="form-control select2dropdown" name="client_id" id="client_id_">
                                <option value="">Select Client ID</option>
                                @foreach($clientIdList as $client)
                                    <option value="{{ $client->id }}"
                                    {{ $userinput && $client->id == $userinput->accurate_client_id ? 'selected' : '' }}>
                                    {{ $client->accurate_client_id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                            <div class="col-md-3 mb-2">
                                <div class="font-weight-bold">Portal Fee Cost</div>
                                <input type="text" id="portal_fee_cost_id" name="portal_fee_cost_id" class="form-control" 
                                    placeholder="Enter Portal Fee Cost" 
                                    value="{{ $userinput ? $userinput->portal_fee_cost : '' }}">
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="font-weight-bold">Source</div>
                                    <select class="form-control select2dropdown" name="source_id" id="source_id">
                                        <option value="">Select Source Info</option>
                                    @foreach ($sourcedetails as $source)
                                    <option value="{{ $source->id }}" {{ ($userinput->source ?? '') == $source->id ? 'selected' : '' }}>
                                        {{ $source->source_name }}
                                    </option>
                                    @endforeach
                                    </select>
                            </div>
                            <div class="col-md-3 mb-2 {{ $orderData->completion_date ? '' : 'd-none' }}" id="production_date_container">
                                <div class="font-weight-bold">Completion Date and Time</div>
                                <input type="text" id="" name="" class="form-control"
                                value="{{ $orderData->completion_date ? (($formattedDate = date('m/d/Y H:i', strtotime($orderData->completion_date))) !== false ? $formattedDate . ' (EST)' : '-') : '-' }}" readonly>
                            </div>
                        <!-- </div>
                            <div class="row mt-3 mb-2"> -->
                                <div class="col-md-3 mb-2">
                                    <div class="font-weight-bold">Copy Cost</div>
                                    <input type="text" id="copy_cost_id" name="copy_cost_id" class="form-control" 
                                        placeholder="Enter Copy Cost" 
                                        value="{{ $userinput ? $userinput->copy_cost : '' }}">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <div class="font-weight-bold">No of Search Done</div>
                                    <input type="text" id="no_of_search_id" name="no_of_search_id" class="form-control" 
                                        placeholder="Enter No of Search" 
                                        value="{{ $userinput ? $userinput->no_of_search_done : '' }}">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <div class="font-weight-bold">No of Documents Retrieved in TP/Other Applications:</div>
                                    <input type="text" id="document_retrive_id" name="document_retrive_id" class="form-control" 
                                        placeholder="Enter No of Documents Retrieved" 
                                        value="{{ $userinput ? $userinput->no_of_documents_retrieved : '' }}">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <div class="font-weight-bold">Title Point Account</div>
                                    <input type="text" id="title_point_account_id" name="title_point_account_id" class="form-control" 
                                        placeholder="Enter Title Point Account" 
                                        value="{{ $userinput ? $userinput->title_point_account : '' }}">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <div class="font-weight-bold">Purchase Link</div>
                                    <input type="text" id="purchase_link_id" name="purchase_link_id" class="form-control" 
                                        placeholder="Enter Purchase Link" 
                                        value="{{ $userinput ? $userinput->purchase_link : '' }}">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <div class="font-weight-bold">User Name</div>
                                    <input type="text" id="username_id" name="username_id" class="form-control" 
                                        placeholder="Enter User Name" 
                                        value="{{ $userinput ? $userinput->username : '' }}">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <div class="font-weight-bold">Password</div>
                                    <input type="text" id="password_id" name="password_id" class="form-control" 
                                        placeholder="Enter Password" 
                                        value="{{ $userinput ? $userinput->password : '' }}">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <div class="font-weight-bold">File Path</div>
                                    <input type="text" id="file_path_id" name="file_path_id" class="form-control" 
                                        placeholder="Enter File Path" 
                                        value="{{ $userinput ? $userinput->file_path : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- // -->
                <h6 class="font-weight-bold">Vendor Requirements:</h6>
                <div class="card shadow shadow-md rounded showdow-grey mb-4">
                    <div class="card-body">
                        <div class="row col-md-12">
                            <div class="row font-weight-bold p-2">State Specific Instructions:</div>
                            <p id="state_specific_instructions" class="col-md-12 px-1" style="font-size: 14px !important;"></p>
                            </div>
                            <div class="row col-md-12">
                                <div class="row font-weight-bold p-2">Stop Notes:</div>
                            <p id="stop_notes" class="col-md-12 px-1" style="font-size: 14px !important;"></p>
                        </div>
                            <div class="row col-md-12">
                                <div class="row font-weight-bold p-2">Order Requirements:</div>
                            <p id="order_requirements" class="col-md-12 px-1" style="font-size: 14px !important;"></p>
                            </div>
                        </div>
                    </div>
                 <!-- // -->
                 @if($orderData->client_id == 82)
                 @if(!@empty($vendorequirements))
                <input type="hidden" name="instructionId" id="instructionId" value="{{$instructionId}}">
                <h6 class="font-weight-bold">Source Information :</h6>
                    <div class="card shadow shadow-md rounded showdow-grey mb-4">
                        <div class="card-body">
                            <table id="source_datatable" class="table table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead>
                                    <tr>
                                        <th width="20%">Source</th>
                                        <th width="30%">Source Type</th>
                                        <th width="50%">Site Link</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach (['ASSESSOR', 'JUDGEMENTS', 'MAPPING', 'RECORDER', 'TREASURER', 'STATUTES', 'UCC'] as $key)
                                    <tr>
                                        <td>{{ ucfirst(strtolower($key)) }}</td>
                                        <td>{{ $vendorequirements['SOURCE']  }}</td>
                                        <td>
                                            @if (trim($vendorequirements[$key]) === 'Not Available')
                                                Not Available
                                            @else
                                                {!! nl2br(implode('<br>', array_map(function($url) {
                                                    // Remove leading numbers, dots, and spaces before the URL
                                                    $url = preg_replace('/^\d+\.\s*/', '', $url);
                                                    return '<a href="' . $url . '" target="_blank">' . $url . '</a>';
                                                }, explode(' ', preg_replace('/\s+/', ' ', trim($vendorequirements[$key])))))) !!}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                    </div>
                </div>
                @endif
                @endif
                  <!-- // -->
                @endif
                    @if($orderData->client_id == 16)
                @if(!@empty($countyInfo))
                <input type="hidden" name="instructionId" id="instructionId" value="{{$instructionId}}">
                <h6 class="font-weight-bold">Source Information :</h6>
                <div class="card shadow shadow-md rounded showdow-grey mb-4">
                    <div class="card-body">
                        <div class="row mb-2 mx-2">
                            <div class="col-md-3">
                                <div class="font-weight-bold">Primary Source</div>
                                <div>{{$countyInfo['PRIMARY']['PRIMARY_SOURCE']}}</div>
                            </div>
                            {{-- <div class="col-md-3">
                                <div class="font-weight-bold">Primary Image Source</div>
                                <div>{{$countyInfo['PRIMARY']['PRIMARY_IMAGE_SOURCE']}}</div>
                            </div>
                            <!-- <div class="col-md-3">
                                <div class="font-weight-bold">Secondary Source</div>
                                <div>{{$countyInfo['SECONDARY']['SECONDARY_SOURCE']}}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="font-weight-bold">Secondary Image Source</div>
                                <div>{{$countyInfo['SECONDARY']['SECONDARY_IMAGE_SOURCE']}}</div>
                            </div> --> --}}
                        </div>
                        <table id="source_datatable" class="table table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>Source</th>
                                    <th>Site Link</th>
                                    <th>Username</th>
                                    <th>Password</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tax</td>
                                                <td><a href="{{$countyInfo['TAX']['TAX_SITE']}}" target="_blank">{{$countyInfo['TAX']['TAX_SITE']}}</a></td>
                                    <td>{{$countyInfo['TAX']['TAX_USERNAME']}}</td>
                                    <td>{{$countyInfo['TAX']['TAX_PASSWORD']}}</td>
                                </tr>
                                <tr>
                                    <td>Court</td>
                                                <td><a href="{{$countyInfo['COURT']['COURT_SITE']}}" target="_blank">{{$countyInfo['COURT']['COURT_SITE']}}</a></td>
                                    <td>{{$countyInfo['COURT']['COURT_PASSWORD']}}</td>
                                    <td>{{$countyInfo['COURT']['COURT_USERNAME']}}</td>
                                </tr>
                                <tr>
                                    <td>Recorder</td>
                                                <td><a href="{{$countyInfo['RECORDER']['RECORDER_SITE']}}" target="_blank">{{$countyInfo['RECORDER']['RECORDER_SITE']}}</a></td>
                                    <td>{{$countyInfo['RECORDER']['RECORDER_USERNAME']}}</td>
                                    <td>{{$countyInfo['RECORDER']['RECORDER_PASSWORD']}}</td>
                                </tr>
                                <tr>
                                    <td>Assessor</td>
                                                <td><a href="{{$countyInfo['ASSESSOR']['ASSESSOR_SITE']}}" target="_blank">{{$countyInfo['ASSESSOR']['ASSESSOR_SITE']}}</a></td>
                                    <td>{{$countyInfo['ASSESSOR']['ASSESSOR_USERNAME']}}</td>
                                    <td>{{$countyInfo['ASSESSOR']['ASSESSOR_PASSWORD']}}</td>
                                </tr>
                                <tr>
                                    <td>Probate Court</td>
                                                <td><a href="{{$countyInfo['PROBATE_COURT']['PROBATE_LINK']}}" target="_blank">{{$countyInfo['PROBATE_COURT']['PROBATE_LINK']}}</a></td>
                                    <td>{{$countyInfo['PROBATE_COURT']['PROBATE_USERNAME']}}</td>
                                    <td>{{$countyInfo['PROBATE_COURT']['PROBATE_PASSWORD']}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
                <h6 class="font-weight-bold">Order Submission :</h6>
                <div class="card shadow shadow-md rounded showdow-grey mb-4">
                    <div class="card-body">
                            @if(isset($checklist_conditions) && count($checklist_conditions) > 0)
                                <div class="font-weight-bold"> Special Checklist :</div>
                                <div class="row mt-1 mb-4  mx-5 ">
                                    <div class="col-12 row bg-danger justify-content-center" id="checklist-container" style="border-radius:14px;">
                                        @php $counter = 0; @endphp
                                        @foreach($checklist_conditions as $checklist_condition)
                                            <div class="row col-12 {{ $counter > 0 ? '' : 'box' }}" style="{{  $counter > 0 ? 'margin-top: -9px; padding-top: 0;' : '' }}">
                                                <input type="checkbox" class="p-0 checklist-item" name="checks[]" id="check_{{ $checklist_condition->id }}" value="{{ $checklist_condition->id }}">
                                                <label class="text-white font-weight-bold text-uppercase px-1" style="font-size: 14px !important;">{{ $checklist_condition->check_condition }}</label>
                                            </div>
                                            @php $counter++; @endphp
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            <div class="row mt-4 mb-4">
                                <div class="col-12 card-body ">
                                    @if(isset($checklist_conditions_2) && count($checklist_conditions_2) > 0)
                                    <div class="font-weight-bold">Checklist :</div>
                                    <div class="row mt-1  mx-5 ">
                                        <div class="col-12  ">
                                            @php $counter = 0; @endphp
                                            @foreach($checklist_conditions_2 as $checklist_condition)
                                                <div class="row col-12 ">
                                                    <input type="checkbox" class="p-0" name="checks[]" id="check_{{ $checklist_condition->id }}" value="{{ $checklist_condition->id }}">
                                                    <label class="text-black   px-1" >{{ $checklist_condition->check_condition }}</label>
                                            </div>
                                                @php $counter++; @endphp
                                        @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                <div class="col-lg-4 ">
                                    <div class="font-weight-bold">Comments :</div>
                                    <textarea name="order_comment" style="width: 100%;" class="mx-5 mt-2" id="order_comment" cols="30" rows="4">{!! (isset($orderHistory) && isset($orderHistory->comment)) ? $orderHistory->comment : '' !!}</textarea>
                                </div>
                                <div class="col-lg-5 mx-5 mt-1">
                                        <div class="row">
                                        <div class="col-10 mb-2">
                                            <div class="font-weight-bold mb-1 mt-1"><span style="color:red;">*</span>Primary Source :</div>
                                                <select id="primary_source" name="primary_source" class="form-control select2dropdown required-field" data-parsley-required="true">
                                                    <option disabled selected value="">Select Primary Source</option>
                                                    @foreach($primarySource as $source)
                                                        <option value="{{ $source->id }}" 
                                                            {{ isset($countyInfo['PRIMARY']) && $source->source_name == ($countyInfo['PRIMARY']['PRIMARY_SOURCE'] ?? '') ? 'selected' : '' }}>
                                                            {{ $source->source_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                        </div>
                                            <div class="col-10 mb-2">
                                    <div class="font-weight-bold mb-1 mt-1">Status :</div>
                                        <select style=" "  class="form-control" name="order_status" id="order_status" @if(!isset($orderData->assignee_user)) disabled @endif>
                                            <option value="1" id="status_1" @if($orderData->status_id == 1) selected @endif>WIP</option>
                                            <option value="2" id="status_2" @if($orderData->status_id == 2) selected @endif>Hold</option>
                                            <option value="3" id="status_3" @if($orderData->status_id == 3) selected @endif>Cancelled</option>
                                            <option value="4" id="status_4" @if($orderData->status_id == 4) selected @endif>Send for QC</option>
                                            <option value="5" id="status_5" @if($orderData->status_id == 5) selected @endif>Completed</option>
                                            <option value="13" id="status_13" @if($orderData->status_id == 13) selected @endif>Coversheet Prep</option>
                                            <option value="14" id="status_14"  @if($orderData->status_id == 14) selected @endif>Clarification</option>
                                        </select>
                                    </div>
                                </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center my-4">
                                    <button class="btn btn-primary btn-sm mx-2" id="ordersubmit" onclick="order_submition({{$orderData->id}},1)" type="submit">Submit</button>
                                    <button class="btn btn-info btn-sm mx-2" id="coversheetsubmit" name="coversheetsubmit" onclick="order_submition({{$orderData->id}},2)" type="submit">Coversheet Prep & Submit</button>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
                @endif
                <!-- // -->
                @if($orderData->client_id == 82)
                <h6 class="font-weight-bold">Order Submission :</h6>
                <div class="card shadow shadow-md rounded showdow-grey mb-4">
                        <div class="card-body">
                            <div class="row mt-4 mb-4">
                                <div class="col-lg-4 ">
                                    <div class="font-weight-bold">Comments :</div>
                                    <textarea name="order_comment" style="width: 100%;" class="mx-5 mt-2" id="order_comment" cols="30" rows="4">{!! (isset($orderHistory) && isset($orderHistory->comment)) ? $orderHistory->comment : '' !!}</textarea>
                                </div>
                                <div class="col-lg-4 mx-5 mt-1">
                                    <div class="row">
                                        <div class="col-10 mb-2">
                                            <div class="col-10 mb-2">
                                                <div class="font-weight-bold mb-1 mt-1">Status :</div>
                                                    <select class="form-control" name="order_status" id="order_status" @if(!isset($orderData->assignee_user)) disabled @endif>
                                                        @if(!Auth::user()->hasRole('Typist') && !Auth::user()->hasRole('Typist/Qcer'))
                                                        <option value="1" id="status_1" @if($orderData->status_id == 1) selected @endif>WIP</option>
                                                        <option value="15" id="status_15"  @if($orderData->status_id == 15) selected @endif>Purchaser</option>
                                                        <option value="14" id="status_14"  @if($orderData->status_id == 14) selected @endif>Clarification</option>
                                                        @endif
                                                        @if(isset($orderData->assignee_qa))
                                                        <option value="4" id="status_4" @if($orderData->status_id == 4) selected @endif>Send for QC</option>
                                                        @endif
                                                        @if(isset($orderData->typist_user))
                                                        <option value="16" id="status_16"  @if($orderData->status_id == 16) selected @endif>Typing</option>
                                                        @endif
                                                        @if(isset($orderData->typist_qa))
                                                        <option value="17" id="status_17"  @if($orderData->status_id == 17) selected @endif>Typing QC</option>
                                                        @endif
                                                        <option value="2" id="status_2" @if($orderData->status_id == 2) selected @endif>Hold</option>
                                                        <option value="3" id="status_3" @if($orderData->status_id == 3) selected @endif>Cancelled</option>
                                                        <option value="5" id="status_5" @if($orderData->status_id == 5) selected @endif>Completed</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-center my-4">
                                                <button class="btn btn-primary btn-sm mx-2" id="ordersubmit" onclick="order_submition({{$orderData->id}},1)" type="submit">Submit</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <table id="orderstatusdetail_datatable" class="table table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>Comments</th>
                                                <th>Status</th>
                                                <th>User</th>
                                                <th>Date and Time (EST)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if($orderstatusInfo && count($orderstatusInfo) > 0)
                                                @foreach($orderstatusInfo as $status)
                                                    <tr>
                                                        <td>{{ $status->comment ?? 'N/A' }}</td>
                                                        <td>{{ $status->status }}</td>
                                                        <td>{{ $status->username ?? 'N/A' }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($status->created_at)->format('m-d-Y H:i:s') }}</td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="4">No status history available for this order.</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                    </div>
                            </div>
                        </div>
                    </div>
                   
                <!-- // -->
            </div>
            @endif
    </div>
</div>

<script>

$(document).ready(function() {
    // Handle the change event on the select element
    $('#order_status').change(function() {
        let status = $(this).val();
        @if(Auth::user()->hasRole('Process')) // Get the selected value
        if (status == 14 || status == 4 || status == 2 || status == 5) {
            $('#order_status').prop('disabled', true);
        } else {
            $('#order_status').prop('disabled', false);
        }
        @endif

        @if(Auth::user()->hasRole('Qcer'))
        if (status == 2 || status == 5 ) {
            $('#order_status').prop('disabled', true);
        } else {
            $('#order_status').prop('disabled', false);
        }
        @endif

        @if(Auth::user()->hasRole('Process/Qcer'))
        if (status == 2 || status == 5 ) {
            $('#order_status').prop('disabled', true);
        } else {
            $('#order_status').prop('disabled', false);
        }
        @endif


    });

    $('#order_status').trigger('change');
});

var changeState = false;
var changeCounty = false;


    $(document).ready(function() {
        var orderStatus = $('#order_status');
        var currentStatus = {{ $orderData->status_id }};

        orderStatus.on('change', 'option', function() {
            for (var i = 0; i < orderStatus[0].options.length; i++) {
                if (parseInt(orderStatus[0].options[i].value) === currentStatus) {
                    orderStatus[0].options[i].disabled = true;
                break;
            }
        }
        }).trigger('change');
    });

$('#city').on('change', function () {
        $('#primary_source').removeClass('required-field');
        changeCounty = true;
        $('#ordersubmit').click();
});



    $(document).ready( function () {
        $('#source_datatable').DataTable({
            paging: false,
            info: false,
            searching: false
        });

        @if(isset($orderHistory))
            var idString = "{{ $orderHistory->checked_array }}";
            var checkedIds = idString.split(',');
            checkedIds.forEach(function(id) {
                $('#check_' + id).prop('checked', true);
            });
        @endif

        $("#order_status,#process_id,#tier_id,#property_state,#property_county").select2();
    });


function order_submition(orderId, type) {
        var checklistItems = [];
        $("input[name='checks[]']:checked").each(function() {
            checklistItems.push($(this).val());
        });
        var check_box_id = $("#check_box_id").val();
        var orderComment = $("#order_comment").val();
        var orderStatus = $("#order_status").val();
        var tierId = $("#tier_id").val();
        var productId = $("#process_id").val();
        var propertystate = $("#property_state").val();
        var propertycounty = $("#property_county").val();
        var city = $("#city").val();
        var primarySource = $("#primary_source").val();
        var instructionId = $("#instructionId").val();

    // Accurate
    var getID = $("#getID").val();
    var accurateClientId = $("#client_id_").val();
    var portalfeecost = $("#portal_fee_cost_id").val();
    var source = $("#source_id").val();
    // var productionDate = $("#production_date_id").val();
    var copyCost = $("#copy_cost_id").val();
    var noOfSearch = $("#no_of_search_id").val();
    var documentRetrive = $("#document_retrive_id").val();
    var titlePointAccount = $("#title_point_account_id").val();
    var purchase_link = $("#purchase_link_id").val();
    var username = $("#username_id").val();
    var password = $("#password_id").val();
    var file_path = $("#file_path_id").val();


    if ($('#primary_source').hasClass('required-field') && !primarySource) {
            Swal.fire({
                title: "Error",
                text: "Please select Primary Source field",
                icon: "error"
            });
            return false;
        }

        var data = {
            orderId: orderId,
            checklistItems: checklistItems.join(),
            check_box_id: check_box_id,
            orderComment: orderComment,
            orderStatus: orderStatus,
            stateId: propertystate,
            cityId: city,
            countyId: propertycounty,
            tierId: tierId,
            productId: productId,
            primarySource: primarySource,
            instructionId: instructionId,

        // Accurate
        getID: getID,
        accurateClientId: accurateClientId,
        portalfeecost: portalfeecost,
        source: source,
        // productionDate: productionDate,
        copyCost: copyCost,
        noOfSearch: noOfSearch,
        documentRetrive: documentRetrive,
        titlePointAccount: titlePointAccount,
        purchase_link: purchase_link,
        username: username,
        password: password,
        file_path: file_path,
            submit_type: type,
            _token: '{{ csrf_token() }}'
        };


        $.ajax({
            url: '{{url("orderform_submit")}}',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {

            if (response.redirect) {
                if (response.redirect === 'orders') {
                    if (changeState || changeCounty) {
                        location.reload();
                    } else {
                        window.location.href = '{{ url("orders_status") }}';
                    }
                } else {
                    window.location.href = '{{ url("/coversheet-prep") }}/' + response.redirect;
                }
            } else if (response.success) {
                    Swal.fire({
                        title: "Success",
                        text: response.success,
                        icon: "success"
                    }).then((result) => {
                        if (result.value) {
                            location.reload();
                        }
                    });
            } else if (response.error) {
                    Swal.fire({
                        title: "Error",
                        text: response.error,
                        icon: "error"
                    });
            } else if (response.errors) {
                let errorMessages = "";
                for (let key in response.errors) {
                    if (response.errors.hasOwnProperty(key)) {
                        errorMessages += response.errors[key].join(" ") + "\n";
                    }
                }
                Swal.fire({
                    title: "Required",
                    text: errorMessages,
                    icon: "error"
                });
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            let errorResponse = JSON.parse(xhr.responseText);
            if (errorResponse.message) {
                Swal.fire({
                    title: "Required",
                    text: errorResponse.message,
                    icon: "error"
                });
            }
            if (errorResponse.errors) {
                let errorMessages = "";
                for (let key in errorResponse.errors) {
                    if (errorResponse.errors.hasOwnProperty(key)) {
                        errorMessages += errorResponse.errors[key].join(" ") + "\n";
                    }
                }
                Swal.fire({
                    title: "Required",
                    text: errorMessages,
                    icon: "error"
        });
    }
        }
    });
}


    $('#property_state').on('change', function () {
        var state_id = $("#property_state").val();
        $("#property_county").html('');
        $.ajax({
            url: "{{url('getCounty')}}",
            type: "POST",
            data: {
                state_id: state_id,
                _token: '{{csrf_token()}}'
            },
            dataType: 'json',
            success: function (result) {
                $('#property_county').html('<option value="">Select County</option>');
                $.each(result.county, function (key, value) {
                    $("#property_county").append('<option value="' + value
                        .id + '">' + value.county_name + '</option>');
                });
            }
        });
        $('#primary_source').removeClass('required-field');
        changeState = true;
        $('#ordersubmit').click();
    });

    $('#property_county').on('change', function () {
        $('#primary_source').removeClass('required-field');
        changeCounty = true;
        $('#ordersubmit').click();
    });

    $(document).ready(function() {
        function updateSubmitButtonVisibility() {
            const totalCheckboxes = $('.checklist-item').length;
            const checkedCheckboxes = $('.checklist-item:checked').length;

            if (totalCheckboxes === checkedCheckboxes) {
                $('#ordersubmit').show();
                $('#coversheetsubmit').show();
            } else {
                $('#ordersubmit').hide();
                $('#coversheetsubmit').hide();
            }
        }

        updateSubmitButtonVisibility();

        $('#checklist-container').on('change', '.checklist-item', function() {
            updateSubmitButtonVisibility();
        });
    });



$(document).ready(function () {
    initializeTimer();
    function fetchClientData() {
        var client_id = $("#client_id_").val();
        var product_id = $("#accurate_product_id").val(); 
        var order_id = $("#order_id").val(); 

        $.ajax({
            url: "{{url('getaccurateClientId')}}",
            type: "POST",
            data: {
                order_id: order_id,
                client_id: client_id,
                product_id: product_id, 
                _token: '{{csrf_token()}}'
            },
            dataType: 'json',
            success: function (response) {
                var userinputdetails = response.getUserInputdetails || {};
                var vendordetails = response.vendorDetail || {};

                $('#portal_fee_cost_id').val(userinputdetails.portal_fee_cost || '');
                $('#source_id').val(userinputdetails.source || '');
                // $('#production_date_id').val(userinputdetails.production_date ? userinputdetails.production_date.split(' ')[0] : '');
                $('#copy_cost_id').val(userinputdetails.copy_cost || '');
                $('#no_of_search_id').val(userinputdetails.no_of_search_done || '');
                $('#document_retrive_id').val(userinputdetails.no_of_documents_retrieved || '');
                $('#title_point_account_id').val(userinputdetails.title_point_account || '');

                $('#state_specific_instructions').html(formatContent(vendordetails.state_specific_instructions || 'N/A'));
                $('#stop_notes').html(formatContent(vendordetails.stop_notes || 'N/A'));
                $('#order_requirements').html(formatContent(vendordetails.order_requirements || 'N/A'));
            }
        });
    }

function formatContent(content) {
        return content
        .replace(/\[ \] ([^\[\]]+)/g, '<br>[ ] $1') 
        .replace(/:/g, ':<br>') 
        .replace(/\,/g, ',<br>')
        .replace(/•/g, '•<br>')
        .replace(/(?:\r\n|\r|\n)/g, '<br>')  
        .replace(/(\d+\.)/g, '<br><b>$1</b>'); 
}


    fetchClientData();

    $('#client_id_').on('change', function () {
        fetchClientData();
    });
});

function initializeTimer() {
    var orderRecDate = "{{ $orderData->order_date ? date('m/d/Y H:i', strtotime($orderData->order_date)) : '' }}";
    var tatValue = "{{ $orderData->tat_value }}";
    var compareDate = new Date(orderRecDate);
    var deadline = new Date(compareDate.getTime() + tatValue * 60 * 60 * 1000);
    
    var timer = setInterval(function() {
        updateTimer(compareDate, deadline);
        }, 1000);

    function updateTimer(toDate, deadline) {
        var now = new Date(new Intl.DateTimeFormat('en-US', {
            timeZone: 'America/New_York',
            year: 'numeric', month: 'numeric', day: 'numeric',
            hour: 'numeric', minute: 'numeric', second: 'numeric',
            hour12: false
        }).format(new Date()));

        var difference = now.getTime() - toDate.getTime();
        var deadlineDifference = now.getTime() - deadline.getTime();

        var seconds = Math.floor(difference / 1000);
        var minutes = Math.floor(seconds / 60);
        var hours = Math.floor(minutes / 60);
        var days = Math.floor(hours / 24);

        hours %= 24;
        minutes %= 60;
        seconds %= 60;

        document.getElementById("days").textContent = days;
        document.getElementById("hours").textContent = hours;
        document.getElementById("minutes").textContent = minutes;
        document.getElementById("seconds").textContent = seconds;

    if (!tatValue || tatValue.trim() === '' || tatValue == 0) {
        document.getElementById("days").classList.add("timer-black");
        document.getElementById("hours").classList.add("timer-black");
        document.getElementById("minutes").classList.add("timer-black");
        document.getElementById("seconds").classList.add("timer-black");
        document.getElementById("headline").classList.add("timer-black");
        return;
    }

        if (deadlineDifference <= 0) {
            document.getElementById("days").classList.add("timer-green");
            document.getElementById("hours").classList.add("timer-green");
            document.getElementById("minutes").classList.add("timer-green");
            document.getElementById("seconds").classList.add("timer-green");
            document.getElementById("headline").classList.add("timer-green");

            document.getElementById("days").classList.remove("timer-red");
            document.getElementById("hours").classList.remove("timer-red");
            document.getElementById("minutes").classList.remove("timer-red");
            document.getElementById("seconds").classList.remove("timer-red");
            document.getElementById("headline").classList.remove("timer-red");

        } else {
            document.getElementById("days").classList.add("timer-red");
            document.getElementById("hours").classList.add("timer-red");
            document.getElementById("minutes").classList.add("timer-red");
            document.getElementById("seconds").classList.add("timer-red");
            document.getElementById("headline").classList.add("timer-red");

            document.getElementById("days").classList.remove("timer-green");
            document.getElementById("hours").classList.remove("timer-green");
            document.getElementById("minutes").classList.remove("timer-green");
            document.getElementById("seconds").classList.remove("timer-green");
            document.getElementById("headline").classList.remove("timer-green");
        }
    }
}
$(document).ready(function() {
    $('#orderstatusdetail_datatable').DataTable({
        "ordering": true, // Enable sorting
        "searching": true, // Enable searching
        "paging": true, // Enable pagination
        "info": true, // Show table information
        "lengthChange": true, // Allow the user to change the number of rows shown
        "pageLength": 10, // Default number of rows per page
        "order": [[3, 'desc']] // Order by the 'Date and Time (EST)' column by default
    });
});

</script>
@endsection

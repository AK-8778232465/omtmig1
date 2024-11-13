@extends('layouts.app')
@section('title', config('app.name') . ' | Orders')
@section('content')
<style>

         .card-body #timer ul li {
  display: inline-block;
  font-size: 0.5em;
  list-style-type: none;
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

        .timer-gold {
            color: #0000FF;
            font-size: 14px;
        }

        .timer-orange {
            color: orange;
            font-size: 14px;      
        }

        .timer-red {
    color: red;
    font-size: 14px;
        }
        .timer-brown {
            color: #654321;
            font-size: 14px;
        }

        #headline.timer-red {
    color: white;  
    background-color: red !important; 
        }

        #headline.timer-green {
    color: white; 
    background-color: #41B680 !important; 
        }

        #headline.timer-gold {
    color: white; 
            background-color: #0000FF !important;
        }

        #headline.timer-orange {
            color: white;
            background-color: orange !important;
        }
        #headline.timer-brown {
            color: white;
            background-color: #654321 !important;
        }

        .sticky-container {
    position: sticky;
    top: 60px; 
    z-index: 10; 
    background-color: white;
        }

        .error-message {
        font-size: 3rem; 
        font-weight: bold;
        color: #333;
        margin-top: 20px;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
    }

    .btn-active {
        background-color: orange;
        color: white;
    }

    .btn-inactive {
        background-color: #9ba7ca;
        color: white;
    }
    #hide_card {
    border-top-left-radius: 0;
    border-top-right-radius: 0;
    border-bottom-left-radius: 8px; /* Adjust the radius as needed */
    border-bottom-right-radius: 8px;
}

/* Tick icon specific to #showTaxForm with data-status="tick" */
#showTaxForm[data-status="tick"]::after {
    content: '✓';
    font-size: 0.6rem;
    color: white;
    background-color: green;
    width: 16px;
    height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    position: absolute;
    right: 4px;
    top: 50%;
    transform: translateY(-50%);
}

#showTaxForm[data-status="cross"]::after {
    content: '!';
    font-size: 0.6rem;
    color: white;
    background-color: red;
    width: 17px;
    height: 17px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    position: absolute;
    right: 4px;
    top: 50%;
    transform: translateY(-50%);
}


</style>
<div class="col-lg-12">
    <div class="col-lg-12 mt-2" id="ip_div">
                    <div class="sticky-container shadow shadow-md rounded showdow-grey mb-4">
    <div class="bg-light">
        <div class="row justify-content-between">
            <div class="col-md-3 mt-3" style="max-width: 300px;">
                                    <div class="d-flex sticky1 ml-3">
                            <h5 class="font-weight-bold">LOB:</h5> 
                            <div style="margin-left: 10px;">
                                <h5 class="border bg-light rounded font-weight-bold fs-3 text-uppercase  p-1 mt-1">
                                    {{ $orderData->lob_id ? ($lobList->where('id', $orderData->lob_id)->first()->name ?? '-') : '' }}
                                </h5>
                            </div>
                        </div>
                    </div>
            <div class="col-md-5 text-center mt-2" style="max-width: 300px;">
                <h5 class="border bg-info rounded font-weight-bold fs-2 text-uppercase border-grey px-2 py-1">
                    {{$orderData->client_name}} - {{$orderData->process_type}}
                </h5>
            </div>
            <div id="timer-container" class="col-md-4" style="max-width: 220px;">
                        <div class="card-body">
                    <div id="timer" class="text-center bg-white rounded font-weight-bold">
                        <h5 id="headline" class="rounded font-weight-bold" style="font-size: 12px !important; margin-top: 0px !important; margin-bottom: 0px !important;">
                                                Date  Time (EST)
                                            </h5>
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
                            <div id="completion-timing" class="text-center bg-white border rounded font-weight-bold" style="display: block; max-height: 2px !important;">
                                <h5 class="rounded bg-info font-weight-bold" style="font-size: 15px !important; margin-top: 0px !important; margin-bottom: 0px !important;max-height: 16px;">
                                    TAT Time
                                </h5>
                                <p class="rounded font-weight-bold" id="completion-time" style="font-size: 15px;margin-bottom: 0px;max-height: 15px;">4962:13:38</p>
                                <p style="font-size: 12px;margin-top: 2px;max-height: 14px;">HH:MM:SS</p>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
                        </div>
        <div class="card">
            <div class="card-body">
                <div class="p-0">
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
                                <select name="tier_id" id="tier_id" class="form-control" style="width: 100%"  {{ !is_null($getTaxBucket[0]->tax_bucket) ? 'disabled' : '' }}>
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
                                        {{ $orderData->order_date ? (($formattedDate = date('m/d/Y H:i:s', strtotime($orderData->order_date))) !== false ? $formattedDate : '-') : '-' }} ({{('EST')}})
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
                                <select class="form-control select2dropdown" style="width:100%" name="property_state" id="property_state" aria-hidden="true"
                                {{ !is_null($getTaxBucket[0]->tax_bucket) ? 'disabled' : '' }}>
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
                                <select class="form-control select2dropdown" style="width:100%" name="property_county" id="property_county" aria-hidden="true"
                                {{ !is_null($getTaxBucket[0]->tax_bucket) ? 'disabled' : '' }}>
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
                                <select id="city" name="city" class="form-control select2dropdown" data-parsley-required="true" {{ !is_null($getTaxBucket[0]->tax_bucket) ? 'disabled' : '' }}>
                                    <option value="">Select Municipality</option>
                                    @foreach($cityList as $city)
                                        <option value="{{ $city->id }}" {{ ($orderData->city_id == $city->id) ? 'selected' : '' }}>
                                            {{ $city->city }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        @if($orderData->client_id == 82 || $orderData->client_id == 84 || $orderData->client_id == 85 || $orderData->client_id == 86 || $orderData->client_id == 87 || $orderData->client_id == 89 || $orderData->client_id == 91)
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
                <?php
                $readValue = $orderData->read_value;
                ?>
                <h6 class="font-weight-bold">User Inputs :</h6>
                <div class="card shadow shadow-md rounded showdow-grey mb-4">
                <input type="hidden" id="getID" value="{{($orderData->client_id)}}">
                <input type="hidden" id="order_id" value="{{($orderData->id)}}">
                    <div class="card-body">
                        <div class="row">
                        <div class="col-md-3 mb-2">
                            <div class="font-weight-bold">Client ID<span style="color:red;">*</span></div>
                            <select class="form-control select2dropdown" name="client_id" id="client_id_" required>
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
                                <div class="font-weight-bold">Portal Fee Cost<span style="color:red;">*</span></div>
                                <input type="text" id="portal_fee_cost_id" name="portal_fee_cost_id" class="form-control" 
                                    placeholder="Enter Portal Fee Cost" 
                                    value="{{ $userinput ? $userinput->portal_fee_cost : '' }}" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="font-weight-bold">Source<span style="color:red;">*</span></div>
                                    <select class="form-control select2dropdown" name="source_id" id="source_id" required>
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
                                    <div class="font-weight-bold">Copy Cost<span style="color:red;">*</span></div>
                                    <input type="text" id="copy_cost_id" name="copy_cost_id" class="form-control" 
                                        placeholder="Enter Copy Cost" 
                                        value="{{ $userinput ? $userinput->copy_cost : '' }}" required>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <div class="font-weight-bold">No of Search Done<span style="color:red;">*</span></div>
                                    <input type="text" id="no_of_search_id" name="no_of_search_id" class="form-control" 
                                        placeholder="Enter No of Search" 
                                        value="{{ $userinput ? $userinput->no_of_search_done : '' }}" required>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <div class="font-weight-bold">No of Documents Retrieved in TP/Other Applications<span style="color:red;">*</span></div>
                                    <input type="text" id="document_retrive_id" name="document_retrive_id" class="form-control" 
                                        placeholder="Enter No of Documents Retrieved" 
                                        value="{{ $userinput ? $userinput->no_of_documents_retrieved : '' }}" required>
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
                    <div class="card-body col-12 mt-1 mb-1">
                        <div class="row mx-2">
                            <div class="card col-md-3">
                            <div class="row font-weight-bold p-2">State Specific Instructions:</div>
                            <p id="state_specific_instructions" class="col-md-12 px-1" style="font-size: 14px !important;"></p>
                            </div>
                            <div class="card col-md-4">
                                <div class="row font-weight-bold p-2">Stop Notes:</div>
                            <p id="stop_notes" class="col-md-12 px-1" style="font-size: 14px !important;"></p>
                        </div>
                            <div class="card col-md-5">
                                <div class="row font-weight-bold p-2">Order Requirements:</div>
                            <p id="order_requirements" class="col-md-12 px-1" style="font-size: 14px !important;"></p>
                            </div>
                        </div>
                        <div class="d-flex justify-content-center mt-4">
                            @if(is_null($readValue) && $orderData->status_id == 1)
                            <?php if (is_null($readValue) && $orderData->status_id == 1): ?>
                                    <button class="btn btn-primary" id="proceedButton" value="1" style="cursor: pointer;" type="button">Proceed</button>
                                <?php endif; ?>
                            @endif
                            @if(!is_null($readValue))
                            <?php if (!is_null($readValue)): ?>
                                <input type="hidden" name="proceedButton" id="proceedButton" value="{{$readValue}}">
                            <?php endif; ?>
                            @endif
                        </div>
                    </div>
                    </div>
                 <!-- // -->
                 @if($orderData->client_id == 82)
                 @if(!@empty($vendorequirements))
                <input type="hidden" name="instructionId" id="instructionId" value="{{$instructionId}}">
                <h6 class="read_value <?php echo (is_null($readValue) && $orderData->status_id == 1) ? 'd-none' : ''; ?> font-weight-bold">Source Information :</h6>
                    <div class="read_value <?php echo (is_null($readValue) && $orderData->status_id == 1) ? 'd-none' : ''; ?> card shadow shadow-md rounded showdow-grey mb-4">
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
                        @if(!in_array($orderData->stl_process_id, [2, 4, 6]))
				@php
                    $segments = explode('/', Request::path());
                    $lastSegment = end($segments);
                @endphp

            @if($lastSegment != "tax")
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
                @endif
                    @endif
                    @if(in_array($orderData->stl_process_id, [2, 4, 6]))
                        <h6 class="font-weight-bold">Product And State Specific Information :</h6>
                        <div class="card shadow shadow-md rounded shadow-grey mb-4">
                            <div class="card-body">
                                <table id="fams_typing_datatable" class="table table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                    <thead>
                                        <tr>
                                        <th style="width: 30%;">Area</th>
                                            <th>Comments</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($famsTypingInfo as $info)
                                            <tr>
                                                <td>{{ $info->area ?? '-' }}</td>
                                                <td>{!! nl2br(e($info->comments ?? '-')) !!}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                    @if(!in_array($orderData->stl_process_id, [2, 4, 6]))
        <!-- //s -->
        <div class="container-fluid">
            <div class="card shadow shadow-md rounded showdow-grey p-4">
                <div class="row justify-content-start mb-0" style="margin-left: 1px;" id="statusButtons">
                    <div class="bg-info p-0 text-white" style="text-decoration: none; font-size:0.4rem;">
                            <button type="button" class="btn btn-inactive" id="showOrderForm" style="padding: 5px 25px; font-size: 0.9rem;">
                                Order Submission
                        </button>
                        <button type="button" class="btn btn-inactive" id="showTaxForm"
                                    style="padding: 5px 25px; font-size: 0.9rem; position: relative;"
                                    @if(isset($getjsonDetails[0]['order_id']) && $getjsonDetails[0]['order_id'] != null)
                                        data-status="tick"
                                    @else
                                        data-status="cross"
                                    @endif>
                                TAX
                        </button>
                    </div>
                </div>
                <div class="card p-1" id="hide_card">
                <input type="hidden" id="order_id" value="{{ $orderData->id ?? '' }}">
                    <!-- <div class="card p-0"> -->
                    <!-- Tax Form - Hidden by Default -->
                    <div id="taxForm" class="p-3" style="display:none; ">
                        <h5>Taxes :</h5>
                        <div class="row mb-5 mt-2">
                            `@if(isset($getjsonDetails[0]['order_id']) && $getjsonDetails[0]['order_id'] != null)
                                <div class="col-2 d-flex align-items-center">
                                    <label for="tax_status" class="mr-2 font-weight-bold">Tax:</label>
                                    <select class="form-control" name="tax_status" id="tax_status" disabled>
                                        <option value="">Select Tax</option>
                                        <option value="online" {{ isset($getTaxJson['tax_status']) && $getTaxJson['tax_status'] == 'online' ? 'selected' : '' }}>Online</option>
                                        <option value="offline" {{ isset($getTaxJson['tax_status']) && $getTaxJson['tax_status'] == 'offline' ? 'selected' : '' }}>Offline</option>
                                    </select>
                                </div>
                                <div class="col-2 d-flex align-items-center">
                                    <label for="get_data" class="mr-2 font-weight-bold">Select:</label>
                                    <select class="form-control" name="get_data" id="get_data" disabled>
                                        <option value="">Select Source</option>
                                        <option value="apn" {{ isset($getTaxJson['get_data']) && $getTaxJson['get_data'] == 'apn' ? 'selected' : '' }}>APN</option>
                                        <option value="address" {{ isset($getTaxJson['get_data']) && $getTaxJson['get_data'] == 'address' ? 'selected' : '' }}>Address</option>
                                    </select>
                                </div>
                                <div class="col-2 d-flex align-items-center">
                                    <input class="form-control" id="search_input" name="search_input" type="text" placeholder="Enter APN/Address" value="{{ isset($getTaxJson['search_input']) ? $getTaxJson['search_input'] : '' }}" readonly>
                                </div>
                                <div class="col-md-2 align-items-center">
                                    <input type="hidden" id="order_id" value="{{ $orderData->id ?? '' }}">
                                    <button type="submit" class="btn btn-primary" id="fetchButton" disabled>Fetch</button>
                                    <button type="submit" class="btn btn-primary" id="SaveButton" disabled>Save</button>
                                </div>
                            @else
                            <div class="col-2 d-flex align-items-center">
                                <label for="tax_status" class="mr-2 font-weight-bold">Tax:</label>
                                <select class="form-control" name="tax_status" id="tax_status">
                                    <option value="">Select Tax</option>
                                    <option value="online" {{ isset($getTaxJson['tax_status']) && $getTaxJson['tax_status'] == 'online' ? 'selected' : '' }}>Online</option>
                                    <option value="offline" {{ isset($getTaxJson['tax_status']) && $getTaxJson['tax_status'] == 'offline' ? 'selected' : '' }}>Offline</option>
                                </select>
                            </div>
                            <div class="col-2 d-flex align-items-center">
                                <label for="get_data" class="mr-2 font-weight-bold">Select:</label>
                                <select class="form-control" name="get_data" id="get_data">
                                    <option value="">Select Source</option>
                                    <option value="apn" {{ isset($getTaxJson['get_data']) && $getTaxJson['get_data'] == 'apn' ? 'selected' : '' }}>APN</option>
                                    <option value="address" {{ isset($getTaxJson['get_data']) && $getTaxJson['get_data'] == 'address' ? 'selected' : '' }}>Address</option>
                                </select>
                            </div>
                            <div class="col-4 d-flex align-items-center">
                                <input class="form-control" id="search_input" name="search_input" type="text" placeholder="Enter APN/Address" value="{{ isset($getTaxJson['search_input']) ? $getTaxJson['search_input'] : '' }}">
                            </div>
                            <div class="col-md-2 align-items-center">
                                <input type="hidden" id="order_id" value="{{ $orderData->id ?? '' }}">
                                <button type="submit" class="btn btn-primary" id="fetchButton">Fetch</button>

                                <button type="submit" class="btn btn-primary" id="SaveButton">Save</button>
                            </div>
                            @endif`
                        </div>

                        <form id="taxFormValues">
                        <input type="hidden" name="order_id" value="{{$orderData->id}}">
                            <!-- Top Section -->
                            <div class="form-row">
                                <!-- Left Column -->
                                <div class="col-6">
                                    <div class="form-group" style="display: flex; align-items: center;">
                                    <label class="required" style="margin-right: 10px; width: 150px;">Type :<span style="color:red;">*</span></label>
                                    <select class="form-control" style="flex: 1;" id="type_id" name="type_id" required>
                                        <option selected disabled value="">Select Tax Type</option>
                                        @foreach ($taxType as $type)
                                            <option value="{{ $type->id }}" 
                                                {{ isset($getjsonDetails[0]['type_dd']) && $getjsonDetails[0]['type_dd'] == $type->id ? 'selected' : '' }}>
                                                {{ $type->tax_type }}
                                            </option>
                                        @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group" style="display: flex; align-items: center;">
                                        <label class="required" style="margin-right: 10px; width: 150px;">
                                            Calendar/Fiscal Year: <span style="color:red;">*</span>
                                        </label>
                                        <input class="form-control" type="number" placeholder="Enter Calendar/Fiscal Year" 
                                            style="flex: 1;" id="fiscal_yr_id" name="fiscal_yr_id" required min="0" step="1" value="{{ $getjsonDetails[0]['fiscal_year'] ?? '' }}">
                                    </div>

                                    <div class="form-group" style="display: flex; align-items: center;">
                                        <label class="required" style="margin-right: 10px; width: 150px;">
                                            Tax ID Number: <span style="color:red;">*</span>
                                        </label>
                                        <input class="form-control" type="text" placeholder="Enter Tax ID Number" 
                                            style="flex: 1;" id="tax_id" name="tax_id" required 
                                            pattern="^[a-zA-Z0-9]+$" 
                                            title="Tax ID must contain only letters and numbers without special characters" value="{{ $getjsonDetails[0]['tax_id_number'] ?? '' }}">
                                    </div>


                                    <div class="form-group" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Tax ID Number Described :</label>
                                        <input class="form-control" type="text"
                                            placeholder="Enter Tax ID Number Described" style="flex: 1;" id="tax_described_id" name="tax_described_id" value="{{ $getjsonDetails[0]['tax_described_number'] ?? '' }}">
                                    </div>

                                    <div class="form-group" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">State ID Number :</label>
                                        <input class="form-control" type="text" placeholder="Enter State ID Number"
                                            style="flex: 1;" id="tax_state_id" name="tax_state_id" value="{{ $getjsonDetails[0]['tax_state_number'] ?? '' }}">
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="col-6">
                                    <div class="form-group ml-3" style="display: flex; align-items: center;">
                                        <label class="required" style="margin-right: 10px; width: 150px;">Taxing Entity
                                            :<span style="color:red;">*</span></label>
                                        <select class="form-control" style="flex: 1;" id="taxing_id" name="taxing_id" required>
                                            <option>Select Taxing Entity</option>
                                            @foreach ($taxEntity as $entity)
                                                <option value="{{ $entity->id }}" 
                                                    {{ isset($getjsonDetails[0]['taxing_entity_dd']) && $getjsonDetails[0]['taxing_entity_dd'] == $entity->id ? 'selected' : '' }}>
                                                    {{ $entity->tax_entity_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group ml-3" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Phone :</label>
                                        <input class="form-control" type="tel" 
                                            placeholder="(XXX) YYY-ZZZZ x123"
                                            style="flex: 1;" 
                                            id="phone_num" 
                                            name="phone_num" 
                                            value="{{ $getjsonDetails[0]['phone_number'] ?? '' }}">
                                    </div>

                                    <div class="form-group ml-3" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Street Address 1 :</label>
                                        <input class="form-control" type="text" placeholder="Enter Street Address 1"
                                            style="flex: 1;" id="street_address1" name="street_address1" value="{{ $getjsonDetails[0]['street_address1'] ?? '' }}">
                                    </div>

                                    <div class="form-group ml-3"
                                        style="display: flex;align-items: center;margin-bottom: 0px;">
                                        <label style="margin-right: 10px; width: 150px;">Street Address 2 :</label>
                                        <input class="form-control" type="text" placeholder="Enter Street Address 2"
                                            style="flex: 1;" id="street_address2" name="street_address2" value="{{ $getjsonDetails[0]['street_address2'] ?? '' }}">
                                    </div>

                                    <div class="form-group ml-3" style="display: flex; align-items: center; margin-bottom: 0px;">
                                        <label style="margin-right: 10px; width: 150px;">Zip :</label>
                                        <input class="form-control" type="text" placeholder="Enter Zip"
                                            style="flex: 1;" id="zip_id" name="zip_id"
                                            maxlength="5"
                                            value="{{ $getjsonDetails[0]['zip_number'] ?? '' }}">
                                        <div class="p-4">
                                            <label class="checkbox-label">
                                            <!-- Hidden input to submit "0" if checkbox is unchecked -->
                                            <input type="hidden" name="override_id" value="0">
                                            <input type="checkbox" id="override_id" name="override_id" value="1" {{ isset($getjsonDetails[0]['override_id']) && $getjsonDetails[0]['override_id'] == 1 ? 'checked' : '' }}> Override
                                            </label>
                                        </div>
                                    </div>


                                    <div class="form-group ml-3" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">City :</label>
                                        <input class="form-control" type="text" placeholder="Enter City"
                                            style="flex: 1;" id="city_id" name="city_id" value="{{ $getjsonDetails[0]['city_number'] ?? '' }}">
                                    </div>

                                    <div class="form-group ml-3" style="display: flex; align-items: center;">
                                        <label class="required" style="margin-right: 10px; width: 150px;">State:</label>
                                        <select class="form-control" style="flex: 1;" id="state" name="state">
                                            <option value="">Select State</option>
                                            @foreach ($stateList as $state)
                                                <option value="{{ $state->id }}" {{ (isset($getjsonDetails[0]['state_dd']) && $getjsonDetails[0]['state_dd'] == $state->id) ? 'selected' : '' }}>
                                                    {{ $state->short_code }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <hr style="border: 1px solid #ccc; margin: 20px 0;">
                            <!-- Middle Section -->
                            <div class="form-row">
                                <!-- Left Column -->
                                <div class="col-6">
                                    <div class="form-group" style="display: flex; align-items: center;">
                                        <label class="required" style="margin-right: 10px; width: 150px;">Total Annual Tax :<span style="color:red;">*</span></label>
                                        <input class="form-control" type="text" placeholder="Enter Total Annual Tax" style="flex: 1;" id="total_annual_tax" 
                                            name="total_annual_tax" value="{{ $getjsonDetails[0]['total_annual_tax'] ?? '' }}" pattern="^\d+(\.\d{1,2})?$" title="Please enter a valid decimal value with up to two decimal places." required>
                                    </div>


                                    <div class="form-group" style="display: flex; align-items: center;">
                                        <label class="required" style="margin-right: 10px; width: 150px;">Payment Frequency :<span style="color:red;">*</span></label>
                                        <select class="form-control" style="flex: 1;" id="payment_frequency" name="payment_frequency" value="{{ $getjsonDetails[0]['payment_frequency_dd'] ?? '' }}" required>
                                            <option>Select Payment Frequency</option>
                                            @foreach ($taxPaymentFrequency as $frequency)
                                                <option value="{{ $frequency->id }}" 
                                                    {{ isset($getjsonDetails[0]['payment_frequency_dd']) && $getjsonDetails[0]['payment_frequency_dd'] == $frequency->id ? 'selected' : '' }}>
                                                    {{ $frequency->payment_frequency }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="col-6">
                                    <div class="form-group ml-3" style="display: flex; align-items: center;">
                                        <label class="required" style="margin-right: 10px; width: 150px;">Land :<span
                                                style="color:red;">*</span></label>
                                        <input class="form-control" type="text" placeholder="Enter Land"
                                            style="flex: 1;" id="land" name="land" value="{{ $getjsonDetails[0]['land_data'] ?? '' }}" required>
                                    </div>

                                    <div class="form-group ml-3" style="display: flex; align-items: center;">
                                        <label class="required" style="margin-right: 10px; width: 150px;">Improvements
                                            :<span style="color:red;">*</span></label>
                                        <input class="form-control" type="text" placeholder="Enter Improvements"
                                            style="flex: 1;" id="improvement" name="improvement" value="{{ $getjsonDetails[0]['improvements'] ?? '' }}" required> 
                                    </div>

                                    <div class="form-group ml-3" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Exemption (Mortgage) :</label>
                                        <input class="form-control" type="text" placeholder="Enter Exemption (Mortgage)"
                                            style="flex: 1;" id="exemption_mortgage" name="exemption_mortgage" value="{{ $getjsonDetails[0]['exemption_mortgage'] ?? '' }}">
                                    </div>

                                    <div class="form-group ml-3" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Exemption (Home Owners)
                                            :</label>
                                        <input class="form-control" type="text"
                                            placeholder="Enter Exemption (Home Owners)" style="flex: 1;" id="exemption_homeowner" name="exemption_homeowner" value="{{ $getjsonDetails[0]['exemption_homeowner'] ?? '' }}">
                                    </div>

                                    <div class="form-group ml-3" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Exemption (Homestead
                                            Supplement) :</label>
                                        <input class="form-control" type="text"
                                            placeholder="Enter Exemption (Homestead Supplement)" style="flex: 1;" id="exemption_homestead" name="exemption_homestead" value="{{ $getjsonDetails[0]['exemption_homestead'] ?? '' }}">
                                    </div>

                                    <div class="form-group ml-3" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Exemption (Additional)
                                            :</label>
                                        <input class="form-control" type="text"
                                            placeholder="Enter Exemption (Additional)" style="flex: 1;" id="exemption_additional" name="exemption_additional" value="{{ $getjsonDetails[0]['exemption_additional'] ?? '' }}">
                                    </div>

                                    <div class="form-group ml-3" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Other :</label>
                                        <input class="form-control" type="text" placeholder="Enter Other"
                                            style="flex: 1;" id="others" name="others" value="{{ $getjsonDetails[0]['others'] ?? '' }}">
                                    </div>

                                    <div class="form-group ml-3" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Net Valuation :</label>
                                        <input class="form-control" type="text" placeholder="Enter Net Valuation"
                                            style="flex: 1;" id="net_value" name="net_value" value="{{ $getjsonDetails[0]['net_value'] ?? '' }}">
                                    </div>
                                </div>
                            </div>

                            <hr style="border: 1px solid #ccc; margin: 20px 0;">
                            <!-- Installments Section -->
                            <div class="row installments-section">
                                <!-- First Installment -->
                                <div class="col-3 installment">
                                    <h6 class="installment-title">First Installment</h6>

                                    <div class="checkbox-group">
                                        <label class="checkbox-label">
                                            <!-- Hidden input to ensure 0 is submitted if checkbox is unchecked -->
                                            <input type="hidden" name="first_estimate_id" value="0">
                                            <input type="checkbox" id="first_estimate_id" name="first_estimate_id" value="1" {{ isset($getjsonDetails[0]['first_estimate_id']) && $getjsonDetails[0]['first_estimate_id'] == 1 ? 'checked' : '' }}> Estimated
                                        </label>
                                    </div>

                                    <div class="form-group"
                                        style="display: flex;align-items: center;margin-bottom: 5px;">
                                        <label style="margin-right: 10px; width: 150px;">Amount :<span
                                                style="color:red;">*</span></label>
                                        <input class="form-control" type="text" placeholder="Enter Amount"
                                            style="flex: 1;" id="first_amount_id" name="first_amount_id" value="{{ $getjsonDetails[0]['first_installment_amount'] ?? '' }}" required>
                                        </div>

                                    <div class="checkbox-group">
                                        <div class="form-group" style="display: flex; align-items: center; margin-bottom: 0px;">
                                            <label class="checkbox-label" style="margin-right: 5px; width: 150px;">
                                                <input type="checkbox" id="first_partially_paid_id" name="first_partially_paid_id" value="1" onclick="toggleReadonly(this, 'first_partially_paid_amount')"> Partially Paid
                                            </label>
                                            <input id="first_partially_paid_amount" name="first_partially_paid_amount" value="{{ $getjsonDetails[0]['first_partially_paid_amount'] ?? '' }}" class="form-control ml-1" type="text" placeholder="Enter Partially Paid Amt" style="flex: 1;" readonly>
                                        </div>
                                        <div>
                                            <label class="checkbox-label">
                                                <input type="checkbox" id="first_paid_id" name="first_paid_id" value="1" onclick="onlyOne(this)"
                                                    {{ isset($getjsonDetails[0]) && isset($getjsonDetails[0]['first_paid_id']) && $getjsonDetails[0]['first_paid_id'] == 1 ? 'checked' : '' }} > Paid
                                            </label>
                                        </div>
                                        <div>
                                            <label class="checkbox-label">
                                                <input type="checkbox" id="first_due_id" name="first_due_id" value="1" onclick="onlyOne(this)"
                                                {{ isset($getjsonDetails[0]) && isset($getjsonDetails[0]['first_due_id']) && $getjsonDetails[0]['first_due_id'] == 1 ? 'checked' : '' }}> Due
                                            </label>
                                        </div>
                                        <div>
                                            <label class="checkbox-label">
                                                <input type="checkbox" id="first_delinquent_id" name="first_delinquent_id" value="1" onclick="onlyOne(this)"
                                                {{ isset($getjsonDetails[0]) && isset($getjsonDetails[0]['first_delinquent_id']) && $getjsonDetails[0]['first_delinquent_id'] == 1 ? 'checked' : '' }}> Delinquent
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Taxes Out :</label>
                                        <input class="form-control" type="date" style="flex: 1;" id="first_texes_out_id" name="first_texes_out_id" value="{{ $getjsonDetails[0]['first_installment_texes_out'] ?? '' }}">
                                    </div>

                                    <div class="form-group" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Discount Expires :</label>
                                        <input class="form-control" type="date" style="flex: 1;" id="first_discount_expires_id" name="first_discount_expires_id" value="{{ $getjsonDetails[0]['first_installment_discount_expires'] ?? '' }}">
                                    </div>

                                    <div class="form-group" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Due :</label>
                                        <input class="form-control" type="date" style="flex: 1;" id="first_tax_due_id" name="first_tax_due_id" value="{{ $getjsonDetails[0]['first_installment_tax_due'] ?? '' }}">
                                    </div>

                                    <div class="form-group" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Delinquent :</label>
                                        <input class="form-control" type="date" style="flex: 1;" id="first_tax_delinquent_id" name="first_tax_delinquent_id" value="{{ $getjsonDetails[0]['first_installment_tax_delinquent'] ?? '' }}">
                                    </div>

                                    <div class="form-group" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Good through :</label>
                                        <input class="form-control" type="date" style="flex: 1;" id="first_good_through_id" name="first_good_through_id" value="{{ $getjsonDetails[0]['first_installment_good_through'] ?? '' }}">
                                    </div>

                                    <div class="form-group" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Paid :</label>
                                        <input class="form-control" type="date" style="flex: 1;" id="first_tax_paid_id" name="first_tax_paid_id" value="{{ $getjsonDetails[0]['first_installment_tax_paid'] ?? '' }}">
                                    </div>
                                </div>

                                <!-- Second Installment -->
                                <div class="col-3 installment">
                                    <h6 class="installment-title">Second Installment</h6>

                                    <div class="checkbox-group">
                                        <label class="checkbox-label">
                                            <!-- Hidden input to ensure 0 is submitted if checkbox is unchecked -->
                                            <input type="hidden" name="second_estimate_id" value="0">
                                            <input type="checkbox" id="second_estimate_id" name="second_estimate_id" value="1" {{ isset($getjsonDetails[0]['second_estimate_id']) && $getjsonDetails[0]['second_estimate_id'] == 1 ? 'checked' : '' }}> Estimated
                                        </label>
                                    </div>

                                    <div class="form-group"
                                        style="display: flex;align-items: center;margin-bottom: 5px;">
                                        <label style="margin-right: 10px; width: 150px;">Amount :<span
                                                style="color:red;">*</span></label>
                                        <input class="form-control" type="text" placeholder="Enter Amount"
                                            style="flex: 1;" id="second_amount_id" name="second_amount_id" value="{{ $getjsonDetails[0]['second_installment_amount'] ?? '' }}" required>
                                    </div>

                                    <div class="checkbox-group">
                                        <div class="form-group" style="display: flex; align-items: center; margin-bottom: 0px;">
                                            <label class="checkbox-label" style="margin-right: 5px; width: 150px;">
                                                <input type="checkbox" id="second_partially_paid_id" name="second_partially_paid_id" value="1" onclick="toggleReadonly(this, 'second_partially_paid_amount')"> Partially Paid
                                            </label>
                                            <input id="second_partially_paid_amount" name="second_partially_paid_amount" value="{{ $getjsonDetails[0]['second_partially_paid_amount'] ?? '' }}" class="form-control ml-1" type="text" placeholder="Enter Partially Paid Amt" style="flex: 1;" readonly>
                                        </div>
                                        <div>
                                            <label class="checkbox-label">
                                                <input type="checkbox" id="second_paid_id" name="second_paid_id" value="1" onclick="onlyOne(this)"
                                                    {{ isset($getjsonDetails[0]) && isset($getjsonDetails[0]['second_paid_id']) && $getjsonDetails[0]['second_paid_id'] == 1 ? 'checked' : '' }}> Paid
                                            </label>
                                        </div>
                                        <div>
                                            <label class="checkbox-label">
                                                <input type="checkbox" id="second_due_id" name="second_due_id" value="1" onclick="onlyOne(this)"
                                                {{ isset($getjsonDetails[0]) && isset($getjsonDetails[0]['second_due_id']) && $getjsonDetails[0]['second_due_id'] == 1 ? 'checked' : '' }}> Due
                                            </label>
                                        </div>
                                        <div>
                                            <label class="checkbox-label">
                                                <input type="checkbox" id="second_delinquent_id" name="second_delinquent_id" value="1" onclick="onlyOne(this)"
                                                {{ isset($getjsonDetails[0]) && isset($getjsonDetails[0]['second_delinquent_id']) && $getjsonDetails[0]['second_delinquent_id'] == 1 ? 'checked' : '' }}> Delinquent
                                            </label>
                                        </div>
                                    </div>


                                    <div class="form-group" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Taxes Out :</label>
                                        <input class="form-control" type="date" style="flex: 1;" id="second_texes_out_id" name="second_texes_out_id" value="{{ $getjsonDetails[0]['second_installment_texes_out'] ?? '' }}">
                                    </div>

                                    <div class="form-group" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Discount Expires :</label>
                                        <input class="form-control" type="date" style="flex: 1;" id="second_discount_expires_id" name="second_discount_expires_id" value="{{ $getjsonDetails[0]['second_installment_discount_expires'] ?? '' }}">
                                    </div>

                                    <div class="form-group" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Due :</label>
                                        <input class="form-control" type="date" style="flex: 1;" id="second_tax_due_id" name="second_tax_due_id" value="{{ $getjsonDetails[0]['second_installment_tax_due'] ?? '' }}">
                                    </div>

                                    <div class="form-group" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Delinquent :</label>
                                        <input class="form-control" type="date" style="flex: 1;" id="second_tax_delinquent_id" name="second_tax_delinquent_id" value="{{ $getjsonDetails[0]['second_installment_tax_delinquent'] ?? '' }}">
                                    </div>

                                    <div class="form-group" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Good through :</label>
                                        <input class="form-control" type="date" style="flex: 1;" id="second_good_through_id" name="second_good_through_id" value="{{ $getjsonDetails[0]['second_installment_good_through'] ?? '' }}">
                                    </div>

                                    <div class="form-group" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Paid :</label>
                                        <input class="form-control" type="date" style="flex: 1;" id="second_tax_paid_id" name="second_tax_paid_id" value="{{ $getjsonDetails[0]['second_installment_tax_paid'] ?? '' }}">
                                    </div>
                                </div>

                                <!-- Third Installment -->
                                <div class="col-3 installment">
                                    <h6 class="installment-title">Third Installment</h6>

                                    <div class="checkbox-group">
                                        <label class="checkbox-label">
                                            <!-- Hidden input to ensure 0 is submitted if checkbox is unchecked -->
                                            <input type="hidden" name="third_estimate_id" value="0">
                                            <input type="checkbox" id="third_estimate_id" name="third_estimate_id" value="1" {{ isset($getjsonDetails[0]['third_estimate_id']) && $getjsonDetails[0]['third_estimate_id'] == 1 ? 'checked' : '' }}> Estimated
                                        </label>
                                    </div>

                                    <div class="form-group"
                                        style="display: flex;align-items: center;margin-bottom: 5px;">
                                        <label style="margin-right: 10px; width: 150px;">Amount :<span
                                                style="color:red;">*</span></label>
                                        <input class="form-control" type="text" placeholder="Enter Amount"
                                            style="flex: 1;" id="third_amount_id" name="third_amount_id" value="{{ $getjsonDetails[0]['third_installment_amount'] ?? '' }}" required>
                                    </div>

                                    <div class="checkbox-group">
                                        <div class="form-group" style="display: flex; align-items: center; margin-bottom: 0px;">
                                            <label class="checkbox-label" style="margin-right: 5px; width: 150px;">
                                                <input type="checkbox" id="third_partially_paid_id" name="third_partially_paid_id" value="1" onclick="toggleReadonly(this, 'third_partially_paid_amount')"> Partially Paid
                                            </label>
                                            <input id="third_partially_paid_amount" name="third_partially_paid_amount" value="{{ $getjsonDetails[0]['third_partially_paid_amount'] ?? '' }}" class="form-control ml-1" type="text" placeholder="Enter Partially Paid Amt" style="flex: 1;" readonly>
                                        </div>
                                        <div>
                                            <label class="checkbox-label">
                                                <input type="checkbox" id="third_paid_id" name="third_paid_id" value="1" onclick="onlyOne(this)"
                                                {{ isset($getjsonDetails[0]) && isset($getjsonDetails[0]['third_paid_id']) && $getjsonDetails[0]['third_paid_id'] == 1 ? 'checked' : '' }}> Paid
                                            </label>
                                        </div>
                                        <div>
                                            <label class="checkbox-label">
                                                <input type="checkbox" id="third_due_id" name="third_due_id" value="1" onclick="onlyOne(this)"
                                                {{ isset($getjsonDetails[0]) && isset($getjsonDetails[0]['third_due_id']) && $getjsonDetails[0]['third_due_id'] == 1 ? 'checked' : '' }}> Due
                                            </label>
                                        </div>
                                        <div>
                                            <label class="checkbox-label">
                                                <input type="checkbox" id="third_delinquent_id" name="third_delinquent_id" value="1" onclick="onlyOne(this)"
                                                {{ isset($getjsonDetails[0]) && isset($getjsonDetails[0]['third_delinquent_id']) && $getjsonDetails[0]['third_delinquent_id'] == 1 ? 'checked' : '' }}> Delinquent
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Taxes Out :</label>
                                        <input class="form-control" type="date" style="flex: 1;" id="third_texes_out_id" name="third_texes_out_id" value="{{ $getjsonDetails[0]['third_installment_texes_out'] ?? '' }}">
                                    </div>

                                    <div class="form-group" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Discount Expires :</label>
                                        <input class="form-control" type="date" style="flex: 1;" id="third_discount_expires_id" name="third_discount_expires_id" value="{{ $getjsonDetails[0]['third_installment_discount_expires'] ?? '' }}">
                                    </div>

                                    <div class="form-group" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Due :</label>
                                        <input class="form-control" type="date" style="flex: 1;" id="third_tax_due_id" name="third_tax_due_id" value="{{ $getjsonDetails[0]['third_installment_tax_due'] ?? '' }}">
                                    </div>

                                    <div class="form-group" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Delinquent :</label>
                                        <input class="form-control" type="date" style="flex: 1;" id="third_tax_delinquent_id" name="third_tax_delinquent_id" value="{{ $getjsonDetails[0]['third_installment_tax_delinquent'] ?? '' }}">
                                    </div>

                                    <div class="form-group" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Good through :</label>
                                        <input class="form-control" type="date" style="flex: 1;" id="third_good_through_id" name="third_good_through_id" value="{{ $getjsonDetails[0]['third_installment_good_through'] ?? '' }}">
                                    </div>

                                    <div class="form-group" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Paid :</label>
                                        <input class="form-control" type="date" style="flex: 1;" id="third_tax_paid_id" name="third_tax_paid_id" value="{{ $getjsonDetails[0]['third_installment_tax_paid'] ?? '' }}">
                                    </div>
                                </div>

                                <!-- Fourth Installment -->
                                <div class="col-3 installment">
                                    <h6 class="installment-title">Fourth Installment</h6>

                                    <div class="checkbox-group">
                                        <label class="checkbox-label">
                                            <!-- Hidden input to ensure 0 is submitted if checkbox is unchecked -->
                                            <input type="hidden" name="fourth_estimate_id" value="0">
                                            <input type="checkbox" id="fourth_estimate_id" name="fourth_estimate_id" value="1" {{ isset($getjsonDetails[0]['fourth_estimate_id']) && $getjsonDetails[0]['fourth_estimate_id'] == 1 ? 'checked' : '' }}> Estimated
                                        </label>
                                    </div>

                                    <div class="form-group"
                                        style="display: flex;align-items: center;margin-bottom: 5px;">
                                        <label style="margin-right: 10px; width: 150px;">Amount :<span
                                                style="color:red;">*</span></label>
                                        <input class="form-control" type="text" placeholder="Enter Amount"
                                            style="flex: 1;" id="fourth_amount_id" name="fourth_amount_id" value="{{ $getjsonDetails[0]['fourth_installment_amount'] ?? '' }}" required>
                                    </div>

                                    <div class="checkbox-group">
                                        <div class="form-group" style="display: flex; align-items: center; margin-bottom: 0px;">
                                            <label class="checkbox-label" style="margin-right: 5px; width: 150px;">
                                                <input type="checkbox" id="fourth_partially_paid_id" name="fourth_partially_paid_id" value="1" onclick="toggleReadonly(this, 'fourth_partially_paid_amount')"> Partially Paid
                                            </label>
                                            <input id="fourth_partially_paid_amount" name="fourth_partially_paid_amount" value="{{ $getjsonDetails[0]['fourth_partially_paid_amount'] ?? '' }}" class="form-control ml-1" type="text" placeholder="Enter Partially Paid Amt" style="flex: 1;" readonly>
                                        </div>
                                        <div>
                                            <label class="checkbox-label">
                                                <input type="checkbox" id="fourth_paid_id" name="fourth_paid_id" value="1" onclick="onlyOne(this)"
                                                {{ isset($getjsonDetails[0]) && isset($getjsonDetails[0]['fourth_paid_id']) && $getjsonDetails[0]['fourth_paid_id'] == 1 ? 'checked' : '' }}> Paid
                                            </label>
                                        </div>
                                        <div>
                                            <label class="checkbox-label">
                                                <input type="checkbox" id="fourth_due_id" name="fourth_due_id" value="1" onclick="onlyOne(this)"
                                                {{ isset($getjsonDetails[0]) && isset($getjsonDetails[0]['fourth_due_id']) && $getjsonDetails[0]['fourth_due_id'] == 1 ? 'checked' : '' }}> Due
                                            </label>
                                        </div>
                                        <div>
                                            <label class="checkbox-label">
                                                <input type="checkbox" id="fourth_delinquent_id" name="fourth_delinquent_id" value="1" onclick="onlyOne(this)"
                                                {{ isset($getjsonDetails[0]) && isset($getjsonDetails[0]['fourth_delinquent_id']) && $getjsonDetails[0]['fourth_delinquent_id'] == 1 ? 'checked' : '' }}> Delinquent
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Taxes Out :</label>
                                        <input class="form-control" type="date" style="flex: 1;" id="fourth_texes_out_id" name="fourth_texes_out_id" value="{{ $getjsonDetails[0]['fourth_installment_texes_out'] ?? '' }}">
                                    </div>

                                    <div class="form-group" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Discount Expires :</label>
                                        <input class="form-control" type="date" style="flex: 1;" id="fourth_discount_expires_id" name="fourth_discount_expires_id" value="{{ $getjsonDetails[0]['fourth_installment_discount_expires'] ?? '' }}">
                                    </div>

                                    <div class="form-group" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Due :</label>
                                        <input class="form-control" type="date" style="flex: 1;" id="fourth_tax_due_id"  name="fourth_tax_due_id" value="{{ $getjsonDetails[0]['fourth_installment_tax_due'] ?? '' }}">
                                    </div>

                                    <div class="form-group" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Delinquent :</label>
                                        <input class="form-control" type="date" style="flex: 1;" id="fourth_tax_delinquent_id" name="fourth_tax_delinquent_id" value="{{ $getjsonDetails[0]['fourth_installment_tax_delinquent'] ?? '' }}">
                                    </div>

                                    <div class="form-group" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Good through :</label>
                                        <input class="form-control" type="date" style="flex: 1;" id="fourth_good_through_id" name="fourth_good_through_id" value="{{ $getjsonDetails[0]['fourth_installment_good_through'] ?? '' }}">
                                    </div>

                                    <div class="form-group" style="display: flex; align-items: center;">
                                        <label style="margin-right: 10px; width: 150px;">Paid :</label>
                                        <input class="form-control" type="date" style="flex: 1;" id="fourth_tax_paid_id" name="fourth_tax_paid_id" value="{{ $getjsonDetails[0]['fourth_installment_tax_paid'] ?? '' }}">
                                    </div>
                                </div>
                            </div>
                            <!-- <label for="files" style="font-size: 24px;"><i class="fas fa-paperclip" style="font-size: 24px;"></i> Attach Files:</label> -->
                            <label for="files"><i class="fas fa-paperclip" style="font-size: 24px;"></i></label>
                            <input type="file" id="attachment" name="attachment[]" accept=".pdf, image/*" multiple>

                            <!-- The actual file input field (hidden for user interaction) -->
                                <input type="file" id="attachment" name="attachment[]" multiple style="display: none;">

                                <!-- Container to display uploaded file names -->
                                <div class="ml-2 mt-2" id="file-list">
                                    <!-- File names will be shown here -->
                                </div>
                                <div class=" mt-4 col-12">
                                    <div id="attachmentsHeader" class="header" style="display: none;"></div>
                                    <div id="fileList" class="col-lg-6 col-md-6">
                                        <!-- Files will be dynamically inserted here -->
                                    </div>
                                </div>
                                <!-- Modal to display uploaded file content (hidden by default) -->
                                <div id="file-modal" style="display: none; background-color: rgba(0, 0, 0, 0.5); position: fixed; top: 0; left: 0; width: 100%; height: 100%; justify-content: center; align-items: center;">
                                    <div style="background-color: white; padding: 20px; max-width: 800px; margin: auto;">
                                        <h4>File Preview</h4>
                                        <div id="file-preview-container">
                                            <!-- File content will be shown here -->
                                        </div>
                                        <div id="merged-file-preview">
                                            <!-- Merged file preview will be displayed here -->
                                        </div>
                                        <button class="btn btn-warning" id="close-modal" style="margin-top: 10px;">Close</button>
                                    </div>
                                </div>
                                <button id="merge-button" style="display: none; margin-top: 10px;">Merge Selected Files</button>

                            <!-- Notes Section -->
                            <div class="form-group mt-2">
                                <label for="">Comments :</label>
                                <textarea class="form-control" id="exampleFormControlTextarea1" name="exampleFormControlTextarea1" rows="3"
                                    placeholder="Enter Notes">{{ $getjsonDetails[0]['notes'] ?? '' }}</textarea>
                            </div>
                            @if(isset($getjsonDetails[0]['order_id']) && $getjsonDetails[0]['order_id'] != null)
                            <div class="row">
                            </div>
                            @else
                            <div class="row">
                                    <div class="col-md-12 text-center modelopenhide">
                                    <button type="submit" class="btn btn-primary">SUBMIT</button>
                                </div>
                            </div>
                            @endif
                            <!-- /s history -->
                            <div class="card-body mt-3">
                                <table id="attachmentHistoryTable" class="table table-bordered nowrap"
                                    style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                    <thead>
                                        <tr>
                                            <th>File Name</th>
                                            <th>User Name</th>
                                            <th>Action</th>
                                            <th>Updated At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data populated by DataTables via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-body">
                                <table id="orderstatusdetail_datatable" class="table table-bordered nowrap"
                                    style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead>
                                    <tr>
                                        <th>Comments</th>
                                        <th>User</th>
                                        <th>Date and Time (EST)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($orderTaxInfo && count($orderTaxInfo) > 0)
                                        @foreach($orderTaxInfo as $status)
                                            <tr>
                                                <td>{{ $status->comment ?? 'N/A' }}</td>
                                                <td>{{ $status->username ?? 'N/A' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($status->updated_at)->format('m-d-Y H:i:s') }}
                                            </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td class="text-center" colspan="4">No history available for this
                                                order.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                             <!-- /e history -->
                        </form>
                    </div>
                    <!-- Order Submission Form - Hidden by Default -->
                    <div id="orderForm" class="p-3" style="display:none;">
                        <!-- <h6 class="font-weight-bold">Order Submission :</h6> -->
                        <h5 style="margin-bottom: 0px; margin-top: 5px;">Order Submission :</h5>
                        <div class="mb-4">
                            <div>
                            @if(isset($checklist_conditions) && count($checklist_conditions) > 0)
                                <div class="font-weight-bold"> Special Checklist :</div>
                                <div class="row mt-1 mb-4  mx-5 ">
                                    <div class="col-12 row bg-danger justify-content-center" id="checklist-container"
                                        style="border-radius:14px;">
                                        @php $counter = 0; @endphp
                                        @foreach($checklist_conditions as $checklist_condition)
                                        <div class="row col-12 {{ $counter > 0 ? '' : 'box' }}"
                                            style="{{  $counter > 0 ? 'margin-top: -9px; padding-top: 0;' : '' }}">
                                            <input type="checkbox" class="p-0 checklist-item" name="checks[]"
                                                id="check_{{ $checklist_condition->id }}"
                                                value="{{ $checklist_condition->id }}">
                                            <label class="text-white font-weight-bold text-uppercase px-1"
                                                style="font-size: 14px !important;">{{ $checklist_condition->check_condition }}</label>
                                            </div>
                                            @php $counter++; @endphp
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                                <div class="row mt-2 mb-4">
                                <div class="col-12 card-body ">
                                    @if(isset($checklist_conditions_2) && count($checklist_conditions_2) > 0)
                                    <div class="font-weight-bold">Checklist :</div>
                                    <div class="row mt-1  mx-5 ">
                                        <div class="col-12  ">
                                            @php $counter = 0; @endphp
                                            @foreach($checklist_conditions_2 as $checklist_condition)
                                                <div class="row col-12 ">
                                                    <input type="checkbox" class="p-0" name="checks[]"
                                                        id="check_{{ $checklist_condition->id }}"
                                                        value="{{ $checklist_condition->id }}">
                                                    <label
                                                        class="text-black   px-1">{{ $checklist_condition->check_condition }}</label>
                                            </div>
                                                @php $counter++; @endphp
                                        @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                <div class="col-lg-4 ">
                                    <div class="font-weight-bold">Comments :</div>
                                        <textarea name="order_comment" style="width: 100%;" class="mx-5 mt-2"
                                            id="order_comment" cols="30" rows="4"></textarea>
                                </div>
                                <div class="col-lg-5 mx-5 mt-1">
                                        <div class="row">
                                        <div class="col-10 mb-2">
                                                <div class="font-weight-bold mb-1 mt-1"><span
                                                        style="color:red;">*</span>Primary Source :</div>
                                                <select id="primary_source" name="primary_source"
                                                    class="form-control select2dropdown required-field"
                                                    data-parsley-required="true">
                                                    <option disabled selected value="">Select Primary Source</option>
                                                    @foreach($primarySource as $source)
                                                        <option value="{{ $source->id }}" 
                                                            {{ isset($countyInfo['PRIMARY']) && $source->source_name == ($countyInfo['PRIMARY']['PRIMARY_SOURCE'] ?? '') ? 'selected' : '' }}>
                                                            {{ $source->source_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                        </div>
                                            <div class="col-12 mb-2">
                                    <div class="font-weight-bold mb-1 mt-1">Status :</div>
                                                <input type="hidden" id="current_status_id" name="current_status_id"
                                                    value="{{ $orderData->status_id }}">
                                                <select style="width: 83%;" class="form-control" name="order_status"
                                                    id="order_status" @if(!isset($orderData->assignee_user)) disabled
                                                    @endif>
                                                    <option value="1" id="status_1" @if($orderData->status_id == 1)
                                                        selected @endif>WIP</option>
                                                    <option value="2" id="status_2" @if($orderData->status_id == 2)
                                                        selected @endif>Hold</option>
                                                    <option value="3" id="status_3" @if($orderData->status_id == 3)
                                                        selected @endif>Cancelled</option>
                                                    <option value="4" id="status_4" @if($orderData->status_id == 4)
                                                        selected @endif>Send for QC</option>
                                                    <option value="5" id="status_5" @if($orderData->status_id == 5)
                                                        selected @endif>Completed</option>
                                                    <option value="13" id="status_13" @if($orderData->status_id == 13)
                                                        selected @endif>Coversheet Prep</option>
                                                    <option value="14" id="status_14" @if($orderData->status_id == 14)
                                                        selected @endif>Clarification</option>
                                                    <option value="18" id="status_18" @if($orderData->status_id == 18)
                                                        selected @endif>Ground Abstractor</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if(is_null($getTaxBucket[0]->tax_bucket))
                                    <div class="d-flex justify-content-center my-4">
                                        <button class="btn btn-primary btn-sm mx-2" id="ordersubmit"
                                            onclick="order_submition({{ $orderData->id }}, 1)" type="submit">Submit
                                    </button>
                                    <button class="btn btn-info btn-sm mx-2" id="coversheetsubmit"
                                            name="coversheetsubmit" onclick="order_submition({{ $orderData->id }}, 2)"
                                        type="submit">Coversheet Prep & Submit
                                    </button>
                        </div>
                                @endif
                    </div>
                            <!-- s -->
                        <div class="card-body">
                                <table id="orderstatusdetail_datatable" class="table table-bordered nowrap"
                                    style="border-collapse: collapse; border-spacing: 0; width: 100%;">
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
                                            <td>{{ \Carbon\Carbon::parse($status->created_at)->format('m-d-Y H:i:s') }}
                                            </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td class="text-center" colspan="4">No status history available for this
                                                order.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                            <!-- e -->
                        </div>
                    </div>
                </div>
                    @endif
                    @if(in_array($orderData->stl_process_id, [2, 4, 6]))
                    <h6 class="font-weight-bold">Order Submission :</h6>
                    <div class="card shadow shadow-md rounded showdow-grey mb-4">
                        <div class="card-body">
                                    <div class="row mt-4 mb-4">
                                <div class="col-lg-4 ">
                                    <div class="font-weight-bold">Comments :</div>
                                <textarea name="order_comment" style="width: 100%;" class="mx-5 mt-2" id="order_comment"
                                    cols="30" rows="4"></textarea>
                                </div>
                                    <div class="col-lg-5 mx-5 mt-1">
                                        <div class="row">
                                            <div class="col-10 mb-2">
                                                <div class="font-weight-bold mb-1 mt-1">Status :</div>
                                        <input type="hidden" id="current_status_id" name="current_status_id"
                                            value="{{ $orderData->status_id }}">
                                        <select style="width:300px" class="form-control" name="order_status"
                                            id="order_status" @if(!isset($orderData->assignee_user)) disabled @endif>
                                            <option value="1" id="status_1" @if($orderData->status_id == 1) selected
                                                @endif>WIP</option>
                                            <option value="2" id="status_2" @if($orderData->status_id == 2) selected
                                                @endif>Hold</option>
                                            <option value="3" id="status_3" @if($orderData->status_id == 3) selected
                                                @endif>Cancelled</option>
                                            <option value="4" id="status_4" @if($orderData->status_id == 4) selected
                                                @endif>Send for QC</option>
                                            <option value="5" id="status_5" @if($orderData->status_id == 5) selected
                                                @endif>Completed</option>
                                            <option value="13" id="status_13" @if($orderData->status_id == 13) selected
                                                @endif>Coversheet Prep</option>
                                            <option value="14" id="status_14" @if($orderData->status_id == 14) selected
                                                @endif>Clarification</option>
                                            <option value="18" id="status_18" @if($orderData->status_id == 18) selected
                                                @endif>Ground Abstractor</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center my-4">
                            <button class="btn btn-primary btn-sm mx-2" id="ordersubmit"
                                onclick="order_submition({{$orderData->id}},1)" type="submit">Submit
                            </button>
                            <button class="btn btn-info btn-sm mx-2" id="coversheetsubmit" name="coversheetsubmit"
                                onclick="order_submition({{$orderData->id}},2)" type="submit">Coversheet Prep &
                                Submit
                            </button>
                                </div>
                        </div>
                    </div>
                        <div class="card-body">
                    <table id="orderstatusdetail_datatable" class="table table-bordered nowrap"
                        style="border-collapse: collapse; border-spacing: 0; width: 100%;">
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
                                            <td class="text-center" colspan="4">No status history available for this order.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
</div>
</div>
@endif
<!-- // -->
                @if($orderData->client_id == 82)
                <h6 class="read_value <?php echo (is_null($readValue) && $orderData->status_id == 1) ? 'd-none' : ''; ?> font-weight-bold">Order Submission :</h6>
                <div class="read_value <?php echo (is_null($readValue) && $orderData->status_id == 1) ? 'd-none' : ''; ?> card shadow shadow-md rounded showdow-grey mb-4">
                        <div class="read_value <?php echo (is_null($readValue) && $orderData->status_id == 1) ? 'd-none' : ''; ?> card-body">
                            <div class="row mt-4 mb-4">
                                <div class="col-lg-4 ">
                                    <div class="font-weight-bold">Comments :</div>
                                        <textarea name="order_comment" style="width: 100%;" class="mx-5 mt-2" id="order_comment" cols="30" rows="4"></textarea>
                                </div>
                                <div class="col-lg-4 mx-5 mt-1">
                                    <div class="row">
                                        <div class="col-10 mb-2">
                                            <div class="col-10 mb-2">
                                                <div class="font-weight-bold mb-1 mt-1">Status :</div>
                                                <input type="hidden" id="current_status_id" name="current_status_id" value="{{ $orderData->status_id }}">
                                                    <select class="form-control" style="width:300px" name="order_status" id="order_status" @if(!isset($orderData->assignee_user)) disabled @endif>
                                                        @if(!Auth::user()->hasRole('Typist') && !Auth::user()->hasRole('Typist/Qcer'))
                                                        <option value="1" id="status_1" @if($orderData->status_id == 1) selected @endif>WIP</option>
                                                            <option value="15" id="status_15"  @if($orderData->status_id == 15) selected @endif>Doc Purchase</option>
                                                        <option value="14" id="status_14"  @if($orderData->status_id == 14) selected @endif>Clarification</option>
                                                        @endif

                                                        <option value="4" id="status_4" @if($orderData->status_id == 4) selected @endif>Send for QC</option>
                                                        @if(in_array($orderData->stl_process_id, [12, 7]))
                                                        <option value="16" id="status_16"  @if($orderData->status_id == 16) selected @endif>Typing</option>

                                                        <option value="17" id="status_17"  @if($orderData->status_id == 17) selected @endif>Typing QC</option>
                                                        @endif
                                                        <option value="18" id="status_18"  @if($orderData->status_id == 18) selected @endif>Ground Abstractor</option>
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
                                <div class="read_value <?php echo (is_null($readValue) && $orderData->status_id == 1) ? 'd-none' : ''; ?> card-body">
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
                                                    <td class="text-center" colspan="4">No status history available for this order.</td>
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
            @if(in_array($orderData->client_id, [84, 85, 86, 87, 88, 89, 90, 91, 92, 13, 2]))
            <h6 class="read_value  font-weight-bold">Order Submission :</h6>
                <div class="read_value card shadow shadow-md rounded showdow-grey mb-4">
                        <div class="read_value card-body">
                            <div class="row mt-4 mb-4">
                                <div class="col-lg-4 ">
                                    <div class="font-weight-bold">Comments :</div>
                                        <textarea name="order_comment" style="width: 100%;" class="mx-5 mt-2" id="order_comment" cols="30" rows="4"></textarea>
                                </div>
                                <div class="col-lg-4 mx-5 mt-1">
                                    <div class="row">
                                        <div class="col-10 mb-2">
                                            <div class="col-10 mb-2">
                                                <div class="font-weight-bold mb-1 mt-1">Status :</div>
                                                    <input type="hidden" id="current_status_id" name="current_status_id" value="{{ $orderData->status_id }}">
                                                    <select class="form-control" style="width:300px" name="order_status" id="order_status" @if(!isset($orderData->assignee_user)) disabled @endif>
                                                        @if(!Auth::user()->hasRole('Typist') && !Auth::user()->hasRole('Typist/Qcer'))
                                                        <option value="1" id="status_1" @if($orderData->status_id == 1) selected @endif>WIP</option>
                                                        <!-- @if(in_array($orderData->stl_process_id, [12, 7, 8, 9]) || !in_array($orderData->client_id, [84, 85, 86]))
                                                            <option value="15" id="status_15"  @if($orderData->status_id == 15) selected @endif>Doc Purchase</option>
                                                        @endif                                               -->
                                                        <option value="14" id="status_14"  @if($orderData->status_id == 14) selected @endif>Clarification</option>
                                                        @endif

                                                        <option value="4" id="status_4" @if($orderData->status_id == 4) selected @endif>Send for QC</option>
                                                        @if(in_array($orderData->stl_process_id, [12, 7]))
                                                        <option value="16" id="status_16"  @if($orderData->status_id == 16) selected @endif>Typing</option>

                                                        <option value="17" id="status_17"  @if($orderData->status_id == 17) selected @endif>Typing QC</option>
                                                        @endif
                                                        <option value="18" id="status_18"  @if($orderData->status_id == 18) selected @endif>Ground Abstractor</option>
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
                                <div class="read_value card-body">
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
                                                    <td class="text-center" colspan="4">No status history available for this order.</td>
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
    @if($orderData->status_id == 15)
    <div class="modal fade" id="ipErrorModal" tabindex="-1" aria-labelledby="ipErrorModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center">
                <div class="modal-body d-flex justify-content-center align-items-center flex-column" style="border-top: none !important;">
                    <img src="{{ asset('assets/images/p_ip_error.png') }}" style="height: 30vh;" alt="IP Error">
                    <span class="error-message mt-3">
                    <span style="font-size: 30px;">Switch to US IP Address</span>
    </span>
                </div>
                <div class="modal-footer d-flex justify-content-center">
                    @if($orderData && $orderData->id)
                        <button 
                            onclick="window.location.href = '{{ url('orderform/') }}/{{ $orderData->id }}';" 
                            class="btn btn-success me-2">
        Refresh
    </button>
                    @endif
                    <button type="button" class="btn btn-warning" data-bs-dismiss="modal" aria-label="Close" onclick="window.location.href='{{ url('orders_status') }}'">Back</button>
                </div>
            </div>
    </div>
    </div>
    @endif
</div>
@if($orderData->status_id == 15)
<script>
$(document).ready(function() {
    $.get('https://api.ipify.org?format=json', function(data) {
        var ipAddress = data.ip;
        $('#user_ip').html(ipAddress);
        $.ajax({
            url: `https://ipinfo.io/${ipAddress}/json`,
            method: 'GET',
            dataType: 'json',
            success: function(ipData) {
                
                if (ipData.country && ipData.country === 'US') {
                    $('#ipErrorModal').modal('hide');
                } else {
                   $('#ipErrorModal').modal('show');
                    $('#ipErrorModal').on('hide.bs.modal', function (e) {
                        e.preventDefault();
                    });
                }
                
            },
            error: function() {
                console.error("Failed to get IP information.");
            }
        });
    });
});
</script>
@endif

@if(!in_array($orderData->stl_process_id, [2, 4, 6]))
<script>


let isTaxFormVisible = false;
let isOrderFormVisible = false;

function updateHideCardVisibility() {
    document.getElementById('hide_card').style.display = (isTaxFormVisible || isOrderFormVisible) ? 'block' : 'none';
}

if (lastSegment === 'tax') {
        document.getElementById('showOrderForm').disabled = true;
        document.getElementById('showOrderForm').classList.add('btn-inactive');

    } else {
        document.getElementById('showOrderForm').disabled = false;
        document.getElementById('showOrderForm').classList.remove('btn-inactive');
    }



document.getElementById('showTaxForm').addEventListener('click', function() {
    isTaxFormVisible = !isTaxFormVisible;
    document.getElementById('taxForm').style.display = isTaxFormVisible ? 'block' : 'none';

    this.classList.toggle('btn-active', isTaxFormVisible);
    this.classList.toggle('btn-inactive', !isTaxFormVisible);

    if (isOrderFormVisible) {
        isOrderFormVisible = false;
        document.getElementById('orderForm').style.display = 'none';
        document.getElementById('showOrderForm').classList.remove('btn-active');
        document.getElementById('showOrderForm').classList.add('btn-inactive');
    }

    updateHideCardVisibility();
});

document.getElementById('showOrderForm').addEventListener('click', function() {
    isOrderFormVisible = !isOrderFormVisible;
    document.getElementById('orderForm').style.display = isOrderFormVisible ? 'block' : 'none';

    this.classList.toggle('btn-active', isOrderFormVisible);
    this.classList.toggle('btn-inactive', !isOrderFormVisible);

    if (isTaxFormVisible) {
        isTaxFormVisible = false;
        document.getElementById('taxForm').style.display = 'none';
        document.getElementById('showTaxForm').classList.remove('btn-active');
        document.getElementById('showTaxForm').classList.add('btn-inactive');
    }

    updateHideCardVisibility();
});

updateHideCardVisibility();


    if (lastSegment === 'tax') {
        document.getElementById('showTaxForm').click();
    }else{
        document.getElementById('showOrderForm').click();
    }

$(document).ready(function() {
  $("#taxFormValues").on("submit", function(event) {
    event.preventDefault(); 
    var formData = $(this).serialize();

    // Append additional data from the specified input fields
    var taxStatus = $("#tax_status").val();
    var getData = $("#get_data").val();
    var searchInput = $("#search_input").val();

    // Create an object to hold the serialized data and additional fields
    var additionalData = {
      tax_status: taxStatus,
      get_data: getData,
      search_input: searchInput
    };

    // Convert the additionalData object to a query string
    var additionalDataString = $.param(additionalData);
    
    // Combine the original form data with the additional data
    var combinedData = formData + '&' + additionalDataString;

    $.ajax({
      url: "{{ url('taxform_submit') }}",
      type: "POST",
      data: combinedData,
      headers: {
        'X-CSRF-TOKEN': $('input[name="_token"]').val()
      },
      success: function(response) {
        Swal.fire({
          title: 'Success!',
          text: response.message,
          icon: 'success',
          confirmButtonText: 'OK'
        });
      },
      error: function(error) {        
        Swal.fire({
          title: 'Error!',
          text: 'Failed to send data. Please try again.',
          icon: 'error',
          confirmButtonText: 'OK'
        });
      }
    });
  });
});

$(document).ready(function () {
    // Trigger file upload on file selection change
    $("#attachment").on("change", function () {
        uploadFiles();
    });

    function uploadFiles() {
        let orderId = $("#order_id").val();
        let fileList = $("#attachment")[0].files;
        
        if (fileList.length > 0) {
            for (let i = 0; i < fileList.length; i++) {
                let formData = new FormData();
                formData.append('file', fileList[i]);
                formData.append('order_id', orderId);

                $.ajax({
                    url: "{{ url('storeFile') }}",
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        console.log("File stored successfully:", response);
                        // Refresh the file list after each successful upload
                        displayFiles();
                    },
                    error: function (xhr, status, error) {
                        console.error("File upload failed:", error);
                    }
                });
            }
        }
    }

    function displayFiles() {
        let orderId = $("#order_id").val();

        $.ajax({
            url: "{{ url('getFiles') }}",
            type: 'GET',
            data: { order_id: orderId },
            success: function (data) {
                let fileList = $('#fileList');
                let attachmentsHeader = $('#attachmentsHeader');
                fileList.empty(); // Clear previous file list

                if (data.length > 0) {
                    attachmentsHeader.show();
                    data.forEach(file => {
                        let fileType = file.name.split('.').pop().toLowerCase();
                        let filePreview = '';

                        if (['jpg', 'jpeg', 'png', 'gif'].includes(fileType)) {
                            filePreview = `<p><a href="#" class="file-link" data-file-url="${file.path}">${file.name}</a></p>`;
                        } else if (fileType === 'pdf') {
                            filePreview = `<p><a href="#" class="file-link" data-file-url="${file.path}">${file.name}</a></p>`;
                        } else if (['doc', 'docx', 'xls', 'xlsx', 'eml'].includes(fileType)) {
                            filePreview = `<p><a href="${file.path}" download="${file.name}">${file.name}</a></p>`;
                        } else {
                            filePreview = `<p>${file.name} <span class="badge bg-secondary">Unknown file type</span></p>`;
                        }

                        fileList.append(`<div class="m-2">${filePreview}</div>`);
                    });

                    $('.file-link').on('click', function (e) {
                        e.preventDefault();
                        let fileUrl = $(this).data('file-url');
                        let fileType = fileUrl.split('.').pop().toLowerCase();

                        if (['jpg', 'jpeg', 'png', 'gif'].includes(fileType)) {
                            Swal.fire({ title: 'View Image', html: `<img src="${fileUrl}" style="width:100%; height:auto;" />`, showCloseButton: true, confirmButtonText: 'Close', width: '80%' });
                        } else if (fileType === 'pdf') {
                            Swal.fire({ title: 'View File', html: `<iframe src="${fileUrl}" style="width:100%; height:500px;" frameborder="0"></iframe>`, showCloseButton: true, confirmButtonText: 'Close', width: '80%' });
                        } else if (['doc', 'docx', 'xls', 'xlsx', 'eml'].includes(fileType)) {
                            window.open(fileUrl, '_blank');
                        } else {
                            Swal.fire({ icon: 'error', title: 'Unsupported File Type', text: 'This file type is not supported for viewing.', confirmButtonText: 'OK' });
                        }
                    });
                } else {
                    attachmentsHeader.hide(); // Hide header if no files are available
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                Swal.fire({ icon: 'error', title: 'Oops...', text: 'An error occurred while retrieving the files.', confirmButtonText: 'OK' });
            }
        });
    }

    // Initial call to load files if `order_id` is already set
    displayFiles();
});


$(document).ready(function() {
    // Handle file selection and display clickable file names


    // Bind click event to file name text for preview
    $("#file-list").on("click", ".file-name", function() {
        var fileName = $(this).data("filename"); // Get the clicked file name
        var file = getFileByName(fileName);

        if (file) {
            showFilePreview(file);  // Show the preview in modal if the file exists
            $("#file-modal").show(); // Display modal
            $(".sticky-container, .topbar, .modelopenhide").hide(); // Hide other elements
        }
    });

    // Close modal without form submission
    $("#close-modal").on("click", function(event) {
        event.preventDefault();
        $("#file-modal").hide();  // Hide modal
        $(".sticky-container, .topbar, .modelopenhide").show(); // Restore hidden elements
    });

    // Helper function to retrieve file by name
    function getFileByName(fileName) {
        var fileList = $("#attachment")[0].files;
        for (var i = 0; i < fileList.length; i++) {
            if (fileList[i].name === fileName) {
                return fileList[i];
            }
        }
        return null;
    }

    // Display file preview in modal
    function showFilePreview(file) {
        var reader = new FileReader();

        reader.onload = function(e) {
            var filePreviewContainer = $("#file-preview-container");

            if (file.type.startsWith("image/")) {
                filePreviewContainer.html(`<img src="${e.target.result}" style="max-width: 100%; height: auto;" />`);
            } else if (file.type === "application/pdf") {
                filePreviewContainer.html(`<embed src="${e.target.result}" width="100%" height="400px" />`);
            } else {
                filePreviewContainer.html(`<pre>${e.target.result}</pre>`);
            }
        };

        reader.readAsDataURL(file);
    }
});

// $(document).ready(function() {
//     // Track the number of merges
//     var mergeCount = 0;

//     // Handle file selection and display clickable file names with checkboxes
//     $("#attachment").on("change", function() {
//         var fileList = $(this)[0].files;
//         var output = '';

//         // Display file names with checkboxes when two or more files are selected
//         if (fileList.length >= 2) {
//             for (var i = 0; i < fileList.length; i++) {
//                 var fileName = fileList[i].name;
//                 output += `<div>
//                     <input type="checkbox" class="file-checkbox" data-filename="${fileName}" /> 
//                     <span class="file-name" data-filename="${fileName}" style="cursor: pointer; color: blue;">${fileName}</span>
//                 </div>`;
//             }
//             $("#file-list").html(output);
//             $("#merge-button").show();  // Show the merge button when two or more files are uploaded
//         } else {
//             // Only show the file names if less than two files are uploaded
//             for (var i = 0; i < fileList.length; i++) {
//                 var fileName = fileList[i].name;
//                 output += `<div><span class="file-name" data-filename="${fileName}" style="cursor: pointer; color: blue;">${fileName}</span></div>`;
//             }
//             $("#file-list").html(output);
//             $("#merge-button").hide();  // Hide the merge button if less than 2 files are uploaded
//         }

//         toggleMergeButton(); // Check if the merge button should be displayed based on selected checkboxes
//     });

//     // Show or hide the Merge button based on selected checkboxes
//     $(document).on("change", ".file-checkbox", function() {
//         toggleMergeButton();
//     });

//     // Function to show or hide the Merge button
//     function toggleMergeButton() {
//         var selectedCount = $(".file-checkbox:checked").length;
//         if (selectedCount >= 2) {
//             $("#merge-button").show(); // Show the button if two or more files are selected
//         } else {
//             $("#merge-button").hide(); // Hide the button if less than 2 files are selected
//         }
//     }

//     // Merge button action - prevent form submission and merge files
//     $("#merge-button").on("click", function(event) {
//         event.preventDefault(); // Prevent form submission

//         var selectedFiles = [];
//         $(".file-checkbox:checked").each(function() {
//             var fileName = $(this).data("filename");
//             var file = getFileByName(fileName);
//             if (file) {
//                 selectedFiles.push(file);
//             }
//         });

//         if (selectedFiles.length >= 2) {
//             // Send the files to the server for merging
//             var formData = new FormData();
//             selectedFiles.forEach(function(file) {
//                 formData.append('files[]', file);
//             });

//             // Perform the AJAX request to merge the files
//             $.ajax({ 
//                 url: "{{ url('mergeFiles') }}",  // Laravel route to handle file merge
//                 type: 'POST',
//                 data: formData,
//                 processData: false,  // Prevent jQuery from processing the data
//                 contentType: false,  // Prevent jQuery from setting content type
//                 headers: {
//                     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // CSRF token
//                 },
//                 success: function(response) {
//                     if (response.status === 'success') {
//                         // Increment merge count
//                         mergeCount++;

//                         // Generate a dynamic name for the merged file
//                         var mergedFileName = "merged_file_" + mergeCount;
                        
//                         // Display the merged file content in the preview
//                         showFilePreview(response.mergedFileUrl, mergedFileName);
//                     } else {
//                         alert('Error merging files!');
//                     }
//                 },
//                 error: function() {
//                     alert('Something went wrong!');
//                 }
//             });
//         } else {
//             alert("Please select at least 2 files to merge.");
//         }
//     });

//     // // Helper function to get file by name
//     // function getFileByName(fileName) {
//     //     var fileList = $("#attachment")[0].files;
//     //     for (var i = 0; i < fileList.length; i++) {
//     //         if (fileList[i].name === fileName) {
//     //             return fileList[i];
//     //         }
//     //     }
//     //     return null;
//     // }

//     // // Function to preview the merged file
//     // function showFilePreview(fileUrl, fileName) {
//     //     var previewContainer = $("#file-list");
        
//     //     // Clear any previous preview
//     //     previewContainer.html('');

//     //     // Add the merged file preview
//     //     previewContainer.append(`
//     //         <a href="${fileUrl}" target="_blank">Download ${fileName}</a>
//     //     `);
//     // }
// });


$(document).ready(function() {
    $("#phone_num").on("input", function() {
        var input = $(this).val();
        input = input.replace(/[^0-9x]/g, '');

        if (input.length <= 10) {
            input = input.replace(/(\d{3})(\d{3})(\d{4})/, "($1) $2-$3");
        } else {
            input = input.replace(/(\d{3})(\d{3})(\d{4})(\d+)/, "($1) $2-$3 x$4");
        }
        $(this).val(input);
    });
});

    window.addEventListener('DOMContentLoaded', function() {
    var taxStatusValue = document.getElementById('tax_status').value;
    var fetchButton = document.getElementById('fetchButton');
    var saveButton = document.getElementById('SaveButton');

    // Check the initial value of tax_status and display the correct button
    if (taxStatusValue === '') {
        fetchButton.style.display = 'none';
        saveButton.style.display = 'none';
    } else if (taxStatusValue === 'online') {
        fetchButton.style.display = 'inline-block';
        saveButton.style.display = 'none';
    } else if (taxStatusValue === 'offline') {
        fetchButton.style.display = 'none';
        saveButton.style.display = 'inline-block';
    }
});


    document.getElementById('tax_status').addEventListener('change', function() {
        var fetchButton = document.getElementById('fetchButton');
    var SaveButton = document.getElementById('SaveButton');
    
    // Show Fetch button for 'online', Save button for 'offline'
        fetchButton.style.display = this.value === 'online' ? 'inline-block' : 'none';
    SaveButton.style.display = this.value === 'offline' ? 'inline-block' : 'none';
});

      function validateDecimalInput(event) {
        const value = event.target.value;
        // Allow only numbers with optional decimal points and two decimal places
        if (!/^\d*\.?\d{0,2}$/.test(value)) {
            event.target.value = value.slice(0, -1); // Remove last character if invalid
        }
    }

    // Attach event listeners to each input field
    const decimalFields = [
        'land', 'improvement', 'exemption_mortgage', 'exemption_homeowner',
        'exemption_homestead', 'exemption_additional', 'others', 'total_annual_tax',
        'net_value','first_amount_id','second_amount_id','third_amount_id','fourth_amount_id'
    ];

    decimalFields.forEach(id => {
        document.getElementById(id).addEventListener('input', validateDecimalInput);
    });

function onlyOne(checkbox) {
    const checkboxes = checkbox.closest('.checkbox-group').querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach((cb) => {
        if (cb !== checkbox) cb.checked = false;
    });
}

function toggleReadonly(checkbox, inputId) {
        const inputField = document.getElementById(inputId);
        inputField.readOnly = !checkbox.checked;
    }


$(function() {
    $('#SaveButton').on('click', function(e) {
        e.preventDefault();

        // Collect data from form
        const taxStatus = $('#tax_status').val();
        const getData = $('#get_data').val();
        const searchInput = $('#search_input').val();
        const orderId = $("#order_id").val();

        // Validate that all fields are filled
        if (taxStatus && getData && searchInput) {
            // AJAX request
            $.ajax({
                url: '{{ url("moveToTaxStatus") }}',
                type: 'POST',
                dataType: 'json',
                data: {
                    orderId: orderId,
                    tax_status: taxStatus,
                    get_data: getData,
                    search_input: searchInput,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    // Display success message with Swal
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Tax Form Moved To Tax Status',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Reload the page after closing the success alert
                        window.location.reload();
                    });
                },
                error: function(xhr, status, error) {
                    // Display error message with Swal
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred. Please try again.',
                        confirmButtonText: 'OK'
                    });
                }
            });
        } else {
            // Display warning if fields are missing
            Swal.fire({
                icon: 'warning',
                title: 'Incomplete Data',
                text: 'Please fill in all fields before saving.',
                confirmButtonText: 'OK'
            });
        }
    });
});
</script>
@endif

<script>

$(document).ready(function() {
    $('#order_status').change(function() {
        let status = $(this).val();
        @if(Auth::user()->hasRole('Process'))
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
    var getID = $("#getID").val();
    if (getID == 82) {
        const requiredFields = [
            { id: "#client_id_", message: "Client ID is required." },
            { id: "#portal_fee_cost_id", message: "Portal Fee Cost is required." },
            { id: "#source_id", message: "Source is required." },
            { id: "#copy_cost_id", message: "Copy Cost is required." },
            { id: "#no_of_search_id", message: "No of Search Done is required." },
            { id: "#document_retrive_id", message: "No of Documents Retrieved is required." }
        ];

        for (const field of requiredFields) {
            if (!$(field.id).val()) {
                Swal.fire({
                    title: "Please fill the Column",
                    text: field.message,
                    icon: "error"
                });
                return false;
            }
        }
    }

        var checklistItems = [];
        $("input[name='checks[]']:checked").each(function() {
            checklistItems.push($(this).val());
        });
        var check_box_id = $("#check_box_id").val();
        var orderComment = $("#order_comment").val();
        var orderStatus = $("#order_status").val();
    var currentStatusId = $("#current_status_id").val();
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
    var copyCost = $("#copy_cost_id").val();
    var noOfSearch = $("#no_of_search_id").val();
    var documentRetrive = $("#document_retrive_id").val();
    var titlePointAccount = $("#title_point_account_id").val();
    var purchase_link = $("#purchase_link_id").val();
    var username = $("#username_id").val();
    var password = $("#password_id").val();
    var file_path = $("#file_path_id").val();
        var readed_value = $("#proceedButton").val();

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
            currentStatusId: currentStatusId,
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
        copyCost: copyCost,
        noOfSearch: noOfSearch,
        documentRetrive: documentRetrive,
        titlePointAccount: titlePointAccount,
        purchase_link: purchase_link,
        username: username,
        password: password,
        file_path: file_path,
        readed_value: readed_value,
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
                $('#copy_cost_id').val(userinputdetails.copy_cost || '');
                $('#no_of_search_id').val(userinputdetails.no_of_search_done || '');
                $('#document_retrive_id').val(userinputdetails.no_of_documents_retrieved || '');
                $('#title_point_account_id').val(userinputdetails.title_point_account || '');
                $('#purchase_link_id').val(userinputdetails.purchase_link || '');
                $('#username_id').val(userinputdetails.username || '');
                $('#password_id').val(userinputdetails.password || '');
                $('#file_path_id').val(userinputdetails.file_path || '');

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
    var completionDate = "{{ $orderData->completion_date }}";
    var status = "{{ $orderData->status_id }}";

    var compareDate = new Date(orderRecDate);
    var deadline = new Date(compareDate.getTime() + tatValue * 60 * 60 * 1000);
    
    var phaseDuration = tatValue / 4;

    if (status === "5") {
        document.getElementById("timer").style.display = "none";
        document.getElementById("completion-timing").style.display = "block";
        displayCompletionTime(compareDate, new Date(completionDate));
    } else {
        document.getElementById("timer").style.display = "block";
        document.getElementById("completion-timing").style.display = "none";

    var timer = setInterval(function() {
        updateTimer(compareDate, deadline, phaseDuration);
        }, 1000);
    }

    function updateTimer(toDate, deadline, phaseDuration) {
        var now = new Date(new Intl.DateTimeFormat('en-US', {
            timeZone: 'America/New_York',
            year: 'numeric', month: 'numeric', day: 'numeric',
            hour: 'numeric', minute: 'numeric', second: 'numeric',
            hour12: false
        }).format(new Date()));

        var elapsed = now.getTime() - toDate.getTime();
        var elapsedHours = elapsed / (1000 * 60 * 60);

        var seconds = Math.floor(elapsed / 1000);
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

        ["days", "hours", "minutes", "seconds", "headline"].forEach(id => {
            document.getElementById(id).classList.remove("timer-green", "timer-gold", "timer-orange", "timer-red");
        });

        if (elapsedHours <= phaseDuration) {
            applyTimerClass("timer-green");
        } else if (elapsedHours <= phaseDuration * 2) {
            applyTimerClass("timer-gold");
        } else if (elapsedHours <= phaseDuration * 3) {
            applyTimerClass("timer-orange");
        } else if (elapsedHours <= phaseDuration * 4) {
            applyTimerClass("timer-red");
        } else {
            applyTimerClass("timer-brown");
        }
    }

    function applyTimerClass(className) {
        ["days", "hours", "minutes", "seconds", "headline"].forEach(id => {
            document.getElementById(id).classList.add(className);
        });
    }

    function displayCompletionTime(startDate, endDate) {
        var diff = endDate.getTime() - startDate.getTime();
        var totalDays = Math.floor(diff / (1000 * 60 * 60 * 24));
        var remainingHours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var remainingMinutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        var remainingSeconds = Math.floor((diff % (1000 * 60)) / 1000);
        var totalHours = (totalDays * 24) + remainingHours;

        document.getElementById("completion-time").textContent = 
            (totalHours < 10 ? '0' : '') + totalHours + ':' + 
            (remainingMinutes < 10 ? '0' : '') + remainingMinutes + ':' + 
            (remainingSeconds < 10 ? '0' : '') + remainingSeconds;
        }

}
initializeTimer();

$(document).ready(function() {
    $('#orderstatusdetail_datatable').DataTable({
        "ordering": true, 
        "searching": true, 
        "paging": true, 
        "info": true, 
        "lengthChange": true, 
        "pageLength": 10, 
        "order": [[3, 'desc']]
    });
});

function updateESTTime() {
            const now = new Date();
            const estDate = new Date(now.toLocaleString('en-US', { timeZone: 'America/New_York' }));
            const options = { 
                year: 'numeric', 
                month: '2-digit', 
                day: '2-digit', 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit', 
                hour12: false 
            };
            const estFormatted = estDate.toLocaleString('en-US', options).replace(',', '');

            document.getElementById('headline').textContent = estFormatted + " (EST)";
        }

        updateESTTime();
        setInterval(updateESTTime, 1000);

    document.addEventListener('DOMContentLoaded', function() {
        const proceedButton = document.getElementById('proceedButton');
        if (proceedButton) {
            proceedButton.addEventListener('click', function() {
                document.querySelectorAll('.read_value').forEach(function(element) {
                    element.classList.remove('d-none');
                });

                this.classList.add('d-none');
            });
        }
    });

    $(document).ready(function () {
    // Automatically trigger displayFiles when order_id changes
    function displayFiles() {
        let orderId = $("#order_id").val();
        $.ajax({
            url: "{{ url('getFiles') }}",
            type: 'GET',
            data: {
                order_id: orderId // Use the dynamic ID
            },
            success: function (data) {
                let fileList = $('#fileList');
                let attachmentsHeader = $('#attachmentsHeader');
                fileList.empty(); // Clear previous file list

                if (data.length > 0) {
                    attachmentsHeader.show();
                    data.forEach(file => {
                        let fileType = file.name.split('.').pop().toLowerCase();
                        let filePreview = '';

                        if (['jpg', 'jpeg', 'png', 'gif'].includes(fileType)) {
                            filePreview = `<div class="d-flex "><a href="#" class="file-link" data-file-url="${file.path}">${file.name}</a></div>`;
                        } else if (fileType === 'pdf') {
                            filePreview = `<div class="d-flex "><a href="#" class="file-link" data-file-url="${file.path}">${file.name}</a></div>`;
                        } else if (['doc', 'docx', 'xls', 'xlsx', 'eml'].includes(fileType)) {
                            filePreview = `<div class="d-flex "><a href="${file.path}" download="${file.name}">${file.name}</a></div>`;
                        } else {
                            filePreview = `<div class="d-flex ">${file.name} <span class="badge bg-secondary">Unknown file type</span></div>`;
                        }

                        fileList.append(`
                            <div class="d-flex align-items-center row" data-file-id="${file.id}">
                                <div class="align-item-center">
                                    <button class="btn btn-link text-danger delete-file" data-file-id="${file.id}" title="Delete">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div style="flex: 1;">
                                    ${filePreview}
                                </div>
                            </div>
                        `);
                    });

                    // Event listener for viewing files
                    $('.file-link').on('click', function (e) {
                        e.preventDefault();
                        let fileUrl = $(this).data('file-url');
                        let fileType = fileUrl.split('.').pop().toLowerCase();

                        if (['jpg', 'jpeg', 'png', 'gif'].includes(fileType)) {
                            Swal.fire({
                                title: 'View Image',
                                html: `<img src="${fileUrl}" style="width:100%; height:auto;" />`,
                                showCloseButton: true,
                                confirmButtonText: 'Close',
                                width: '80%'
                            });
                        } else if (fileType === 'pdf') {
                            Swal.fire({
                                title: 'View File',
                                html: `<iframe src="${fileUrl}" style="width:100%; height:500px;" frameborder="0"></iframe>`,
                                showCloseButton: true,
                                confirmButtonText: 'Close',
                                width: '80%'
                            });
                        } else if (['doc', 'docx', 'xls', 'xlsx', 'eml'].includes(fileType)) {
                            window.open(fileUrl, '_blank');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Unsupported File Type',
                                text: 'This file type is not supported for viewing.',
                                confirmButtonText: 'OK'
                            });
                        }
                    });

                } else {
                    attachmentsHeader.hide(); // Hide header if no files are available
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'An error occurred while retrieving the files.',
                    confirmButtonText: 'OK'
                });
            }
        });
    }

    // Initially load the files
    displayFiles();

    // Delete file functionality
    $(document).on('click', '.delete-file', function (e) {
        e.preventDefault();
        let fileId = $(this).data('file-id');
        let fileRow = $(this).closest('.row'); // Find the closest row that contains the file

        // Show confirmation dialog using Swal
        Swal.fire({
            title: 'Are you sure?',
            text: "This will delete the file permanently.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            console.log(result); // Check the full result object for debugging
            if (result.value) {
               
                $.ajax({
                    url: "{{ url('deleteFile') }}",
                    type: 'DELETE',
                    data: {
                        file_id: fileId
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function () {
                        Swal.fire('Deleted!', 'Your file has been deleted.', 'success');
                        fileRow.remove(); // Remove the file row from the UI
                        displayFiles(); // Refresh file list after deletion
                    },
                    error: function () {
                        Swal.fire('Error!', 'There was a problem deleting the file.', 'error');
                    }
                });
            }
        });
    });

});

$(document).ready(function() {
            // Initialize DataTable with AJAX data
            var table = $('#attachmentHistoryTable').DataTable({

                ajax: {
                    url: "{{ url('attachmentHistoryData') }}",
                    data: function(d) {
                        d.order_id = $("#order_id").val(); // Send `order_id` as a parameter
                    },
                    type: 'GET'
                },
                order: [[3, 'desc']],
                columns: [
                    { data: 'file_name' },
                    { data: 'user.username', defaultContent: '' }, 
                    { data: 'action' },
                    {
                        data: 'updated_at',
                        render: function(data) {
                            // Format the date (e.g., "YYYY-MM-DD HH:mm:ss" to "MM/DD/YYYY HH:mm")
                            let date = new Date(data);
                            return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                        }
                    }
                ]
            });

        
        });

</script>
@endsection

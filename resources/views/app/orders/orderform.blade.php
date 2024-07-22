@extends('layouts.app')
@section('title', config('app.name') . ' | Orders')
@section('content')
<div class="col-lg-12 mt-2">
    <div class="card">
        <div class="card-body">
            <div class="p-0">
                <div class="d-flex justify-content-center">
                    <h5 class="border bg-info rounded font-weight-bold fs-4 text-uppercase border-grey px-2 py-1">{{$orderData->client_name}} - {{$orderData->process_type}}</h5>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="d-flex">
                            <h5 class="font-weight-bold">LOB:</h5> <!-- Added a colon after "LOB" -->
                            <div style="margin-left: 10px;"> <!-- Added margin for space -->
                                <h5 class="border bg-light rounded font-weight-bold fs-3 text-uppercase  p-1 mt-1">{{ $orderData->lob_id ? ($lobList->where('id', $orderData->lob_id)->first()->name ?? '-') : '' }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
                <h6 class="font-weight-bold">Order Information :</h6>
                <div class="card shadow shadow-md rounded showdow-grey mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="font-weight-bold">Order No</div>
                                <div>{{ $orderData->order_id }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="font-weight-bold">Product</div>
                                <div>{{($orderData->process_name) ? $orderData->process_name : '-' }}</div>
                            </div>
                            <div class="col-md-3">
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
                            <div class="col-md-3">
                                <div class="font-weight-bold">Order Rec Date and Time</div>
                                <div>
                                    {{ $orderData->order_date ? (($formattedDate = date('m/d/Y H:i', strtotime($orderData->order_date))) !== false ? $formattedDate : '-') : '-' }}
                                </div>
 
                            </div>
                        </div>
                        <div class="row mt-1">
                            <div class="col-md-3">
                                <div class="font-weight-bold">Emp Id</div>
                                <div>{!! isset($orderData->assignee_user) ? trim(explode('(', $orderData->assignee_user)[0]) : '-' !!}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="font-weight-bold">Emp Name</div>
                                <div>{!! isset($orderData->assignee_user) ? trim(explode(')', explode('(', $orderData->assignee_user)[1])[0]) : '-' !!}</div>
                            </div>
                            <div class="col-md-3">
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
                            <div class="col-md-3">
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
                            <div class="col-md-3">
                                <div class="font-weight-bold">Municipality</div>
                                <select id="city" name="city" class="form-control select2dropdown" data-parsley-required="true">
                                    <option value="">Select Municipality</option>
                                    @foreach($cityList as $city)
                                        <option value="{{ $city->id }}" {{ ($orderData->city_id == $city->id) ? 'selected' : '' }}>
                                            {{ $city->city }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
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
                                    <td><a href="{{$countyInfo['TAX']['TAX_SITE']}}">{{$countyInfo['TAX']['TAX_SITE']}}</a></td>
                                    <td>{{$countyInfo['TAX']['TAX_USERNAME']}}</td>
                                    <td>{{$countyInfo['TAX']['TAX_PASSWORD']}}</td>
                                </tr>
                                <tr>
                                    <td>Court</td>
                                    <td><a href="{{$countyInfo['COURT']['COURT_SITE']}}">{{$countyInfo['COURT']['COURT_SITE']}}</a></td>
                                    <td>{{$countyInfo['COURT']['COURT_PASSWORD']}}</td>
                                    <td>{{$countyInfo['COURT']['COURT_USERNAME']}}</td>
                                </tr>
                                <tr>
                                    <td>Recorder</td>
                                    <td><a href="{{$countyInfo['RECORDER']['RECORDER_SITE']}}">{{$countyInfo['RECORDER']['RECORDER_SITE']}}</a></td>
                                    <td>{{$countyInfo['RECORDER']['RECORDER_USERNAME']}}</td>
                                    <td>{{$countyInfo['RECORDER']['RECORDER_PASSWORD']}}</td>
                                </tr>
                                <tr>
                                    <td>Assessor</td>
                                    <td><a href="{{$countyInfo['ASSESSOR']['ASSESSOR_SITE']}}">{{$countyInfo['ASSESSOR']['ASSESSOR_SITE']}}</a></td>
                                    <td>{{$countyInfo['ASSESSOR']['ASSESSOR_USERNAME']}}</td>
                                    <td>{{$countyInfo['ASSESSOR']['ASSESSOR_PASSWORD']}}</td>
                                </tr>
                                <tr>
                                    <td>Probate Court</td>
                                    <td><a href="{{$countyInfo['PROBATE_COURT']['PROBATE_LINK']}}">{{$countyInfo['PROBATE_COURT']['PROBATE_LINK']}}</a></td>
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
                                            <div class="font-weight-bold mb-1 mt-1">Primary Source :</div>
                                                <select id="primary_source" name="primary_source" class="form-control select2dropdown" data-parsley-required="true">
                                                    <option value="">Select Primary Source</option>
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
                                            <option value="1" @if($orderData->status_id == 1) selected @endif>WIP</option>
                                            <option value="2" @if($orderData->status_id == 2) selected @endif>Hold</option>
                                            <option value="3" @if($orderData->status_id == 3) selected @endif>Cancelled</option>
                                            <option value="4" @if($orderData->status_id == 4) selected @endif>Send for QC</option>
                                            <option value="5" @if($orderData->status_id == 5) selected @endif>Completed</option>
                                            <option value="13" @if($orderData->status_id == 13) selected @endif>Coversheet Prep</option>
                                            <option value="14" @if($orderData->status_id == 14) selected @endif>Clarification</option>
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
    </div>
</div>

<script>
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
            submit_type: type,
            _token: '{{ csrf_token() }}'
        };

        // Perform AJAX request
        $.ajax({
            url: '{{url("orderform_submit")}}',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {

            if (response.redirect) {
                if (response.redirect === 'orders') {
                    if(changeState || changeCounty) {
                        location.reload();
                    } else {
                        window.location.href = '{{ url("orders_status") }}';
                    }
                } else {
                    window.location.href = '{{ url("/coversheet-prep") }}/' + response.redirect;
                }
                }
                else if(response.success) {
                    Swal.fire({
                        title: "Success",
                        text: response.success,
                        icon: "success"
                    }).then((result) => {
                        if (result.value) {
                            location.reload();
                        }
                    });
                }
                else if(response.error) {
                    Swal.fire({
                        title: "Error",
                        text: response.error,
                        icon: "error"
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                Swal.fire({
                    title: "Error",
                    text: 'Error submitting order. Please try again later.',
                    icon: "error"
                });
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
        changeState = true;
        $('#ordersubmit').click();
    });

    $('#property_county').on('change', function () {
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

        // Initial check on page load
        updateSubmitButtonVisibility();

        // Event delegation for checkbox changes
        $('#checklist-container').on('change', '.checklist-item', function() {
            updateSubmitButtonVisibility();
        });
    });

</script>
@endsection

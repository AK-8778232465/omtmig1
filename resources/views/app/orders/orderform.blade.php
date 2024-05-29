@extends('layouts.app')
@section('title', config('app.name') . ' | Orders')
@section('content')
<div class="col-lg-12 mt-2">
    <div class="card">
        <div class="card-body">
            <div class="p-0">
                <div class="d-flex justify-content-center">
                    <h5 class="border bg-info rounded font-weight-bold fs-4 text-uppercase border-grey px-2 py-1">{{$orderData->process_name}}</h5>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="d-flex">
                            <h5 class="font-weight-bold">LOB:</h5> <!-- Added a colon after "LOB" -->
                            <div style="margin-left: 10px;"> <!-- Added margin for space -->
                                <h5 class="border bg-primary rounded font-weight-bold fs-4 text-uppercase border-grey">{{ $orderData->lob_id ? ($lobList->where('id', $orderData->lob_id)->first()->name ?? '') : '-' }}</h5>
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
                                <div class="font-weight-bold">Product Type</div>
                                <div>{{($orderData->product_name) ? $orderData->product_name : '-' }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="font-weight-bold">Tier :</div>
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
                                <div>{{ $orderData->order_date ? (($formattedDate = date('m/d/Y h:i A', strtotime($orderData->order_date))) !== false ? $formattedDate : '-') : '-' }}</div>
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
                    </div>
                </div>
                @if(!@empty($countyInfo))
                <h6 class="font-weight-bold">Source Information :</h6>
                <div class="card shadow shadow-md rounded showdow-grey mb-4">
                    <div class="card-body">
                        <div class="row mb-2 mx-2">
                            <div class="col-md-3">
                                <div class="font-weight-bold">Primary Source</div>
                                <div>{{$countyInfo['PRIMARY']['PRIMARY_SOURCE']}}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="font-weight-bold">Primary Image Source</div>
                                <div>{{$countyInfo['PRIMARY']['PRIMARY_IMAGE_SOURCE']}}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="font-weight-bold">Secondary Source</div>
                                <div>{{$countyInfo['SECONDARY']['SECONDARY_SOURCE']}}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="font-weight-bold">Secondary Image Source</div>
                                <div>{{$countyInfo['SECONDARY']['SECONDARY_IMAGE_SOURCE']}}</div>
                            </div>
                        </div>
                        <table id="source_datatable" class="table table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>Source</th>
                                    <th>Name</th>
                                    <th>Site Link</th>
                                    <th>Username</th>
                                    <th>Password</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Recorder</td>
                                    <td>{{$countyInfo['RECORDER']['RECORDER']}}</td>
                                    <td><a href="{{$countyInfo['RECORDER']['RECORDER_SITE']}}">{{$countyInfo['RECORDER']['RECORDER_SITE']}}</a></td>
                                    <td>{{$countyInfo['RECORDER']['RECORDER_USERNAME']}}</td>
                                    <td>{{$countyInfo['RECORDER']['RECORDER_PASSWORD']}}</td>
                                </tr>
                                <tr>
                                    <td>Court</td>
                                    <td>{{$countyInfo['COURT']['COURT']}}</td>
                                    <td><a href="{{$countyInfo['COURT']['COURT_SITE']}}">{{$countyInfo['COURT']['COURT_SITE']}}</a></td>
                                    <td>{{$countyInfo['COURT']['COURT_USERNAME']}}</td>
                                    <td>{{$countyInfo['COURT']['COURT_PASSWORD']}}</td>
                                </tr>
                                <tr>
                                    <td>Assessor</td>
                                    <td>{{$countyInfo['ASSESSOR']['ASSESSOR']}}</td>
                                    <td><a href="{{$countyInfo['ASSESSOR']['ASSESSOR_SITE']}}">{{$countyInfo['ASSESSOR']['ASSESSOR_SITE']}}</a></td>
                                    <td>{{$countyInfo['ASSESSOR']['ASSESSOR_USERNAME']}}</td>
                                    <td>{{$countyInfo['ASSESSOR']['ASSESSOR_PASSWORD']}}</td>
                                </tr>
                                <tr>
                                    <td>Tax</td>
                                    <td>{{$countyInfo['TAX']['TAX']}}</td>
                                    <td><a href="{{$countyInfo['TAX']['TAX_SITE']}}">{{$countyInfo['TAX']['TAX_SITE']}}</a></td>
                                    <td>{{$countyInfo['TAX']['TAX_USERNAME']}}</td>
                                    <td>{{$countyInfo['TAX']['TAX_PASSWORD']}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
                <h6 class="font-weight-bold">Order Submition :</h6>
                <div class="card shadow shadow-md rounded showdow-grey mb-4">
                    <div class="card-body">
                            <!-- <div class="d-flex justify-content-center"> -->
                                <!-- <div class="col-lg-4 col-xl-4">
                                    <div class="font-weight-bold">LOB :</div>
                                    <select name="lob_id" id="lob_id" class="form-control">
                                        <option value="">Select LOB</option>
                                        {{-- @foreach($lobData as $lob) --}}
                                        {{-- <option value="{{ $lob->id }}">{{ $lob->name }}</option> --}}
                                        {{-- @endforeach --}}
                                    </select>
                                </div>
                                <div class="col-lg-4 col-xl-4">
                                    <div class="font-weight-bold">Product :</div>
                                    <select name="product_id" class="form-control" id="product_id">
                                        <option value="">Select Product</option>
                                    </select>
                                </div>
                                <div class="col-lg-4 col-xl-4">
                                    <div class="font-weight-bold">Tier :</div>
                                        <select name="tier_id" id="tier_id" class="form-control">
                                            <option value="">Select Tier</option>
                                            <option value="1">Tier 1</option>
                                            <option value="2">Tier 2</option>
                                        </select>
                                    </div>
                                </div> -->

                            <!-- <div class="row mt-4 mb-4 m-4">
                                <div class="col-12 d-flex bg-danger justify-content-center" style="border-radius:14px;">
                                <input type="checkbox" name="checks[]" id="check_box_id" value="{{ isset($checklist_condition->state_id) ? htmlspecialchars($checklist_condition->state_id) : null }}">
                                     <label class="text-white font-weight-bold text-uppercase px-1 py-3" style="font-size: 14px !important;">{{ isset($checklist_condition[0]) ? htmlspecialchars($checklist_condition[0]->check_condition) : null }}</label>
                                </div>
                            </div> -->
                            @foreach($checklist_conditions as $checklist_condition)
                                <div class="row mt-4 mb-4 m-4">
                                    <div class="col-12 d-flex bg-danger justify-content-center" style="border-radius:14px;">
                                        <input type="checkbox" name="checks[]" id="check_{{ $checklist_condition->state_id }}" value="{{ $checklist_condition->state_id }}">
                                        <label class="text-white font-weight-bold text-uppercase px-1 py-3" style="font-size: 14px !important;">{{ $checklist_condition->check_condition }}</label>
                                    </div>
                                </div>
                            @endforeach
                            <div class="row mt-4 mb-4">
                                <!-- <div class="col-lg-4">
                                    <div class="font-weight-bold ml-2">Checklist :</div>
                                    @if(!@empty($checklist))
                                        {{-- <div class=""></div> --}}
                                        @foreach ($checklist as $check)
                                            <div class="row ml-4">
                                                <input class="mx-2" type="checkbox" name="checks[]" id="check_{{$check->id}}" value="{{$check->id}}">
                                                <span>{{$check->check_condition}}</span>
                                            </div>
                                        @endforeach
                                    @endif
                                </div> -->
                                <div class="col-lg-4">
                                    <div class="font-weight-bold">Comments :</div>
                                    <textarea name="order_comment" style="width: 100%" id="order_comment" cols="30" rows="4">{!! (isset($orderHistory) && isset($orderHistory->comment)) ? $orderHistory->comment : '' !!}</textarea>
                                </div>
                                <div class="col-lg-4">
                                    <div class="font-weight-bold">Status :</div>
                                        <select style="width:100%" class="form-control mx-2" name="order_status" id="order_status" @if(!isset($orderData->assignee_user)) disabled @endif>
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
                                <div class="d-flex justify-content-center my-4">
                                    <button class="btn btn-primary btn-sm mx-2" onclick="order_submition({{$orderData->id}})" type="submit">Submit</button>
                                    <button class="btn btn-info btn-sm mx-2" type="submit">Coversheet Prep & Submit</button>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>


document.addEventListener('DOMContentLoaded', function() {
        var orderStatus = document.getElementById('order_status');
        var currentStatus = {{ $orderData->status_id }};

        for (var i = 0; i < orderStatus.options.length; i++) {
            if (parseInt(orderStatus.options[i].value) === currentStatus) {
                orderStatus.options[i].disabled = true;
                break;
            }
        }
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

        $("#order_status,#lob_id,#product_id,#tier_id,#property_state,#property_county").select2();
    });

    function order_submition(orderId) {
        var checklistItems = [];
        $("input[name='checks[]']:checked").each(function() {
            checklistItems.push($(this).val());
        });
        var check_box_id = $("#check_box_id").val();
        var orderComment = $("#order_comment").val();
        var orderStatus = $("#order_status").val();
        var tierId = $("#tier_id").val();
        var productId = $("#product_id").val();
        var propertystate = $("#property_state").val();
        var propertycounty = $("#property_county").val();
        var data = {
            orderId: orderId,
            checklistItems: checklistItems.join(),
            check_box_id: check_box_id,
            orderComment: orderComment,
            orderStatus: orderStatus,
            stateId: propertystate,
            countyId: propertycounty,
            tierId: tierId,
            productId: productId,
            _token: '{{ csrf_token() }}'
        };

        // Perform AJAX request
        $.ajax({
            url: '{{url("orderform_submit")}}',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if(response.success) {
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

    $('#lob_id').on('change', function () {
        var getlob_id = $("#lob_id").val();
        $("#product_id").html('');
        $.ajax({
            url: "{{url('Product_dropdown')}}",
            type: "POST",
            data: {
                getlob_id: getlob_id,
                _token: '{{csrf_token()}}'
            },
            dataType: 'json',
            success: function (result) {
                $('#product_id').html('<option value="">Select Product</option>');
                $.each(result.product, function (key, value) {
                    $("#product_id").append('<option value="' + value
                        .id + '">' + value.product_name + '</option>');
                });
            }
        });
    });

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
    });

</script>
@endsection

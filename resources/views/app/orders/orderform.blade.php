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
                                <div>{!! isset($orderData->product_type) ? $orderData->product_type : '-' !!}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="font-weight-bold">Tier</div>
                                <div>{!! isset($orderData->tier) ? $orderData->tier : '-' !!}</div>
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
                                <div>{{ $orderData->short_code }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="font-weight-bold">County</div>
                                <div>{{ $orderData->county_name }}</div>
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
                        <div class="d-flex justify-content-center">
                            <div class="col-lg-5">
                                <div class="font-weight-bold">Checklist :</div>
                                @if(!@empty($checklist))
                                    {{-- <div class=""></div> --}}
                                    @foreach ($checklist as $check)
                                        <div class="row ml-4">
                                            <input class="mx-2" type="checkbox" name="checks[]" id="check_{{$check->id}}" value="{{$check->id}}">
                                            <span>{{$check->check_condition}}</span>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <div class="col-lg-4">
                                <div class="font-weight-bold">Comments :</div>
                                <textarea name="order_comment" style="width: 100%" id="order_comment" cols="30" rows="4">{!! (isset($orderHistory) && isset($orderHistory->comment)) ? $orderHistory->comment : '' !!}</textarea>
                            </div>
                            <div class="col-lg-3">
                                <div class="font-weight-bold">Status :</div>
                                <select style="width:100%" class="form-control mx-2" name="order_status" id="order_status">
                                    <option value="1" @if($orderData->status_id == 1) selected @endif>WIP</option>
                                    <option value="2" @if($orderData->status_id == 2) selected @endif>Hold</option>
                                    <option value="3" @if($orderData->status_id == 3) selected @endif>Cancelled</option>
                                    <option value="4" @if($orderData->status_id == 4) selected @endif>Send for QC</option>
                                    <option value="5" @if($orderData->status_id == 5) selected @endif>Completed</option>
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

<script>
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

        $("#order_status").select2();
    });

    function order_submition(orderId) {
        var checklistItems = [];
        $("input[name='checks[]']:checked").each(function() {
            checklistItems.push($(this).val());
        });
        var orderComment = $("#order_comment").val();
        var orderStatus = $("#order_status").val();

        var data = {
            orderId: orderId,
            checklistItems: checklistItems.join(),
            orderComment: orderComment,
            orderStatus: orderStatus,
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
</script>
@endsection

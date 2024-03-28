@extends('layouts.app')
@section('title', config('app.name') . ' | Order Creation')
@section('content')
<style>
    /* Loader */
    .content-loaded{
        display: none;
    }
    .frame {
      position: fixed;
      top: 50%;
      left: 50%;
      width: 400px;
      height: 400px;
      margin-top: -200px;
      margin-left: -200px;
      border-radius: 2px;
      /* background: #ffffff; */
      color: #fff;
    }

    .center {
      position: absolute;
      width: 220px;
      height: 220px;
      top: 90px;
      left: 90px;
    }

    .dot-1 {
      position: absolute;
      z-index: 3;
      width: 30px;
      height: 30px;
      top: 95px;
      left: 95px;
      background: #fff;
      border-radius: 50%;
      -webkit-animation-fill-mode: both;
              animation-fill-mode: both;
      -webkit-animation: jump-jump-1 2s cubic-bezier(0.21, 0.98, 0.6, 0.99) infinite alternate;
              animation: jump-jump-1 2s cubic-bezier(0.21, 0.98, 0.6, 0.99) infinite alternate;
    }

    .dot-2 {
      position: absolute;
      z-index: 2;
      width: 60px;
      height: 60px;
      top: 80px;
      left: 80px;
      background: #fff;
      border-radius: 50%;
      -webkit-animation-fill-mode: both;
              animation-fill-mode: both;
      -webkit-animation: jump-jump-2 2s cubic-bezier(0.21, 0.98, 0.6, 0.99) infinite alternate;
              animation: jump-jump-2 2s cubic-bezier(0.21, 0.98, 0.6, 0.99) infinite alternate;
    }

    .dot-3 {
      position: absolute;
      z-index: 1;
      width: 90px;
      height: 90px;
      top: 65px;
      left: 65px;
      background: #fff;
      border-radius: 50%;
      -webkit-animation-fill-mode: both;
              animation-fill-mode: both;
      -webkit-animation: jump-jump-3 2s cubic-bezier(0.21, 0.98, 0.6, 0.99) infinite alternate;
              animation: jump-jump-3 2s cubic-bezier(0.21, 0.98, 0.6, 0.99) infinite alternate;
    }

    @-webkit-keyframes jump-jump-1 {
      0%, 70% {
        box-shadow: 2px 2px 3px 2px rgba(0, 0, 0, 0.2);
        -webkit-transform: scale(0);
                transform: scale(0);
      }
      100% {
        box-shadow: 10px 10px 15px 0 rgba(0, 0, 0, 0.3);
        -webkit-transform: scale(1);
                transform: scale(1);
      }
    }

    @keyframes jump-jump-1 {
      0%, 70% {
        box-shadow: 2px 2px 3px 2px rgba(0, 0, 0, 0.2);
        -webkit-transform: scale(0);
                transform: scale(0);
      }
      100% {
        box-shadow: 10px 10px 15px 0 rgba(0, 0, 0, 0.3);
        -webkit-transform: scale(1);
                transform: scale(1);
      }
    }
    @-webkit-keyframes jump-jump-2 {
      0%, 40% {
        box-shadow: 2px 2px 3px 2px rgba(0, 0, 0, 0.2);
        -webkit-transform: scale(0);
                transform: scale(0);
      }
      100% {
        box-shadow: 10px 10px 15px 0 rgba(0, 0, 0, 0.3);
        -webkit-transform: scale(1);
                transform: scale(1);
      }
    }
    @keyframes jump-jump-2 {
      0%, 40% {
        box-shadow: 2px 2px 3px 2px rgba(0, 0, 0, 0.2);
        -webkit-transform: scale(0);
                transform: scale(0);
      }
      100% {
        box-shadow: 10px 10px 15px 0 rgba(0, 0, 0, 0.3);
        -webkit-transform: scale(1);
                transform: scale(1);
      }
    }
    @-webkit-keyframes jump-jump-3 {
      0%, 10% {
        box-shadow: 2px 2px 3px 2px rgba(0, 0, 0, 0.2);
        -webkit-transform: scale(0);
                transform: scale(0);
      }
      100% {
        box-shadow: 10px 10px 15px 0 rgba(0, 0, 0, 0.3);
        -webkit-transform: scale(1);
                transform: scale(1);
      }
    }
    @keyframes jump-jump-3 {
      0%, 10% {
        box-shadow: 2px 2px 3px 2px rgba(0, 0, 0, 0.2);
        -webkit-transform: scale(0);
                transform: scale(0);
      }
      100% {
        box-shadow: 10px 10px 15px 0 rgba(0, 0, 0, 0.3);
        -webkit-transform: scale(1);
                transform: scale(1);
      }
    }
</style>
<div class="container-fluid mt-2">
    <div class="frame d-flex align-items-center justify-content-center" style="height: 100%; width: 100%;">
        <div class="center">
            <div class="dot-1"></div>
            <div class="dot-2"></div>
            <div class="dot-3"></div>
        </div>
    </div>
    <div class="col-md-12 pl-1 mt-2 p-3 content-loaded">
        <form id="orderInputForm" enctype="multipart/form-data" data-parsley-validate name="orderInputForm">
            @csrf
            <div class="card">
                <div class="card-body rounded shadow-sm " style="border-top:3px solid #0e7c31">
                    <div class="form-group row mb-4 pb-0 pl-3 pr-3 mt-3">
                        <div class="form-group col-lg-3 mb-0 pb-0">
                            <label class="font-weight-bold">Order ID<span style="color:red;">*</span></label>
                            <input type="text" id="order_id" name="order_id" class="form-control" placeholder="Enter Order ID" required data-parsley-pattern="^\d+$" data-parsley-error-message="Only numbers allowed" data-parsley-trigger="focusout keyup" maxlength="20"></input>
                        </div>
                        <div class="form-group col-lg-3 mb-0 pb-0">
                            <label for="order_date" class="font-weight-bold">Order Received Date<span style="color:red;">*</span></label>
                            <br>
                            <input type="date" id="order_date" class="form-control" name="order_date">
                        </div>
                        <div class="form-group col-lg-3 mb-0 pb-0">
                            <label class="font-weight-bold">Project Code<span style="color:red;">*</span></label><br>
                            <select class="form-control select2dropdown" style="width:100%" name="process_code" id="process_code" aria-hidden="true" required>
                                <option selected="" disabled="" value="">Select Project Code</option>
                                @foreach ($processList as $process)
                                    <option value="{{ $process->id }}">{!! $process->project_code.' ('.$process->process_name.')' !!}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-3 mb-0 pb-0">
                            <label class="font-weight-bold">State</label><br>
                            <select class="form-control select2dropdown" style="width:100%" name="property_state" id="property_state" aria-hidden="true">
                            <option selected="" disabled="" value="">Select State</option>
                            @foreach ($stateList as $state)
                                    <option value="{{ $state->id }}">{{ $state->short_code }}</option>
                            @endforeach
                        </select>
                        </div>
                    </div>
                    <div class="form-group row mb-4 pb-0 pl-3 pr-3">
                        <div class="form-group col-lg-3 mb-0 pb-0">
                            <label class="font-weight-bold">County</label>
                            <select id="property_county" name="property_county" type="text" class="form-control select2dropdown" style="width:100%" autocomplete="off" placeholder="Enter Property County"  data-parsley-trigger="focusout" data-parsley-trigger="keyup">
                                <option selected="" disabled="" value="">Select County</option>
                            </select>
                        </div>
                        <div class="form-group col-lg-3 mb-0 pb-0">
                            <label class="font-weight-bold">Status<span style="color:red;">*</span></label>
                            <select id="order_status" name="order_status" type="text" class="form-control" autocomplete="off" placeholder="Enter Status"  data-parsley-trigger="focusout" data-parsley-trigger="keyup">
                                <option selected="" disabled="" value="">Select Status</option>
                                @foreach ($statusList as $status)
                                @if($status->id == 1)
                                    <option value="{{ $status->id }}">{{ $status->status }}</option>
                                @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-3 mb-0 pb-0">
                            <label class="font-weight-bold">Assign User</label>
                            <select id="assignee_user" name="assignee_user" type="text" class="form-control select2dropdown" style="width:100%" autocomplete="off" placeholder="Enter Status"  data-parsley-trigger="focusout" data-parsley-trigger="keyup">
                                <option selected disabled value="">Select Assignee</option>
                                @foreach ($processors as $processor)
                                    <option value="{{ $processor->id }}">{{ $processor->emp_id. " (" .$processor->username. ")"  }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-3 mb-0 pb-0">
                            <label class="font-weight-bold">Assign QA</label>
                            <select id="assignee_qa" name="assignee_qa" type="text" class="form-control select2dropdown" style="width:100%" autocomplete="off" placeholder="Enter Status"  data-parsley-trigger="focusout" data-parsley-trigger="keyup">
                                <option selected="" disabled="" value="">Select QA</option>
                                @foreach ($qcers as $qcer)
                                    <option value="{{ $qcer->id }}">{{ $qcer->emp_id. " (" .$qcer->username. ")"  }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="text-center form-group row mb-3 pb-2">
                        <div class="form-group col-lg-12 mb-0 pb-0">
                            <button type="submit" class="btn  btn-sm btn-primary" name="submit" id="submit"
                            onclick="createOrder()">Create Order</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div class="card-body rounded shadow" style="border-top: 3px solid #0e7c31">
            <form id="excelImport" class="p-2" method="post" enctype="multipart/form-data">
                @csrf
                <div class="form-group row mb-0 pb-0 pl-3 pr-3 align-items-center" style="flex-direction: column;">
                    <div class="form-group col-lg-6 mb-2 pb-0">

                        <input type="file" id="file" name="file"  data-height="75" height="100px" class="form-controlfile suppdoc dropify" accept=".csv,.xlsx,.ods">
                    </div>

                    <div class="form-group col-lg-6 mb-2 mt-2 pb-0 text-center" style="margin-top: auto;">
                    <a class="btn btn-sm btn-info mx-2" href="{{asset('/template/sample_template.xlsx')}}" ><i class="fas fa-download"></i> Sample Format</a>
                        <button type="submit" class="btn btn-sm btn-primary" maxlength="100">
                            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="13" height="14" x="0" y="0" viewBox="0 0 459.904 459.904" style="enable-background:new 0 0 512 512" xml:space="preserve" class="">
                                <g>
                                    <path d="M123.465 168.28h46.543v138.07c0 14.008 11.358 25.352 25.352 25.352h69.2c13.993 0 25.352-11.343 25.352-25.352V168.28h46.527c7.708 0 14.637-4.641 17.601-11.764 2.933-7.094 1.301-15.295-4.145-20.741L243.413 29.28c-7.437-7.422-19.485-7.422-26.938 0L110.011 135.775a19.023 19.023 0 0 0-4.13 20.741c2.962 7.109 9.876 11.764 17.584 11.764z" fill="#ffffff" opacity="1" data-original="#ffffff" class=""></path>
                                    <path d="M437.036 220.029c-12.617 0-22.852 10.237-22.852 22.867v95.615c0 28.643-23.317 51.944-51.961 51.944H97.679c-28.644 0-51.945-23.301-51.945-51.944v-95.615c0-12.63-10.251-22.867-22.867-22.867C10.236 220.029 0 230.266 0 242.897v95.615c0 53.859 43.818 97.679 97.679 97.679h264.544c53.861 0 97.681-43.819 97.681-97.679v-95.615c0-12.631-10.237-22.868-22.868-22.868z" fill="#ffffff" opacity="1" data-original="#ffffff" class=""></path>
                                </g>
                            </svg> Upload File
                        </button>
                        <div class="form-group col-lg-6 mb-2 mt-2 pb-0 text-center" style="margin-top: auto;">
                    </div>
                    </div>
                </div>
                <div id="progressId" class="d-none d-flex justify-content-center align-items-center">
                    <div style="width: 60%" class="progress">
                        <div id="progress-bar"
                             class="progress-bar progress-bar-striped progress-bar-animated"
                             role="progressbar"
                             aria-valuenow="1"
                             aria-valuemin="0"
                             aria-valuemax="100"
                             style="width: 0%">
                            0%
                        </div>
                    </div>
                </div>
            </form>

            <table id="datatable" class="table table-striped table-bordered p-2">
                <thead>
                <tr>
                    <th>S.NO</th>
                    <th>File Name</th>
                    <th>Total</th>
                    <th>Completed</th>
                    <th>Failed</th>
                    <th>Uploaded At</th>
                    <th>Uploaded By</th>
                </tr>
                </thead>
                <tbody>
                    @foreach($exceldetail as $detail)
                        <tr>
                            <td>{{ $loop->iteration}}</td>
                            <td>{{ $detail->file_name}}</td>
                            <td>{{ $detail->total_rows}}</td>
                            <td>{{ $detail->successfull_rows}}</td>
                            <td style="font-weight:600">
                                @if ($detail->unsuccessfull_rows > 0 && $detail->unsuccessfull_rows != "")
                                    <a href="{{ route('exportFailedOrders', ['id' => $detail->id]) }}">
                                        {{ $detail->unsuccessfull_rows }}
                                        <i class="fas fa-download"></i>
                                    </a>
                                @endif
                            </td>
                            <td>{{ date('m/d/Y H:i:s', strtotime($detail->created_at)) }}</td>
                            <td>{!! optional($detail->users)->emp_id . " (" . optional($detail->users)->username. ")" !!}</td>
                        </tr>
                        @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Js --}}
<script type="text/javascript">

    $(function() {
        @if (Session::has('success'))
            new PNotify({
                title: 'Success',
                delay: 500,
                text: "{{ Session::get('success') }}",
                type: 'success'
            });
        @endif
        @if ($errors->any())
            var err = "";
            @foreach ($errors->all() as $error)

                new PNotify({
                    title: 'Error',
                    text: "{{ $error }}",
                    delay: 800,
                    type: 'error'
                });
            @endforeach
        @endif
    });

    function createOrder() {
        if ($("#orderInputForm").parsley()) {
            if ($("#orderInputForm").parsley().validate()) {
                event.preventDefault();
                if ($("#orderInputForm").parsley().isValid()) {
                    $.ajax({
                        type: "POST",
                        cache: false,
                        async: false,
                        url: "{{ url('InsertOrder') }}",
                        data: new FormData($("#orderInputForm")[0]),
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.msg == "Order Created Successfully!") {

                                new PNotify({
                                    title: 'Success',
                                    text: response.msg,
                                    type: 'success'
                                });
                                setTimeout(function() {
                                    location.reload();
                                }, 1000);
                            }else if(response.msg == "Order already exists."){
                                new PNotify({
                                    title: 'Error',
                                    text: response.msg,

                                });
                            }
                        },
                        error: function(response) {

                            var err = "";
                            $.each(response.responseJSON.errors, function(field_name, error) {
                                err = err + error;
                            });
                            new PNotify({
                                title: 'Error',
                                text: err,
                                type: 'error',
                                delay: 1000
                            });
                        }
                    });
                }
            }
        }
    }

    $(document).ready(function() {
        $('.select2dropdown').select2();
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

    $(document).ready(function() {
        var form = document.getElementById('orderInputForm');

        // Set autocomplete attribute for input fields
        var inputFields = form.querySelectorAll('input');
        inputFields.forEach(function (input) {
            input.setAttribute('autocomplete', 'off');
        });

        // Set autocomplete attribute for textarea fields
        var textareaFields = form.querySelectorAll('textarea');
        textareaFields.forEach(function (textarea) {
            textarea.setAttribute('autocomplete', 'off');
        });
    });

    $(document).ready(function() {
        $('.content-loaded').show();
        $('.frame').addClass('d-none');
    });

    $('#excelImport').on('submit', function(event){
            event.preventDefault();
            $('.content-loaded').hide();
            $('.frame').removeClass('d-none');
            if($('#excelImport').parsley().isValid()){
                $.ajax({
                    type: "POST",
                    url: "{{ route('OrderCreationsImport') }}",
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        $('.content-loaded').show();
                        $('.frame').addClass('d-none');
                        // Swal.fire({
                        //     text: "File Uploaded Successfully",
                        //     icon: "success",
                        //     confirmButtonText: "OK"
                        // }).then((result) => {
                        //     if (result.value) {
                        //         location.reload();
                        //     }
                        // });
                        console.log(response.bacthId);
                        if(response.bacthId != undefined) {
                            getUploadProgress(response.bacthId);
                            $('#progressId').removeClass('d-none');
                        }
                    },
                    error: function (response) {
                        $('#progressId').addClass('d-none');
                        $('.content-loaded').show();
                         $('.frame').addClass('d-none');
                        Swal.fire({
                            text: "File Upload Failed",
                            icon: "error",
                            confirmButtonText: "OK"
                        });
                    }
                });
            }
        });

        function getUploadProgress(batchId) {
            let progressBar = $("#progress-bar");

            let progressResponse = setInterval(() => {
                $.ajax({
                    url: '/progressBar',
                    method: 'GET',
                    data: { id: batchId },
                    success: function (response) {
                        let totalJobs = parseInt(response.total_rows);
                        let completedJobs = parseInt(response.successfull_rows);
                        let pendingJobs = totalJobs - completedJobs;


                        let progressPercentage = pendingJobs === 0 ? 100 : parseInt((completedJobs / totalJobs) * 100);
                        progressBar.css("width", progressPercentage + "%");
                        progressBar.text(progressPercentage + "%");

                        if (progressPercentage >= 100) {
                            clearInterval(progressResponse);
                        }
                    },
                    error: function (error) {
                        console.error(error);
                    }
                });
            }, 1000);
        }
</script>

@endsection

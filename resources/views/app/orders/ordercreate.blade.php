@extends('layouts.app')
@section('title', config('app.name') . ' | Order Creation')
@section('content')
<style>
        #filename-display {
            margin-top: 10px;
            font-size: 14px; /* Adjust the font size as needed */
            color: #333; /* Change to the desired color */
            font-weight: bold; /* Makes the text bold */
            background-color: #f9f9f9; /* Light grey background */
            padding: 5px 10px; /* Adds some padding */
            border-radius: 5px; /* Rounds the corners */
            border: 1px solid #ddd; /* Adds a light border */
            display: inline-block; /* Makes the element fit the content */
        }

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
                            <input type="text" id="order_id" name="order_id" class="form-control" placeholder="Enter Order ID" required
                            data-parsley-error-message="Order ID should not be empty."
                            data-parsley-trigger="focusout keyup"
                            maxlength="20">
                        </div>
                       {{--new changes--}}
                        <div class="form-group col-lg-3 mb-0 pb-0">
                            <label for="order_date" class="font-weight-bold">Order Received Date and Time<span style="color:red;">*</span></label>
                            <br>
                            <div class="input-container">
                            <input type="datetime-local" id="order_date" class="form-control" step="1" name="order_date" required data-parsley-trigger="focusout keyup" data-parsley-error-message="Order Received Date and Time should not be empty" format="MM-DD-YYYY THH:mm" hour24="true">
                            </div>
                        </div>
                        <div class="form-group col-lg-3 mb-0 pb-0">
                            <label class="font-weight-bold">Product<span style="color:red;">*</span></label><br>
                            <select class="form-control select2dropdown" style="width:100%" name="process_code" id="process_code" aria-hidden="true" data-parsley-trigger="focusout keyup"
                            data-parsley-error-message="Product Code should not be empty" data-parsley-errors-container="#process_code_error" required>
                                <option selected="" disabled="" value="">Select Product</option>
                                @foreach ($processList as $process)
                                <option value="{{ $process->id }}" data-client-id="{{ $process->client_id }}">
                                    {!! $process->project_code.' ('.$process->process_name.')' !!}</option>
                                @endforeach
                            </select>
                            <div id="process_code_error" class="parsley-error"></div>
                        </div>
                        <div class="form-group col-lg-3 mb-0 pb-0">
                                <label class="font-weight-bold">Lob</label>
                                <select id="lob_id" name="lob_id" type="text" class="form-control select2dropdown" style="width:100%" autocomplete="off" placeholder="Select Lob"  data-parsley-trigger="focusout" data-parsley-trigger="keyup">
                                    <option selected="" disabled="" value="">Select lob</option>
                                </select>
                        </div>
                    </div>
                    <div class="form-group row mb-4 pb-0 pl-3 pr-3">
                         <div class="form-group col-lg-3 mb-0 pb-0">
                            <label class="font-weight-bold">State Code</label><br>
                            <select class="form-control select2dropdown" style="width:100%" name="property_state" id="property_state" aria-hidden="true">
                            <option selected="" disabled="" value="">Select State Code</option>
                            @foreach ($stateList as $state)
                                    <option value="{{ $state->id }}">{{ $state->short_code }}</option>
                            @endforeach
                        </select>
                        </div>
                        <div class="form-group col-lg-3 mb-0 pb-0">
                            <label class="font-weight-bold">County</label>
                            <select id="property_county" name="property_county" type="text" class="form-control select2dropdown" style="width:100%" autocomplete="off" placeholder="Enter Property County"  data-parsley-trigger="focusout" data-parsley-trigger="keyup">
                                <option selected="" disabled="" value="">Select County</option>
                            </select>
                        </div>
                        <div class="form-group col-lg-3 mb-0 pb-0" id= "municipality-container">
                            <label class="font-weight-bold">Municipality</label>
                            <select id="city" name="city" class="form-control select2dropdown" style="width:100%" autocomplete="off"  data-parsley-trigger="focusout keyup">
                                <option selected="" disabled="" value="">Select Municipality</option>
                            </select>
                        </div>
                        <div class="form-group col-lg-3 mb-0 pb-0">
                            <label class="font-weight-bold">Status<span style="color:red;">*</span></label>
                            <select id="order_status" name="order_status" required type="text" class="form-control" autocomplete="off" placeholder="Enter Status"  data-parsley-trigger="focusout" data-parsley-trigger="keyup" data-parsley-error-message="Status should not be Empty ">
                                <option selected="" disabled="" value="">Select Status</option>
                                @foreach ($statusList as $status)
                                @if($status->id == 1)
                                    <option value="{{ $status->id }}">{{ $status->status }}</option>
                                @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row mb-4 pb-0 pl-3 pr-3">
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
                            <div class="form-group col-lg-3 mb-0 pb-0" id= "tier-container">
                            <label class="font-weight-bold">Tier</label>
                            <select id="tier_id" name="tier_id" type="text" class="form-control select2dropdown" style="width:100%" autocomplete="off" placeholder="Select Tier"  data-parsley-trigger="focusout" data-parsley-trigger="keyup">
                                <option selected="" disabled="" value="">Select Tier</option>
                                @foreach ($tierList as $tier)
                                    <option value="{{ $tier->id }}">{{ $tier->Tier_id }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-3 mb-0 pb-0" id= "typist-container">
                            <label class="font-weight-bold">Typist</label>
                            <select id="typist_id" name="typist_id" type="text" class="form-control select2dropdown" style="width:100%" autocomplete="off" placeholder="Select Typist"  data-parsley-trigger="focusout" data-parsley-trigger="keyup">
                                <option selected="" disabled="" value="">Select Typist</option>
                                @foreach ($typists as $typist)
                                    <option value="{{ $typist->id }}">{{ $typist->username }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                <div class="form-group row mb-4 pb-0 pl-3 pr-3" id= "typist-qc-container">
                    <div class="form-group col-lg-3 mb-0 pb-0">
                        <label class="font-weight-bold">Typist QC</label>
                        <select id="typist_qc_id" name="typist_qc_id" type="text" class="form-control select2dropdown" style="width:100%" autocomplete="off" placeholder="Select Typist QC"  data-parsley-trigger="focusout" data-parsley-trigger="keyup">
                            <option selected="" disabled="" value="">Select Typist QC</option>
                            @foreach ($typist_qcs as $typist_qc)
                                <option value="{{ $typist_qc->id }}">{{ $typist_qc->username }}</option>
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
                        <p id="filename-display" style="margin-top: 10px;"></p> <!-- Element to display the filename -->
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
                    @php $j = 1 @endphp
                    @foreach($exceldetail as $detail)
                    @if(!empty($detail->unsuccessfull_rows) || !empty($detail->successfull_rows))
                        <tr>
                            <td>{{ $j++ }}</td>
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
                    @endif
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


    // Event listener for copy action to format date string
    document.getElementById('order_date').addEventListener('copy', function(event) {
        var dateValue = this.value;

        if (dateValue) {
            var date = new Date(dateValue);

            // Extract date and time components
            var month = ('0' + (date.getMonth() + 1)).slice(-2);
            var day = ('0' + date.getDate()).slice(-2);
            var year = date.getFullYear();
            var hours24 = date.getHours();
            var minutes = ('0' + date.getMinutes()).slice(-2);
            var seconds = ('0' + date.getSeconds()).slice(-2);

            // Convert to 12-hour format
            var ampm = hours24 >= 12 ? 'PM' : 'AM';
            var hours12 = hours24 % 12;
            hours12 = hours12 ? hours12 : 12; // the hour '0' should be '12'
            hours12 = ('0' + hours12).slice(-2);

            // Construct the formatted date string
            var formattedDate = `${month}-${day}-${year} ${hours12}:${minutes}:${seconds} ${ampm}`;

            // Set the clipboard data
            event.clipboardData.setData('text/plain', formattedDate);
            event.preventDefault(); // Prevent the default copy action
        }
    });

    // Event listener for paste action to validate the format
    document.getElementById('order_date').addEventListener('paste', function(event) {
        // Prevent the default paste action
        event.preventDefault();

        // Get the pasted data
        var pastedData = event.clipboardData.getData('text').trim();
        
        // Check if the pasted data matches the expected format
        if (isValidFormat(pastedData)) {
            var parsedDate = parseDate(pastedData);

            if (parsedDate) {
                var currentDate = new Date();
                var pastedDateObject = new Date(parsedDate + ':00'); // Append seconds for full date-time

                // Check if the pasted date is in the future
                if (pastedDateObject > currentDate) {
                    alert('The pasted date is in the future date.');
                } else {
                    // Set the formatted date to the input field
                    this.value = parsedDate;
                }
            } else {
                alert('Error parsing date. Please use MM-DD-YYYY HH:MM:SS AM/PM format.');
            }
        } else {
            alert('Invalid format. Please use MM-DD-YYYY HH:MM:SS AM/PM format.');
        }
    });

    // Function to parse and format the pasted date string
    function parseDate(dateString) {
        // Example input format: "08-15-2024 12:11:37 PM"
        var datePattern = /^(\d{2})-(\d{2})-(\d{4}) (\d{2}):(\d{2}):(\d{2}) (AM|PM)$/;
        var match = dateString.match(datePattern);

        if (match) {
            var month = match[1];
            var day = match[2];
            var year = match[3];
            var hours = parseInt(match[4], 10);
            var minutes = match[5];
            var seconds = match[6];
            var ampm = match[7];
            
            // Convert 12-hour format to 24-hour format
            if (ampm === 'PM' && hours !== 12) hours += 12;
            if (ampm === 'AM' && hours === 12) hours = 0;

            // Pad single digit hours and minutes
            hours = ('0' + hours).slice(-2);
            minutes = ('0' + minutes).slice(-2);
            seconds = ('0' + seconds).slice(-2);

            // Return the formatted string for the datetime-local input
            return `${year}-${month}-${day}T${hours}:${minutes}`;
        }
        return null;
    }

    // Function to check if the date string matches the expected format
    function isValidFormat(dateString) {
        // Expected format: "MM-DD-YYYY HH:MM:SS AM/PM"
        var datePattern = /^\d{2}-\d{2}-\d{4} \d{2}:\d{2}:\d{2} (AM|PM)$/;
        var isValid = datePattern.test(dateString);

        if (!isValid) {
            return false; // Invalid format if it doesn't match the pattern
        }

        // Additional check for valid month and day
        var parts = dateString.split(' ');
        var dateParts = parts[0].split('-');
        var month = parseInt(dateParts[0], 10);
        var day = parseInt(dateParts[1], 10);

        // Validate month (1-12) and day (1-31)
        if (month < 1 || month > 12 || day < 1 || day > 31) {
            return false;
        }

        return true;
    }
// const orderDateInput = document.getElementById('order_date');

document.getElementById('order_date').addEventListener('change', function() {
  const selectedDate = new Date(this.value);
  const currentDate = new Date();
  if (selectedDate > currentDate) {
    alert('Future date cannot be selected.');
    this.value = '';
  }
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

$('#property_county').on('change', function () {
    var county_id = $(this).val();
    $("#city").html(''); // Clear previous options

    if (county_id) {
        $.ajax({
            url: "{{ route('getCities') }}",
            type: "POST",
            data: {
                county_id: county_id,
                _token: '{{ csrf_token() }}'
            },
            dataType: 'json',
            success: function (result) {
                if (result.length > 0 && result[0].id !== null) {
                    $('#city').html('<option value="">Select City</option>');
                    $.each(result, function (key, value) {
                        $("#city").append('<option value="' + value.id + '">' + value.city + '</option>');
                    });
                } else {
                    $('#city').html('<option value="">No Cities Found</option>');
                }
            },
            error: function (xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    } else {
        $('#city').html('<option value="">Select City</option>');
    }
});



///
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
        if ($('#excelImport').parsley().isValid()) {
            $.ajax({
                type: "POST",
                url: "{{ route('OrderCreationsImport') }}",
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function (response) {
                    $('.content-loaded').show();
                    $('.frame').addClass('d-none');
                    if (response.bacthId !== undefined) {
                        getUploadProgress(response.bacthId);
                        $('#progressId').removeClass('d-none');
                    } else {
                        location.reload(); // Reload if no batchId is returned
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
                    }).then(() => {
                        location.reload();
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
                let progressPercentage = totalJobs === 0 ? 100 : parseInt((completedJobs / totalJobs) * 100);

                progressBar.css("width", progressPercentage + "%");
                progressBar.text(progressPercentage + "%");


                if (progressPercentage === 100) {
                    clearInterval(progressResponse);
                    Swal.fire({
                        title: 'Upload Complete',
                        text: 'All orders have been successfully uploaded.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        console.log("Reloading...");
                        window.location.reload();
                    });
                } else {

                    clearInterval(progressResponse);
                    setTimeout(() => {
                        Swal.fire({
                            title: 'Upload Incomplete',
                            text: 'Some orders have failed to upload.',
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            console.log("Reloading...");
                            window.location.reload();
                        });
                    }, 3000);
                }
            },
            error: function (error) {
                console.error(error);
                clearInterval(progressResponse);
                window.location.reload();
            }
        });
    }, 1000);
}

$('#process_code').on('change', function () {
    var process_id = $("#process_code").val();
    $("#lob_id").html('');
    $.ajax({
        url: "{{ url('getlob') }}",
        type: "POST",
        data: {
            process_id: process_id,
            _token: '{{ csrf_token() }}'
        },
        dataType: 'json',
        success: function (response) {
            $('#lob_id').html('<option value="">Select LOB</option>');
            $.each(response, function (key, value) {
                $("#lob_id").append('<option value="' + value.id + '">' + value.name + '</option>');
            });
        }
    });
});


$('#lob_id').on('change', function () {
    var lob_id = $("#lob_id").val();
    $("#product_id").html('');
    $.ajax({
        url: "{{ url('getproduct') }}",
        type: "POST",
        data: {
            lob_id: lob_id,
            _token: '{{ csrf_token() }}'
        },
        dataType: 'json',
        success: function (response) {
            $('#product_id').html('<option value="">Select Product</option>');
            $.each(response, function (key, value) {
                $("#product_id").append('<option value="' + value.id + '">' + value.product_name + '</option>');
            });
        }
    });
});

// Js Dropify
$(document).ready(function() {
        // Initialize Dropify
        $('.dropify').dropify();
        // Event listener for file change
        $('.dropify').on('change', function(event) {
            var input = event.target;
            if (input.files && input.files[0]) {
                var fileName = input.files[0].name;
            $('#filename-display').text('Selected file: ' + fileName).show(); // Show the element and set text
            } else {
            $('#filename-display').text('').hide(); // Clear text and hide the element
            }
        });

        // Event listener for Dropify's events (for example, file clear event)
        var drEvent = $('.dropify').dropify();

        drEvent.on('dropify.afterClear', function(event, element){
        $('#filename-display').text('').hide(); // Clear text and hide the element
    });

    // Hide initially if it's empty
    if ($('#filename-display').text().trim() === '') {
        $('#filename-display').hide();
    }
});


$(document).ready(function() {
    $('#process_code').change(function() {
        var selectedOption = $(this).find('option:selected');
        var clientId = selectedOption.data('client-id');
console.log(clientId);
        // Check if the client_id is 16
        if (clientId == 16) {
            $('#typist-container').hide();
            $('#typist-qc-container').hide();
        } else {
            $('#typist-container').show();
            $('#typist-qc-container').show();
        }

        if(clientId == 82){
            $('#municipality-container').hide();
            $('#tier-container').hide();
        }else{
            $('#municipality-container').show();
            $('#tier-container').show();
        }
    });

    // Trigger change event on page load in case there's a pre-selected option
    $('#process_code').trigger('change');
});


</script>

@endsection

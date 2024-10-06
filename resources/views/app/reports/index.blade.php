@extends('layouts.app')
@section('title', config('app.name') . ' | Reports')
@php
    $currentDate = \Carbon\Carbon::now()->toDateString();
    @endphp
@section('content')
<style>

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .left-menu {
        top: 10%;
        height: 100%;
        padding: 20px;
        }
    .reports{
        padding-top: 1rem;
    }


    .left-menu h6 {
        font-size: 18px;
        font-weight: bold;
        color: #333333;
        border-bottom: 1px solid #e0e0e0;
        margin-bottom: 10px;
    }

    .left-menu ul {
        list-style-type: none;
        padding: 0;
        margin: 0;
    }

    .left-menu li {
        padding: 8px 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .left-menu li:hover {
        background-color: #d1d0d0;
    }

    @keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}
.report-item:hover {
    background-color: #28a745;
    color: black;
    cursor: pointer;
}
.report-item.active {
    background-color: #28a745;
    color: black;
}


#customfromRange {
    flex-wrap: wrap;
    align-items: center; /* Align items vertically centered */
    gap: 15px; /* Space between items */
}

#customfromRange label {
    font-weight: bold;
    color: #007bff; /* Change to your preferred color */
}

#customfromRange input[type="date"] {
    border: 1px solid #007bff; /* Match the border color with the label color */
    padding: 5px;
    border-radius: 4px;
}

#customfromRange .input-group-text {
    font-weight: bold;
    color: #007bff; /* Change to your preferred color */
}


#customfromRange .input-wrapper {
    display: flex;
    align-items: center;
    gap: 10px; /* Space between label and input */
}

#customToRange {
    display: flex;
    flex-wrap: wrap;
    align-items: center; /* Align items vertically centered */
    gap: 15px; /* Space between items */
}


#customToRange .input-group-text {
    font-weight: bold;
    color: #007bff;
     /* Change to your preferred color */
}

#customToRange input[type="date"] {
    border: 1px solid #007bff; /* Match the border color with the label color */
    padding: 5px;
    border-radius: 4px;
    width: 100px;
}

    #orderwise_timetaken_datatable th {
    border: 1px solid rgb(230, 230, 230); /* Light and slightly transparent cement color border */
}

</style>
<div class="container-fluid d-flex reports">
    <div class="col-md-2 text-center left-menu">
        <h6 class="mt-2">List of Reports</h6>
        <ul style="padding-left: 0; text-align: left; list-style-type: none;">
            <li id="userwise-details" class="report-item active">Userwise Details</li>
            <li id="orderwise-details" class="report-item">Orderwise Details</li>
            <li id="ordercompletion-details" class="report-item">Order Completion Details</li>
            <li id="orderprogress-details" class="report-item">Order Progress Details</li>
            <li id="attendance-details" class="report-item">Attendance Details</li>
        </ul>
    </div>

    <div class="col-md-12">
        <div class="col-md-9 row justify-content-center">
            <div class="card">
                <div class="card-body" id="reports-content mt-1">
                    <h5 id="reports-heading">Reports - Userwise Details</h5>
                </div>
            </div>
        </div>
        <div class="row" id="hidefilter">
            <div class="col-md-4" style="width: 350px!important;">
                <div class="form-group" >
                    <label for="dateFilter" required>Selected received date range:</label>
                    <select class="form-control" style=" width: 250px !important; " id="dateFilter" onchange="selectDateFilter(this.value)">
                        <option value="" >Select Date Range</option>
                        <option value="last_week">Last Week</option>
                        <option value="current_week">current week</option>
                        <option value="last_month">Last Month</option>
                        <option value="current_month" selected>Current Month</option>
                        <option value="yearly">Yearly</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                <div class="d-flex">
                        <div class="col-md-6 mt-3 p-0 m-0"  id="customfromRange" >
                            <div class="input-group">
                                <span class="input-group-text">From:</span>
                                <input type="date" class="form-control" id="fromDate_range">
                            </div>
                        </div>

                    <div class="col-md-6 mt-3 " id="customToRange" >
                        <div class="input-group">
                            <span class="input-group-text">To:</span>
                            <input type="date" class="form-control" id="toDate_range">
                        </div>
                    </div>
                    <b class="mt-0" id="selectedDate"></b>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="client">Client</label>
                    <select class="form-control select2-basic-multiple" name="dcf_client_id[]" id="client_id_dcf" multiple="multiple">
                        <option selected value="All">All</option>
                        @forelse($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->client_no }} ({{ $client->client_name }})</option>
                        @empty
                        @endforelse
                    </select>
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group">
                    <label for="lob_id">Lob</label>
                    <select class="form-control select2-basic-multiple" style="width:100%" name="lob_id" id="lob_id" multiple="multiple">
                        <option selected value="Select Lob">Select Lob</option>
                    </select>
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group">
                    <label for="process_type_id">Process</label>
                    <select class="form-control select2-basic-multiple" style="width:100%" name="process_type_id" id="process_type_id" multiple="multiple">
                        <option selected value="All">All</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="mb-4" id="datepicker">
            <div class="col-3 mb-2" >
                <div class="input-group">
                    <div class="row">
                        <span style="color: grey;">Select Date:</span>
                        <input type="date" class="form-control" id="defultDate">
                    </div>
                    <div class="col-md-2 mt-3 p-3">
                        <button type="submit" id="filterButton2" class="btn btn-primary">Filter</button>
                    </div>
                </div>
            </div>
        </div>

<div class= "col-md-7 d-flex row" >
        <div class="col-md-4 mt-3" id="hidefilter_2">
            <div class="form-group">
                <label for="product_id">Product</label>
                <select class="form-control select2-basic-multiple" style="width:100%" name="product_id" id="product_id" multiple="multiple">
                    <option selected value="All">All Products</option>
                </select>
            </div>
        </div>
        <div class="col-md-3" style="margin-top:41px;" id="hidefilter_3">
            <button type="submit" id="filterButton" class="btn btn-primary">Filter</button>
        </div>
</div>

        <div class="card col-md-10 mt-5 tabledetails" id="userwise_table" style="font-size: 12px;">
            <h4 class="text-center mt-3">Userwise Details</h4>
                <div class="card-body">
                    <div class="p-0">
                    <div class="order-count mb-3" style="font-size: 14px; font-weight: bold;">
                        <span><strong>Total No of users:</strong> <span id="order-count" style="font-weight: normal;">0</span></span>
                    </div>
                        <table id="userwise_datatable" class="table table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead class="text-center" style="font-size: 12px;">
                                <tr>
                                    <th width="12%">Users</th>
                                    <th width="11%">WIP</th>
                                    <th width="11%">Coversheet Prep</th>
                                    <th width="11%">Clarification</th>
                                    <th width="11%">Send For QC</th>
                                    <th width="11%">Hold</th>
                                    <th width="11%">Cancelled</th>
                                    <th width="11%">Completed</th>
                                    <th width="11%">All</th>
                                </tr>
                            </thead>
                            <tbody class="text-center" style="font-size: 12px;"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card col-md-10 mt-5 orderWise" id="newreports_table" style="font-size: 12px;">
                <h4 class="text-center mt-3">Orderwise Details</h4>
                <div class="card-body">
                <div class="p-0">
                    <div class="order-count mb-3" style="font-size: 14px; font-weight: bold;">
                        <span><strong>Total No of Orders:</strong> <span id="order-count-orderWise" style="font-weight: normal;">0</span></span>
                    </div>
                    <div class="table-responsive">
                        <table id="newreports_datatable" class="table table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead class="text-center" style="font-size: 12px;">
                                <tr>
                                    <th width="5%">S.No</th>
                                    <th width="11%">Process</th>
                                    <th width="8%">Order Received Date</th>
                                    <th width="8%">Production Date</th>
                                    <th width="11%">Order ID</th>
                                    <th width="5%">State</th>
                                    <th width="5%">County Name</th>
                                    <th width="5%">Status Updated Date</th>
                                    <th width="11%">Status</th>
                                    <th width="11%">Status Comments</th>
                                    <th width="5%">Primary Source</th>
                                    <th width="5%">Process type</th>
                                    <th width="11%">User Emp Id</th>
                                    <th width="11%">User Emp Name</th>
                                    <th width="5%">QA Emp Id</th>
                                    <th width="6%">QA Emp Name</th>
                                    <th width="11%">QA Comments</th>
                                </tr>
                            </thead>
                            <tbody class="text-center" style="font-size: 12px;"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

            <div class="card col-md-10 mt-5 tabledetails" id="timetaken_table" style="font-size: 12px;">
            <h4 class="text-center mt-3">Order completion details</h4>
                <div class="card-body">
                    <div class="p-0">
                    <div style="overflow-x: auto;">
                        <table id="timetaken_datatable" class="table table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead class="text-center" style="font-size: 12px;">
                                <tr>
                                    <th width="12%">Emp ID</th>
                                    <th width="12%">Users</th>
                                    <th width="12%">Product Type</th>
                                    <th width="12%">No Of Assigned Orders</th>
                                    <th width="12%">No Of Completed Orders</th>
                                    <th width="11%">Total Time Taken For Completed Orders</th>
                                    <th width="11%">Avg Time Taken For Completed Orders</th>
                                </tr>
                            </thead>
                            <tbody class="text-center" style="font-size: 12px;"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="card col-md-10 mt-2 tabledetails" id="orderwise_timetaken_table" style="font-size: 12px; overflow-x: auto;">
                <h4 class="text-center mt-3">Order Progress Details</h4>
                <div class="card-body">
                    <div class="p-0">
            <table id="orderwise_timetaken_datatable" class="table table-bordered" style="border-collapse: collapse; width: 100%; background-color: #f5f5f5;">
                <thead class="text-center" style="font-size: 12px; background-color: ;">
                                <tr>
                        <th colspan="4">Emp Details</th>
                                    <th colspan="2">WIP</th>
                                    <th colspan="2">Coversheet Prep</th>
                                    <th colspan="2">Clarification</th>
                                    <th colspan="2">Send for QC</th>
                                </tr>
                                <tr>
                                    <th>Emp ID</th>
                                    <th>User</th>
                        <th>Product Type</th>
                        <th>No Assigned Orders</th>
                        <th>No of Orders Transferred</th>
                                    <th>Time Taken</th>
                        <th>No of Orders Transferred</th>
                                    <th>Time Taken</th>
                        <th>No of Orders Transferred</th>
                                    <th>Time Taken</th>
                        <th>No of Orders Transferred</th>
                                    <th>Time Taken</th>
                                </tr>
                            </thead>
                <tbody class="text-center" style="font-size: 12px; background-color: #ffffff;"></tbody>
            </table>
        </div>
    </div>
            </div>
            <div class="card col-md-10 mt-2 tabledetails" id="attendance_report" style="font-size: 12px; overflow-x: auto;">
                <h4 class="text-center mt-3">Attendance Report</h4>
                <div class="card-body">
                    <div class="p-0">
                        <table id="attendance_datatable" class="table table-bordered" style="border-collapse: collapse; width: 100%; background-color: #f5f5f5;">
                            <thead class="text-center" style="font-size: 12px; background-color: ;">
                                <tr>
                                    <th>Emp ID</th>
                                    <th>Emp Name</th>
                                    <th>Total Time Spent </th>
                                </tr>
                            </thead>
                            <tbody class="text-center" style="font-size: 12px; background-color: #ffffff;"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{asset('./assets/js/jquery.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>

<script>

$(document).ready(function() {
        $('#orderwise_timetaken_datatable').DataTable();
    });

let selectedDateFilter = '';
document.addEventListener('DOMContentLoaded', function() {
    selectDateFilter('');
});
function selectDateFilter(value) {
    let dateDisplay = document.getElementById('selectedDate');
    let customRangeDiv1 = document.getElementById('customfromRange');
    let customRangeDiv2 = document.getElementById('customToRange');
    let fromDateInput = document.getElementById('fromDate_range');
    let toDateInput = document.getElementById('toDate_range');

    // Hide custom date range by default
    customRangeDiv1.style.display = 'none';
    customRangeDiv2.style.display = 'none';

    // Clear the custom date inputs if changing to a predefined date range
    if (value !== 'custom') {
        fromDateInput.value = '';
        toDateInput.value = '';
    }

    switch (value) {
    case 'last_week':
        selectedDateFilter = getLastWeekDate();
        break;
    case 'current_week':
        selectedDateFilter = getCurrentWeekDate();
        break;
    case 'current_month':
        selectedDateFilter = getCurrentMonthDate();
        break;
    case 'last_month':
        selectedDateFilter = getLastMonthDate();
        break;
    case 'yearly':
        selectedDateFilter = getYearlyDate();
        break;
    case 'custom':
        selectedDateFilter = '';
        customRangeDiv1.style.display = 'block';
        customRangeDiv2.style.display = 'block';
        break;
    default:
        selectedDateFilter = getCurrentMonthDate();
        value = 'current_month';
}

    dateDisplay.textContent = selectedDateFilter;
}
function getTodayDate() {
    let StartDate = new Date();
    return `Selected: ${formatDate(StartDate)}`;
}

function getYesterdayDate() {
    let StartDate = new Date();
    StartDate.setDate(StartDate.getDate() - 1);
    return `Selected: ${formatDate(StartDate)}`;
}


function getLastWeekDate() {
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are zero-based
        const day = String(date.getDate()).padStart(2, '0');
        return `${month}-${day}-${year}`;
    }

    let today = new Date();
    let dayOfWeek = today.getDay();
    let currentWeekStart = new Date(today);
    currentWeekStart.setDate(today.getDate() - ((dayOfWeek + 6) % 7));
    let StartDate = new Date(currentWeekStart);
    StartDate.setDate(currentWeekStart.getDate() - 7);
    let EndDate = new Date(StartDate);
    EndDate.setDate(StartDate.getDate() + 6);
    return `Selected: ${formatDate(StartDate)} to ${formatDate(EndDate)}`;
}

function getCurrentMonthDate() {
function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are zero-based
    const day = String(date.getDate()).padStart(2, '0');
    return `${month}-${day}-${year}`;
}

let today = new Date();

let StartDate = new Date(today.getFullYear(), today.getMonth(), 1);

let endDateOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);

let EndDate = today > endDateOfMonth ? endDateOfMonth : today;

return `Selected: ${formatDate(StartDate)} to ${formatDate(EndDate)}`;
}

function getLastMonthDate() {
    let today = new Date();
    let StartDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
    let EndDate = new Date(today.getFullYear(), today.getMonth(), 0);
    return `Selected: ${formatDate(StartDate)} to ${formatDate(EndDate)}`;
}

function getCurrentWeekDate() {
function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are zero-based
    const day = String(date.getDate()).padStart(2, '0');
    return `${month}-${day}-${year}`;
}
let EndDate = new Date();
let dayOfWeek = EndDate.getDay(); // Sunday is 0, Monday is 1, ..., Saturday is 6
let StartDate = new Date(EndDate);
StartDate.setDate(EndDate.getDate() - dayOfWeek + 1);
if (dayOfWeek === 0) {
    StartDate.setDate(StartDate.getDate() - 6);
}
return `Selected: ${formatDate(StartDate)} to ${formatDate(EndDate)}`;
}


function getYearlyDate() {
    let today = new Date();
    let startOfYear = new Date(today.getFullYear(), 0, 1);
    let endOfYear = today; // Set endOfYear to today's date

    return `Selected: ${formatDate(startOfYear)} to ${formatDate(endOfYear)}`;
}

// Example formatDate function to format dates as 'YYYY-MM-DD'
function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are 0-based
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}


function formatDate(date) {
    let day = date.getDate();
    let month = date.getMonth() + 1;
    let year = date.getFullYear();
    return `${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}-${year}`;

    var customRangeDiv1 = document.getElementById('customfromRange');
    var customRangeDiv2 = document.getElementById('customToRange');


    // Hide customRangeDiv by default
    customRangeDiv1.style.display = 'none';
    customRangeDiv2.style.display = 'none';


    switch (value) {
        case 'custom':
            customRangeDiv1.style.display = 'block';
            customRangeDiv2.style.display = 'block';

            break;
        default:
            resetDateInputs();
            break;
    }
}


$(document).ready(function() {
        var isClientChanging = false;
        $(document).on('change', '#client_id_dcf', function() {
            if (isClientChanging) return;
            isClientChanging = true;
            var selectedClientOption = $(this).val();
            $("#client_id_dcf").val(selectedClientOption && selectedClientOption.includes('All') ? ['All'] : selectedClientOption);
            if ($("#client_id_dcf").val() !== selectedClientOption) {
                $("#client_id_dcf").trigger('change');
            }
            isClientChanging = false;
        });

        var isLobChanging = false;
        $(document).on('change', '#lob_id', function() {
            if (isLobChanging) return;
            isLobChanging = true;
            var selectedLobOption = $(this).val();
            $("#lob_id").val(selectedLobOption && selectedLobOption.includes('Select Lob') ? ['Select Lob'] : selectedLobOption);
            if ($("#lob_id").val() !== selectedLobOption) {
                $("#lob_id").trigger('change');
            }
            isLobChanging = false;
        });

        var isProcessChanging = false;
        $(document).on('change', '#process_type_id', function() {
            if (isProcessChanging) return;
            isProcessChanging = true;
            var selectedProcessOption = $(this).val();
            $("#process_type_id").val(selectedProcessOption && selectedProcessOption.includes('All') ? ['All'] : selectedProcessOption);
            if ($("#process_type_id").val() !== selectedProcessOption) {
                $("#process_type_id").trigger('change');
            }
            isProcessChanging = false;
        });


        var isProductChanging = false;
        $(document).on('change', '#product_id', function() {
            if (isProductChanging) return;
            isProductChanging = true;
            var selectedProductOption = $(this).val();
            $("#product_id").val(selectedProductOption && selectedProductOption.includes('All') ? ['All'] : selectedProductOption);
            if ($("#product_id").val() !== selectedProductOption) {
                $("#product_id").trigger('change');
            }
            isProductChanging = false;
        });
    });


function orderWise() {
    var fromDate = $('#fromDate_range').val();
    var toDate = $('#toDate_range').val();
    var client_id = $('#client_id_dcf').val();
    var lob_id = $('#lob_id').val();
    var process_type_id = $('#process_type_id').val();
    var product_id = $('#product_id').val();

    var table = $('#newreports_datatable').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('orderWise') }}",
            type: 'POST',
            data: function(d) {
                d.fromDate_range  = fromDate;
                d.toDate_range = toDate;
                d.client_id = client_id;
                d.product_id = product_id;
                d.lob_id = lob_id;
                d.process_type_id = process_type_id;
                d.selectedDateFilter = selectedDateFilter;
                d._token = '{{ csrf_token() }}';
            },
            dataSrc: function(response) {
                // Calculate total number of orders
                var totalOrders = response.recordsTotal; // Use recordsTotal from server response

                // Update the total orders count
                $('#order-count-orderWise').text(totalOrders);

                // Return data for DataTables
                return response.data;
            }
        },
        columns: [
            {
                data: null,
                name: 's_no',
                orderable: false,
                searchable: false,
                render: function(data, type, row, meta) {
                    var pageInfo = meta.settings.oInstance.api().page.info();
                    return pageInfo.start + meta.row + 1;
                }
            },
            { data: 'process', name: 'process' },
            {
                data: 'order_date',
                name: 'order_date',
                render: function(data, type, row) {
                    return data ? formatExcelDate(data) : '';
                }
            },
            {
                data: 'completion_date',
                name: 'completion_date',
                render: function(data, type, row) {
                    return data ? formatExcelDate(data) : '';
                }
            },
            { data: 'order_id', name: 'order_id' },
            { data: 'short_code', name: 'short_code' },
            { data: 'county_name', name: 'county_name' },
            { data: 'status_updated_time',
              name: 'status_updated_time',
              render: function(data, type, row) {
                return data ? formatExcelDate(data) : '';
              }},
            { data: 'status', name: 'status' },
            { data: 'status_comment', name: 'status_comment' },
            { data: 'primary_source', name: 'primary_source' },
            { data: 'process_name', name: 'process_name' },
            { data: 'emp_id', name: 'emp_id' },
            { data: 'emp_name', name: 'emp_name' },
            {data: 'qc_EmpId', name: 'qc_EmpId'},
            {data: 'qa_user', name: 'qa_user'},
            {data: 'qc_comment', name: 'qc_comment'},
            





        ],
        dom: 'l<"toolbar">Bfrtip',
        buttons: [
            {
                extend: 'excel',
                action: function (e, dt, button, config) {
                    $.ajax({
                        url: "{{ route('orderWise') }}",
                        type: 'POST',
                        data: {
                            toDate_range: toDate,
                            fromDate_range: fromDate,
                            client_id : client_id,
                            product_id : product_id,
                            lob_id : lob_id,
                            process_type_id : process_type_id,
                            selectedDateFilter: selectedDateFilter,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            var data = response.data;

                            var headers = ["S.No", "Process", "Order Date", "Completion Date", "Order ID", "Short Code", "County Name", "Date of Movement", "Status", "Status Comment", "Primary Source","Process Type", "User Emp Id", "User Emp Name", "QA Emp Id", "QA Emp Name", "QA Comments"];
                            var exportData = data.map((row, index) => [
                                index + 1,
                                row.process,
                                formatExcelDate(row.order_date),
                                formatExcelDate(row.completion_date),
                                row.order_id,
                                row.short_code,
                                row.county_name,
                                formatExcelDate(row.status_updated_time),
                                row.status,
                                row.status_comment,
                                row.primary_source,
                                row.process_name.replace(/&amp;/g, '&'),
                                row.emp_id,
                                row.emp_name,
                                row.qc_EmpId,
                                row.qa_user,
                                row.qc_comment,


                            ]);

                            var wb = XLSX.utils.book_new();
                            var ws = XLSX.utils.aoa_to_sheet([headers].concat(exportData));
                            XLSX.utils.book_append_sheet(wb, ws, "New Reports");
                            XLSX.writeFile(wb, "orderWise.xlsx");
                        }
                    });
                }
            }
        ]
    });

    function formatExcelDate(dateString) {
        if (dateString) {
            var date = new Date(dateString);
            if (isNaN(date.getTime())) {
                return dateString; // If date parsing fails, return original string
            }
            var month = ('0' + (date.getMonth() + 1)).slice(-2);
            var day = ('0' + date.getDate()).slice(-2);
            var year = date.getFullYear();
            var hour = ('0' + date.getHours()).slice(-2);
            var minute = ('0' + date.getMinutes()).slice(-2);
            var second = ('0' + date.getSeconds()).slice(-2);
            return `${month}/${day}/${year} ${hour}:${minute}:${second}`;
        }
        return '';
    }
}

$('#newreports_datatable').on('draw.dt', function () {
    $('#newreports_table').removeClass('d-none');
});


    function fetchProData(client_id) {
        $.ajax({
            url: "{{ url('get_lob') }}",
            type: "POST",
            data: {
                client_id: client_id,
                _token: '{{ csrf_token() }}'
            },
            dataType: 'json',
            success: function (response) {
                $('#lob_id').html('<option selected value="Select Lob">Select Lob</option>');
                $.each(response, function (index, item) {
                    $("#lob_id").append('<option value="' + item.id + '">' + item.name + '</option>');


                });
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error: ' + status + ' - ' + error);
            }
        });
    }

    $('#lob_id').on('change', function () {
    var lob_id = $(this).val();
    $("#process_type_id").html(''); 
        $.ajax({
            url: "{{ url('get_process') }}",
            type: "POST",
            data: {
                lob_id: lob_id,
                _token: '{{ csrf_token() }}'
            },
            dataType: 'json',
            success: function (response) {
                $('#process_type_id').html('<option selected value="All">All</option>');
                $.each(response, function (index, item) {
                    $("#process_type_id").append('<option value="' + item.id + '">' + item.name + '</option>');

                });
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error: ' + status + ' - ' + error);
            }
        });
    })



    $('#process_type_id').on('change', function () {
    var process_type_id = $(this).val();
    var client_id = $('#client_id_dcf').val();
    var lob_id = $('#lob_id').val();

    console.log(process_type_id);
    $("#product_id").html(''); 
        $.ajax({
            url: "{{ url('get_product') }}",
            type: "POST",
            data: {
                process_type_id: process_type_id,
                client_id: client_id,
                lob_id:lob_id,
                _token: '{{ csrf_token() }}'
            },
            dataType: 'json',
            success: function (response) {
                $('#product_id').html('<option selected value="All">All</option>');
                $.each(response, function (index, item) {
                    $("#product_id").append('<option value="' + item.id + '">' + '(' + item.project_code + ') ' + item.process_name + '</option>');

                });
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error: ' + status + ' - ' + error);
            }
        });
    })

    function userwise_datatable() {
    var fromDate =  $('#fromDate_range').val();
    var toDate = $('#toDate_range').val();
    var client_id = $('#client_id_dcf').val();
    var lob_id = $('#lob_id').val();
    var process_type_id = $('#process_type_id').val();
    var product_id = $('#product_id').val();

    var table = $('#userwise_datatable').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('userwise_count') }}",
            type: 'POST',
            data: function(d) {
                d.toDate_range = toDate;
                d.fromDate_range = fromDate;
                d.client_id = client_id;
                d.lob_id = lob_id;
                d.process_type_id = process_type_id,
                d.product_id = product_id,
                d.selectedDateFilter = selectedDateFilter;
                d._token = '{{ csrf_token() }}';
            },
            dataSrc: function(response) {
                // Calculate total unique users
                var userCount = response.data.length;

                // Update the total users count
                $('#order-count').text(userCount);

                // Return data for DataTables
                return response.data;
            }
        },
        columns: [
            { data: 'userinfo', name: 'userinfo', class: 'text-left' },
            { data: 'status_1', name: 'status_1', visible:@if(Auth::user()->hasRole('Qcer')) false @else true @endif},
            { data: 'status_13', name: 'status_13' },
            { data: 'status_14', name: 'status_14' },
            { data: 'status_4', name: 'status_4' },
            { data: 'status_2', name: 'status_2' },
            { data: 'status_3', name: 'status_3' },
            { data: 'status_5', name: 'status_5' },
            { data: 'All', name: 'All' }
        ],
        dom: 'l<"toolbar">Bfrtip',
        buttons: [
            {
                extend: 'excel',
                action: function (e, dt, button, config) {
                    $.ajax({
                        url: "{{ route('userwise_count') }}",
                        type: 'POST',
                        data: {
                            toDate_range: toDate,
                            fromDate_range: fromDate,
                            client_id : client_id,
                            lob_id : lob_id,
                            process_type_id : process_type_id,
                            product_id : product_id,
                            selectedDateFilter : selectedDateFilter,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            var data = response.data;

                            var headers = ["Users", "WIP", "Coversheet Prep", "Clarification", "Send For QC", "Hold", "Cancelled", "Completed", "All"];
                            var exportData = data.map(row => [
                                row.userinfo,
                                row.status_1,
                                row.status_13,
                                row.status_14,
                                row.status_4,
                                row.status_2,
                                row.status_3,
                                row.status_5,
                                row.All
                            ]);

                            var wb = XLSX.utils.book_new();
                            var ws = XLSX.utils.aoa_to_sheet([headers].concat(exportData));
                            XLSX.utils.book_append_sheet(wb, ws, "Userwise Data");
                            XLSX.writeFile(wb, "userwise_data.xlsx");
                        }
                    });
                }
            }
        ]
    });
}


    $('#userwise_datatable').on('draw.dt', function () {
        $('#userwise_table').removeClass('d-none');
    });


$('#client_id_dcf').on('change', function () {
    let client_id = $("#client_id_dcf").val();
    $("#lob_id").html('<option selected value="All">All</option>');
    fetchProData(client_id);
});

$(document).ready(function() {
    fetchProData('All');
        $("#project_id").select2();
        $("#project_id_dcf").select2();
        $("#client_id_dcf").select2();

    $('.select2-basic-multiple').select2();

    $('#client_id_dcf').on('change', function () {
       let getproject_id = $("#client_id_dcf").val();
       $("#project_id_dcf").html('All');
        fetchProData(getproject_id);
    });
});

$(document).on('click', '#filterButton,#filterButton2', function () {
    // Find the active report item
    const activeItem = $('.report-item.active');

    // Check if an active item exists and call the respective function
    if (activeItem.length) {
        const activeId = activeItem.attr('id'); // Get the ID of the active item

        // Run the corresponding function based on the active item ID
        switch (activeId) {
            case 'orderwise-details':
                orderWise();
                break;
            case 'userwise-details':
                userwise_datatable();
                break;
            case 'ordercompletion-details':
                userTimeTaken_datatable();
                break;
            case 'orderprogress-details':
                orderTimeTaken_datatable();
                break;
            default:
                console.log('No valid report item is active.');
        }
    } else {
        console.log('No report item is currently active.');
    }
});




$(document).ready(function() {
    function showReport(reportId) {
        $('#reports-heading').text('Reports - ' + $('#' + reportId).text());
        $('.report-item').removeClass('active');
        $('#' + reportId).addClass('active');

        // Hide all tables initially
        $('#userwise_table').hide();
        $('#newreports_table').hide(); // Fixed table ID
        $('#timetaken_table').hide();
        $('#txn_revenue_table').hide();
        $('#fte_revenue_table').hide();
        $('#orderwise_timetaken_table').hide();
        $('#attendance_report').hide();
        $('#datepicker').hide();



        // Show the selected table based on ID
        if (reportId === 'userwise-details') {
            $('#userwise_table').show();
            $('#datepicker').hide();
            $('#hidefilter').show();
            $('#hidefilter_2').show();
            $('#hidefilter_3').show();

        } else if (reportId === 'orderwise-details') {
            $('#newreports_table').show();
            $('#datepicker').hide();
            $('#hidefilter').show();
            $('#hidefilter_2').show();
            $('#hidefilter_3').show();

        } else if (reportId === 'ordercompletion-details') {
            $('#timetaken_table').show();
            $('#datepicker').hide();
            $('#hidefilter').show();
            $('#hidefilter_2').show();
            $('#hidefilter_3').show();

        } else if (reportId === 'orderprogress-details') {
            $('#orderwise_timetaken_table').show();
            $('#datepicker').hide();
            $('#hidefilter').show();
            $('#hidefilter_2').show();
            $('#hidefilter_3').show();


        } else if (reportId === 'attendance-details') {
            $('#attendance_report').show();
            $('#hidefilter').hide();
            $('#hidefilter_2').hide();
            $('#hidefilter_3').hide();
            $('#datepicker').show();
        }
    }

    showReport('userwise-details');

    $('.report-item').click(function() {
        var reportId = $(this).attr('id');
        showReport(reportId);
    });
});

function isFutureDate(date) {
    const today = new Date();
    today.setHours(0, 0, 0, 0); 
    return date > today;
}

function normalizeDate(date) {
    const normalized = new Date(date);
    normalized.setHours(0, 0, 0, 0); 
    return normalized;
}

function setDateToInput(inputElement, date) {
    inputElement.value = date.toISOString().split('T')[0]; 
}

const currentDate = normalizeDate(new Date());

document.getElementById('toDate_range').addEventListener('change', function() {
    const selectedDate = normalizeDate(new Date(this.value));
    const fromDate = normalizeDate(new Date(document.getElementById('fromDate_range').value || currentDate));

    if (isFutureDate(selectedDate)) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'You cannot select a future date.'
        });
        setDateToInput(this, currentDate); 
    } else if (selectedDate < fromDate) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'The "To Date" cannot be earlier than the "From Date".'
        });
        setDateToInput(this, fromDate); // Set the value back to the "From Date"
    }
});

document.getElementById('fromDate_range').addEventListener('change', function() {
    const selectedDate = normalizeDate(new Date(this.value));
    const toDate = normalizeDate(new Date(document.getElementById('toDate_range').value || currentDate));

    if (isFutureDate(selectedDate)) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'You cannot select a future date.'
        });
        setDateToInput(this, currentDate); 
    } else if (toDate < selectedDate) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'The "From Date" cannot be later than the "To Date".'
        });
        setDateToInput(document.getElementById('toDate_range'), selectedDate);
    }
});


function userTimeTaken_datatable() {
    var fromDate = $('#fromDate_range').val();
    var toDate = $('#toDate_range').val();
    let client_id = $("#client_id_dcf").val();
    let lob_id = $("#lob_id").val();
    let process_type_id = $('#process_type_id').val();
    let product_id = $('#product_id').val();

    $('#timetaken_datatable').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('get_timetaken') }}",
            type: 'POST',
            data: {
                    toDate_range: toDate,
                    fromDate_range: fromDate,
                    client_id: client_id,
                    lob_id: lob_id,
                    process_type_id: process_type_id,
                    product_id : product_id,
                    selectedDateFilter: selectedDateFilter,
                    _token: '{{ csrf_token() }}'
            },
            dataSrc: function (json) {
                if (Array.isArray(product_id) && product_id.includes('All')) {
                    json.data.forEach(function (item) {
                        item.Product_Type = 'All Products';
                    });
                } else if (product_id === 'All') {
                    json.data.forEach(function (item) {
                        item.Product_Type = 'All';
                    });
                }
                return json.data; 
            },
        },
        columns: [
            { data: 'emp_id', name: 'emp_id', class: 'text-left' },
            { data: 'Users', name: 'Users', class: 'text-left' },
            { data: 'Product_Type', name: 'Product_Type', class: 'text-left' },
            { data: 'NO_OF_ASSIGNED_ORDERS', name: 'NO_OF_ASSIGNED_ORDERS' },
            { data: 'NO_OF_COMPLETED_ORDERS', name: 'NO_OF_COMPLETED_ORDERS' },
            { data: 'TOTAL_TIME_TAKEN_FOR_COMPLETED_ORDERS', name: 'TOTAL_TIME_TAKEN_FOR_COMPLETED_ORDERS' },
            { data: 'AVG_TIME_TAKEN_FOR_COMPLETED_ORDERS', name: 'AVG_TIME_TAKEN_FOR_COMPLETED_ORDERS' }
        ],
        dom: 'lBfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                title: 'ordercompletion details'  // Set the filename here
            }
        ],
        lengthMenu: [ 10, 25, 50, 75, 100 ]
    });
}


function orderTimeTaken_datatable() {
    var fromDate = $('#fromDate_range').val();
    var toDate = $('#toDate_range').val();
    let client_id = $("#client_id_dcf").val();
    let lob_id = $("#lob_id").val();
    let process_type_id = $('#process_type_id').val();
    let product_id = $('#product_id').val();

    $('#orderwise_timetaken_datatable').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('orderTimeTaken') }}",
            type: 'POST',
            data: {
                toDate_range: toDate,
                fromDate_range: fromDate,
                client_id: client_id,
                lob_id: lob_id,
                process_type_id : process_type_id,
                product_id: product_id,
                selectedDateFilter: selectedDateFilter,
                _token: '{{ csrf_token() }}'
            },
            dataSrc: function (json) {
                if (Array.isArray(product_id) && product_id.includes('All')) {
                    json.data.forEach(function (item) {
                        item.Product_Type = 'All  Products';
                    });
                } else if (product_id === 'All') {
                    json.data.forEach(function (item) {
                        item.Product_Type = 'All';
                    });
                }
                return json.data; 
            },
        },
        columns: [
            { data: 'Emp ID', name: 'Emp ID', class: 'text-left' },
            { data: 'Users', name: 'Users', class: 'text-left' },
            { data: 'Product_Type', name: 'Product_Type' },
            { data: 'Assigned Orders', name: 'Assigned Orders' },
            { data: 'WIP.count', name: 'WIP.count' },
            { data: 'WIP.time', name: 'WIP.time' },
            { data: 'COVERSHEET PRP.count', name: 'COVERSHEET PRP.count' },
            { data: 'COVERSHEET PRP.time', name: 'COVERSHEET PRP.time' },
            { data: 'CLARIFICATION.count', name: 'CLARIFICATION.count' },
            { data: 'CLARIFICATION.time', name: 'CLARIFICATION.time' },
            { data: 'SEND FOR QC.count', name: 'SEND FOR QC.count' },
            { data: 'SEND FOR QC.time', name: 'SEND FOR QC.time' },
        ],
        dom: 'lBfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                title: 'orderprogress details'  // Set the filename here
            }
        ],
        lengthMenu: [10, 25, 50, 75, 100],
        order: [[0, 'asc']]
    });
}

$(document).ready(function() {
    // Function to set the default date to today
    function setDefaultDate() {
        var today = new Date();
        var yyyy = today.getFullYear();
        var mm = String(today.getMonth() + 1).padStart(2, '0'); // Months are zero-based
        var dd = String(today.getDate()).padStart(2, '0');
        var todayFormatted = yyyy + '-' + mm + '-' + dd; // Format as yyyy-mm-dd

        $("#defultDate").val(todayFormatted); // Set today's date in the input field
    }

    // Call the function to set the default date when the page loads
    setDefaultDate();
    // Event listener for the filter button click
    $('#filterButton2').on('click', function () {
        var selectedDate = $('#defultDate').val(); // Capture the selected date
        attendance_report(selectedDate); // Pass the selected date to the function
    });
});

function attendance_report(selectedDate) {
    $('#attendance_datatable').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('attendance_report') }}",
            type: 'POST',
            data: {
                selectDate: selectedDate, // Send the selected date
                _token: '{{ csrf_token() }}'
            },
            dataSrc: function (json) {
                return json.data; // Access the data array
            },
            error: function (xhr, status, error) {
                alert('Failed to load data. Please try again.');
            }
        },
        columns: [
            { data: 'Emp ID', name: 'Emp ID', class: 'text-left' },
            { data: 'Emp Name', name: 'Emp Name', class: 'text-left' },
            { data: 'Total Time Spent', name: 'Total Time Spent' },
        ],
        dom: 'lBfrtip',
        buttons: ['excel'],
        lengthMenu: [10, 25, 50, 75, 100],
        order: [[0, 'asc']]
    });
}





</script>

@endsection


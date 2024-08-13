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
    color: white;
    cursor: pointer;
}
.report-item.active {
    background-color: #28a745;
    color: white;
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

</style>
<div class="container-fluid d-flex reports">
    <div class="col-md-2 text-center left-menu">
        <h6 class="mt-2">List of Reports</h6>
        <ul>
            <li id="userwise-details" class="report-item active">Userwise Details</li>
            <li id="orderwise-details" class="report-item">Orderwise Details</li>
            <li id="timetaken-details" class="report-item">Average Time Taken</li>
            {{-- <li id="txn-revenue-details" class="report-item">TXN Revenue Details</li>
            <li id="fte-revenue-details" class="report-item">FTE Revenue Details</li> --}}
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
        <div class="row">
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
            <div class="col-md-3">
                <div class="form-group">
                    <label for="project">Product</label>
                    <select class="form-control select2-basic-multiple" style="width:100%" name="dcf_project_id[]" id="project_id_dcf" multiple="multiple">
                        <option selected value="All">All Products</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2 mt-4">
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
                                    <th width="11%">Send For Qc</th>
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

            <div class="card col-md-10 mt-5 newreports" id="newreports_table" style="font-size: 12px;">
                <h4 class="text-center mt-3">Orderwise Details</h4>
                <div class="card-body">
                <div class="p-0">
                    <div class="order-count mb-3" style="font-size: 14px; font-weight: bold;">
                        <span><strong>Total No of Orders:</strong> <span id="order-count-newreports" style="font-weight: normal;">0</span></span>
                    </div>
                    <div class="table-responsive">
                        <table id="newreports_datatable" class="table table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead class="text-center" style="font-size: 12px;">
                                <tr>
                                    <th width="12%">S.No</th>
                                    <th width="11%">Product Type</th>
                                    <th width="11%">Order Received</th>
                                    <th width="11%">Production Date</th>
                                    <th width="11%">Order Id</th>
                                    <th width="11%">State</th>
                                    <th width="11%">County</th>
                                    <th width="11%">Status</th>
                                    <th width="11%">Comments</th>
                                    <th width="11%">Primary Source</th>
                                </tr>
                            </thead>
                            <tbody class="text-center" style="font-size: 12px;"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card col-md-10 mt-5 tabledetails " id="timetaken_table" style="font-size: 12px;">
                <h4 class="text-center mt-3" >Userwise Details</h4>
                <div class="card-body">
                    <div class="p-0">
                        <table id="timetaken_datatable" class="table table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead class="text-center" style="font-size: 12px;">
                                <tr>
                                    <th width="12%">Emp ID</th>
                                    <th width="12%">Users</th>
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
            
            <div class="card col-md-10 mt-2 tabledetails d-none" id="orderwise_timetaken_table" style="font-size: 12px; overflow-x: auto;">
                <h4 class="text-center mt-3">Orderwise Details</h4>
                <div class="card-body">
                    <div class="p-0">
                        <table id="orderwise_timetaken_datatable" class="table table-bordered" style="border-collapse: collapse; width: 100%;">
                            <thead class="text-center" style="font-size: 12px;">
                                <tr>
                                    <th>Emp ID</th>
                                    <th>User</th>
                                    <th>Assigned Orders</th>
                                    <th>No. of Orders Moved (WIP)</th>
                                    <th>Total Time Taken (WIP)</th>
                                    <th>No. of Orders Moved (Coversheet Prep)</th>
                                    <th>Total Time Taken (Coversheet Prep)</th>
                                    <th>No. of Orders Moved (Clarification)</th>
                                    <th>Total Time Taken (Clarification)</th>
                                    <th>No. of Orders Moved (Send for QC)</th>
                                    <th>Total Time Taken (Send for QC)</th>
                                </tr>
                            </thead>
                            <tbody class="text-center" style="font-size: 12px;"></tbody>
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

function newreports() {
    var fromDate = $('#fromDate_range').val();
    var toDate = $('#toDate_range').val();
    var client_id = $('#client_id_dcf').val();
    var project_id = $('#project_id_dcf').val();

    var table = $('#newreports_datatable').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('newreports') }}",
            type: 'POST',
            data: function(d) {
                d.fromDate_range  = fromDate;
                d.toDate_range = toDate;
                d.client_id = client_id;
                d.project_id = project_id;
                d.selectedDateFilter = selectedDateFilter;
                d._token = '{{ csrf_token() }}';
            },
            dataSrc: function(response) {
                // Calculate total number of orders
                var totalOrders = response.recordsTotal; // Use recordsTotal from server response

                // Update the total orders count
                $('#order-count-newreports').text(totalOrders);

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
            { data: 'status', name: 'status' },
             { data: 'status_comment', name: 'status_comment' },
            { data: 'primary_source', name: 'primary_source' }
        ],
        dom: 'l<"toolbar">Bfrtip',
        buttons: [
            {
                extend: 'excel',
                action: function (e, dt, button, config) {
                    $.ajax({
                        url: "{{ route('newreports') }}",
                        type: 'POST',
                        data: {
                            toDate_range: toDate,
                            fromDate_range: fromDate,
                            client_id: client_id,
                            project_id: project_id,
                            selectedDateFilter: selectedDateFilter,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            var data = response.data;

                            var headers = ["S.No", "Process", "Order Date", "Completion Date", "Order ID", "Short Code", "County Name", "Status", "Status Comment", "Primary Source"];
                            var exportData = data.map((row, index) => [
                                index + 1,
                                row.process,
                                formatExcelDate(row.order_date),
                                formatExcelDate(row.completion_date),
                                row.order_id,
                                row.short_code,
                                row.county_name,
                                row.status,
                                row.status_comment,
                                row.primary_source
                            ]);

                            var wb = XLSX.utils.book_new();
                            var ws = XLSX.utils.aoa_to_sheet([headers].concat(exportData));
                            XLSX.utils.book_append_sheet(wb, ws, "New Reports");
                            XLSX.writeFile(wb, "newreports.xlsx");
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
            url: "{{ url('Productdropdown') }}",
            type: "POST",
            data: {
                client_id: client_id,
                _token: '{{ csrf_token() }}'
            },
            dataType: 'json',
            success: function (response) {
                $('#project_id_dcf').html('<option selected value="All">All Products</option>');
                $.each(response, function (index, item) {
                    $("#project_id_dcf").append('<option value="' + item.id + '">' + item.project_code + ' - (' + item.process_name + ')</option>');

                });
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error: ' + status + ' - ' + error);
            }
        });
    }

    function userwise_datatable() {
    var fromDate =  $('#fromDate_range').val();
    var toDate = $('#toDate_range').val();
    var client_id = $('#client_id_dcf').val();
    var project_id = $('#project_id_dcf').val();

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
                d.project_id = project_id;
                d.selectedDateFilter = selectedDateFilter;
                d._token = '{{ csrf_token() }}';
            },
            dataSrc: function(response) {
                // Update the total users count
                $('#order-count').text(response.recordsTotal);

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
                            client_id: client_id,
                            project_id: project_id,
                            selectedDateFilter : selectedDateFilter,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            var data = response.data;

                            var headers = ["Users", "WIP", "Coversheet Prep", "Clarification", "Send For Qc", "Hold", "Cancelled", "Completed", "All"];
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


$("#filterButton").on('click', function() {
    let fromDate = $("#fromDate_range").val();
    let toDate = $("#toDate_range").val();
    let client_id = $("#client_id_dcf").val();
    let project_id = $("#project_id_dcf").val();
    userwise_datatable(fromDate, toDate, client_id, project_id);
    newreports(fromDate, toDate, client_id, project_id);

    if (fromDate && toDate) {
        userwise_datatable(fromDate, toDate, client_id, project_id);
        newreports(fromDate, toDate, client_id, project_id);
        userTimeTaken_datatable(fromDate, toDate, client_id, project_id);
        orderTimeTaken_datatable(fromDate, toDate, client_id, project_id);
    } else if ($("#dateFilter").val() === 'custom') {
        if (!fromDate || !toDate) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Date Range',
                text: 'Please select both "From Date" and "To Date" before filtering.'
            });
        } else {
            userwise_datatable(fromDate, toDate, client_id, project_id);
            newreports(fromDate, toDate, client_id, project_id);
            userTimeTaken_datatable(fromDate, toDate, client_id, project_id);
            orderTimeTaken_datatable(fromDate, toDate, client_id, project_id);
        }
    } else {
        userwise_datatable(fromDate, toDate, client_id, project_id);
        newreports(fromDate, toDate, client_id, project_id);
        userTimeTaken_datatable(fromDate, toDate, client_id, project_id);
        orderTimeTaken_datatable(fromDate, toDate, client_id, project_id);
    }
});

$('#client_id_dcf').on('change', function () {
    let getproject_id = $("#client_id_dcf").val();
    $("#project_id_dcf").html('<option selected value="All">All Products</option>');
    fetchProData(getproject_id);
});

$(document).ready(function() {
    fetchProData('All');
    console.log('1');
        $("#project_id").select2();
        $("#project_id_dcf").select2();
        $("#client_id_dcf").select2();

    $('.select2-basic-multiple').select2();
    userwise_datatable();
    userTimeTaken_datatable();
    orderTimeTaken_datatable();
    newreports();


    $('#client_id_dcf').on('change', function () {
       let getproject_id = $("#client_id_dcf").val();
       $("#project_id_dcf").html('All');
        fetchProData(getproject_id);
    });
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

        // Show the selected table based on ID
        if (reportId === 'userwise-details') {
            $('#userwise_table').show();
        } else if (reportId === 'orderwise-details') {
            $('#newreports_table').show();
        } else if (reportId === 'timetaken-details') {
            $('#timetaken_table').show();
            $('#orderwise_timetaken_table').show();
        // } else if (reportId === 'txn-revenue-details') {
        //     $('#txn_revenue_table').show();
        // } else if (reportId === 'fte-revenue-details') {
        //     $('#fte_revenue_table').show();
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
    var client_id = $('#client_id_dcf').val();
    var project_id = $('#project_id_dcf').val();

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
                    project_id: project_id,
                selectedDateFilter: selectedDateFilter,
                    _token: '{{ csrf_token() }}'
            },
            dataSrc: 'data'
        },
        columns: [
            { data: 'emp_id', name: 'emp_id', class: 'text-left' },
            { data: 'Users', name: 'Users', class: 'text-left' },
            { data: 'NO_OF_ASSIGNED_ORDERS', name: 'NO_OF_ASSIGNED_ORDERS' },
            { data: 'NO_OF_COMPLETED_ORDERS', name: 'NO_OF_COMPLETED_ORDERS' },
            { data: 'TOTAL_TIME_TAKEN_FOR_COMPLETED_ORDERS', name: 'TOTAL_TIME_TAKEN_FOR_COMPLETED_ORDERS' },
            { data: 'AVG_TIME_TAKEN_FOR_COMPLETED_ORDERS', name: 'AVG_TIME_TAKEN_FOR_COMPLETED_ORDERS' }
        ],
        dom: 'lBfrtip',
        buttons: [
            'excel',
        ],
        lengthMenu: [ 10, 25, 50, 75, 100 ]
    });
}


function orderTimeTaken_datatable() {
    var fromDate = $('#fromDate_range').val();
    var toDate = $('#toDate_range').val();
    var client_id = $('#client_id_dcf').val();
    var project_id = $('#project_id_dcf').val();
    var selectedDateFilter = $('#selectedDateFilter').val(); // Assuming you have an element for this

    $('#orderwise_timetaken_datatable').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('orderTimeTaken') }}",
            type: 'POST',
            data: {
                fromDate_range: fromDate,
                toDate_range: toDate,
                client_id: client_id,
                project_id: project_id,
                selectedDateFilter: selectedDateFilter,
                _token: '{{ csrf_token() }}'
            },
            dataSrc: ''
        },
        columns: [
            { data: 'Emp ID', name: 'Emp ID', class: 'text-left' },
            { data: 'Users', name: 'Users', class: 'text-left' },
            { data: 'NO OF ORDERS', name: 'NO OF ORDERS' },
            { data: 'WIP', name: 'WIP' },
            { data: 'COVERSHEET PRP', name: 'COVERSHEET PRP' },
            { data: 'CLARIFICATION', name: 'CLARIFICATION' },
            { data: 'SEND FOR QC', name: 'SEND FOR QC' },
            { data: 'HOLD', name: 'HOLD' },
            { data: 'COMPLETED', name: 'COMPLETED' },
            { data: 'COMPLETED_AVG', name: 'COMPLETED_AVG' }
        ],
        dom: 'lBfrtip',
        buttons: [
            'excel',
        ],
        lengthMenu: [10, 25, 50, 75, 100],
        order: [[0, 'asc']] // Optional: Order by the first column (Emp ID)
    });
}





</script>

@endsection


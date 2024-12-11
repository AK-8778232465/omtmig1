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
    align-items: center; 
    gap: 15px; 
}

#customfromRange label {
    font-weight: bold;
    color: #007bff;
}

#customfromRange input[type="date"] {
    border: 1px solid #007bff;
    padding: 5px;
    border-radius: 4px;
}

#customfromRange .input-group-text {
    font-weight: bold;
    color: #007bff; 
}


#customfromRange .input-wrapper {
    display: flex;
    align-items: center;
    gap: 10px;
}

#customToRange {
    display: flex;
    flex-wrap: wrap;
    align-items: center; 
    gap: 15px; 
}


#customToRange .input-group-text {
    font-weight: bold;
    color: #007bff;
}

#customToRange input[type="date"] {
    border: 1px solid #007bff;
    padding: 5px;
    border-radius: 4px;
    width: 100px;
}

    #orderwise_timetaken_datatable th {
    border: 1px solid rgb(230, 230, 230); 
}
/* // */
.tabledetails {
    overflow-x: auto; 
}

#production_datatable {
    border-collapse: collapse;
    width: 100%; 
    background-color: #f5f5f5; 
}


#production_datatable td {
    white-space: nowrap; 
    padding: 8px; 
    text-align: center; 
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
            <li id="production-report" class="report-item">Accurate - Production Report</li>
            <li id="orderInflow-report" class="report-item">Order Inflow Details</li>
            <li id="acm-report" class="report-item">ACM Report</li>
            <li id="daily-completion" class="report-item">Daily Completion Status</li>

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

<div class="d-flex">
        <div class="col-md-2" id="role_filter">
            <div class="form-group">
                <label for="role">Roles</label>
                <select class="form-control select2-basic-multiple" name="role_id" id="role_id">
                    <option selected value="">Select Role</option>
                    @forelse($roles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @empty
                    @endforelse
                </select>
            </div>
        </div>


        <div class="col-md-2" id="user_filter">
            <div class="form-group">
                <label for="user">Users</label>
                <select class="form-control select2-basic-multiple" name="user_id" id="user_id">
                    <option selected value="">Select Users</option>
                </select>
            </div>
        </div>

</div>


        <div class="row" id="hidefilter">
            <div class="col-md-4" style="width: 350px!important;" id="selected_date_range">
                <div class="form-group">
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
            <div class="col-md-2" id="client_filter">
                <div class="form-group">
                    <label for="client">Client</label>
                    <select class="form-control select2-basic-multiple" name="dcf_client_id" id="client_id_dcf">
                        <option selected value="">Select Client</option>
                        @forelse($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->client_no }} ({{ $client->client_name }})</option>
                        @empty
                        @endforelse
                    </select>
                </div>
            </div>
            <div class="col-md-2" id="client_filter_2">
                <div class="form-group">
                    <label for="client">Client</label>
                    <select class="form-control select2-basic-multiple" name="dcf_client_id_2" id="client_id_dcf_2" multiple="multiple">
                        <option selected value="">Select Client</option>
                        @forelse($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->client_no }} ({{ $client->client_name }})</option>
                        @empty
                        @endforelse
                    </select>
                </div>
            </div>

            <div class="col-md-2" id="lob_filter">
                <div class="form-group">
                    <label for="lob_id">Lob</label>
                    <select class="form-control select2-basic-multiple" style="width:100%" name="lob_id" id="lob_id">
                        <option selected value="All">All</option>
                    </select>
                </div>
            </div>

            <div class="col-md-2" id="process_filter">
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
                    <option selected value="All">All</option>
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
                                    <th width="8%">Doc Purchaser</th>
                                    <th width="8%">Ground Abstractor</th>
                                    <th width="8%">Clarification</th>
                                    <th width="8%">Typing</th>
                                    <th width="8%">Typing QC</th>
                                    <th width="8%">Send For QC</th>
                                    <th width="8%">Hold</th>
                                    <th width="8%">Partially Cancelled</th>
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
                                    <th width="11%">Product</th>
                                    <th width="8%">Order Received Date</th>
                                    <th width="8%">Production Date</th>
                                    <th width="11%">Order ID</th>
                                    <th width="11%">Client Name</th>
                                    <th width="11%">LOB</th>
                                    <th width="11%">Process</th>
                                    <th width="5%">State</th>
                                    <th width="5%">County Name</th>
                                    <th width="5%">Status Updated Date</th>
                                    <th width="11%">Status</th>
                                    <th width="11%">Status Comments</th>
                                    <th width="5%">Primary Source</th>
                                    <th width="5%">Process type</th>
                                    <th width="5%">Tier</th>
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
            <div class="card col-md-10 mt-2 tabledetails" id="production_report" style="font-size: 12px; overflow-x: auto;">
    <h4 class="text-center mt-3">Production Report</h4>
    <div class="card-body">
        <div class="p-0">
            <div style="overflow-x:auto;">
                <table id="production_datatable" class="table table-bordered" style="border-collapse: collapse; width: 100%; background-color: #f5f5f5;">
                    <thead class="text-center" style="font-size: 12px;">
                        <tr>
                            <th style="white-space: nowrap; width: 150px;">S.No</th>
                            <th style="white-space: nowrap; width: 150px;">Received EST</th>
                            <th style="white-space: nowrap; width: 100px;">Client ID</th>
                            <th style="white-space: nowrap; width: 150px;">Product</th>
                            <th style="white-space: nowrap; width: 100px;">Order Num</th>
                            <th style="white-space: nowrap; width: 100px;">State</th>
                            <th style="white-space: nowrap; width: 100px;">County</th>
                            <th style="white-space: nowrap; width: 150px;">Portal Fee Cost</th>
                            <th style="white-space: nowrap; width: 100px;">Source</th>
                            <th style="white-space: nowrap; width: 150px;">Production date</th>
                            <th style="white-space: nowrap; width: 100px;">User EMP ID</th>
                            <th style="white-space: nowrap; width: 100px;">QA EMP ID</th>
                            <th style="white-space: nowrap; width: 100px;">Typist EMP ID</th>
                            <th style="white-space: nowrap; width: 150px;">Typist QC EMP ID</th>
                            <th style="white-space: nowrap; width: 100px;">Copy Cost</th>
                            <th style="white-space: nowrap; width: 150px;">No. of Search done</th>
                            <th style="white-space: nowrap; width: 250px;">No of Documents Retrieved in TP/Other Applications</th>
                            <th style="white-space: nowrap; width: 150px;">Title Point Account</th>
                            <th style="white-space: nowrap; width: 150px;">Purchase Link</th>
                            <th style="white-space: nowrap; width: 100px;">User Name</th>
                            <th style="white-space: nowrap; width: 100px;">Password</th>
                            <th style="white-space: nowrap; width: 150px;">Completed Time (in EST)</th>
                            <th style="white-space: nowrap; width: 150px;">Search Final Status</th>
                            <th style="white-space: nowrap; width: 100px;">TAT Time</th>
                            <th style="white-space: nowrap; width: 200px;">Comments</th>
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
    <div class="card col-md-10 mt-2 tabledetails" id="orderInflow_report" style="font-size: 12px; overflow-x: auto; margin-left:250px;">
        <h4 class="text-center mt-3">Order Inflow Report</h4>
        <div class="card-body">
            <div class="p-0">
                <table id="orderInflow_report_table" class="table table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                    <thead class="text-center" style="font-size: 12px;">
                        <tr>
                            <th width="10%">Client Name</th>
                            <th width="12%">Carry Forward</th>
                            <th width="12%">Received</th>
                            <th width="12%">Completed</th>
                            <th width="12%">Pending</th>
                        </tr>
                    </thead>
                    <tbody class="text-center" style="font-size: 12px;"></tbody>
                </table>
                <h5><span style="color:red;">*</span><strong>Note:</strong> Cancelled Count is not Considered in Carry forward Count</h5>
            </div>
        </div>
    </div>

    {{-- ACM Report --}}

    <div class="card col-md-10 mt-2 tabledetails" id="acm_report" style="font-size: 12px; overflow-x: auto; margin-left:250px;">
        <h4 class="text-center mt-3">ACM Report</h4>
        <div class="card-body">
            <div class="p-0">
                <table id="acm_report_table" class="table table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                    <thead class="text-center" style="font-size: 12px;">
                        <tr>
                            <th width="10%">Emp Id</th>
                            <th width="12%">Emp Name</th>
                            <th width="12%">Role</th>
                            <th width="8%">Reporting_to</th>
                        </tr>
                    </thead>
                    <tbody class="text-center" style="font-size: 12px;"></tbody>
                </table>
            </div>
        </div>
    </div>


    {{-- Daily completion --}}

    <div class="card col-md-10 mt-2 tabledetails" id="daily_completion" style="font-size: 12px; overflow-x: auto; margin-left:250px;">
        <h4 class="text-center mt-3">Daily Completion Status</h4>
        <div class="card-body">
            <div class="p-0">
                <table id="daily_completion_table" class="table table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                    <thead class="text-center" style="font-size: 12px;">
                        <tr>
                            <th width="10%">Date</th>
                            <th width="10%">Client Code</th>
                            <th width="12%">Order Received</th>
                            <th width="12%">Yet to Assign</th>
                            <th width="8%">WIP</th>
                            <th width="8%">Coversheet Prep</th>
                            <th width="8%">Doc purchase</th>
                            <th width="8%">Clarification</th>
                            <th width="8%">Ground Abstractor</th>
                            <th width="8%">Send for QC</th>
                            <th width="8%">Typing</th>
                            <th width="8%">Typing QC</th>
                            <th width="8%">Hold</th>
                            <th width="8%">Completed</th>
                            <th width="8%">Partially Cancelled</th>
                            <th width="8%">Cancelled</th>

                        </tr>
                    </thead>
                    <tbody class="text-center" style="font-size: 12px;"></tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script src="{{asset('./assets/js/jquery.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>

<script>

$(document).ready(function() {
    $('.report-item').on('click', function() {
        if ($(this).attr('id') === 'production-report') {
            $('#client_id_dcf').val(82).trigger('change');
            $('#client_id_dcf').prop('disabled', true);
        } else {
            $('#client_id_dcf').val('').trigger('change');
            $('#client_id_dcf').prop('disabled', false);
        }
    });
});

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

     // Display the selected date filter
     dateDisplay.textContent = selectedDateFilter;

     if (selectedDateFilter && value !== 'custom') {
        // Remove the "Selected:" prefix if it exists
        let cleanedFilter = selectedDateFilter.replace("Selected: ", "").trim();

        // Split the cleaned filter into dates
        let dates = cleanedFilter.split(' to ');
        if (dates.length === 2) {
            // Convert the dates into YYYY-MM-DD format
            let fromDate = formatDate1(dates[0].trim());
            let toDate = formatDate1(dates[1].trim());

            // Set the input values
            fromDateInput.value = fromDate; // Set the start date
            toDateInput.value = toDate; // Set the end date
        }
    }
   
}

// Function to convert date to YYYY-MM-DD format
function formatDate1(dateString) {
    let [month, day, year] = dateString.split('-'); // Assuming the input is MM-DD-YYYY
    return `${year}-${month}-${day}`; // Convert to YYYY-MM-DD
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
            if (selectedClientOption === 'All') {
                $("#client_id_dcf").val('All');
            }
            isClientChanging = false;
        });

        $(document).ready(function() {
        var isLobChanging = false;
        $(document).on('change', '#lob_id', function() {
            if (isLobChanging) return;
            isLobChanging = true;
            var selectedLobOption = $(this).val();
                if (selectedLobOption === 'All') {
                    $("#lob_id").val('All');
            }
            isLobChanging = false;
        });
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
            { data: 'client_name', name: 'client_name' },
            { data: 'lob_name', name: 'lob_name' },
            { data: 'process_name', name: 'process_name' },
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
            { data: 'tier', name: 'tier' },
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

                            var headers = ["S.No", "Process", "Order Date", "Completion Date", "Order ID", "Client Name", "LOB", "Process", "Short Code", "County Name", "Date of Movement", "Status", "Status Comment", "Primary Source","Process Type","Tier", "User Emp Id", "User Emp Name", "QA Emp Id", "QA Emp Name", "QA Comments"];
                            var exportData = data.map((row, index) => [
                                index + 1,
                                row.process,
                                formatExcelDate(row.order_date),
                                formatExcelDate(row.completion_date),
                                row.order_id,
                                row.client_name,
                                row.lob_name,
                                row.process_name,
                                row.short_code,
                                row.county_name,
                                formatExcelDate(row.status_updated_time),
                                row.status,
                                row.status_comment,
                                row.primary_source,
                                row.process_name.replace(/&amp;/g, '&'),
                                row.tier,
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


    function fetchProData(client_id, product_id) {
        $.ajax({
            url: "{{ url('get_lob') }}",
            type: "POST",
            data: {
                client_id: client_id,
                product_id:product_id,
                _token: '{{ csrf_token() }}'
            },
            dataType: 'json',
            success: function (response) {
                $('#lob_id').html('<option selected value="All">All</option>');
                $('#product_id').html('<option selected value="All">All</option>');


                $.each(response.lob, function (index, item) {
                    $("#lob_id").append('<option value="' + item.id + '">' + item.name + '</option>');
                });

                $.each(response.products, function (index, item) {
                $("#product_id").append('<option value="' + item.id + '">' + item.process_name + ' (' + item.project_code + ')</option>');
                });
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error: ' + status + ' - ' + error);
            }
        });
    }

    $('#lob_id').on('change', function () {
    var lob_id = $(this).val();
    var client_id = $('#client_id_dcf').val();
    $("#process_type_id").html(''); 
    $("#product_id").html(''); 

        $.ajax({
            url: "{{ url('get_process') }}",
            type: "POST",
            data: {
                lob_id: lob_id,
                client_id: client_id,
                _token: '{{ csrf_token() }}'
            },
            dataType: 'json',
            success: function (response) {
                $('#process_type_id').html('<option selected value="All">All</option>');
                $('#product_id').html('<option selected value="All">All</option>');


                $.each(response.process, function (index, item) {
                    $("#process_type_id").append('<option value="' + item.id + '">' + item.name + '</option>');
                });

                $.each(response.products, function (index, item) {
                $("#product_id").append('<option value="' + item.id + '">' + item.process_name + ' (' + item.project_code + ')</option>');
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
            { data: 'status_15', name: 'status_15' },
            { data: 'status_18', name: 'status_18' },
            { data: 'status_14', name: 'status_14' },
            { data: 'status_16', name: 'status_16' },
            { data: 'status_17', name: 'status_17' },
            { data: 'status_4', name: 'status_4' },
            { data: 'status_2', name: 'status_2' },
            { data: 'status_20', name: 'status_20' },
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

                            var headers = ["Users", "WIP", "Coversheet Prep", "Doc Purchase", "Ground Abstractor", "Clarification", "Send For QC", "Hold", "Partially Cancelled", "Cancelled", "Completed", "All"];
                            var exportData = data.map(row => [
                                row.userinfo,
                                row.status_1,
                                row.status_13,
                                row.status_15,
                                row.status_18,
                                row.status_14,
                                row.status_4,
                                row.status_2,
                                row.status_20,
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
    let product_id = $("#product_id").val();

    $("#lob_id").html('<option selected value="All">All</option>');
    $("#product_id").html('<option selected value="All">All</option>');
    $("#process_type_id").html('<option selected value="All">All</option>');


    fetchProData(client_id,product_id);
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
            case 'production-report':
                 production_report();
                break;
            case 'orderInflow-report':
                 orderInflow_report();
                break;
            case 'daily-completion':
                 daily_completion();
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
        $('#production_report').hide();
        $('#orderInflow_report').hide();
        $('#acm_report').hide();
        $('#daily_completion').hide();






        // Show the selected table based on ID
        if (reportId === 'userwise-details') {
            $('#userwise_table').show();
            $('#datepicker').hide();
            $('#hidefilter').show();
            $('#hidefilter_2').show();
            $('#hidefilter_3').show();
            $('#role_filter').hide();
            $('#user_filter').hide();
            $('#selected_date_range').show();
            $('#client_filter').show();
            $('#lob_filter').show();
            $('#process_filter').show();
            $('#client_filter_2').hide();


        } else if (reportId === 'orderwise-details') {
            $('#newreports_table').show();
            $('#datepicker').hide();
            $('#hidefilter').show();
            $('#hidefilter_2').show();
            $('#hidefilter_3').show();
            $('#role_filter').hide();
            $('#user_filter').hide();
            $('#selected_date_range').show();
            $('#client_filter').show();
            $('#lob_filter').show();
            $('#process_filter').show();
            $('#client_filter_2').hide();


        } else if (reportId === 'ordercompletion-details') {
            $('#timetaken_table').show();
            $('#datepicker').hide();
            $('#hidefilter').show();
            $('#hidefilter_2').show();
            $('#hidefilter_3').show();
            $('#role_filter').hide();
            $('#user_filter').hide();
            $('#selected_date_range').show();
            $('#client_filter').show();
            $('#lob_filter').show();
            $('#process_filter').show();
            $('#client_filter_2').hide();


        } else if (reportId === 'orderprogress-details') {
            $('#orderwise_timetaken_table').show();
            $('#datepicker').hide();
            $('#hidefilter').show();
            $('#hidefilter_2').show();
            $('#hidefilter_3').show();
            $('#role_filter').hide();
            $('#user_filter').hide();
            $('#selected_date_range').show();
            $('#client_filter').show();
            $('#lob_filter').show();
            $('#process_filter').show();
            $('#client_filter_2').hide();



        } else if (reportId === 'attendance-details') {
            $('#attendance_report').show();
            $('#hidefilter').hide();
            $('#hidefilter_2').hide();
            $('#hidefilter_3').hide();
            $('#datepicker').show();
            $('#filterButton2').click();
            $('#role_filter').hide();
            $('#user_filter').hide();
            $('#selected_date_range').hide();
            $('#client_filter').hide();
            $('#lob_filter').hide();
            $('#process_filter').hide();
            $('#client_filter_2').hide();




        }else if (reportId === 'production-report') {
            $('#production_report').show();
            $('#hidefilter').show();
            $('#datepicker').hide();
            $('#hidefilter_2').show();
            $('#hidefilter_3').show();
            $('#role_filter').hide();
            $('#user_filter').hide();
            $('#selected_date_range').show();
            $('#client_filter').show();
            $('#lob_filter').show();
            $('#process_filter').show();
            $('#client_filter_2').hide();




		}else if (reportId === 'orderInflow-report') {
            $('#orderInflow_report').show();
            $('#selected_date_range').show();
            $('#hidefilter_3').show();
            $('#client_filter').hide();
            $('#lob_filter').hide();
            $('#process_filter').hide();
            $('#hidefilter_2').hide();
            $('#role_filter').hide();
            $('#user_filter').hide();
            $('#client_filter_2').hide();


        }else if (reportId === 'acm-report') {
            $('#acm_report').show();
            $('#selected_date_range').hide();
            $('#hidefilter_3').hide();
            $('#client_filter').hide();
            $('#lob_filter').hide();
            $('#process_filter').hide();
            $('#hidefilter_2').hide();
            $('#role_filter').show();
            $('#user_filter').show();
            $('#client_filter_2').hide();


        }else if (reportId === 'daily-completion') {
            $('#daily_completion').show();
            $('#selected_date_range').show();
            $('#hidefilter_3').show();
            $('#client_filter').hide();
            $('#lob_filter').hide();
            $('#process_filter').hide();
            $('#hidefilter_2').hide();
            $('#role_filter').hide();
            $('#user_filter').hide();
            $('#client_filter_2').show();

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
                extend: 'excel',
                action: function (e, dt, button, config) {
                    $.ajax({
                        url: "{{ route('get_timetaken') }}",
                        type: 'POST',
                        data: {
                            toDate_range: toDate,
                            fromDate_range: fromDate,
                            client_id: client_id,
                            lob_id: lob_id,
                            process_type_id: process_type_id,
                            product_id: product_id,
                            selectedDateFilter: selectedDateFilter,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            var data = response.data;

                            var headers = ["Emp Id", "Users", "Product Type", "No of Assigned Orders", "No of Completed Orders", "Total Time taken for Completed Orders", "Avg Time taken for Completed Orders"];
                            var exportData = data.map(row => [
                                row.emp_id,
                                row.Users,
                                row.Product_Type,
                                row.NO_OF_ASSIGNED_ORDERS,
                                row.NO_OF_COMPLETED_ORDERS,
                                row.TOTAL_TIME_TAKEN_FOR_COMPLETED_ORDERS,
                                row.AVG_TIME_TAKEN_FOR_COMPLETED_ORDERS,

                            ]);

                            var wb = XLSX.utils.book_new();
                            var ws = XLSX.utils.aoa_to_sheet([headers].concat(exportData));
                            XLSX.utils.book_append_sheet(wb, ws, "Time Taken Data");
                            XLSX.writeFile(wb, "order_completion_data.xlsx");
                        }
                    });
                }
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

function production_report() {
    var fromDate = $('#fromDate_range').val();
    var toDate = $('#toDate_range').val();
    let client_id = $("#client_id_dcf").val();
    let lob_id = $("#lob_id").val();
    let process_type_id = $('#process_type_id').val();
    let product_id = $('#product_id').val();
    let selectedDateFilter = $('#selectedDateFilter').val(); 
// console.log(selectedDateFilter);
    if ($.fn.DataTable.isDataTable('#production_datatable')) {
        $('#production_datatable').DataTable().clear().destroy();
    }

    var table = $('#production_datatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('production_report') }}", 
            type: 'POST',
            data: {
                fromDate_range: fromDate,
                toDate_range: toDate,
                client_id: client_id,
                lob_id: lob_id,
                process_type_id: process_type_id,
                product_id: product_id,
                selectedDateFilter: selectedDateFilter,
                _token: '{{ csrf_token() }}' 
            },
            dataSrc: function (json) {
                return json.data; 
            }
        },
        columns: [
            {
                data: null,
                title: 'S.No',
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1; // Serial number
                }
            },
            { data: 'order_date', title: 'Received EST' },
            { data: 'acc_client_id', title: 'Client ID' },
            // { data: 'process_name', title: 'Product' },
            { 
                data: 'process_name', 
                title: 'Product', 
                className: 'text-left'  // Left-align the Product column
            },
            { data: 'order_num', title: 'Order Num' },
            { data: 'short_code', title: 'State' },
            { data: 'county_name', title: 'County' },
            { data: 'portal_fee_cost', title: 'Portal Fee Cost' },
            { data: 'source_name', title: 'Source' },
            { data: 'completion_date', title: 'Production Date' },
            { data: 'assignee_empid', title: 'User EMP ID' },
            { data: 'qa_empid', title: 'QA EMP ID' },
            { data: 'typist_empid', title: 'Typist EMP ID' },
            { data: 'typist_qc_empid', title: 'Typist QC EMP ID' },
            { data: 'copy_cost', title: 'Copy Cost' },
            { data: 'no_of_search_done', title: 'No. of Search Done' },
            { data: 'no_of_documents_retrieved', title: 'No of Documents Retrieved in TP/Other Applications' },
            { data: 'title_point_account', title: 'Title Point Account' },
            {
                data: 'purchase_link',
                title: 'Purchase Link',
                render: function (data, type, row) {
                    if (data && data.trim() !== "") {
                        return `<a href="${data}" target="_blank" title="${data}">${data}</a>`; // Link for purchase
                    } else {
                        return '';
                    }
                }
            },
            { data: 'production_username', title: 'User Name' },
            { data: 'password', title: 'Password' },
            { data: 'completion_date', title: 'Completed Time (in EST)' },
            { data: 'status', title: 'Search Final Status' },
            {
                title: 'TAT Time',
                data: null,
                render: function (data, type, row) {
                    var orderDate = new Date(row.order_date);
                    var completionDate = row.completion_date ? new Date(row.completion_date) : null;

                    if (!isNaN(orderDate) && completionDate && !isNaN(completionDate)) {
                        var diffMs = completionDate - orderDate;
                        var totalSeconds = Math.floor(diffMs / 1000);

                        var hours = Math.floor(totalSeconds / 3600);
                        var minutes = Math.floor((totalSeconds % 3600) / 60);
                        var seconds = totalSeconds % 60;

                        hours = String(hours).padStart(2, '0');
                        minutes = String(minutes).padStart(2, '0');
                        seconds = String(seconds).padStart(2, '0');

                        return hours + ':' + minutes + ':' + seconds;
                    } else {
                        return '';
                    }
                }
            },
            { data: 'comment', title: 'Comments' }
        ],
        dom: 'l<"toolbar">Bfrtip', 
        buttons: [
            {
                text: 'Excel',
                action: function (e, dt, button, config) {
                    $.ajax({
                        url: "{{ route('exportProductionReport') }}",
                        type: 'POST',
                        data: {
                            fromDate_range: fromDate,
                            toDate_range: toDate,
                            client_id: client_id,
                            lob_id: lob_id,
                            process_type_id: process_type_id,
                            product_id: product_id,
                            selectedDateFilter: selectedDateFilter,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            exportToExcel(response.data); 
                        }
                    });
                }
            }
        ],
        lengthMenu: [10, 25, 50, 75, 100],
        order: [[0, 'asc']],  
        scrollX: true, 
        fixedHeader: true,  
        fixedFooter: true  
    });
}



function orderInflow_report() {
    var fromDate = $('#fromDate_range').val();
    var toDate = $('#toDate_range').val();
    $('#orderInflow_report_table').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        searching: true,
        ajax: {
            url: "{{ route('orderInflow_data') }}",
            type: 'POST',
            data: {
                toDate_range: toDate,
                fromDate_range: fromDate,
                selectedDateFilter: selectedDateFilter, // Send the selected date
                _token: '{{ csrf_token() }}'
            },
            dataSrc: function(json) {
                console.log("Backend Response:", json);

                if (json.data && typeof json.data === 'object') {
                    return Object.values(json.data);
                } else {
                    console.error('Data is not in expected format');
                    return [];
                }
            },
            error: function (xhr, status, error) {
                console.error('Error loading data: ', error);
                alert('Failed to load data. Please try again.');
            }
        },
        columns: [
            { data: 'client_name', name: 'client_name' },
            { data: 'carry_forward', name: 'carry_forward' },
            { data: 'received', name: 'received' },
            { data: 'completed', name: 'completed' },
            { data: 'pending', name: 'pending' }
        ],
        dom: 'lBfrtip',
        buttons:[
                    {
                        extend: 'excel',
                        title: 'Order_Inflow Report',  // Set the title for the exported Excel file
                    }
                ],
        lengthMenu: [10, 25, 50, 75, 100],
        order: [[0, 'asc']] // Order by client name
    });
}



function formatResponseData(response) {
    let formattedData = [];

    // Group data by date and client_name
    response.forEach(item => {
        // Find if an entry for the same date and client_name exists in the formattedData
        let existing = formattedData.find(entry => entry.date === item.date && entry.client_name === item.client_name);

        if (!existing) {
            // If no entry exists, create a new one
            formattedData.push({
                date: item.date,
                client_name: item.client_name,
                "Order Received": 0, // Initialize the "Order Received" count
                "Yet to Assign": 0,
                "WIP": 0,
                "Coversheet Prep": 0,
                "Doc Purchaser": 0,
                "Clarification": 0,
                "Ground Abstractor": 0,
                "Send for QC": 0,
                "Typing": 0,
                "Typing QC": 0,
                "Hold": 0,
                "Completed": 0,
                "Partially Cancelled": 0,
                "Cancelled": 0
            });
            existing = formattedData[formattedData.length - 1]; // Get reference to the new entry
        }

        // Increment the count for the corresponding status
        existing[item.status] += item.count;

        // Add to the "Order Received" count (sum of all statuses for this date/client)
        existing["Order Received"] += item.count;
    });

    return formattedData;
}

function daily_completion() {
    var fromDate = $('#fromDate_range').val();
    var toDate = $('#toDate_range').val();
    let client_id = $("#client_id_dcf_2").val();
    console.log(client_id);

    $.ajax({
        url: "{{ route('daily_completion') }}",
        type: 'POST',
        data: {
            toDate_range: toDate,
            fromDate_range: fromDate,
            client_id: client_id,
            selectedDateFilter: selectedDateFilter,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            let formattedData = formatResponseData(response);

            $('#daily_completion_table').DataTable({
                destroy: true,
                data: formattedData,
                columns: [
                    { data: 'date' },
                    { data: 'client_name' },
                    { data: 'Order Received' },
                    { data: 'Yet to Assign' },
                    { data: 'WIP' },
                    { data: 'Coversheet Prep' },
                    { data: 'Doc Purchaser' },
                    { data: 'Clarification' },
                    { data: 'Ground Abstractor' },
                    { data: 'Send for QC' },
                    { data: 'Typing' },
                    { data: 'Typing QC' },
                    { data: 'Hold' },
                    { data: 'Completed' },
                    { data: 'Partially Cancelled' },
                    { data: 'Cancelled' }
                ],
                processing: true,
                serverSide: false, // Local processing now
                searching: true,
                dom: 'lBfrtip',
                buttons:[
                    {
                        extend: 'excel',
                        title: 'Daily Completion_Reports',  // Set the title for the exported Excel file
                    }
                ],
                lengthMenu: [10, 25, 50, 75, 100],
                order: [[0, 'asc']]
            });

        }
    });
}



function exportToExcel(data) {
    var exportData = data.map(function (row, index) {
        return {
            "S.No": index + 1,
            "Received EST": row.order_date,
            "Client ID": row.acc_client_id,
            "Product": row.process_name,
            "Order Num": row.order_num,
            "State": row.short_code,
            "County": row.county_name,
            "Portal Fee Cost": row.portal_fee_cost,
            "Source": row.source,
            "Production Date": row.completion_date,
            "User EMP ID": row.assignee_empid,
            "QA EMP ID": row.qa_empid,
            "Typist EMP ID": row.typist_empid,
            "Typist QC EMP ID": row.typist_qc_empid,
            "Copy Cost": row.copy_cost,
            "No. of Search Done": row.no_of_search_done,
            "No of Documents Retrieved in TP/Other Applications": row.no_of_documents_retrieved,
            "Title Point Account": row.title_point_account,
            "Purchase Link": row.purchase_link,
            "User Name": row.production_username,
            "Password": row.password,
            "Completed Time (in EST)": row.completion_date,
            "Search Final Status": row.status,
            "TAT Time": calculateTatTime(row.order_date, row.completion_date),
            "Comments": row.comment
        };
    });

    var wb = XLSX.utils.book_new();
    var ws = XLSX.utils.json_to_sheet(exportData);

    XLSX.utils.book_append_sheet(wb, ws, "Production Report");

    XLSX.writeFile(wb, "Production_Report.xlsx");
}

function calculateTatTime(orderDate, completionDate) {
    var orderDateObj = new Date(orderDate);
    var completionDateObj = completionDate ? new Date(completionDate) : null;

    if (!isNaN(orderDateObj) && completionDateObj && !isNaN(completionDateObj)) {
        var diffMs = completionDateObj - orderDateObj;
        var totalSeconds = Math.floor(diffMs / 1000);

        var hours = Math.floor(totalSeconds / 3600);
        var minutes = Math.floor((totalSeconds % 3600) / 60);
        var seconds = totalSeconds % 60;

        hours = String(hours).padStart(2, '0');
        minutes = String(minutes).padStart(2, '0');
        seconds = String(seconds).padStart(2, '0');

        return hours + ':' + minutes + ':' + seconds;
    } else {
        return '';
    }
}



$(document).ready(function() {
    // When the role dropdown changes
    $('#role_id').on('change', function() {
        var roleId = $(this).val();
        console.log(roleId); // Get the selected role ID

        // Check if a role is selected
        if (roleId) {
            // Make an AJAX call to fetch users
            $.ajax({
                url: "{{ url('getUsersByRole') }}",
                type: 'POST',
                data: {
                    role_id: roleId,
                     _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    // Clear the current user dropdown
                    $('#user_id').empty();
                    $('#user_id').append('<option selected value="">Select Users</option>');

                    // Check if there are users returned
                    if (response.users.length > 0) {
                        // Loop through the users and append them to the user dropdown
                        $.each(response.users, function(index, user) {
                            $('#user_id').append('<option value="' + user.id + '">' + user.username + '</option>');
                        });
                    } else {
                        $('#user_id').append('<option value="">No users found</option>');
                    }
                },
                error: function() {
                    alert('Error fetching users');
                }
            });
        } else {
            // If no role is selected, clear the user dropdown
            $('#user_id').empty();
            $('#user_id').append('<option selected value="">Select Users</option>');
        }
    });
});


$('#user_id').on('change', function() {
    var userId = $(this).val();
    console.log(userId);  // Get the selected user_id

    // Check if a user is selected
    if (userId) {
        // Make an AJAX request to fetch user data
        $.ajax({
            url: "{{ route('getUserData') }}",  // Route to the Laravel controller
            type: 'POST',
            data: {
                user_id: userId,  // Send the selected user_id
                _token: '{{ csrf_token() }}'  // CSRF token for security
            },
            success: function(response) {
                if (response.users && response.users.length > 0) {
                    // Initialize DataTable with the response data
                    $('#acm_report_table').DataTable({
                        destroy: true, // To reset the table on every new selection
                        data: response.users,
                        columns: [
                            { data: 'emp_id', title: 'Emp Id' },
                            { data: 'username', title: 'Emp Name' },
                            { data: 'role', title: 'Role' },
                            { data: 'reporting_to_username', title: 'Reporting_to', defaultContent: 'N/A' }
                        ],
                        dom: 'lBfrtip',  // Add the Excel button functionality
                        buttons: ['excel'],  // Excel export button
                        lengthMenu: [10, 25, 50, 75, 100],
                        order: [[0, 'asc']],  // Sort by Emp Id (or adjust as needed)
                    });
                }else {
                    // If no users, clear the table
                    var table = $('#acm_report_table').DataTable();
                    table.clear(); // Remove all data
                    table.draw();  // Redraw the table to reflect the empty state
                }
            },

        });
    }
});

</script>

@endsection


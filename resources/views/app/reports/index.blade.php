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
    color: White;
}

.report-item.active:hover {
    background-color: #28a745;
    color: White;
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


/* Apply a fixed table layout for consistent column width */
#daily_completion_table {
    table-layout: fixed;  /* Ensure that the table respects fixed column widths */
    width: 100%;
}

/* Remove padding and margin between the first two columns to avoid unwanted spacing */
#daily_completion_table th,
#daily_completion_table td {
    padding: 8px;  /* Standard padding */
    margin: 0;  /* Ensure no margin */
    border: 1px solid #ddd;  /* Tight border between cells */
}

/* Ensure that the first two columns (Date and Client Code) have no space between them */
#daily_completion_table th:nth-child(1),
#daily_completion_table td:nth-child(1),
#daily_completion_table th:nth-child(2),
#daily_completion_table td:nth-child(2) {
    padding-left: 8px;   /* Padding to align text */
    padding-right: 8px;
    margin-left: 0;
    margin-right: 0;
}

/* Fix the first column (Date) with sticky positioning */
#daily_completion_table th:nth-child(1),
#daily_completion_table td:nth-child(1) {
    position: sticky;
    left: 0%;   /* Stick it to the left side */
    background-color: #fff;  /* Match the background color */
    z-index: 1;  /* Ensure it stays above other content */
    width: 10%;  /* Fix the width of the first column */
}

/* Fix the second column (Client Code) with sticky positioning right next to the first column */
#daily_completion_table th:nth-child(2),
#daily_completion_table td:nth-child(2) {
    position: sticky;
    left: 12%;  /* Position this column right next to the first column */
    background-color: #fff;  /* Match the background color */
    z-index: 1;  /* Ensure it stays below the first column */
    width: 10%;  /* Fix the width of the second column */
}

/* Ensure no extra space between the first two columns */
#daily_completion_table th:nth-child(1),
#daily_completion_table td:nth-child(1),
#daily_completion_table th:nth-child(2),
#daily_completion_table td:nth-child(2) {
    border-right: 1px solid #ddd;  /* Tighten borders between the first and second columns */
}

/* Optional: Add a subtle shadow effect for visual separation */
#daily_completion_table th:nth-child(1),
#daily_completion_table td:nth-child(1),
#daily_completion_table th:nth-child(2),
#daily_completion_table td:nth-child(2) {
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
}

/* Apply a fixed width for the first two columns to prevent shifting */
#daily_completion_table th:nth-child(1),
#daily_completion_table td:nth-child(1) {
    width: 10%;  /* Fix the width of the first column */
}

#daily_completion_table th:nth-child(2),
#daily_completion_table td:nth-child(2) {
    width: 10%;  /* Fix the width of the second column */
}

/* Prevent column width changes for the rest of the columns */
#daily_completion_table th:nth-child(n+3),
#daily_completion_table td:nth-child(n+3) {
    width: 8%;  /* Set fixed widths for other columns */
}

/* Optional: Tighten the overall border for better alignment */
#daily_completion_table th,
#daily_completion_table td {
    border-left: 1px solid #ddd;
    border-right: 1px solid #ddd;
}


/* Make all table rows white, including the table header */
#daily_completion_table th,
#daily_completion_table tbody tr {
    background-color: white !important;  /* Set header and row backgrounds to white */
    color: #333;  /* Dark text for good contrast */
}

/* Remove hover effects for rows */
#daily_completion_table tbody tr:hover {
    background-color: white !important;  /* Ensure hover rows are also white */
}

/* Optional: Set header styling to match the white background */
#daily_completion_table th {
    background-color: white !important;  /* Ensure the headers are white */
    color: #333;  /* Set text color to dark for readability */
    border-bottom: 2px solid #ddd;  /* Light border under the header */
}

/* Optional: Remove any default row striping (even/odd) */
#daily_completion_table tbody tr.odd,
#daily_completion_table tbody tr.even {
    background-color: white !important;  /* Remove zebra striping */
}

/* Remove any background color from rows with hover */
#daily_completion_table tbody tr:hover {
    background-color: white !important;  /* Ensure hover does not change the row color */
}


.highlight-container {
        text-align: center;
        margin: 10px;
        width: 120px;
        white-space: nowrap;
    }

    .highlight-text {
        display: block;
        font-weight: bold;
        color: #fff;
        background-color: #959796; /* Green color for the text */
        padding: 3px 6px;
        border-radius: 4px;
        font-size: 11px; /* Reduced font size for the text */
    }

    .highlight-count {
        display: block;
        font-weight: bold;
        color: #121213; /* Blue color for the count */
        font-size: 11px; /* Reduced font size for the count */
    }


    /* CSS for Loader Spinner */
#loader {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 9999;
    text-align: center;
}

.loader {
    border: 8px solid #f3f3f3; /* Light gray background */
    border-top: 8px solid #3498db; /* Blue spinner color */
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 2s linear infinite; /* Infinite spin animation */
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.dataTables_scrollBody::-webkit-scrollbar,   
.tabledetails::-webkit-scrollbar {
    height: 8px; 
    background-color: #F5F5F5;
}
.dataTables_scrollBody::-webkit-scrollbar-track,
.tabledetails::-webkit-scrollbar-track {
    background-color: #F5F5F5;
    border-radius: 10px;        
    -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
}
.dataTables_scrollBody::-webkit-scrollbar-thumb,
.tabledetails::-webkit-scrollbar-thumb {
    background-color: #AAA;   
    border-radius: 10px;        
    background-image: -webkit-linear-gradient(0deg, 
                                              rgba(255, 255, 255, 0.5) 25%,
                                              transparent 25%,
                                              transparent 50%,
                                              rgba(255, 255, 255, 0.5) 50%,
                                              rgba(255, 255, 255, 0.5) 75%,
                                              transparent 75%,
                                              transparent); 
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
            <div class="col-md-3 mr-2" style="width: 350px!important;" id="selected_date_range">
                <div class="form-group">
                    <label for="dateFilter" required>Selected received date range:</label>
                    <select class="form-control" style=" width: 310px !important; " id="dateFilter" onchange="selectDateFilter(this.value)">
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
            <div class="col-md-2 mt-3 pr-2 pt-2 " id="hidefilter_4">
                 <button type="submit" id="filterButton" class="btn btn-primary in-order">Filter</button>
            </div>
            
            <div class="col-md-2 mr-3" id="client_filter">
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
            <div class="col-md-2 mr-3" id="client_filter_2">
                <div class="form-group">
                    <label for="client">Client</label>
                    <select class="form-control select2-basic-multiple" name="dcf_client_id_2" id="client_id_dcf_2">
                        <option selected value="All">Select Client</option>
                        @forelse($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->client_no }} ({{ $client->client_name }})</option>
                        @empty
                        @endforelse
                    </select>
                </div>
            </div>

            <div class="col-md-2 mr-3" id="lob_filter">
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
                    <div class="tabledetails">
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
                                    <th width="11%">Typist Id</th>
                                    <th width="11%">Typist Name</th>
                                    <th width="11%">Typist_QC Id</th>
                                    <th width="11%">Typist_QC Name</th>

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
                    <div class="tabledetails">
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
  <div class="card col-md-10 mt-2" id="production_report" style="font-size: 12px;">
    <h4 class="text-center mt-3">Production Report</h4>
    <div class="card-body">
        <div class="p-0">
            <div class="tabledetails">
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
            <div class="card col-md-10 mt-2 tabledetails" id="orderInflow_report" style="font-size: 12px; overflow-x: auto;">
        <h4 class="text-center mt-3">Order Inflow Report</h4>
        <div class="card-body ">
            <div class="p-0 tabledetails">
                <table id="orderInflow_report_table" class="table table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                    <thead class="text-center" style="font-size: 12px;">
                        <tr>
                            <th width="10%">Client Name</th>
                            <th width="12%">Carry Forward</th>
                            <th width="12%">Received</th>
                            <th width="12%">Completed</th>
                            <th width="12%">Pending</th>
                            <th width="12%">Cancelled</th>
                            <th width="12%">Partially Cancelled</th>
                        </tr>
                    </thead>
                    <tbody class="text-center" style="font-size: 12px;"></tbody>
                </table>
                <h5><span style="color:red;">*</span><strong>Note:</strong> Cancelled Count is not Considered in Carry forward Count</h5>
            </div>
        </div>
    </div>

    {{-- ACM Report --}}

            <div class="card col-md-10 mt-2 tabledetails" id="acm_report" style="font-size: 12px; overflow-x: auto;">
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

    <div class="card col-md-10 mt-2 " id="daily_completion" style="font-size: 12px;">
        <h4 class="text-center mt-3">Daily Completion Status</h4>
        <div class="card shadow" style="width: 100%; margin: 20px;">
            <div class="card-body">
                <h5 class="card-title ml-3" style="font-weight:bold;">Order Summary:</h5>

                <!-- First Row for all the buttons -->
                <div class="d-flex flex-wrap">
                    <div class="highlight-container">
                        <span class="highlight-text">Orders Received</span>
                        <span id="total_orders_sum" class="highlight-count">0</span>
                    </div>
                    <div class="highlight-container">
                        <span class="highlight-text">Yet to Assign</span>
                        <span id="yet_to_Assign_sum" class="highlight-count">0</span>
                    </div>
                    <div class="highlight-container">
                        <span class="highlight-text">WIP</span>
                        <span id="wip_sum" class="highlight-count">0</span>
                    </div>
                    <div class="highlight-container">
                        <span class="highlight-text">Coversheet Prep</span>
                        <span id="coversheet_sum" class="highlight-count">0</span>
                    </div>
                    <div class="highlight-container">
                        <span class="highlight-text">Doc Purchase</span>
                        <span id="doc_purchase_sum" class="highlight-count">0</span>
                    </div>
                    <div class="highlight-container">
                        <span class="highlight-text">Clarification</span>
                        <span id="clarification_sum" class="highlight-count">0</span>
                    </div>
                    <div class="highlight-container">
                        <span class="highlight-text">Ground Abstractor</span>
                        <span id="ground_sum" class="highlight-count">0</span>
                    </div>
                    <div class="highlight-container">
                        <span class="highlight-text">Send for QC</span>
                        <span id="send_qc_sum" class="highlight-count">0</span>
                    </div>
                    <div class="highlight-container">
                        <span class="highlight-text">Typing</span>
                        <span id="typing_sum" class="highlight-count">0</span>
                    </div>
                    <div class="highlight-container">
                        <span class="highlight-text">Typing QC</span>
                        <span id="typing_qc_sum" class="highlight-count">0</span>
                    </div>
                    <div class="highlight-container">
                        <span class="highlight-text">Hold</span>
                        <span id="hold_sum" class="highlight-count">0</span>
                    </div>

                    <div class="highlight-container">
                        <span class="highlight-text">Completed</span>
                        <span id="completed_sum" class="highlight-count">0</span>
                    </div>
                    <div class="highlight-container">
                        <span class="highlight-text">Partially Cancelled</span>
                        <span id="partially_can_sum" class="highlight-count">0</span>
                    </div>
                    <div class="highlight-container">
                        <span class="highlight-text">Cancelled</span>
                        <span id="cancelled_sum" class="highlight-count">0</span>
                    </div>
                    <div class="highlight-container">
                        <span class="highlight-text">Pending</span>
                        <span id="pending_sum" class="highlight-count">0</span>
                </div>
                </div>
            </div>
        </div>




        <div class="card-body">
            <div class="p-0 tabledetails">
                <table id="daily_completion_table" class="table table-striped table-bordered">
                    <thead class="text-center" style="font-size: 12px;">
                        <tr>
                            <th style="white-space: nowrap; width:150px">Date</th>
                            <th style="white-space: nowrap; width: 150px">Client Code</th>
                            <th style="white-space: nowrap; width: 150px;">Order Received</th>
                            <th style="white-space: nowrap; width: 150px;">Yet to Assign</th>
                            <th style="white-space: nowrap; width: 150px;">WIP</th>
                            <th style="white-space: nowrap; width: 150px;">Coversheet Prep</th>
                            <th style="white-space: nowrap; width: 150px;">Doc purchase</th>
                            <th style="white-space: nowrap; width: 150px;">Clarification</th>
                            <th style="white-space: nowrap; width: 150px;">Ground Abstractor</th>
                            <th style="white-space: nowrap; width: 150px;">Send for QC</th>
                            <th style="white-space: nowrap; width: 150px;">Typing</th>
                            <th style="white-space: nowrap; width: 150px;">Typing QC</th>
                            <th style="white-space: nowrap; width: 150px;">Hold</th>
                            <th style="white-space: nowrap; width: 150px;">Completed</th>
                            <th style="white-space: nowrap; width: 150px;">Partially Cancelled</th>
                            <th style="white-space: nowrap; width: 150px;">Cancelled</th>
                            <th style="white-space: nowrap; width: 150px;">Pending</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <!-- Data will be populated dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
        </div>
    </div>


    <!-- Modal -->
    <!-- Modal to display order details -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1"  aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title" id="orderDetailsModalLabel">Order Details</h5>
                    <button id="exportModalData" class="btn" style="background-color: #28a745; color: white;">Export to Excel</button>
                </div>
                <div class="modal-body">
                <table class= "table table-bordered display" id="orderDetailsTable">
                        <thead>
                            <tr>
                            <th>Order Date</th>
                                <th>Order ID</th>
                                <th>Client</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                    <tbody id="orderDetailsBody">
                            <!-- Static or Dynamic order details will be appended here -->
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- Loading spinner (Initially hidden) -->
<div id="loader" style="display: none;">
    <div class="loader"></div>
    <div class="loading-text" style="font-size:14px;font-weight:bold;">Loading...</div>
</div>


<script src="{{asset('./assets/js/jquery.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>


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

    $(document).ready(function() {
        var isClientChanging = false;
        $(document).on('change', '#client_id_dcf_2', function() {
            if (isClientChanging) return;
            isClientChanging = true;
            var selectedClientOption = $(this).val();
            if (selectedClientOption === 'All') {
                $("#client_id_dcf_2").val('All');
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
            { data: 'typist_emp_id', name: 'typist_emp_id' },
            { data: 'typist_emp_name', name: 'typist_emp_name' },

            { data: 'typist_qc_emp_id', name: 'typist_qc_emp_id' },
            { data: 'typist_qc_emp_name', name: 'typist_qc_emp_name' },
            





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

                            // Define headers with the "Received EST" column next to "Order Date"
                            var headers = ["S.No", "Process", "Order Date", "Received EST", "Completion Date", "Order ID", "Client Name", "LOB", "Process", "Short Code", "County Name", "Date of Movement", "Status", "Status Comment", "Primary Source", "Process Type", "Tier", "User Emp Id", "User Emp Name", "QA Emp Id", "QA Emp Name", "QA Comments", "Typist Id", "Typist Name", "Typist_QC Id", "Typist_QC Name"];

                            // Add the "Received EST" column (next to "Order Date") with only the date (no time)
                            var exportData = data.map((row, index) => [
                                index + 1,
                                row.process,
                                formatExcelDate(row.order_date),
                                formatDateOnly(row.order_date), // Received EST (date part only)
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
                                row.typist_emp_id,
                                row.typist_emp_name,
                                row.typist_qc_emp_id,
                                row.typist_qc_emp_name,



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

function formatDateOnly(datetime) {
    if (!datetime) return '';
    var date = new Date(datetime);
    return date.toLocaleDateString(); // Format the date (e.g., "MM/DD/YYYY")
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

    $('#lob_id').on('change', function () {
    var lob_id = $(this).val();
    var client_id = $('#client_id_dcf_2').val();
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

    $('#process_type_id').on('change', function () {
    var process_type_id = $(this).val();
    var client_id = $('#client_id_dcf_2').val();
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

$('#client_id_dcf_2').on('change', function () {
    let client_id = $("#client_id_dcf_2").val();
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
    $('#client_id_dcf_2').on('change', function () {
       let getproject_id = $("#client_id_dcf_2").val();
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
            $('#hidefilter_4').hide();


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
            $('#hidefilter_4').hide();


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
            $('#hidefilter_4').hide();


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
            $('#hidefilter_4').hide();



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
            $('#hidefilter_4').hide();




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
            $('#hidefilter_4').hide();




		}else if (reportId === 'orderInflow-report') {
            $('#orderInflow_report').show();
            $('#selected_date_range').show();
            $('#hidefilter_3').hide();
            $('#client_filter').hide();
            $('#lob_filter').hide();
            $('#process_filter').hide();
            $('#hidefilter_2').hide();
            $('#role_filter').hide();
            $('#user_filter').hide();
            $('#client_filter_2').hide();            
            $('#hidefilter_4').show();


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
            $('#hidefilter_4').hide();


        }else if (reportId === 'daily-completion') {
            $('#daily_completion').show();
            $('#selected_date_range').show();
            $('#hidefilter_3').show();
            $('#client_filter').hide();
            $('#lob_filter').show();
            $('#process_filter').show();
            $('#hidefilter_2').show();
            $('#role_filter').hide();
            $('#user_filter').hide();
            $('#client_filter_2').show();
            $('#hidefilter_4').hide();

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


// function orderTimeTaken_datatable() {
//     var fromDate = $('#fromDate_range').val();
//     var toDate = $('#toDate_range').val();
//     let client_id = $("#client_id_dcf").val();
//     let lob_id = $("#lob_id").val();
//     let process_type_id = $('#process_type_id').val();
//     let product_id = $('#product_id').val();

//     $('#orderwise_timetaken_datatable').DataTable({
//         destroy: true,
//         processing: true,
//         serverSide: true,
//         ajax: {
//             url: "{{ route('orderTimeTaken') }}",
//             type: 'POST',
//             data: {
//                 toDate_range: toDate,
//                 fromDate_range: fromDate,
//                 client_id: client_id,
//                 lob_id: lob_id,
//                 process_type_id : process_type_id,
//                 product_id: product_id,
//                 selectedDateFilter: selectedDateFilter,
//                 _token: '{{ csrf_token() }}'
//             },
//             dataSrc: function (json) {
//                 if (Array.isArray(product_id) && product_id.includes('All')) {
//                     json.data.forEach(function (item) {
//                         item.Product_Type = 'All  Products';
//                     });
//                 } else if (product_id === 'All') {
//                     json.data.forEach(function (item) {
//                         item.Product_Type = 'All';
//                     });
//                 }
//                 return json.data; 
//             },
//         },
//         columns: [
//             { data: 'Emp ID', name: 'Emp ID', class: 'text-left' },
//             { data: 'Users', name: 'Users', class: 'text-left' },
//             { data: 'Product_Type', name: 'Product_Type' },
//             { data: 'Assigned Orders', name: 'Assigned Orders' },
//             { data: 'WIP.count', name: 'WIP.count' },
//             { data: 'WIP.time', name: 'WIP.time' },
//             { data: 'COVERSHEET PRP.count', name: 'COVERSHEET PRP.count' },
//             { data: 'COVERSHEET PRP.time', name: 'COVERSHEET PRP.time' },
//             { data: 'CLARIFICATION.count', name: 'CLARIFICATION.count' },
//             { data: 'CLARIFICATION.time', name: 'CLARIFICATION.time' },
//             { data: 'SEND FOR QC.count', name: 'SEND FOR QC.count' },
//             { data: 'SEND FOR QC.time', name: 'SEND FOR QC.time' },
//         ],
//         dom: 'lBfrtip',
//         buttons: [
//             {
//                 extend: 'excelHtml5',
//                 title: 'orderprogress details'  // Set the filename here
//             }
//         ],
//         lengthMenu: [10, 25, 50, 75, 100],
//         order: [[0, 'asc']]
//     });
// }


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
                process_type_id: process_type_id,
                product_id: product_id,
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
                title: 'Emp Details',
                customize: function (xlsx) {
                    var sheet = xlsx.xl.worksheets['sheet1.xml'];
                    var sheetData = sheet.getElementsByTagName('sheetData')[0];

                    // Add the grouped header row
                    var groupedHeaderRow = `
                        <row r="1">
                            <c t="inlineStr" s="2" r="A1"><is><t>Emp Details</t></is></c>
                            <c t="inlineStr" s="2" r="A1"><is><t>Emp Details</t></is></c>
                            <c t="inlineStr" s="2" r="E1"><is><t>WIP</t></is></c>
                            <c t="inlineStr" s="2" r="G1"><is><t>Coversheet Prep</t></is></c>
                            <c t="inlineStr" s="2" r="I1"><is><t>Clarification</t></is></c>
                            <c t="inlineStr" s="2" r="K1"><is><t>Send for QC</t></is></c>
                        </row>
                    `;
                    sheetData.innerHTML = groupedHeaderRow + sheetData.innerHTML;

                    // Add merge cells
                    var mergeCells = `
                        <mergeCell ref="A1:D1"/> <!-- Emp Details -->
                        <mergeCell ref="E1:F1"/> <!-- WIP -->
                        <mergeCell ref="G1:H1"/> <!-- Coversheet Prep -->
                        <mergeCell ref="I1:J1"/> <!-- Clarification -->
                        <mergeCell ref="K1:L1"/> <!-- Send for QC -->
                    `;

                    // Add alignment styles to the styles.xml
                    var styles = xlsx.xl['styles.xml'];
                    var cellXfs = styles.getElementsByTagName('cellXfs')[0];

                    // Add a new style for bold and center-aligned text
                    var boldCenteredStyle = `
                        <xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1">
                            <alignment horizontal="center" vertical="center"/>
                        </xf>
                    `;
                    cellXfs.innerHTML += boldCenteredStyle;

                    // Update the styles attribute in the cells
                    var mergeCellsContainer = sheet.getElementsByTagName('mergeCells')[0];
                    if (!mergeCellsContainer) {
                        mergeCellsContainer = document.createElement('mergeCells');
                        mergeCellsContainer.setAttribute('count', '0');
                        sheet.getElementsByTagName('worksheet')[0].appendChild(mergeCellsContainer);
                    }
                    mergeCellsContainer.innerHTML = mergeCells + mergeCellsContainer.innerHTML;

                    // Update the count of merge cells
                    var totalMergeCells = mergeCellsContainer.getElementsByTagName('mergeCell').length;
                    mergeCellsContainer.setAttribute('count', totalMergeCells.toString());
                }
            },
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
            { data: 'order_date', title: 'Received EST',render: function (data, type, row) {
                return moment(data).format('MM/DD/YYYY HH:mm:ss'); // Format the date and time using moment.js
            }},
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
    var selectedDateFilter = $('#selectedDateFilter').val();

    $('#orderInflow_report_table').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        searching: true,
    pageLength: 10,
        ajax: {
            url: "{{ route('orderInflow_data') }}",
            type: 'POST',
        data: function (d) {
                return {
            fromDate_range: fromDate,
                toDate_range: toDate,
                selectedDateFilter: selectedDateFilter, // Send the selected date
                search_value: d.search.value,
                start: d.start,
                length: d.length,
                _token: '{{ csrf_token() }}'
                };
            },
            dataSrc: function(json) {
            return json.data;
            }
        },
        columns: [
            { data: 'client_name', name: 'client_name' },
            { data: 'carry_forward', name: 'carry_forward' },
            { data: 'received', name: 'received' },
            { data: 'completed', name: 'completed' },
            { data: 'pending', name: 'pending' },
            { data: 'cancelled', name: 'cancelled' },
        { data: 'partially_cancelled', name: 'partially_cancelled' }


        ],
        dom: 'lBfrtip',
        buttons:[
                    {
                        extend: 'excel',
                action: function (e, dt, button, config) {
                $('#loader').show();
            $.ajax({
                url: "{{ route('orderInflow_export') }}",
                type: 'POST',
                data: {
                    fromDate_range: fromDate,
                    toDate_range: toDate,
                    selectedDateFilter: selectedDateFilter,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    var data = response.data;

                    // Define headers with the "Received EST" column next to "Order Date"
                    var headers = ["S.No", "Client Name", "Carry Forward", "Received", "Completed", "Pending", "Cancelled"];

                    // Map the response data into a format suitable for export
                    var exportData = data.map((row, index) => [
                        index + 1,              // Serial number (S.No)
                        row.client_name,        // Client Name
                        row.carry_forward,      // Carry Forward
                        row.received,           // Received
                        row.completed,          // Completed
                        row.pending,            // Pending
                        row.cancelled           // Cancelled
                    ]);

                    // Create a new workbook
                    var wb = XLSX.utils.book_new();

                    // Convert the data into a worksheet and append it to the workbook
                    var ws = XLSX.utils.aoa_to_sheet([headers].concat(exportData));

                    // Append the worksheet to the workbook
                    XLSX.utils.book_append_sheet(wb, ws, "Order Data");

                    // Generate and download the Excel file
                    XLSX.writeFile(wb, "Order_Inflow.xlsx");
                    $('#loader').hide();
                }
            });

            }
                    }
                ],
    order: [[0, 'asc']],
    });
}



function exportToExcel(data) {
    var exportData = data.map(function (row, index) {
        return {
            "S.No": index + 1,
            "Received EST": row.order_date,
            "Date of order": formatDateOnly(row.order_date),
            "Client ID": row.acc_client_id,
            "Product": row.process_name,
            "Order Num": row.order_num,
            "State": row.short_code,
            "County": row.county_name,
            "Portal Fee Cost": row.portal_fee_cost,
            "Source": row.source_name,
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

function daily_completion() {
    $('#loader').show();  // Display the loader

    var fromDate = $('#fromDate_range').val();
    var toDate = $('#toDate_range').val();
    let client_id = $("#client_id_dcf_2").val();
    var lob_id = $('#lob_id').val();
    var process_type_id = $('#process_type_id').val();
    let product_id = $("#product_id").val();
    let selectedDateFilter = $("#selectedDateFilter").val();  // Assuming this is an input value

    $.ajax({
        url: "{{ route('daily_completion') }}",
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
            const summaryCount = response.summaryCount;

            // Display the summary count values
            $('#yet_to_Assign_sum').text(summaryCount['Yet to Assign']);
            $('#wip_sum').text(summaryCount['WIP']);
            $('#coversheet_sum').text(summaryCount['Coversheet Prep']);
            $('#doc_purchase_sum').text(summaryCount['Doc Purchaser']);
            $('#clarification_sum').text(summaryCount['Clarification']);
            $('#ground_sum').text(summaryCount['Ground Abstractor']);
            $('#send_qc_sum').text(summaryCount['Send for QC']);
            $('#typing_sum').text(summaryCount['Typing']);
            $('#typing_qc_sum').text(summaryCount['Typing QC']);
            $('#hold_sum').text(summaryCount['Hold']);
            $('#completed_sum').text(summaryCount['Completed']);
            $('#partially_can_sum').text(summaryCount['Partially Cancelled']);
            $('#cancelled_sum').text(summaryCount['Cancelled']);
            $('#pending_sum').text(response.pendingCount);

            // Optionally, display the total orders count
            $('#total_orders_sum').text(response.totalOrders);



            const statuses = response.statuses;  // {1: "WIP", 2: "Hold", ...}
            const counts = response.counts;  // { "2024-12-19": { "82": { ... } }, ... }


            // Prepare the data for the table
            const tableData = [];

            // Loop through the dates in the response counts
            for (let date in counts) {
                for (let clientId in counts[date]) {
                    let clientData = counts[date][clientId];
                    let client_code = clientData[Object.keys(clientData)[0]].client_code;

                    let rowData = { date: date, client_code: client_code };

                    // Add the "Yet to Assign" count
                    rowData['Yet to Assign'] = 0;  // Initialize "Yet to Assign" count
                    rowData['Pending'] = 0;
                    rowData['Order Received'] = 0;


                    // Initialize all status counts to 0
                    for (let statusId in statuses) {
                        rowData[statuses[statusId]] = 0;  // Default count is 0
                    }

                    // Add count_html for each status (make sure it's the clickable link)
                    for (let statusName in clientData) {
                        if (statusName !== 'client_code') {
                            let statusData = clientData[statusName];
                            if (statusData.count_html) {
                                rowData[statusName] = statusData.count_html;  // Use the HTML from the controller
                            } else {
                                rowData[statusName] = statusData.count;  // Otherwise, just show the count
                            }
                        }
                    }

                    tableData.push(rowData);
                }
            }

            // Initialize or reinitialize the DataTable with the new data
            $('#daily_completion_table').DataTable({
                destroy: true,   // Destroy the previous DataTable instance
                data: tableData,
                columns: [
                    { data: 'date' },
                    { data: 'client_code' },
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
                    { data: 'Cancelled' },
                    { data: 'Pending' }
                ],
                paging: true,
                searching: true,
                ordering: true,
                info: true,
                lengthChange: true,
                autoWidth: false,
                dom: 'lBfrtip',  // 'B' adds the buttons section to the table
                buttons: [
                    {
                        extend: 'excel',  // Enable Excel export button
                        title: 'Daily_Completion_Report',  // Title for the exported Excel file
                    }
                ]
            });

            // Attach click event to the order-link to capture the order_ids
// This will be used to store the selected order IDs
let selectedOrderIds = [];

$(document).on('click', '.order-link', function() {
    // Capture the order IDs from the clicked row and store them
                let orderIds = $(this).data('order-ids');  // Get the order IDs from the clicked link

    // If orderIds is a string, split it into an array
    if (typeof orderIds === 'string') {
        selectedOrderIds = orderIds.split(','); // Assuming comma-separated order IDs
    } else {
        selectedOrderIds = [orderIds]; // In case it's just a single ID
    }
});

$(document).on('click', '#exportModalData', function() {
    if (selectedOrderIds.length === 0) {
        alert("Please select at least one order to export.");
        return;
    }

    // Define the headers for the Excel sheet
    var headers = ['Date', 'Order ID', 'Client', 'Status'];

    // Perform an AJAX request to get the order details using the selected order IDs
    $.ajax({
        url: 'fetch_order_details',  // Adjust the URL accordingly
        method: 'POST',
        data: {
            order_ids: selectedOrderIds.join(','),  // Send the order IDs as a comma-separated string
            page: 1,          // Current page
            length: 100,      // Number of records per page
            search_value: '',
            export: true,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            var orders = response.orders;

            // Prepare table data array with headers
            var tableData = [headers];

            // Loop through the orders and push them to tableData
            orders.forEach(function(order) {
                var row = [
                    order.order_date,  // Date
                    order.order_id,    // Order ID
                    order.client_name, // Client
                    order.status       // Status
                ];

                tableData.push(row); // Add the order's data to the table data
            });

            // Create a worksheet from the table data
            var ws = XLSX.utils.aoa_to_sheet(tableData);

            // Create a workbook and add the worksheet
            var wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Order Details");

            // Trigger download of the Excel file
            XLSX.writeFile(wb, "Order_Details.xlsx");
        },
        error: function(xhr, status, error) {
            console.error("Error fetching data:", error);
        }
    });
            });
            $('#loader').hide();
        },
        error: function(xhr, status, error) {
            console.error("An error occurred: ", error);
            $('#loader').hide();
        }
    });
}




let currentPage = 1; // Start on page 1
let orderIds = [];    // This should be set dynamically based on the clicked order link
let tableInstance = null;  // To hold the DataTable instance
let fetchingData = false;  // To prevent multiple AJAX calls while the previous one is still in progress

$(document).on('click', '.order-link', function () {
    // Get the order IDs from the clicked link's data attribute
    orderIds = $(this).data('order-ids');
    currentPage = 1; // Reset to first page when a new order link is clicked

    // Send AJAX request to fetch the details of the orders
    fetchOrderDetails(currentPage);
});

// Function to fetch order details for the current page
function fetchOrderDetails(page) {
    // Prevent multiple AJAX requests if one is already in progress
    if (fetchingData) return;

    fetchingData = true;  // Set flag to prevent further requests until the current one completes

    $.ajax({
        url: "{{ route('fetch_order_details') }}",  // Your route to fetch order details
        type: 'POST',
        data: {
            order_ids: orderIds,  // Pass the order_ids
            page: page,
            _token: '{{ csrf_token() }}'          
        },
        success: function (response) {
            console.log(response);  // Log the response to check the structure

            // Check if the response is an array (the expected format for DataTable)
            if (!$('#orderDetailsModal').hasClass('show')) {
                $('#orderDetailsModal').modal('show');
            }

            $('#orderDetailsModal').on('shown.bs.modal', function () {
                // You can perform additional actions here when the modal is fully shown
                console.log("Order Details Modal is now visible.");

                // Example: If you need to focus on the first input inside the modal after it's shown:
                $('#orderDetailsModal input:first').focus();
            });

            // If DataTable already exists, clear the existing data and update the table
            if (tableInstance) {
            // Clear existing data and append new rows
                tableInstance.clear();
                tableInstance.rows.add(response.orders);  // Add new rows
                tableInstance.draw();  // Redraw the table

                // Manually set the current page after loading new data
                tableInstance.page(currentPage - 1).draw('page');
        } else {
                // Show the modal first, then initialize the DataTable
                    tableInstance = $('#orderDetailsTable').DataTable({
                    data: response.orders,  // Pass the order details from the server
                        columns: [
                            { data: 'order_date', title: 'Order Date' },
                            { data: 'order_id', title: 'Order ID' },
                            { data: 'client_name', title: 'Client' },
                            { data: 'status', title: 'Status' },  // You can replace this with actual status name if needed
                        ],
                    paging: true,     // Enable pagination in DataTable
                        searching: true, // Enable search functionality
                        ordering: true,  // Enable column sorting
                    info: true,       // Show table info
                    processing: true, // Show processing indicator
                    serverSide: true, // Enable server-side processing
                    pageLength: 10,   // Set default page length
                    lengthMenu: [10, 25, 50, 100,1000], // Define available page lengths
                    ajax: {
                        url: "{{ route('fetch_order_details') }}",
                        type: "POST",
                        data: function(d) {
                            // Add the orderIds, page number, and CSRF token to the AJAX request
                            d.order_ids = orderIds;
                            d.page = Math.ceil(d.start / d.length) + 1;
                            d.search_value = d.search.value;
                            d._token = '{{ csrf_token() }}';
                        },
                        dataSrc: function (json) {
                            // Check if there are any records, if not hide pagination
                            if (json.orders.length === 0) {
                                $('#orderDetailsTable_paginate').hide();  // Hide pagination
                            } else {
                                $('#orderDetailsTable_paginate').show();  // Show pagination
                            }
                            return json.orders;  // Data to fill in the DataTable
                }
                    },
                    // Callback when DataTable pagination is triggered
                    drawCallback: function(settings) {
                        // Ensure the active page is marked correctly in the UI
                        updatePaginationState(settings);
                    },
                    recordsTotal: function(settings) {
                        return response.recordsTotal; // Pass the total records from the server
                    },
                    recordsFiltered: function(settings) {
                        return response.recordsFiltered; // Pass filtered records from the server
                    },
                });
            }


            fetchingData = false;
        },
        error: function (xhr, status, error) {
            console.error('Error fetching order details:', error);
            fetchingData = false;  // Reset flag in case of an error
        }
    });
}

// Handle the "Next" button click
function updatePaginationState(settings) {
    const totalPages = Math.ceil(settings.fnRecordsDisplay() / settings._iDisplayLength); // Get total pages
    const currentPage = Math.ceil(settings._iDisplayStart / settings._iDisplayLength) + 1; // Get current page (1-based)

    // Loop through all pagination buttons
    $('#orderDetailsTable_paginate .paginate_button').each(function () {
        const pageNum = parseInt($(this).text());

        // Remove 'active' class from all pagination buttons
        $(this).removeClass('active');

        // Add 'active' class to the button of the current page
        if (pageNum === currentPage) {
            $(this).addClass('active');
    }
});

// Update the Next button visibility based on the number of records returned
    const previousButton = $('#orderDetailsTable_previous');
    const nextButton = $('#orderDetailsTable_next');

    if (currentPage === 1) {
        previousButton.addClass('disabled'); // Disable the 'Previous' button if on the first page
    } else {
        previousButton.removeClass('disabled'); // Enable the 'Previous' button
    }

    if (currentPage === totalPages) {
        nextButton.addClass('disabled'); // Disable the 'Next' button if on the last page
    } else {
        nextButton.removeClass('disabled'); // Enable the 'Next' button
    }

    // Handle click event on pagination buttons
    $('#orderDetailsTable_previous').on('click', function() {
        if (currentPage > 1) {
            currentPage -= 1; // Decrease the current page
            tableInstance.page(currentPage - 1).draw('page'); // Go to the previous page
        }
    });

    $('#orderDetailsTable_next').on('click', function() {
        if (currentPage < totalPages) {
            currentPage += 1; // Increase the current page
            tableInstance.page(currentPage - 1).draw('page'); // Go to the next page
    }
    });
}

    // Handling the modal close action via jQuery (for clicking outside or closing via button)
$('#orderDetailsModal').on('hide.bs.modal', function () {
    if (tableInstance) {
        tableInstance.search('').draw();  // Clear the search input and redraw the table
        tableInstance.page.len(10).draw();  // Change entries per page to 10 and redraw the table
    }

    currentPage = 1; // Go to the first page
});


    // Handling the modal close action via jQuery
    $('.btn-secondary').on('click', function() {
        $('#orderDetailsModal').modal('hide'); // Close the modal
    });

    // Optional: Show the modal for demonstration (you can trigger this with a button or event)
    // $('#orderDetailsModal').modal('show');



</script>

@endsection


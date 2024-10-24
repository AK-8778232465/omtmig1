@extends('layouts.app')
@section('title', 'Stellar-OMS | Dashboard')
@section('content')
@php
    $currentDate = \Carbon\Carbon::now()->toDateString();
@endphp

    <style>

        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

        .text-center {
            font-family: "Poppins", sans-serif;
            font-weight: 500;
            font-style: normal;
        }

        .custom-card-bg {
            background-color: #e0dddd85; /* Light grey color */
        }


    .card{
        /* border: 1px solid #f5eea7 !important; */
        transition: box-shadow 0.3s !important;

        }
        .card:hover {
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2); /* Add shadow on hover */
        }

        .tabledetails{
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2) !important;
        }
            /* Switch container */
            .switch-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 20px;
            
        }

        /* The actual switch (hidden) */
        .toggle-switch {
            display: none;
        }

        /* Create a custom toggle switch */
        .toggle-label {
            display: flex;
            align-items: center;
            position: relative;
            width: 60px;
            height: 30px;
            border-radius: 20px;
            background-color: #ccc;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        /* The slider (rounded circle) */
        .slider {
            position: absolute;
            top: 4px;
            left: 4px;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background-color: white;
            transition: transform 0.3s;
        }

        /* Change the background color when the switch is checked */
        .toggle-switch:checked + .toggle-label {
             background-color: #4caf50;
        }

        /* Move the slider when the switch is checked */
        .toggle-switch:checked + .toggle-label .slider {
            transform: translateX(26px);
        }

        /* Labels */
        .label-left,
        .label-right {
            font-size: 15px;
            color: grey;
            font-weight: bold;
        }

        /* Left label */
        .label-left {
            margin-right: 10px;
        }

        /* Right label */
        .label-right {
            margin-left: 10px;
        }

        .dollar-sign::before {
         content: "$";
        }

        #datewise_datatable tbody td:nth-child(2) {
            white-space: normal !important;
            width:22%;
    }
    #fterevenueProjectTable tbody td:nth-child(2) {
            white-space: normal !important;
            width:22%;
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

    </style>
{{-- Order Wise --}}
<div class="modal fade vh-75" id="orderDetailModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Order Wise Detail</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="p-0 order_wise">
                    <h5 class="text-center project_name"></h5>
                    <table id="orderTable" class="table table-bordered nowrap mt-0" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead class="text-center">
                            <tr>
                                <th width="14%">Date</th>
                                <th width="14%">No of orders completed</th>
                                <th width="14%">Unit cost</th>
                                <th width="14%">Total</th>
                            </tr>
                        </thead>
                        <tbody class="text-center"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- FTE Projects --}}
<div class="modal fade vh-75" id="fteDetailModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Product Wise Detail</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="p-0 order_wise">
                    <h5 class="text-center project_name"></h5>
                    <div class="p-0 d-flex d-none justify-content-center">
                        <table id="totalTable" class="table table-bordered nowrap mt-3 w-50" style="border-collapse: collapse; border-spacing: 0;">
                            <thead class="text-center">
                                <tr>
                                    <th width="14%">Total Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                <td id="ProcessCountFTE"></td>
                            </tbody>
                        </table>
                    </div>
                    <table id="fterevenueProject" class="table table-bordered nowrap mt-0" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead class="text-center">
                            <tr>
                                <th width="14%">Date</th>
                                <th width="14%">No of Resources</th>
                                <th width="14%">Unit cost</th>
                                <th width="14%">Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="text-center"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid mt-2 mb-1 p-1" style="width: 90%; height: 90%;">
    <section id="minimal-statistics">
            @if(Auth::user()->hasRole('Business Head') || Auth::user()->hasRole('AVP/VP'))
        <div class="switch-container d-flex justify-content-end">
                <span class="label-left">Revenue</span>
                <input type="checkbox" id="toggleSwitch" class="toggle-switch">
                <label for="toggleSwitch" class="toggle-label">
                    <span class="slider"></span>
                </label>
                <span class="label-right">Production</span>
            </div>
        @endif
                <div class="container-fluid d-flex reports">
                    <div class="col-md-12">
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
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="client">Client</label>
                                    <select class="form-control select2-basic-multiple" name="dcf_client_id" id="client_id_dcf" multiple="multiple">
                                        <option selected value="All">All</option>
                                        @forelse($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->client_no }} ({{ $client->client_name }})</option>
                                        @empty
                                        @endforelse
                                    </select>
                                </div>
                            </div>

                            @if(Auth::user()->hasRole('Business Head') || Auth::user()->hasRole('AVP/VP'))
                                <div class="col-2" id="billing_hide" style="display: none;"><label for="project">Billing Type</label>
                                    <Select class="form-control select_role float-end" name="" id="billing_id_dcf">
                                        <option selected value="All">All</option>
                                        <option value="FTE">FTE</option>
                                        <option value="TXN">TXN</option>
                                    </Select>
                                </div>
                            @endif

                            <div class="col-md-3" id="hide_lob"
                                @if(Auth::user()->hasRole('Process') || Auth::user()->hasRole('Qcer') || Auth::user()->hasRole('Process/Qcer') || Auth::user()->hasRole('SPOC')) 
                                @else 
                                    style="display: none;" 
                                @endif>
                                <div class="form-group">
                                    <label for="lob_id">Lob</label>
                                    <select class="form-control select2-basic-multiple" style="width:100%" name="lob_id" id="lob_id" multiple="multiple">
                                        <option selected value="All">All</option>
                                    </select>
                                </div>
                            </div>

                            <!-- <div class="col-md-2"  id="hide_process_type" style="display: none;">
                                <div class="form-group">
                                    <label for="process_type_id">Process</label>
                                    <select class="form-control select2-basic-multiple" style="width:100%" name="process_type_id" id="process_type_id" multiple="multiple">
                                        <option selected value="All">All</option>
                                    </select>
                                </div>
                            </div> -->

                            <div class="col-md-2" id="hide_process_type"
                                @if(Auth::user()->hasRole('Process') || Auth::user()->hasRole('Qcer') || Auth::user()->hasRole('Process/Qcer') || Auth::user()->hasRole('SPOC')) 
                                @else 
                                    style="display: none;" 
                                @endif>
                                <div class="form-group">
                                    <label for="process_type_id">Process</label>
                                    <select class="form-control select2-basic-multiple" style="width:100%" name="process_type_id" id="process_type_id" multiple="multiple">
                                        <option selected value="All">All</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3" id="hidefilter_2">
                                <div class="form-group">
                                    <label for="product_id">Product</label>
                                    <select class="form-control select2-basic-multiple" style="width:100%" name="product_id" id="product_id" multiple="multiple">
                                        <option selected value="All">All</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2 mt-4 mb-3" style="" id="hidefilter_3">
                                <button type="submit" id="filterButton" class="btn btn-primary">Filter</button>
                            </div>
                        </div>
                <!-- // -->
            <div id="leftContent" style="display: none;">
                <div class="col-12">
                    <div class="row my-2">
                        @if(Auth::user()->hasRole('Super Admin') || Auth::user()->hasRole('Business Head') || Auth::user()->hasRole('AVP/VP'))
                            <div class="col-xl-4 col-sm-6 col-12">
                            <div class="h-100 card">
                                    <div class="card-content">
                                        <div class="card-body">
                                            <div class="media d-flex">
                                                <div class="media-body">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <h4 class="icon-dual-info mb-0">Transactional</h4>
                                                        <div class="row">
                                                            <div class="col mt-2" style="padding-right: 0px;"><i class="icon-dual-warning font-large-2 float-right" style="width: 20px;" data-feather="dollar-sign"></i></div>
                                                            <div class="col" style="padding-left: 0px;"><h4 class="icon-dual-warning mb-0" id="transaction_cost">0.00</h4></div>
                                                        </div>
                                                    </div>
                                                    <div class="justify-content-between align-items-center mt-2">
                                                        <span class="font-weight-bold">Revenue</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <div class="col-xl-4 col-sm-6 col-12">
                            <div class="h-100 card">
                                <div class="card-content">
                                    <div class="card-body">
                                        <div class="media d-flex">
                                            <div class="media-body">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h4 class="icon-dual-info mb-0">FTE</h4>
                                                    <div class="row">
                                                        <div class="col mt-2" style="padding-right: 0px;"><i class="icon-dual-pink font-large-2 float-right" style="width: 20px;" data-feather="dollar-sign"></i></div>
                                                        <div class="col" style="padding-left: 0px;"><h4 class="icon-dual-pink mb-0" id="fte_cost">0.00</h4></div>
                                                    </div>
                                                </div>
                                                <div class="justify-content-between align-items-center mt-2">
                                                    <span class="font-weight-bold">Revenue</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-sm-6 col-12">
                            <div class="h-100 card">
                                <div class="card-content">
                                    <div class="card-body">
                                        <div class="media d-flex">
                                            <div class="media-body">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h4 class="icon-dual-info mb-0">Total</h4>
                                                    <div class="row">
                                                        <div class="col mt-2" style="padding-right: 0px;"><i class="icon-dual-danger font-large-2 float-right" style="width: 20px;" data-feather="dollar-sign"></i></div>
                                                        <div class="col" style="padding-left: 0px;"><h4 class="icon-dual-danger mb-0" id="total_cost">0.00</h4></div>
                                                    </div>
                                                </div>
                                                <div class="justify-content-between align-items-center">
                                                    <span class="font-weight-bold">Revenue</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        </div>
                    </div>
            </div>

@if(!(Auth::user()->hasRole('Process') || Auth::user()->hasRole('Qcer') || Auth::user()->hasRole('Process/Qcer')))
    <div class="card mb-4">
    <div class="col-md-12 d-flex">
    <div class="card col-md-5 mt-3 mb-3 ml-3" id="available_resource_table" style="font-size: 12px;">
        <h4 class="text-center mt-3">Available Resources</h4>
            <div class="card-body">
                <div class="p-0">
                    <div class="d-flex">
                        <div> <h5 class="text-center">No. of Users Available:</h5></div>
                        <div> <h5 id="available_users" style="color:blue;font-weight: bold;margin-left:10px"></h5></div>
                        <div><h5 style="margin-left:3px">/</h5></div>
                        <div><h5 id="total_users" style="color:rgb(247, 8, 8);font-weight: bold;margin-left:3px"></h5></div>                                    
                    </div>
                    <table id="available_resources" class="table table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead class="text-left" style="font-size: 12px;">
                            <tr>
                                <th width="10%">Emp Id</th>
                                <th width="12%">Emp Name</th>
                            </tr>
                        </thead>
                        <tbody class="text-left" style="font-size: 12px;"></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-1"></div>
            <div class="card col-md-5 mt-3 mb-3 ml-3" id="tat_zone_table" style="font-size: 12px;">
                <h4 class="text-center mt-5">TAT</h4>
                <div class="card-body">
                    <div class="p-0">
                        <table id="tat_zone_datatable" class="table table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead style="font-size: 12px;">
                                <tr>
                                    <th class="text-left" width="10%">TAT Zone</th>
                                    <th class="text-center" width="12%">Count</th>
                                </tr>
                            </thead>
                            <tbody class="text-left" style="font-size: 12px;"></tbody>
                        </table>
                         <!-- Note below the DataTable -->
                        <p id="tat_note" style="margin-top: 10px; font-size: 14px; color: #555;">
                            <b>Note:</b> Each TAT zone represents 25% of the total TAT.
                        </p>
                    </div>
                </div>
        </div>
        </div>
</div>
@endif



{{-- carry over count --}}
@if(!(Auth::user()->hasRole('Process') || Auth::user()->hasRole('Qcer') || Auth::user()->hasRole('Process/Qcer')))
<div class="d-flex justify-content-center">
   <div class="card col-md-8 mt-3 mb-3 ml-3 carry_over_monthly_table" id="carry_over_monthly_table" style="font-size: 12px;">
    <h4 class="text-center mt-3">Order Inflow Data</h4>
        <div class="card-body">
            <div class="p-0">
                <table id="carry_over_monthly" class="table table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                    <thead class="text-center" style="font-size: 12px;">
                        <tr>
                            <th width="10%"></th>
                            <th width="12%">Carry Forward</th>
                            <th width="12%">Received</th>
                            <th width="12%">Completed</th>
                            <th width="12%">Pending</th>
                        </tr>
                    </thead>
                    <tbody class="text-center" style="font-size: 12px;"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif

<div id="rightContent">
    <h4 class="text-start mt-3">Volume Analysis:</h4>
    <div class="col-12">
        <div class="row my-2">
        @if(Auth::user()->hasRole('Super Admin') || Auth::user()->hasRole('Business Head') ||Auth::user()->hasRole('PM/TL') || Auth::user()->hasRole('SPOC') || Auth::user()->hasRole('AVP/VP'))
                <div class="col-xl-4 col-sm-6 col-12" onclick="gotoOrders(6)"  style="cursor: pointer;">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body" style = "background-color: #dee2e6;">
                                <div class="media d-flex">
                                    <div class="media-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h3 class="icon-dual-info mb-0 text-success" id="yet_to_assign_cnt">0</h3>
                                        </div>
                                        <div class="justify-content-between align-items-center mt-2 text-dark">
                                            <span>Yet to Assign</span>
                                            <i class="icon-dual-info font-large-2 float-right text-dark" data-feather="book-open"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @if(!Auth::user()->hasRole('Qcer'))
                <div class="col-xl-4 col-sm-6 col-12" onclick="gotoOrders(1)" style="cursor: pointer;">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="media d-flex">
                                    <div class="media-body">
                                        <div class="d-flex align-items-center">
                                            <h3 class="icon-dual-success mb-0 mr-2" id="wip_cnt">0</h3>
                                            <h3 class="plus-symbol mb-0 mr-2 text-info">+</h3>
                                            <h3 class="icon-dual-warning mb-0" id="pre_wip_cnt">0</h3>
                                        </div>
                                        <div class="justify-content-between align-items-center mt-2">
                                            <span>WIP</span>
                                            <i class="icon-dual-pink font-large-2 float-right" data-feather="trending-up"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            

            <!-- Existing sections -->

            <!-- Coversheet Prep -->
            <div class="col-xl-4 col-sm-6 col-12" onclick="gotoOrders(13)"  style="cursor: pointer;">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="media-body">
                                    <div class="d-flex align-items-center">
                                        <h3 class="icon-dual-success mb-0 mr-2" id="coversheet_cnt">0</h3>
                                        <h3 class="plus-symbol mb-0 mr-2 text-info">+</h3>
                                        <h3 class="icon-dual-warning mb-0" id="pre_coversheet_cnt">0</h3>
                                    </div>
                                    <div class="justify-content-between align-items-center mt-2">
                                        <span>Coversheet Prep</span>
                                        <i class="icon-dual-success font-large-2 float-right" data-feather="arrow-up-left"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            <div class="col-xl-4 col-sm-6 col-12" onclick="gotoOrders(15)"  style="cursor: pointer;">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="media-body">
                                    <div class="d-flex align-items-center">
                                        <h3 class="icon-dual-success mb-0 mr-2" id="doc_purchaser_cnt">0</h3>
                                        <h3 class="plus-symbol mb-0 mr-2 text-info">+</h3>
                                        <h3 class="icon-dual-warning mb-0" id="pre_doc_purchaser_cnt">0</h3>
                                    </div>
                                    <div class="justify-content-between align-items-center mt-2">
                                        <span>Doc Purchaser</span>
                                        <i class="icon-dual-success font-large-2 float-right" data-feather="credit-card"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-sm-6 col-12" onclick="gotoOrders(18)"  style="cursor: pointer;">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="media-body">
                                    <div class="d-flex  align-items-center">
                                        <h3 class="icon-dual-success mb-0 mr-2" id="ground_abstractor_cnt">0</h3>
                                        <h3 class="plus-symbol mb-0 mr-2 text-info">+</h3>
                                        <h3 class="icon-dual-warning mb-0" id="pre_ground_abstractor_cnt">0</h3>
                                    </div>
                                    <div class="justify-content-between align-items-center mt-2">
                                        <span>Ground Abstractor</span>
                                        <i class="icon-dual-success font-large-2 float-right" data-feather="map"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Clarification -->
            <div class="col-xl-4 col-sm-6 col-12" onclick="gotoOrders(14)"  style="cursor: pointer;">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="media-body">
                                    <div class="d-flex  align-items-center">
                                        <h3 class="icon-dual-success mb-0 mr-2" id="Clarification_cnt">0</h3>
                                        <h3 class="plus-symbol mb-0 mr-2 text-info">+</h3>
                                        <h3 class="icon-dual-warning mb-0" id="pre_clarification_cnt">0</h3>
                                    </div>
                                    <div class="justify-content-between align-items-center mt-2">
                                        <span>Clarification</span>
                                        <i class="icon-dual-info font-large-2 float-right" data-feather="arrow-up-right"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Send For QC -->
            <div class="col-xl-4 col-sm-6 col-12" onclick="gotoOrders(4)"  style="cursor: pointer;">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="media-body">
                                    <div class="d-flex  align-items-center">
                                        <h3 class="icon-dual-success mb-0 mr-2" id="Qu_cnt">0</h3>
                                        <h3 class="plus-symbol mb-0 mr-2 text-info">+</h3>
                                        <h3 class="icon-dual-warning mb-0" id="pre_qu_cnt">0</h3>
                                    </div>
                                    <div class="justify-content-between align-items-center mt-2">
                                        <span>Send For QC</span>
                                        <i class="icon-dual-warning font-large-2 float-right" data-feather="chevrons-right"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<!-- Typing -->
            <div class="col-xl-4 col-sm-6 col-12" onclick="gotoOrders(16)"  style="cursor: pointer;">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="media-body">
                                    <div class="d-flex align-items-center">
                                        <h3 class="icon-dual-success mb-0 mr-2" id="typing_cnt">0</h3>
                                        <h3 class="plus-symbol mb-0 mr-2 text-info">+</h3>
                                        <h3 class="icon-dual-warning mb-0" id="pre_typing_cnt">0</h3>
                                    </div>
                                    <div class="justify-content-between align-items-center mt-2">
                                        <span>Typing</span>
                                        <i class="icon-dual-success font-large-2 float-right" data-feather="layers"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-sm-6 col-12" onclick="gotoOrders(17)"  style="cursor: pointer;">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="media-body">
                                    <div class="d-flex align-items-center">
                                        <h3 class="icon-dual-success mb-0 mr-2" id="typing_qc_cnt">0</h3>
                                        <h3 class="plus-symbol mb-0 mr-2 text-info">+</h3>
                                        <h3 class="icon-dual-warning mb-0" id="pre_typing_qc_cnt">0</h3>
                                    </div>
                                    <div class="justify-content-between align-items-center mt-2">
                                        <span>Typing QC</span>
                                        <i class="icon-dual-success font-large-2 float-right" data-feather="check-square"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hold -->
            <div class="col-xl-4 col-sm-6 col-12" onclick="gotoOrders(2)"  style="cursor: pointer;">
                            <div class="card">
                                <div class="card-content">
                                    <div class="card-body">
                                        <div class="media d-flex">
                                            <div class="media-body">
                                                <div class="d-flex align-items-center">
                                        <h3 class="icon-dual-success mb-0 mr-2" id="hold_cnt">0</h3>
                                                    <h3 class="plus-symbol mb-0 mr-2 text-info">+</h3>
                                        <h3 class="icon-dual-warning mb-0" id="pre_hold_cnt">0</h3>
                                                </div>
                                                <div class="justify-content-between align-items-center mt-2">
                                        <span>Hold</span>
                                        <i class="icon-dual-purple font-large-2 float-right" data-feather="pause"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

            <!-- Cancelled -->
            <div class="col-xl-4 col-sm-6 col-12" onclick="gotoOrders(3)"  style="cursor: pointer;">
                            <div class="card">
                                <div class="card-content">
                                    <div class="card-body">
                                        <div class="media d-flex">
                                            <div class="media-body">
                                                <div class="d-flex align-items-center">
                                        <h3 class="icon-dual-success mb-0 mr-2" id="cancelled_cnt">0</h3>
                                                </div>
                                                <div class="justify-content-between align-items-center mt-2">
                                        <span>Cancelled</span>
                                        <i class="icon-dual-danger font-large-2 float-right" data-feather="alert-circle"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

            <!-- Completed -->
            <div class="col-xl-4 col-sm-6 col-12" onclick="gotoOrders(5)"  style="cursor: pointer;">
                            <div class="card">
                                <div class="card-content">
                                    <div class="card-body">
                                        <div class="media d-flex">
                                            <div class="media-body">
                                                <div class="d-flex align-items-center">
                                        <h3 class="icon-dual-success mb-0 mr-2" id="completed_cnt">0</h3>
                                                    <h3 class="plus-symbol mb-0 mr-2 text-info">+</h3>
                                        <h3 class="icon-dual-warning mb-0" id="pre_completed_cnt">0</h3>
                                                </div>
                                                <div class="justify-content-between align-items-center mt-2">
                                        <span>Completed</span>
                                                    <i class="icon-dual-success font-large-2 float-right" data-feather="check"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

            <!-- All -->
            <div class="col-xl-4 col-sm-6 col-12" onclick="gotoOrders('All')"  style="cursor: pointer;">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="media-body">
                                    <div class="d-flex  align-items-center">
                                        <h3 class="icon-dual-success mb-0 mr-2" id="all_count">0</h3>
                                        <h3 class="plus-symbol mb-0 mr-2 text-info">+</h3>
                                        <h3 class="icon-dual-warning mb-0" id="pre_all_cnt">0</h3>
                                    </div>
                                    <div class="justify-content-between align-items-center mt-2">
                                        <span>All</span>
                                        <i class="icon-dual-pink font-large-2 float-right" data-feather="box"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if(Auth::user()->hasRole('Super Admin') || Auth::user()->hasRole('Process'))
            <div class="col-xl-4 col-sm-6 col-12">

            </div>
            @endif
            <!-- Carried Over -->
            <div class="col-xl-4 col-sm-6 col-12"  >
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="media-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h3 class="icon-dual-warning mb-0" id="carried_over_cnt">0</h3>
                                    </div>
                                    <div class="justify-content-between align-items-center mt-2">
                                        <span>Carried Over</span>
                                        <i class="icon-dual-warning font-large-2 float-right" data-feather="corner-up-right"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Carried Over & Completed -->
            <div class="col-xl-4 col-sm-6 col-12" >
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="media-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h3 class="icon-dual-warning mb-0" id="carried_over_completed_cnt">0</h3>
                                    </div>
                                    <div class="justify-content-between align-items-center mt-2">
                                        <span>Carried Over & Completed</span>
                                        <i class="icon-dual-blue font-large-2 float-right" data-feather="check-circle"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
            </div>
</section>
        @if(Auth::user()->hasRole('Business Head') || Auth::user()->hasRole('AVP/VP'))
        <div class="card mt-5 tabledetails" id="Trans_hide" style="display: none;">
            <h4 class="text-center mt-3">Revenue Details - Transactional Billing</h4>
            <div class="card-body">
                <div class="p-0 w-75 mx-auto">
                    <h5 class="text-center"> Client Wise Details </h5>
                    <table id="revenueClientTable" class="table table-bordered nowrap mt-0 d-none" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead class="text-center">
                            <tr>
                                {{-- <th width="14%">Date</th> --}}
                                <th width="14%" class="text-left">Client</th>
                                <th width="14%">No of orders completed</th>
                                <th width="14%">Total</th>
                            </tr>
                        </thead>
                        <tbody class="text-center"></tbody>
                    </table>
                </div>
                <div class="p-0 process_wise w-75 mx-auto">
                    <h5 class="text-center"> Product Wise Details </h5>
                    <table id="revenueTable" class="table table-bordered nowrap mt-0 d-none" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead class="text-center">
                            <tr>
                                <th width="14%" class="text-left">Product Code</th>
                                <th width="14%">No of orders completed</th>
                                <th width="14%">Unit cost</th>
                                <th width="14%">Total</th>
                            </tr>
                        </thead>
                        <tbody class="text-center"></tbody>
                    </table>
                </div>
                <div class="p-0 d-flex justify-content-center">
                    <table id="totalTable" class="table table-bordered nowrap mt-3 w-50" style="border-collapse: collapse; border-spacing: 0;">
                        <thead class="text-center">
                            <tr>
                                <th width="14%">Grand Total Transactional Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            <td id="grantCount" class="dollar-sign"></td>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

<div class="card mt-5 tabledetails"  id="ftetabledetails">
        <div class="card-body" id="fteClient" style="display: none;">
            <div class="p-0 w-75 mx-auto" id="fteClientTable" style="display: none;">
                <h4 class="text-center mt-3">Revenue Details - FTE Billing</h4><br>
                <h5 class="text-center"> Client Wise Details </h5>
                <table id="fterevenueClientTable" class="table table-bordered nowrap mt-0" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                <thead class="text-center">
                <tr>
                    <th width="14%">Client</th>
                    <th width="14%">Total Revenue</th>

                </tr>
                </thead>
                <tbody class="text-center"></tbody>
                </table>
            </div>
        </div>

        <div class="card-body" id="fteProject" style="display: none;">
            <div class="p-0 w-100 mx-auto" id="fteProjectTable">
                <h5 class="text-center"> Product Wise Details </h5>
                <table id="fterevenueProjectTable" class="table table-bordered nowrap mt-0 " style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                <thead class="text-center">
                <tr>
                    <th width="10%">Client</th>
                    <th width="15%" class="wrap-column">Product Code</th>
                    <th width="7%" >Pricing</th>
                    <th width="8%">FTE Count</th>
                    <th width="13%">Expected Revenue</th>
                    <th width="8%">Start Date</th>
                    <th width="8%">End Date</th>
                    <th width="6%">Days</th>
                    <th width="17%">Revenue as Selected Date</th>
                </tr>
                </thead>
                <tbody></tbody>
                </table>
            </div>
            <div class="p-0 d-flex justify-content-center">
                <table id="totalFTETable" class="table table-bordered nowrap mt-3 w-50" style="border-collapse: collapse; border-spacing: 0;">
                    <thead class="text-center">
                        <tr>
                            <th width="14%">Grand Total FTE Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <td id='fte_costs' class="dollar-sign"></td>
                    </tbody>
                </table>
            </div>
        </div>
</div>
        @endif

@if(!(Auth::user()->hasRole('Process') || Auth::user()->hasRole('Qcer') || Auth::user()->hasRole('Process/Qcer')))
        <div class="card col-md-12 mt-3" id="pending_status_table" style="font-size: 12px;">
            <h4 class="text-center mt-3">Pending Order Status</h4>
                <div class="card-body">
                    <table id="pending_status" class="table table-bordered nowrap mt-0 " style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead class="text-center">
                        <tr>
                            <th width="10%">Status</th>
                            <th width="15%" class="wrap-column">More than 10 Days</th>
                            <th width="15%" class="wrap-column">More than 20 Days</th>
                            <th width="15%" class="wrap-column">More than 30 Days</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                        </table>
                </div>
            </div>
@endif



        @if(Auth::user()->hasRole(['Super Admin', 'AVP/VP','PM/TL','Business Head']))
        <div class="card mt-5 tabledetails d-none" id="userwise_table">
            <h4 class="text-center mt-3">Userwise Details</h4>
            <div class="card-body">
                <div class="p-0">
                    <table id="userwise_datatable" class="table table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead class="text-center">
                            <tr>
                                <th width="10%">Users</th>
                                <th width="7%">WIP</th>
                                <th width="8%">Coversheet Prep</th>
                                <th width="8%">Doc Purchase</th>
                                <th width="8%">Ground Abstractor</th>
                                <th width="8%">Clarification</th>
                                <th width="8%">Send For QC</th>
                                <th width="7%">Typing</th>
                                <th width="7%">Typing QC</th>
                                <th width="7%">Hold</th>
                                <th width="7%">Cancelled</th>
                                <th width="7%">Completed</th>
                                <th width="7%">All</th>
                            </tr>
                        </thead>
                        <tbody class="text-center"></tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        @if(Auth::user()->hasRole(['Super Admin', 'AVP/VP','PM/TL','Process','Qcer','Process/Qcer','SPOC','Business Head']))
        <div class="card mt-5 tabledetails d-none" id="datewise_table" style="display: none;">
            <h4 class="text-center mt-3">ClientWise Details</h4>
            <div class="card-body">
                <div class="p-0">
                    <table id="datewise_datatable" class="table table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead class="text-center">
                            <tr>
                                <th width="11%">Client</th>
                                <th width="20%" class="wrap-column">Product</th>
                                @if(!Auth::user()->hasRole('Qcer'))
                                <th width="8%">WIP</th>
                                <th width="7%">Coversheet Prep</th>
                                @endif
                                <th width="7%">Doc Purchase</th>
                                <th width="7%">Ground Abstractor</th>
                                <th width="7%">Clarification</th>
                                <th width="7%">Send for QC</th>
                                <th width="7%">Typing</th>
                                <th width="7%">Typing QC</th>
                                <th width="6%">Hold</th>
                                <th width="7%">Cancelled</th>
                                <th width="7%">Completed</th>
                                <th width="7%">All</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
     @endif
</div>

{{-- Js --}}
<script>
// filter add indashboard

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

document.addEventListener('DOMContentLoaded', function() {
    // Get the toggle switch and the elements to show/hide
    const toggleSwitch = document.getElementById('toggleSwitch');
    const leftContent = document.getElementById('leftContent');
    const rightContent = document.getElementById('rightContent');
    const projectIdDcf = document.getElementById('hidefilter_2');
    const billingIdDcf = document.getElementById('billing_hide');
    const lobIdDcf = document.getElementById('hide_lob');
    const processTypeIdDcf = document.getElementById('hide_process_type');
    const fteProjectIdDcf = document.getElementById('fteProject');
    const fteClientIdDcf = document.getElementById('fteClient');

    const userwiseIdDcf = document.getElementById('userwise_table');
    const datewiseIdDcf = document.getElementById('datewise_table');
    const transIdDcf = document.getElementById('Trans_hide');
    const fteIdDcf = document.getElementById('fteClientTable');
    const pendingwise_status = document.getElementById('pending_status_table');
    const available_resource_table = document.getElementById('available_resource_table');
    const tat_zone_table = document.getElementById('tat_zone_table');
    const carry_over_monthly_table = document.getElementById('carry_over_monthly_table');



    // Function to update the visibility based on the toggle switch state
    function updateVisibility() {
        if (toggleSwitch.checked) {
            // If the switch is checked (Revenue side), hide the left content and billing content
            // and show the right content and project content
            leftContent.style.display = 'none';
            projectIdDcf.style.display = 'block';
            rightContent.style.display = 'block';
            billingIdDcf.style.display = 'none';
            userwiseIdDcf.style.display = 'block';
            datewiseIdDcf.style.display = 'block';
            transIdDcf.style.display = 'none';
            fteIdDcf.style.display = 'none';
            fteProjectIdDcf.style.display = 'none';
            fteClientIdDcf.style.display = 'none';
            lobIdDcf.style.display = 'block';
            processTypeIdDcf.style.display = 'block';
            pendingwise_status.style.display = 'block';
            available_resource_table.style.display = 'block';
            tat_zone_table.style.display = 'block';
            carry_over_monthly_table.style.display = 'block';


        } else {
            // If the switch is unchecked (Production side), hide the right content and project content
            // and show the left content and billing content
            leftContent.style.display = 'block';
            projectIdDcf.style.display = 'none';
            rightContent.style.display = 'none';
            billingIdDcf.style.display = 'block';
            userwiseIdDcf.style.display = 'none';
            datewiseIdDcf.style.display = 'none';
            transIdDcf.style.display = 'block';
            fteIdDcf.style.display = 'block';
            fteProjectIdDcf.style.display = 'block';
            fteClientIdDcf.style.display = 'block';
            lobIdDcf.style.display = 'none';
            processTypeIdDcf.style.display = 'none';
            pendingwise_status.style.display = 'none';
            available_resource_table.style.display = 'none';
            tat_zone_table.style.display = 'none';
            carry_over_monthly_table.style.display = 'none';


        }
            }

    updateVisibility();

    toggleSwitch.addEventListener('change', function() {
        updateVisibility();
        });
});

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
        const month = String(date.getMonth() + 1).padStart(2, '0');
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
            $('.select2-basic-multiple').select2();

            $(document).ready(function() {
            var isLobChanging = false;
            $(document).on('change', '#lob_id', function() {
                if (isLobChanging) return;
                isLobChanging = true;
                var selectedLobOption = $(this).val();
                $("#lob_id").val(selectedProcessOption && selectedProcessOption.includes('All') ? ['All'] : selectedProcessOption);
                if ($("#lob_id").val() !== selectedProcessOption) {
                    $("#lob_id").trigger('change');
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
        });

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

        var isClientChanging = false;
        $(document).on('change', '#product_id', function() {
            if (isClientChanging) return;
            isClientChanging = true;
            var selectedClientOption = $(this).val();
            $("#product_id").val(selectedClientOption && selectedClientOption.includes('All') ? ['All'] : selectedClientOption);
            if ($("#product_id").val() !== selectedClientOption) {
                $("#product_id").trigger('change');
            }
            isClientChanging = false;
        });

    });


    let datatable = null;

    $(document).ready(function () {
        // fetchProData('All');
        $("#project_id").select2();
        $("#product_id").select2();
        $("#client_id_dcf").select2();
        $("#billing_id_dcf").select2();

        let projectId = $("#product_id").val();
        let clientId = $("#client_id_dcf").val();
        let lobId = $('#lob_id').val();
        let process_type_id = $('#process_type_id').val();
        let fromDate = $("#fromDate_range").val();
        let toDate = $("#toDate_range").val();
        $("#filterButton").on('click', function() {
            projectId = $("#product_id").val();
            clientId = $("#client_id_dcf").val();
            lobId = $('#lob_id').val();
            process_typeId = $('#process_type_id').val();
            fromDate = $("#fromDate_range").val();
            toDate = $("#toDate_range").val();
            selectedDateFilter = selectedDateFilter;

            fetchOrderData(projectId, clientId, fromDate, toDate, selectedDateFilter);
            getGrandTotal(fromDate, toDate, client_id, selectedDateFilter);
            preOrderData(projectId, clientId, fromDate, toDate, selectedDateFilter)
            datewise_datatable(fromDate, toDate, client_id, projectId, selectedDateFilter)
            userwise_datatable(fromDate, toDate, client_id, projectId, selectedDateFilter);
            tat_zone(fromDate, toDate, clientId, projectId, selectedDateFilter);
            carry_over_monthly(fromDate, toDate, clientId, projectId, selectedDateFilter)
        });
        preOrderData(projectId, clientId, fromDate, toDate, selectedDateFilter)
        fetchOrderData(projectId, clientId, fromDate, toDate, selectedDateFilter);
        datewise_datatable(fromDate, toDate, client_id, projectId, selectedDateFilter);

    $('#client_id_dcf').on('change', function () {
        let client_id = $("#client_id_dcf").val();
        let productId = $("#product_id").val();
        fetchLobData(client_id, productId, selectedDateFilter);
    });

    $('#lob_id').on('change', function () {
        var lob_id = $(this).val();
        let client_id = $("#client_id_dcf").val();
        fetchProcessData(lob_id, client_id, selectedDateFilter);
    });

    $('#process_type_id').on('change', function () {
        let process_type_id = $("#process_type_id").val();
        let client_id = $("#client_id_dcf").val();
        var lob_id = $("#lob_id").val();
        fetchProcessTypeData(process_type_id, client_id, lob_id, selectedDateFilter);
    });


});

$('#client_id_dcf').on('change', function () {
        let client_id = $("#client_id_dcf").val();
        let productId = $("#product_id").val();
        fetchLobData(client_id, productId, selectedDateFilter);
        });

    $('#lob_id').on('change', function () {
        var lob_id = $(this).val();
        let client_id = $("#client_id_dcf").val();
        fetchProcessData(lob_id, client_id, selectedDateFilter);
    });

    $('#process_type_id').on('change', function () {
        let process_type_id = $("#process_type_id").val();
        let client_id = $("#client_id_dcf").val();
        var lob_id = $("#lob_id").val();
        fetchProcessTypeData(process_type_id, client_id, lob_id, selectedDateFilter);
    });

function gotoOrders(StatusId) {
        projectId = $("#product_id").val();
        clientId = $("#client_id_dcf").val();
        fromDate = $("#fromDate_range").val();
        toDate = $("#toDate_range").val();
        selectedDateFilter = selectedDateFilter;
        $.ajax({
            url: "{{ route('redirectwithfilter') }}",
            method: 'POST',
            data: {
                projectId: projectId,
                clientId: clientId,
                fromDate: fromDate,
                toDate: toDate,
                selectedDateFilter: selectedDateFilter,
                _token: '{{csrf_token()}}'
            },
            success: function(response) {
                if(response.success) {
                    window.location.href = '{{url("/orders_status")}}/' + StatusId;
                }
            },
            error: function(xhr, status, error) {
                console.error('Error storing filter values in session:', error);
            }
        });
    }

function fetchOrderData(projectId, clientId, fromDate, toDate, selectedDateFilter) {
    $.ajax({
        url: "{{ route('dashboard_count') }}",
        type: "POST",
        data: {
            project_id: projectId,
            client_id: clientId,
            from_date: fromDate,
            to_date: toDate,
            selectedDateFilter: selectedDateFilter,
            _token: '{{csrf_token()}}'
        },
        dataType: 'json',
        success: function (response) {
            let statusCounts = response.StatusCounts;
            let totalValue = 0;
            for (const key in statusCounts) {
                if (statusCounts.hasOwnProperty(key) && key !== '6') {
                    totalValue += statusCounts[key];
                }
            }
            $('#all_count').text(totalValue);
            $('#yet_to_assign_cnt').text(statusCounts[6] || 0);
            $('#wip_cnt').text(statusCounts[1] || 0);
            $('#hold_cnt').text(statusCounts[2] || 0);
            $('#Qu_cnt').text(statusCounts[4] || 0);
            $('#cancelled_cnt').text(statusCounts[3] || 0);
            $('#completed_cnt').text(statusCounts[5] || 0);
            $('#coversheet_cnt').text(statusCounts[13] || 0);
            $('#Clarification_cnt').text(statusCounts[14] || 0);
            $('#doc_purchaser_cnt').text(statusCounts[15] || 0);
            $('#typing_cnt').text(statusCounts[16] || 0);
            $('#typing_qc_cnt').text(statusCounts[17] || 0);
            $('#ground_abstractor_cnt').text(statusCounts[18] || 0);
        }
    });
}

function preOrderData(projectId, clientId, fromDate, toDate, selectedDateFilter) {
    $.ajax({
        url: "{{ route('previous_count') }}",
        type: "POST",
        data: {
            project_id: projectId,
            client_id: clientId,
            from_date: fromDate,
            to_date: toDate,
            selectedDateFilter: selectedDateFilter,
            _token: '{{csrf_token()}}'
        },
        dataType: 'json',
        success: function (response) {
            let statusCounts = response.StatusCounts;

            // Calculate total orders
            let totalValue = 0;
            for (const key in statusCounts) {
                if (statusCounts.hasOwnProperty(key)) {
                    if (Array.isArray(statusCounts[key])) {
                        statusCounts[key].forEach(count => {
                            totalValue += count.total_orders;
                        });
                    } else if (typeof statusCounts[key] === 'object') {
                        for (const count in statusCounts[key]) {
                            totalValue += statusCounts[key][count];
                        }
                    }
                }
            }
            $('#pre_all_cnt').text(totalValue);

            // Update individual counts
            let carriedCountMap = {};
            let carriedOverCompletedCountMap = {};

            if (Array.isArray(statusCounts.carriedCount)) {
                statusCounts.carriedCount.forEach(item => {
                    carriedCountMap[item.status_id] = item.total_orders;
                });
            }

            for (const statusId in statusCounts.carriedOverCompletedCount) {
                if (statusCounts.carriedOverCompletedCount.hasOwnProperty(statusId)) {
                    carriedOverCompletedCountMap[statusId] = statusCounts.carriedOverCompletedCount[statusId];
                }
            }


            $('#pre_completed_cnt, #carried_over_completed_cnt').text(carriedOverCompletedCountMap[5] || 0);

            // Update specific counts
            let preWipCnt = carriedCountMap[1] || 0;
            let preHoldCnt = carriedCountMap[2] || 0;
            let preQuCnt = carriedCountMap[4] || 0;
            let preCoversheetCnt = carriedCountMap[13] || 0;
            let preClarificationCnt = carriedCountMap[14] || 0;
            let predocPurchaserCnt = carriedCountMap[15] || 0;
            let preTypingCnt = carriedCountMap[16] || 0;
            let preTypingQcCnt = carriedCountMap[17] || 0;
            let pregroundAbstractorCnt = carriedCountMap[18] || 0;



            $('#pre_wip_cnt').text(preWipCnt);
            $('#pre_hold_cnt').text(preHoldCnt);
            $('#pre_qu_cnt').text(preQuCnt);
            $('#pre_coversheet_cnt').text(preCoversheetCnt);
            $('#pre_clarification_cnt').text(preClarificationCnt);
            $('#pre_doc_purchaser_cnt').text(predocPurchaserCnt);
            $('#pre_typing_cnt').text(preTypingCnt);
            $('#pre_typing_qc_cnt').text(preTypingQcCnt);
            $('#pre_ground_abstractor_cnt').text(pregroundAbstractorCnt);

            // Calculate and update the total of the specified values
            let totalSpecificValues = preWipCnt + preHoldCnt + preQuCnt + preCoversheetCnt + preClarificationCnt + predocPurchaserCnt + preTypingCnt + preTypingQcCnt + pregroundAbstractorCnt;
            $('#carried_over_cnt').text(totalSpecificValues);
        },
        error: function (error) {
            console.error('Error:', error);
        }
    });
}


function datewise_datatable(fromDate, toDate, client_id, project_id, selectedDateFilter) {

    fromDate = $('#fromDate_range').val();
    toDate = $('#toDate_range').val();
                client_id = $('#client_id_dcf').val();
    project_id = $('#product_id').val();

                var datatable = $('#datewise_datatable').DataTable({
                destroy: true,
                processing: true,
                serverSide: false,
                searching: true,
                ajax: {
                    url: "{{ route('dashboard_clientwise_count') }}",
                    type: 'POST',
                    data: function(d) {
                        d.to_date = toDate;
                        d.from_date = fromDate;
                        d.client_id = client_id;
                        d.project_id = project_id;
            d.selectedDateFilter = selectedDateFilter;
                        d._token = '{{csrf_token()}}';
                    },
                    dataSrc: 'data'
                },
                columns: [
                    { data: 'client_name', name: 'client_name', className: "text-left" },
                    {
                        data: null,
                        render: function(data, type, row) {
                            return row.project_code +' '+'(' + row.process_name + ')';
                        },
                        name: 'process_name_project_code',
                        className: "text-left"
                    },
                    @if(!Auth::user()->hasRole('Qcer'))
                    { data: 'WIP', name: 'WIP', className: "text-center" },
                    { data: 'Coversheet Prep', name: 'Coversheet Prep', className: "text-center" },
                    @endif
                    { data: 'Doc Purchase', name: 'Doc Purchase', className: "text-center" },
                    { data: 'Ground Abstractor', name: 'Ground Abstractor', className: "text-center" },
                    { data: 'Clarification', name: 'Clarification', className: "text-center" },
                    { data: 'Send for QC', name: 'Send for QC', className: "text-center" },
                    { data: 'Typing', name: 'Typing', className: "text-center" },
                    { data: 'Typing QC', name: 'Typing QC', className: "text-center" },
                    { data: 'Hold', name: 'Hold', className: "text-center" },
                    { data: 'Cancelled', name: 'Cancelled', className: "text-center" },
                    { data: 'Completed', name: 'Completed', className: "text-center" },
                    { data: 'All', name: 'All', className: "text-center" }
                ]
                });
}



$('#datewise_datatable').on('draw.dt', function() {
    $('#datewise_table').removeClass('d-none');
});



    @if(Auth::user()->hasRole(['Super Admin', 'AVP/VP', 'Business Head', 'PM/TL','SPOC']))
    function userwise_datatable(fromDate, toDate, client_id, projectId, selectedDateFilter){
        fromDate = $('#fromDate_range').val();
        toDate = $('#toDate_range').val();
        client_id = $('#client_id_dcf').val();
        project_id = $('#product_id').val();


        datatable = $('#userwise_datatable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('dashboard_userwise_count') }}",
                type: 'POST',
                data: function(d) {
                        d.to_date = toDate;
                        d.from_date = fromDate;
                        d.client_id = client_id;
                        d.project_id = project_id;
                        d.selectedDateFilter = selectedDateFilter;
                        d._token = '{{csrf_token()}}';
                    },
                dataSrc: 'data'
            },
            columns: [
                { data: 'userinfo', name: 'userinfo', class: 'text-left' },
                { data: 'status_1', name: 'status_1', visible:@if(Auth::user()->hasRole('Qcer')) false @else true @endif},
                { data: 'status_13', name: 'status_13' },
                { data: 'status_15', name: 'status_15' },
                { data: 'status_18', name: 'status_18' },
                { data: 'status_14', name: 'status_14' },
                { data: 'status_4', name: 'status_4' },
                { data: 'status_16', name: 'status_16' },
                { data: 'status_17', name: 'status_17' },
                { data: 'status_2', name: 'status_2' },
                { data: 'status_3', name: 'status_3' },
                { data: 'status_5', name: 'status_5' },
                { data: 'All', name: 'All' },
            ],
        });
    }


    $('#userwise_datatable').on('draw.dt', function () {
        $('#userwise_table').removeClass('d-none');
    });
    @endif


 function getGrandTotal(fromDate, toDate, client_id, selectedDateFilter) {
        $.ajax({
            url: "{{ route('getTotalData') }}",
            type: "POST",
            data: {
                from_date: fromDate,
                to_date: toDate,
                client_id: client_id,
                selectedDateFilter: selectedDateFilter,
                _token: '{{csrf_token()}}',
            },
            dataType: 'json',
            success: function (response) {
                let GrandTotal = response.GrandTotal;
                $('#grantCount').text(GrandTotal || 0.00000);
                $('#fte_costs').text(GrandTotal || 0.00000);
                $('#transaction_cost').text(GrandTotal || 0.00000);
                updateTotalCost()
            }
        });
    }

    function updateTotalCost() {
    var fteCost = parseFloat($('#fte_cost').text().replace(/,/g, '')) || 0;
    var transactionCost = parseFloat($('#transaction_cost').text().replace(/,/g, '')) || 0;
    var totalCost = fteCost + transactionCost;

    let formattedFteCost = fteCost.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    let formattedTransactionCost = transactionCost.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    let formattedTotalCost = totalCost.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    $('#fte_cost').text(formattedFteCost); // Formatting fteCost
    $('#fte_costs').text(formattedFteCost); // Displaying formatted fteCost
    $('#transaction_cost').text(formattedTransactionCost); // Formatting and displaying transactionCost
    $('#total_cost').text(formattedTotalCost);
}





    $(document).ready(function () {

        var fromDate = '';
        var toDate = '';
        var client_id = '';

        $('#filterButton').on('click', function (e) {
            e.preventDefault();
            fromDate = $('#fromDate_range').val();
            toDate = $('#toDate_range').val();
            client_id = $('#client_id_dcf').val();
            datatable.ajax.reload();
            selectedDateFilter = selectedDateFilter;
            getGrandTotal(fromDate, toDate,client_id, selectedDateFilter);
            revenueClientWise(fromDate, toDate,client_id, selectedDateFilter);
            processwiseDetail(fromDate, toDate,client_id, selectedDateFilter);
        });

        getGrandTotal(fromDate, toDate,client_id, selectedDateFilter);
        revenueClientWise(fromDate, toDate,client_id, selectedDateFilter);
        processwiseDetail(fromDate, toDate,client_id, selectedDateFilter);


        $(document).on('click', '.project-link', function(event) {
            event.preventDefault(); // Prevent default behavior of the link
            var projectId = $(this).attr('id');
            var processName = $(this).text();
            orderWiseDetail(fromDate, toDate,projectId,processName, selectedDateFilter);
        });


        function processwiseDetail(fromDate, toDate, client_id, selectedDateFilter) {
                datatable = $('#revenueTable').DataTable({
                destroy: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('revenue_detail') }}",
                    type: 'POST',
                    data:  function (d) {
                        d._token = '{{ csrf_token() }}';
                        d.from_date = fromDate;
                        d.to_date = toDate;
                        d.client_id = client_id;
                        d.selectedDateFilter = selectedDateFilter;
                    },
                    dataSrc: function (data) {
                        var rows = [];
                        var grandTotalRevenue = data['Grand Total Revenue'];
                        $.each(data.data, function (index, value) {
                            var date = moment(value['Date']).format('MM/DD/YYYY');
                            var row = {
                                    'Product Code': '<a href="#" id="' + value['id'] + '" class="project-link">' + value['Project Code'] + ' (' + value['Process Name'] + ')</a>',
                                'No of orders completed': value['No of orders completed'],
                                'Unit cost': value['Unit cost'],
                                'Total': value['Total']
                            };
                            rows.push(row);
                        });
                        return rows;
                    }
                },
                columns: [
                        { data: 'Product Code', name: 'Product Code',className:'text-left' },
                    { data: 'No of orders completed', name: 'No of orders completed' },
                        { data: 'Unit cost', name: 'Unit cost',render: function (data){return '$' + data;} },
                        { data: 'Total', name: 'Total',render: function (data){return '$' + data;} }
                ],
            });
            }

        $('#revenueTable').on('draw.dt', function () {
            $('#revenueTable').removeClass('d-none');
        });

        function orderWiseDetail(fromDate, toDate,projectId,processName, selectedDateFilter){
            $('#orderDetailModal').modal('show');
            $('.project_name').text(processName);
            datatable = $('#orderTable').DataTable({
                destroy: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('order_detail') }}",
                    type: 'POST',
                    data:  function (d) {
                        d._token = '{{ csrf_token() }}';
                        d.from_date = fromDate;
                        d.to_date = toDate;
                        d.projectId = projectId;
                        d.selectedDateFilter = selectedDateFilter;
                    },
                    dataSrc: function (data) {
                        var rows = [];
                        $.each(data.data, function (index, value) {
                            var date = moment(value['Date']).format('MM/DD/YYYY');
                            var row = {
                                'Date':  date,
                                'No of orders completed': value['No of orders completed'],
                                'Unit cost': value['Unit cost'],
                                'Total': value['Total']
                            };
                            rows.push(row);
                        });
                        return rows;
                    }
                },
                columns: [
                    { data: 'Date', name: 'Date' },
                    { data: 'No of orders completed', name: 'No of orders completed' },
                    { data: 'Unit cost', name: 'Unit cost' },
                    { data: 'Total', name: 'Total' }
                ],
            });
        }




        var clientId;

        function revenueClientWise(fromDate, toDate,client_id, selectedDateFilter){

            var toDate = toDate;
            var fromDate = fromDate;
            var client_id = client_id;
            // var selectedDateFilter = selectedDateFilter;

            datatable = $('#revenueClientTable').DataTable({
                destroy: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('revenue_detail_client') }}",
                    type: 'POST',
                    data:  function (d) {
                        d.toDate = toDate;
                        d.fromDate = fromDate;
                        d.client_id = client_id;
                        d.selectedDateFilter = selectedDateFilter;
                        d._token = '{{csrf_token()}}';
                    },
                    dataSrc: function (data) {
                        var rows = [];
                        $.each(data.data, function (index, value) {
                            var date = moment(value['Date']).format('MM/DD/YYYY');
                            var row = {
                                // 'Date': date,
                                'Client': value['Client Code'] + ' (' + value['Client Name'] + ')',
                                'No of orders completed': value['No of orders completed'],
                                'Total': value['Total'],
                            };
                            rows.push(row);
                        });
                        return rows;
                    }
                },
                columns: [
                    { data: 'Client', name: 'Client',className:'text-left' },
                    { data: 'No of orders completed', name: 'No of orders completed' },
                    { data: 'Total', name: 'Total',render: function (data) {
						return '$' + data;
					} },
                ],
                createdRow: function (row, data, dataIndex) {
                    $(row).find('.client-link').on('click', function () {
                        var clientId = $(this).attr('id');
                        processwiseDetail(fromDate, toDate,clientId, selectedDateFilter);
                    });
                }
            });

        }
        $('#revenueClientTable').on('draw.dt', function () {
            $('#revenueClientTable').removeClass('d-none');
        });


        });


    // FTE

var fromDate  = "";
var toDate = "";
var client_id  = "";

function pending_status() {

    var datatable = $('#pending_status').DataTable({
    destroy: true,
    processing: true,
    serverSide: false,
    searching: true,
    ajax: {
        url: "{{ route('pending_status') }}",
        type: 'POST',
        data: {
                _token: '{{ csrf_token() }}'
            },
        dataSrc: 'data'
    },
    columns: [
        { data: 'status', name: 'status', className: 'text-center'},
        { data: 'moreThan10Days', name: 'moreThan10Days', className: 'text-center' },
        { data: 'moreThan20Days', name: 'moreThan20Days', className: 'text-center' },
        { data: 'moreThan30Days', name: 'moreThan30Days', className: 'text-center' }
    ]
    });
}

$(document).ready(function() {
    pending_status();
});

    

function tat_zone(fromDate, toDate, clientId, project_id, selectedDateFilter) {
    var fromDate = $('#fromDate_range').val();
    var toDate = $('#toDate_range').val();
    var client_id = $('#client_id_dcf').val();
    var project_id = $('#product_id').val();

    var datatable = $('#tat_zone_datatable').DataTable({
        destroy: true,
        processing: true,
        serverSide: false,
        searching: false, 
        paging: false, 
        info: false, 
    ordering: false,
        ajax: {
            url: "{{ route('tat_zone_count') }}",
            type: 'POST',
            data: {
                fromDate: fromDate,
                toDate: toDate,
                client_id: client_id,
                project_id: project_id,
            selectedDateFilter: selectedDateFilter,
                _token: '{{ csrf_token() }}'
            },
            dataSrc: function (json) {
                var data = [];

            var orderedCounts = [
                json.reachedtat_count,
                json.red_count,
                json.orange_count, 
                json.blue_count, 
                json.green_count 
                ];

                orderedCounts.forEach(function (item) {
                var splitData = item.split(", ");
                    var tat_zone_value = splitData[1].trim(); // Ensure trimming of extra spaces
                    var count_value = splitData[0].trim();

                    data.push({
                        tat_zone: tat_zone_value,
                        count: count_value
                    });
                });

                return data;
            }
        },
        columns: [
            {
            data: 'tat_zone', 
                title: 'TAT Zone',
                render: function (data) {
                    var color = '';
                    var cleanedData = data.toLowerCase().trim(); // Case-insensitive match and trim

                    // Determine the color based on the TAT zone
                    if (cleanedData === 'out of tat') {
                        color = 'brown';
                    } else if (cleanedData === 'super rush') {
                        color = 'red';
                    } else if (cleanedData === 'rush') {
                        color = 'orange';
                    } else if (cleanedData === 'priority') {
                        color = 'blue';
                    } else if (cleanedData === 'non priority') {
                        color = 'green'; // This should correctly match "Non Priority"
                    } else {
                        color = 'black'; // Default color for unknown cases
                    }

                    // Return the colored span
                    return `<span style="color:${color};">${data}</span>`;
                }
            },
        { 
            data: 'count', 
            title: 'Count', 
            render: function (data) {
                    // Inline CSS for center alignment
                    return `<span style="display: block; text-align: center;">${data}</span>`;
            }
        }
        ]
    });
}








$(document).ready(function() {

    $('#filterButton').on('click', function (e) {
        e.preventDefault();
        fromDate = $('#fromDate_range').val();
        toDate = $('#toDate_range').val();
        client_id = $('#client_id_dcf').val();
        selectedDateFilter = selectedDateFilter;
        datatable.ajax.reload();
        fterevenueProjectWise(fromDate, toDate,client_id, selectedDateFilter);
        fterevenueClientWise(fromDate, toDate,client_id, selectedDateFilter);
    });

    $(document).on('click', '.project-link-fte', function(event) {
        event.preventDefault();
        var project_id = $(this).data('id');
        var processName = $(this).text();
        fterevenueProject(fromDate, toDate,processName,project_id, selectedDateFilter);
        ftetotalProccessWise(fromDate, toDate,project_id, selectedDateFilter);
    });


    function ftetotalProccessWise(fromDate, toDate, project_id, selectedDateFilter) {
        $.ajax({
            url: "{{ route('revenue_detail_process_total_fte') }}",
            type: "POST",
            data: {
                from_date: fromDate,
                to_date: toDate,
                project_id: project_id,
                selectedDateFilter: selectedDateFilter,
                _token: '{{csrf_token()}}',
            },
            dataType: 'json',
            success: function (response) {

                let ProcessCountFTE = response.Total;
                $('#ProcessCountFTE').text(ProcessCountFTE);
            }
        });
    }

    // fetchProData(client_id, product_id);
    fterevenueClientWise(fromDate, toDate,client_id, selectedDateFilter);


    function fterevenueClientWise(fromDate, toDate, client_id, selectedDateFilter) {
            fromDate = $('#fromDate_range').val();
            toDate = $('#toDate_range').val();
            client_id = $('#client_id_dcf').val();


            datatable = $('#fterevenueClientTable').DataTable({
                destroy: true,
                processing: true,
                serverSide: false,
                searching: true,
                ajax: {
                    url: "{{ route('revenue_detail_client_fte') }}",
                    type: 'POST',
                    data: function (d) {
                        d.to_date = toDate;
                        d.from_date = fromDate;
                        d.client_id = client_id;
                        d.selectedDateFilter = selectedDateFilter;
                        d._token = '{{csrf_token()}}';
                    },
                    dataSrc: 'data',
                },
                columns: [
                    { data: 'client_name', name: 'client_name', className: "text-left" },
                    { data: 'total_revenue_selected', name: 'total_revenue_selected', className: "text-center",render: function (data) {
						return '$' + data;
					} },
                ],
            });

             datatable.on('draw', function () {
                setTimeout(function () {
                    var total = 0;

                    datatable.rows().every(function (rowIdx, tableLoop, rowLoop) {
                        var data = this.data();
                        var revenueSelected = data.total_revenue_selected.replace(/,/g, '');
                        total += parseFloat(revenueSelected);
                    });


                    $('#fte_cost').text(total.toFixed(2));
                    $('#fte_costs').text(total.toFixed(2));
                    updateTotalCost();
                }, 1000);
            });
    }

    fterevenueProjectWise(fromDate, toDate,client_id, selectedDateFilter);


function fterevenueProjectWise(fromDate, toDate, client_id, selectedDateFilter) {
    var toDate = toDate;
    var fromDate = fromDate;
    var client_id = client_id;
    datatable = $('#fterevenueProjectTable').DataTable({
        destroy: true,
        processing: true,
        serverSide: false,
        searching: true,
        ajax: {
            url: "{{ route('revenue_detail_process_fte') }}",
            type: 'POST',
            data: function (d) {
                d.to_date = toDate;
                d.from_date = fromDate;
                d.client_id = client_id;
                d.selectedDateFilter = selectedDateFilter;
                d._token = '{{csrf_token()}}';
            },
            dataSrc: 'data',
        },
        columns: [
            { data: 'client_name', name: 'client_name', className: "text-left" },
            // { // Merge 'process_name' and 'project_code' into one column
            //     data: function (row) {
            //         return row.project_code + '  (' + row.process_name + ')';
            //     },
            //     name: 'project_code',
            // },

            {
        // Merge 'process_name' and 'project_code' into one column with a link
            data: function (row) {

                return '<a href="#" class="project-link-fte" data-id="' + row.id + '">' + row.project_code + ' (' + row.process_name + ')</a>';
            },

            name: 'project_code', className: "text-left"
                },
            { data: 'unit_cost', name: 'unit_cost', className: "text-center",render: function (data) {
					return '$' + data;
			} },
            { data: 'no_of_resources', name: 'no_of_resources', className: "text-center" },
            { data: 'expected_revenue', name: 'expected_revenue', className: "text-center",render: function (data) {
					return '$' + data;
			} },
            { data: 'start_date', name: 'start_date', className: "text-center" },
            { data: 'end_date', name: 'end_date', className: "text-center" },
            { data: 'days', name: 'days', className: "text-center" },
            { data: 'revenue_selected', name: 'revenue_selected', className: "text-center",render: function (data) {
					return '$' + data;
			} },
        ],
    });
    }

  $(document).on('click', '.project-link-fte', function(event) {
            event.preventDefault();
            var project_id = $(this).data('id');
            var processName = $(this).text();
            fterevenueProject(fromDate, toDate,processName,project_id, selectedDateFilter);
            ftetotalProccessWise(fromDate, toDate,project_id, selectedDateFilter);
        });



            $('#fterevenueProjectTable').on('draw.dt', function () {
                $('#fteClientTable').removeClass('d-none');
            });


          function fterevenueProject(fromDate, toDate,processName,project_id, selectedDateFilter){
                            $('#fteDetailModal').modal('show');
                            $('.project_name').text(processName);

                            datatable = $('#fterevenueProject').DataTable({
                            destroy: true,
                            processing: true,
                            serverSide: true,
                            ajax: {
                                url: "{{ route('revenue_detail_processDetail_fte') }}",
                                type: 'POST',
                                data:  function (d) {
                                    d._token = '{{ csrf_token() }}';
                                    d.from_date = fromDate;
                                    d.to_date = toDate;
                                    d.project_id = project_id;
                                    d.selectedDateFilter = selectedDateFilter;
                                },
                                dataSrc: function (data) {
                                    var rows = [];
                                    $.each(data.data, function (index, value) {

                                        var date = moment(value['Date']).format('MM/DD/YYYY');
                                        var row = {
                                            'Date':  date,
                                            'No of Resources': value['No of Resources'],
                                            'Unit cost': value['Unit Cost'],
                                            'Total': value['Monthly Revenue']
                                        };
                                        rows.push(row);
                                    });
                                    return rows;
                                }
                            },
                            columns: [
                                { data: 'Date', name: 'Date' },
                                { data: 'No of Resources', name: 'No of Resources' },
                                { data: 'Unit cost', name: 'Unit cost',render: function (data) {
                                	return '$' + data;
                                } },
                                { data: 'Total', name: 'Total',render: function (data) {
									return '$' + data;
								} }
                            ],
                        });

                    }
        });



//date script-start

    // Get the current date from PHP and create a new Date object
    let currentDate12 = new Date('<?php echo $currentDate; ?>');

    // Set the modified date as the value of the input element
    document.getElementById('toDate_range').valueAsDate = currentDate12;

    // document.getElementById('toDate_range').valueAsDate = new Date('<?php echo $currentDate; ?>');

    // var currentDate = new Date();
    var currentDate = new Date('<?php echo $currentDate; ?>');

    var firstDayOfMonth = new Date(currentDate.getFullYear(), currentDate.getMonth(), 2);

    var formattedDate = firstDayOfMonth.toISOString().split('T')[0];

    document.getElementById('fromDate_range').value = formattedDate;

    // future date script
function isFutureDate(date) {
    var currentDate = new Date('<?php echo $currentDate; ?>');
    return date > currentDate;
}

// Event listener for toDate_range
document.getElementById('toDate_range').addEventListener('change', function() {
    var selectedDate = new Date(this.value);

    if (isFutureDate(selectedDate)) {
        alert("You cannot select a future date.");
        this.valueAsDate = currentDate12;
    }
});

// Event listener for fromDate_range
document.getElementById('fromDate_range').addEventListener('change', function() {
    var selectedDate = new Date(this.value);
        if (isFutureDate(selectedDate)) {
        alert("You cannot select a future date.");
        this.valueAsDate = currentDate12;
    }
});



$(document).ready(function () {
    $("#filterButton").click();
});

    function fetchLobData(client_id, product_id, selectedDateFilter) {
        $.ajax({
            url: "{{ url('get_lob_dashboard') }}",
            type: "POST",
            data: {
                client_id: client_id,
                product_id: product_id,
                selectedDateFilter: selectedDateFilter,
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


    function fetchProcessData(lob_id, client_id, selectedDateFilter) {
    $("#process_type_id").html('');
    $("#product_id").html('');

    $.ajax({
        url: "{{ url('get_process_dashboard') }}",
        type: "POST",
        data: {
            lob_id: lob_id,
            client_id: client_id,
            selectedDateFilter: selectedDateFilter,
            _token: '{{ csrf_token() }}'
        },
        dataType: 'json',
        success: function (response) {            
            $('#process_type_id').html('<option selected value="All">All</option>');
            $('#product_id').html('<option selected value="All">All</option>');

            // Populate process_type_id
            if(response.process) {
                $.each(response.process, function (index, item) {
                    $("#process_type_id").append('<option value="' + item.id + '">' + item.name + '</option>');
                });
            }

            // Populate product_id
            if(response.products) {
                $.each(response.products, function (index, item) {
                    $("#product_id").append('<option value="' + item.id + '">' + item.process_name + ' (' + item.project_code + ')</option>');
                });
        }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error: ' + status + ' - ' + error);
    }
    });
}
function fetchProcessTypeData(process_type_id, client_id, lob_id, selectedDateFilter) {
    $("#product_id").html(''); 
    $.ajax({
        url: "{{ url('get_product_dashboard') }}",
        type: "POST",
        data: {
            process_type_id: process_type_id,
            client_id: client_id,
            lob_id: lob_id,
            selectedDateFilter: selectedDateFilter,
            _token: '{{ csrf_token() }}'
        },
        dataType: 'json',
        success: function (response) {            
            $('#product_id').html('<option selected value="All">All</option>');

            if(response && response.length > 0) {
                $.each(response, function (index, item) {
                    $("#product_id").append('<option value="' + item.id + '">' + '(' + item.project_code + ') ' + item.process_name + '</option>');
                });
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error: ' + status + ' - ' + error);
        }
    });
}

$(document).ready(function() {
        $('#billing_id_dcf').on('change', function() {
            var value = $(this).val();
            if (value === 'All') {
                $('#ftetabledetails, #Trans_hide').show();
            } else if (value === 'FTE') {
                $('#ftetabledetails').show();
                $('#Trans_hide').hide();
            } else if (value === 'TXN') {
                $('#ftetabledetails').hide();
                $('#Trans_hide').show();
            }
        });
    });



function total_users() {
    $.ajax({
        url: "{{ route('total_users') }}",
        type: "GET",
        dataType: 'json',
        success: function (response) {
            // Update the text of available_users and total_users
            $('#available_users').text(response.active_user); // Display active users
            $('#total_users').text(response.user_lower_count); // Display total lower level users
        },
        error: function (xhr, status, error) {
            console.error("Error fetching user data:", error);
        }
    });
}

// Call the function to execute it
total_users();
setInterval(total_users, 600000);




function total_users_name() {
    var table = $('#available_resources').DataTable({
        destroy: true,
        processing: true,
        serverSide: false,
        searching: true, 
        lengthChange: false,
        pageLength: 5,
        ajax: {
            url: "{{ route('total_users_name') }}", 
            type: "GET",
            dataSrc: 'data', 
        },
        columns: [
            { data: 'emp_id', name: 'emp_id' }, // Maps to Emp Id
            { data: 'username', name: 'username' } // Maps to Emp Name
        ],
        columnDefs: [
            { width: "20px", targets: [0, 1] } // Set width for both columns
        ],
        autoWidth: false

    });
}

$(document).ready(function() {
    total_users_name();
});
setInterval(total_users_name, 600000);



function carry_over_monthly(fromDate, toDate, clientId, projectId, selectedDateFilter) {
    var from_date = $('#fromDate_range').val();
    var to_date = $('#toDate_range').val();
    var client_id = $('#client_id_dcf').val();
    var project_id = $('#product_id').val();

    datatable = $('#carry_over_monthly').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        searching: false,
        paging: false,          // Disable pagination
        lengthChange: false, 
        info: false,
        ajax: {
            url: "{{ route('carry_over_monthly_count') }}",
            type: 'POST',
            data: {
                from_date: from_date,
                to_date: to_date,
                client_id: client_id,
                project_id: project_id,
                selectedDateFilter: selectedDateFilter,
                _token: '{{ csrf_token() }}'
            },
            dataSrc: function(json) {
                // Combine monthly and daily data into a single array
                return [
                    {
                        monthLabel: "MONTHLY",
                        carry_forward: json.data[0].carry_forward,
                        received: json.data[0].received,
                        completed: json.data[0].completed,
                        pending: json.data[0].pending
                    },
                    {
                        monthLabel: "DAILY",
                        carry_forward: json.data[1].carry_forward,
                        received: json.data[1].received,
                        completed: json.data[1].completed,
                        pending: json.data[1].pending
                    }
                ];
            }
        },
        columns: [
            { data: 'monthLabel', name: 'monthLabel' },
            { data: 'carry_forward', name: 'carry_forward' },
            { data: 'received', name: 'received' }, 
            { data: 'completed', name: 'completed' }, 
            { data: 'pending', name: 'pending' } 
        ],
    });
}




  


</script>
@endsection

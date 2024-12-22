@extends('layouts.app')
@section('title', config('app.name') . ' | Orders')
@section('content')
@include('app.orders.style')


<style>
    #searchInputs {
    width: 200px;
    padding: 10px;
    font-size: 14px; /* Adjusted font size */
    border: 1px solid #ccc;
    border-radius: 5px;
    height: 33px;
}

#customfromRange {
    display: flex;
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

#customToRange label {
    font-weight: bold;
    color: #007bff; /* Change to your preferred color */
}

#customToRange input[type="date"] {
    border: 1px solid #007bff; /* Match the border color with the label color */
    padding: 5px;
    border-radius: 4px;
}

#customToRange .input-wrapper {
    display: flex;
    align-items: center;
    gap: 10px; /* Space between label and input */
}

.select-disabled {
    pointer-events: none; 
    cursor: not-allowed; 
}
.div1{
    background-color: lightgrey;
    width: 120px;
    height:40px;
    border: 1px solid green;
    /* padding: 10px;
    margin: 20px; */
}

.border-box {
    border: 1px solid #000; /* Add your preferred color */
    border-radius: 4px; /* Rounded corners */
    /* padding: 10px; Adjust padding */
    height: 30px; /* Adjust height based on content */
    display: inline-block; /* Ensure it wraps only the content */
}

#out_of_tat{
    color: #964B00;
}

#super_rush{
    color:red;
}

#rush{
    color:orange;
}

#non_priority{
    color:green;
}

#priority{
    color:blue;
}
.black-text {
    color: black;
}

</style>

{{-- Edit Model Order --}}
<div class="modal fade" id="myModalEdit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Edit Order</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" id="updateorderfrm" name="updateorderfrm"  data-parsley-validate enctype="multipart/form-data">
                @csrf
                <input  name="id" value="" id="id_ed" type="hidden">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-4">
                            <label for="compamy-name-input" class="font-weight-bold">Order Id<span class="text-danger"> <span></label>
                            <input id="order_id_ed" name="order_id" value="" type="text" class="form-control" autocomplete="off" readonly>
                        </div>
                        <div class="col-lg-4">
                            <label for="order_date" class="font-weight-bold">Order Received Date<span style="color:red;">*</span></label>
                            <br>
                            <input type="datetime-local" id="order_date_ed" class="form-control" step="1" name="order_date" format="MM-DD-YYYY THH:mm:ss" hour24="true">
                        </div>
                        <div class="col-lg-4">
                            <label class="font-weight-bold">Product Code<span style="color:red;">*</span></label><br>
                            <select class="form-control" style="width:100%" name="process_code" id="process_code_ed" aria-hidden="true" required>
                                <option selected="" disabled="" value="">Select Project Code</option>
                                @foreach ($processList as $process)
                                    <option value="{{ $process->id }}">{!! $process->project_code.' ('.$process->process_name.')' !!}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mt-1">
                        <div class="col-lg-4">
                            <label class="font-weight-bold">State</label>
                            <select id="property_state_ed" name="property_state" type="text" class="form-control" autocomplete="off" placeholder="Enter Status"  data-parsley-trigger="focusout" data-parsley-trigger="keyup">
                                <option selected disabled value="">Select State</option>
                                @foreach ($stateList as $state)
                                        <option value="{{ $state->id }}">{{ $state->short_code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4">
                            <label class="font-weight-bold">County</label>
                            <select id="property_county_ed" name="property_county" type="text" class="form-control" autocomplete="off" placeholder="Enter Status"  data-parsley-trigger="focusout" data-parsley-trigger="keyup">
                                <option selected disabled value="">Select County</option>
                                @foreach ($countyList as $county)
                                     <option value="{{ $county->id }}">{{ $county->county_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4 mt-4" id="hide_reasign">
                            <input  type="checkbox" id="re_assign" name="re_assign"><label style="font-size: 0.8rem !important;" class="mx-2" for="">Re-Assign</label>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                        <div class="col-lg-4 mb-4" id="hide_user">
                            <label class="font-weight-bold text-right">Assign User</label>
                            <select id="assign_user_ed" name="assign_user" type="text" class="select2dropdown form-control">
                                <option selected disabled value="">Select Assign User</option>
                                @foreach ($processors as $process)
                                    <option value="{{ $process->id }}">{{ $process->username }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" class="btn btn-info" id="unassign_user">Unassign User</button>

                        <div class="col-lg-4 mb-4" id="hide_qa">
                            <label class="font-weight-bold text-right">Assign QA</label>
                            <select id="assign_qa_ed" name="assign_qa" type="text" class="select2dropdown form-control">
                                <option selected disabled value="">Select Assign QA</option>
                                @foreach ($qcers as $qcer)
                                    <option value="{{ $qcer->id }}">{{ $qcer->username }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" class="btn btn-info" id="unassign_qcer">Unassign QC</button>

                        <button type="button" class="btn btn-danger">Close</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>

                </div>

            </form>
        </div>
    </div>
</div>

<div class="col-lg-12 mt-2">
    <div class="frame d-flex align-items-center justify-content-center" style="height: 100%; width: 100%;">
        <div class="center">
            <div class="dot-1"></div>
            <div class="dot-2"></div>
            <div class="dot-3"></div>
        </div>
    </div>
    <div class="card content-loaded">
        <div class="card-body">
            <div class="row justify-content-start m-3 mt-2 mb-4" id="statusButtons">
                <div class="bg-info shadow-lg p-0 rounded text-white" style="text-decoration: none; font-size:0.7rem">
                    <button id="status_6"  class="btn btn-info status-btn @if(Auth::user()->hasAnyRole(['Qcer', 'Typist', 'Typist/Qcer', 'Typist/Typist_Qcer', 'Tax User'])) d-none @endif" style="cursor: pointer;">Yet to Assign User<span id="status_6_count"></span>
                     <div style="">
                            <div style="display: inline-block;"></div>
                            <!-- <span id="tat_status_6_third_count"></span> -->
                            <div style="display: inline-block;"></div>
                            <!-- <span id="tat_status_6_fourth_count"></span> -->
                        </div>
                    </button>
                    <button id="status_7"  class="btn btn-info status-btn d-none">Yet to Assign QA<span id="status_7_count"></span><div style="">
                            <div style="display: inline-block; background-color: orange; width: 10px; height: 10px; margin-right: 5px;"></div>
                            <span id="tat_status_7_third_count">0</span>
                            <div style="display: inline-block; background-color: red; width: 10px; height: 10px; margin-right: 5px; margin-left: 10px;"></div>
                            <span id="tat_status_7_fourth_count">0</span>
                        </div>
                    </button>
                    <button id="status_1" class="btn btn-info status-btn @if(Auth::user()->hasAnyRole(['Qcer', 'Typist', 'Typist/Qcer', 'Typist/Typist_Qcer', 'Tax User'])) d-none @endif">WIP<span id="status_1_count"></span> <div style="">
                            <div style="display: inline-block; background-color: orange; width: 10px; height: 10px; margin-right: 5px;"></div>
                            <span id="tat_status_1_third_count">0</span>
                            <div style="display: inline-block; background-color: red; width: 10px; height: 10px; margin-right: 5px; margin-left: 10px;"></div>
                            <span id="tat_status_1_fourth_count">0</span>
                        </div></button>
                    <button id="status_13" class="btn btn-info status-btn @if(Auth::user()->hasAnyRole(['Qcer', 'Typist', 'Typist/Qcer', 'Typist/Typist_Qcer', 'Tax User'])) d-none @endif">Coversheet Prep<span id="status_13_count"></span><div style="">
                            <div style="display: inline-block; background-color: orange; width: 10px; height: 10px; margin-right: 5px;"></div>
                            <span id="tat_status_13_third_count">0</span>
                            <div style="display: inline-block; background-color: red; width: 10px; height: 10px; margin-right: 5px; margin-left: 10px;"></div>
                            <span id="tat_status_13_fourth_count">0</span>
                        </div></button>
                    <button id="status_15" class="btn btn-info status-btn @if(Auth::user()->hasAnyRole(['Process/Qcer', 'Typist', 'Typist/Qcer', 'Typist/Typist_Qcer', 'Tax User'])) d-none @endif" >Doc Purchase<span id="status_15_count"></span> <div style="">
                            <div style="display: inline-block; background-color: orange; width: 10px; height: 10px; margin-right: 5px;"></div>
                            <span id="tat_status_15_third_count">0</span>
                            <div style="display: inline-block; background-color: red; width: 10px; height: 10px; margin-right: 5px; margin-left: 10px;"></div>
                            <span id="tat_status_15_fourth_count">0</span>
                        </div></button>
                    <button id="status_18" class="btn btn-info status-btn @if(Auth::user()->hasAnyRole(['Tax User'])) d-none @endif">Ground Abstractor<span id="status_18_count"></span><div style="">
                        <div style="display: inline-block; background-color: orange; width: 10px; height: 10px; margin-right: 5px;"></div>
                        <span id="tat_status_18_third_count">0</span>
                        <div style="display: inline-block; background-color: red; width: 10px; height: 10px; margin-right: 5px; margin-left: 10px;"></div>
                        <span id="tat_status_18_fourth_count">0</span>
                    </div></button>
                    <button id="status_tax" class="btn btn-info status-btn  @if(!(Auth::user()->hasAnyRole(['Tax User', 'Super Admin', 'Business Head', 'PM/TL', 'SPOC', 'AVP', 'Admin', 'VP']))) d-none @endif">TAX<span id="status_tax_count"></span><div style="">
                        <div style="display: inline-block; background-color: ; width: 10px; height: 10px; margin-right: 5px;"></div>
                        <!-- <span id="tat_status_19_third_count">0</span> -->
                        <div style="display: inline-block; background-color: ; width: 10px; height: 10px; margin-right: 5px; margin-left: 10px;"></div>
                        <!-- <span id="tat_status_19_fourth_count">0</span> -->
                    </div></button>
                    <button id="status_14" class="btn btn-info status-btn @if(Auth::user()->hasAnyRole(['Tax User'])) d-none @endif">Clarification<span id="status_14_count"></span><div style="">
                            <div style="display: inline-block; background-color: orange; width: 10px; height: 10px; margin-right: 5px;"></div>
                            <span id="tat_status_14_third_count">0</span>
                            <div style="display: inline-block; background-color: red; width: 10px; height: 10px; margin-right: 5px; margin-left: 10px;"></div>
                            <span id="tat_status_14_fourth_count">0</span>
                        </div></button>
                    <button id="status_4" class="btn btn-info status-btn @if(Auth::user()->hasAnyRole(['Tax User'])) d-none @endif">Send For QC<span id="status_4_count"></span><div style="">
                            <div style="display: inline-block; background-color: orange; width: 10px; height: 10px; margin-right: 5px;"></div>
                            <span id="tat_status_4_third_count">0</span>
                            <div style="display: inline-block; background-color: red; width: 10px; height: 10px; margin-right: 5px; margin-left: 10px;"></div>
                            <span id="tat_status_4_fourth_count">0</span>
                        </div></button>
                    <button id="status_16" class="btn btn-info status-btn @if(Auth::user()->hasAnyRole(['Tax User', 'Typist/Qcer', 'Process/Qcer', 'Qcer', 'Process'])) d-none @endif">Typing<span id="status_16_count"></span><div style="">
                            <div style="display: inline-block; background-color: orange; width: 10px; height: 10px; margin-right: 5px;"></div>
                            <span id="tat_status_16_third_count">0</span>
                            <div style="display: inline-block; background-color: red; width: 10px; height: 10px; margin-right: 5px; margin-left: 10px;"></div>
                            <span id="tat_status_16_fourth_count">0</span>
                        </div></button>
                    <button id="status_17" class="btn btn-info status-btn @if(Auth::user()->hasAnyRole(['Tax User', 'Typist', 'Process/Qcer', 'Qcer', 'Process'])) d-none @endif">Typing QC<span id="status_17_count"></span> <div style="">
                            <div style="display: inline-block; background-color: orange; width: 10px; height: 10px; margin-right: 5px;"></div>
                            <span id="tat_status_17_third_count">0</span>
                            <div style="display: inline-block; background-color: red; width: 10px; height: 10px; margin-right: 5px; margin-left: 10px;"></div>
                            <span id="tat_status_17_fourth_count">0</span>
                        </div></button>
                    <button id="status_2" class="btn btn-info status-btn @if(Auth::user()->hasAnyRole(['Tax User'])) d-none @endif">Hold<span id="status_2_count"></span><div style="">
                            <div style="display: inline-block; background-color: orange; width: 10px; height: 10px; margin-right: 5px;"></div>
                            <span id="tat_status_2_third_count">0</span>
                            <div style="display: inline-block; background-color: red; width: 10px; height: 10px; margin-right: 5px; margin-left: 10px;"></div>
                            <span id="tat_status_2_fourth_count">0</span>
                        </div></button>
                    <button id="status_5" class="btn btn-info status-btn @if(Auth::user()->hasAnyRole(['Tax User'])) d-none @endif">Completed<span id="status_5_count"></span><div style="">
                            <div style="display: inline-block; background-color: orange; width: 10px; height: 10px; margin-right: 5px;"></div>
                            <span id="tat_status_5_third_count">0</span>
                            <div style="display: inline-block; background-color: red; width: 10px; height: 10px; margin-right: 5px; margin-left: 10px;"></div>
                            <span id="tat_status_5_fourth_count">0</span>
                        </div></button>
                    <button id="status_20" class="btn btn-info status-btn @if(Auth::user()->hasAnyRole(['Tax User'])) d-none @endif">Partially Cancelled<span id="status_20_count"></span><div style="">
                            <div style="display: inline-block; background-color: orange; width: 10px; height: 10px; margin-right: 5px;"></div>
                            <span id="tat_status_20_third_count">0</span>
                            <div style="display: inline-block; background-color: red; width: 10px; height: 10px; margin-right: 5px; margin-left: 10px;"></div>
                            <span id="tat_status_20_fourth_count">0</span>
                    </div></button>
                    <button id="status_3" class="btn btn-info status-btn @if(Auth::user()->hasAnyRole(['Tax User'])) d-none @endif">Cancelled<span id="status_3_count"></span><div style="">
                            <div style="display: inline-block; background-color: orange; width: 10px; height: 10px; margin-right: 5px;"></div>
                            <span id="tat_status_3_third_count">0</span>
                            <div style="display: inline-block; background-color: red; width: 10px; height: 10px; margin-right: 5px; margin-left: 10px;"></div>
                            <span id="tat_status_3_fourth_count">0</span>
                        </div></button>
                    <button id="status_All" class="btn btn-info status-btn @if(Auth::user()->hasAnyRole(['Tax User'])) d-none @endif" >All<span id="status_All_count"></span><div style="">
                            <div style="display: inline-block; background-color: orange; width: 10px; height: 10px; margin-right: 5px;"></div>
                            <span id="tat_status_All_third_count">0</span>
                            <div style="display: inline-block; background-color: red; width: 10px; height: 10px; margin-right: 5px; margin-left: 10px;"></div>
                            <span id="tat_status_All_fourth_count">0</span>
                        </div></button>
                </div>
                <div>
                    <div style="background-color: orange; width: 10px; height: 10px; margin-right: 5px; margin-left: 10px; margin-top: 7px; display: inline-block;"></div>
                    <div style="display: inline-block;">Rush</div><br>
                    <div style="background-color: red; width: 10px; height: 10px; margin-right: 5px; margin-left: 10px; margin-top: 7px; display: inline-block;"></div>
                    <div style="display: inline-block;">Super Rush</div>
                </div>
            </div>
           
            
            <div class="p-0 mx-2" id="filter_search">
                <div class="row ml-5">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="dateFilter"><b>Select Date Range:</b></label>
                            <select class="form-control" id="dateFilter" onchange="selectDateFilter(this.value)" style="border:1px solid blue;">
                                <option value="" selected >Select Date Range</option>
                                <option value="last_week">Last week</option>
                                <option value="this_week">Current week</option>
                                <option value="last_30_days">Last Month</option>
                                <option value="this_month">Current Month</option>
                                <option value="yearly">Yearly</option>
                                <option value="custom">Custom Range</option>

                            </select>
                        </div>
                        <b class ="mt-0 ml-2 mb-1" id="selectedDate"></b>
                    </div>

                    {{-- From to --}}
                    <div class="col-md-2 mt-4"  id="customfromRange"  style="display: none;">
                        <div class="input-group">
                            <span class="input-group-text">From:</span>
                            <input type="date" class="form-control" id="fromDate_range">
                        </div>
                    </div>

                    <div class="col-md-2 mt-4"  id="customToRange"  style="display: none;">
                        <div class="input-group">
                            <span class="input-group-text">To:</span>
                            <input type="date" class="form-control" id="toDate_range">
                        </div>
                    </div>
                    {{--  --}}
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="searchType"><b>Search Type:</b></label>
                            <select class="form-control " id="searchType" name="searchType" style="border:1px solid blue;">
                                <option value="" selected >Select Search Type</option>
                                <option value="1" id="search_orderid">Order Id</option>
                                <option value="2" id="search_productcode">Product Code</option>
                                <option value="3" id="search_client">Client</option>
                                <option value="4" id="search_lob">Lob</option>
                                <option value="5" id="search_process">Process</option>
                                <option value="6" id="search_product">Product</option>
                                <option value="7" id="search_tier">Tier</option>
                                <option value="8" id="search_state">State</option>
                                <option value="9" id="search_County">County</option>
                                @if(!Auth::user()->hasRole('Process'))
                                <option value="10" id="search_user">User</option>
                                @endif
                                @if(!Auth::user()->hasRole('Qcer'))
                                <option value="11" id="search_qa">QA</option>
                                @endif
                            </select>
                        </div>
            
                    </div>


                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="searchInputs"><b>Search</b>:</label>
                            <input type="text" class="form-control" id="searchInputs" style="border:1px solid blue;">
                            <p id="orderIdTip" class="red-text" style="display:none; color:red;">Use Comma separator for multiple search</p>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="tat_zone_filter"><b>TAT ZONE:</b></label>
                            <select class="form-control " id="tat_zone_filter" name="tat_zone_filter" style="border:1px solid blue;">
                                <option value="" selected >Select Tat Zone</option>
                                <option value="1" id="out_of_tat">Out of TAT</option>
                                <option value="2" id="super_rush">Super Rush</option>
                                <option value="3" id="rush">Rush</option>
                                <option value="4" id="priority">Priority</option>
                                <option value="5" id="non_priority">Non Priority</option>
                            </select>
                        </div>

                    </div>

                    <div class="col-md-1 mt-4">
                        <button type="submit" id="filterButton" class="btn btn-primary">Filter</button>
                    </div>
                    
                    
                </div>
            </div>
                
                <form id="assignmentForm" method="POST" data-parsley-validate>
                    <table id="order_datatable" class="table table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                        <tr>
                            <th style="width:10%">Order ID</th>
                            <th style="width:10%">Received Date</th>
                            <th style="width:10%">Product Code @for($i = 0; $i < 5; $i++) &nbsp; @endfor</th>
                            <th style="width:10%">Client @for($i = 0; $i < 5; $i++) &nbsp; @endfor</th> 
                            <th style="width:10%">Lob</th>
                            <th style="width:10%">Process</th>
                            <th style="width:10%">Product</th>
                            <th style="width:10%">Tier</th>
                            <th style="width:15%">State @for($i = 0; $i < 5; $i++) &nbsp; @endfor</th>
                            <th style="width:15%">County @for($i = 0; $i < 5; $i++) &nbsp; @endfor</th>
                            <th style="width:20%">Status @for($i = 0; $i < 17; $i++) &nbsp; @endfor</th>
                            <th style="width:10%">User</th>
                            <th style="width:10%">QA</th>
                            <th style="width:10%">Typists</th>
                            <th style="width:10%">Typists QC</th>
                            @if(Auth::user()->hasRole('Process') || Auth::user()->hasRole('Qcer') || Auth::user()->hasRole('Process/Qcer'))
                            <th style="width: 7%">Assign</th>
                            @else
                            <th style="width: 5%">
                                <input type="checkbox" class="check-all">
                                All
                            </th>
                            @endif
                            <th style="width:7%">Action</th>
                            <th style="width:10%">Coversheet Preparer</th>
                            <th style="width:7%">Created Date</th>

                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>


                    <div class="form-group">
                        <div class="row d-none" id="assign_tab">
                            <div class="col-12 row mt-5">
                                <div class="col-2">
                                    <select style="width: 100%;" class="form-control form-control-sm" id="user_id" name="user_id">
                                        <option selected disabled value="">Select User</option>
                                        @foreach ($processors as $processor)
                                            <option value="{{ $processor->id }}">{{ $processor->username }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-2">
                                    <select style="width: 100%;" class="form-control form-control-sm" id="qcer_id" name="qcer_id">
                                        <option selected disabled value="">Select Qcer</option>
                                        @foreach ($qcers as $qcer)
                                            <option value="{{ $qcer->id }}">{{ $qcer->username }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-2" id="coversheetPrepDropdown">
                                    <select style="width: 100%;"  class="form-control form-control-sm" id="cover_prep_id" name="cover_prep_id">
                                    <option selected disabled value=""> Select coversheet-preparor</option>
                                    </select>
                                </div>
                                <div class="col-2" id="typist_div">
                                    <select style="width: 100%;" class="form-control form-control-sm" id="typist_id" name="typist_id">
                                        <option selected disabled value="">Select Typists</option>
                                        @foreach ($typists as $typist)
                                            <option value="{{ $typist->id }}">{{ $typist->username }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="col-2" id="typist_qc_div">
                                    <select style="width: 100%;" class="form-control form-control-sm" id="typist_qc_id" name="typist_qc_id">
                                        <option selected disabled value="">Select Typists_QC</option>
                                        @foreach ($typists_qcs as $typists_qc)
                                            <option value="{{ $typists_qc->id }}">{{ $typists_qc->username }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                 <div class="col-2" id="status_change_div">
                                    <select style="width: 100%;" class="form-control form-control" id="status_change" name="status_change">
                                        <option selected disabled value="">Select Status</option>
                                        @foreach ($status_changes as $status_change)
                                            <option value="{{ $status_change->id }}">{{ $status_change->status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-2">
                                    <button type="submit" class="btn btn-sm btn-primary" id="assignBtn" name="assign">Assign</button>
                                    <button class="btn btn-sm ml-2" id="deleteBtn" name="deleteBtn" style="background-color: #ef4d56; color: white; border: none;">Delete</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">

//date validation


document.addEventListener('DOMContentLoaded', function() {
        var fromDateInput = document.getElementById('fromDate_range');
        var toDateInput = document.getElementById('toDate_range');

        // Add change event listener to fromDateInput
        fromDateInput.addEventListener('change', function() {
            var fromDate = new Date(fromDateInput.value);
            var today = new Date();

            // Check if fromDate is greater than today
            if (fromDate > today) {
                alert('From Date cannot be a future date.');
                fromDateInput.value = ''; // Reset fromDate if invalid
            }
        });

        // Add change event listener to toDateInput
        toDateInput.addEventListener('change', function() {
            var toDate = new Date(toDateInput.value);
            var today = new Date();

            // Check if toDate is greater than today
            if (toDate > today) {
                alert('To Date cannot be greater than today.');
                toDateInput.value = ''; // Reset toDate if invalid
            }

            // Check if toDate is less than fromDate
            var fromDate = new Date(fromDateInput.value);
            if (toDate < fromDate) {
                alert('To Date must be greater than From Date.');
                toDateInput.value = ''; // Reset toDate if invalid
            }
        });
    });


let selectedDateFilter = ''; // Global variable to hold selected date filter

function selectDateFilter(value) {
    let dateDisplay = document.getElementById('selectedDate');
    let customRangeDiv1 = document.getElementById('customfromRange');
    let customRangeDiv2 = document.getElementById('customToRange');
    let fromDateInput = $('#fromDate_range');
    let toDateInput = $('#toDate_range');

    // Reset custom range inputs before processing new selection
    fromDateInput.val('');
    toDateInput.val('');
    customRangeDiv1.style.display = 'none';
    customRangeDiv2.style.display = 'none';

    switch (value) {
        case 'last_week':
            selectedDateFilter = getLastWeekDate();
            break;
        case 'this_week':
            selectedDateFilter = getThisWeekDate();
            break;
        case 'last_30_days':
            selectedDateFilter = getLastMonthDate();
            break;
        case 'this_month':
            selectedDateFilter = getCurrentMonthDate();
            break;
        case 'yearly':
            selectedDateFilter = getYearlyDate();
            break;
        case 'custom':
            selectedDateFilter = ''; // This will be handled separately
            customRangeDiv1.style.display = 'block';
            customRangeDiv2.style.display = 'block'; // Show custom range inputs
            break;
        default:
            selectedDateFilter = '';
    }

    dateDisplay.textContent = selectedDateFilter;
}

// Example functions to get dynamic date ranges
function getTodayDate() {
    let  StartDate = new Date();
    return `Selected:${formatDate(StartDate)}`;
}

function getYesterdayDate() {
    let StartDate = new Date();
    StartDate.setDate(StartDate.getDate() - 1);
    return `Selected:${formatDate(StartDate)}`;
}

function getLastWeekDate() {
    // Helper function to format the date in YYYY-MM-DD format
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are zero-based
        const day = String(date.getDate()).padStart(2, '0');
        return `${month}-${day}-${year}`;
    }

    let today = new Date();
    let dayOfWeek = today.getDay(); // Sunday is 0, Monday is 1, ..., Saturday is 6

    // Calculate the start of the current week (Monday)
    let currentWeekStart = new Date(today);
    currentWeekStart.setDate(today.getDate() - ((dayOfWeek + 6) % 7));

    // Calculate the start of the previous week
    let StartDate = new Date(currentWeekStart);
    StartDate.setDate(currentWeekStart.getDate() - 7);

    // Calculate the end of the previous week (Sunday)
    let EndDate = new Date(StartDate);
    EndDate.setDate(StartDate.getDate() + 6);

    return `Selected:${formatDate(StartDate)} to ${formatDate(EndDate)}`;
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


function getThisWeekDate() {

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


    return `Selected:${formatDate(StartDate)} to ${formatDate(EndDate)}`;
}

function getLastMonthDate() {

    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are zero-based
        const day = String(date.getDate()).padStart(2, '0');
        return `${month}-${day}-${year}`;
    }

    let today = new Date();


    let firstDayOfCurrentMonth = new Date(today.getFullYear(), today.getMonth(), 1);

    let EndDate = new Date(firstDayOfCurrentMonth - 1);


    let StartDate = new Date(EndDate.getFullYear(), EndDate.getMonth(), 1);

    return `Selected:${formatDate(StartDate)} to ${formatDate(EndDate)}`;
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

    return `Selected:${formatDate(StartDate)} to ${formatDate(EndDate)}`;
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



function resetDateInputs() {
    document.getElementById('fromDate').value = '';
    document.getElementById('toDate').value = '';
}



$(document).ready(function() {
        // Function to show/hide the paragraph based on dropdown selection
        $('#searchType').change(function() {
            var selectedValue = $(this).val();

            // Check if "Order Id" (value 1) is selected
            if (selectedValue === "1") {
                $('#orderIdTip').show(); // Show the paragraph
            } else {
                $('#orderIdTip').hide(); // Hide the paragraph
            }
        });
    });


    $(function () {
        @if(Session::has('success'))
        new PNotify({
        title: 'Success',
        delay: 500,
        text:  "{{Session::get('success')}}",
        type: 'success'
        });

        @endif
        @if ($errors->any())
        var err = "";
        @foreach ($errors->all() as $error)

            new PNotify({
            title: 'Error',
            text: "{{$error}}",
            delay: 800,
            type: 'error'
            });
            @endforeach
        @endif
    });

    let datatable = null;
    let sessionfilter = false;
    $(function () {
        let defaultStatus = null;

        $('.status-btn').click(function() {
        // Get the id of the clicked button
        let buttonId = $(this).attr('id');

        switch (buttonId) {
            case 'status_6':
                defaultStatus = 6;
                break;
            case 'status_7':
                defaultStatus = 7;
                break;
            case 'status_1':
                defaultStatus = 1;
                break;
            case 'status_13':
                defaultStatus = 13;
                break;
            case 'status_14':
                defaultStatus = 14;
                break;
            case 'status_15':
                defaultStatus = 15;
                break;
            case 'status_16':
                defaultStatus = 16;
                break;
            case 'status_17':
                defaultStatus = 17;
                break;
            case 'status_18':
                defaultStatus = 18;
                break;
            case 'status_4':
                defaultStatus = 4;
                break;
            case 'status_2':
                defaultStatus = 2;
                break;
            case 'status_5':
                defaultStatus = 5;
                break;
            case 'status_3':
                defaultStatus = 3;
                break;

            case 'status_20':
                defaultStatus = 20;
                break;

            case 'status_All':
                defaultStatus = 'All'; // Adjust if 'All' should be treated differently
                break;
            case 'status_tax':
                defaultStatus = 'tax';
                break;
            default:
                defaultStatus = null; // Set a default fallback
                break;
        }
    }
)
        var currentURI = window.location.href;
        var match = currentURI.match(/\/orders_status\/(\d+|All)/);
        if (match) {
            var statusID = match[1];
            defaultStatus = statusID;
            @if(Session::has('dashboardfilters') && Session::get('dashboardfilters') == true)
                sessionfilter = true;
                $('#statusButtons').hide();
                $('#filter_search').hide();
                
            @endif
        } else {
            defaultStatus = @if(Auth::user()->hasRole('Process') || Auth::user()->hasRole('Qcer') || Auth::user()->hasRole('Process/Qcer')) 1 @else 6 @endif;
        }

        datatable = $('#order_datatable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            scrollX: true,
            lengthMenu:  [10, 25, 50, 100, 500, 750, 1000],
            dom: 'lrtip',
            ajax: {
                url: '{{ route("getOrderData") }}',
                type: 'POST',
                data: function (d) {
                d._token = '{{ csrf_token() }}';
                d.status = defaultStatus;
                d.searchType = $('#searchType').val();
                d.searchInputs = $('#searchInputs').val();
                d.selectedDateFilter = selectedDateFilter;
               d.fromDate_range = $('#fromDate_range').val();
               d.toDate_range = $('#toDate_range').val();
               d.sessionfilter = sessionfilter;
               d.tat_zone_filter = $('#tat_zone_filter').val();


              
                }
            },
            "columns": [
                { "data": "order_id", "name": "order_id" },
                { "data": "order_date", "name": "order_date" },
                { "data": "project_code", "name": "project_code" },
                { "data": "client_name", "name": "client_name" },
                { "data": "lob_name", "name": "lob_name" },
                { "data": "process_name", "name": "process_name" },
                { "data": "process", "name": "process" },
                { "data": "tier_name", "name": "tier_name" },
                { "data": "short_code", "name": "short_code" },
                { "data": "county_name", "name": "county_name" },
                { "data": "status", "name": "status" },
                { "data": "assignee_user", "name": "assignee_user" },
                { "data": "assignee_qa", "name": "assignee_qa" },
                { "data": "typist_user", "name": "typist_user", "visible": @if(Auth::user()->hasRole('Qcer') || Auth::user()->hasRole('Process/Qcer') || Auth::user()->hasRole('Process')) false @else true @endif },
                { "data": "typist_qa", "name": "typist_qa", "visible": @if(Auth::user()->hasRole('Qcer') || Auth::user()->hasRole('Process/Qcer') || Auth::user()->hasRole('Process')) false @else true @endif },
               

                {
                    "data": "checkbox",
                    "name": "checkbox",
                    "visible": @if(Auth::user()->hasRole('Process') || Auth::user()->hasRole('Qcer') || Auth::user()->hasRole('Process/Qcer')) false @else true @endif,
                    "orderable": false,
                },
                {
                    "data": "action",
                    "name": "action",
                    "visible": @if(Auth::user()->hasRole('Process') || Auth::user()->hasRole('Qcer') || Auth::user()->hasRole('Process/Qcer') || Auth::user()->hasRole('Typist/Typist_Qcer') || Auth::user()->hasRole('Typist') || Auth::user()->hasRole('Typist_Qcer')) false @else true @endif,
                    "orderable": false,
                },
                { "data": "associate_name", "name": "associate_name", "visible": true},
                { "data": "created_at", "name": "created_at", "visible": true},

            ],
            "order": [[1, 'asc']],
            createdRow: function (row, data, dataIndex) {
                    let status = data.status_id; 
                    let tat_value = data.tat_value;
                    let orderDate = new Date(data.order_date);
                    var currentDate = new Date(new Intl.DateTimeFormat('en-US', {
                            timeZone: 'America/New_York',
                            year: 'numeric', month: 'numeric', day: 'numeric',
                            hour: 'numeric', minute: 'numeric', second: 'numeric',
                            hour12: false
                        }).format(new Date()));
                    let timeDiff = (currentDate - orderDate) / (1000 * 60 * 60);

                    if (tat_value > 0) {
                        if (status == 1 || status == 4 || status == 13) {
                            if (timeDiff >= tat_value) { 
                                $(row).addClass('text-danger');
                            }
                        }
                    }
                }
            });
            $('#filterButton').on('click', function () {
        datatable.ajax.reload();
    });

        $('.status-btn').removeClass('btn-primary').addClass('text-white');
        $('#status_' + defaultStatus).removeClass('btn-info').addClass('btn-primary');
        $('.status-dropdown').prop('disabled', true);
        updateStatusCounts();
        
        if (!sessionfilter) {
        var lastOrderStatus = localStorage.getItem("lastOrderStatus");

        if (lastOrderStatus) {
            $('#' + lastOrderStatus).click();
        }

        lastOrderStatus = null;
    }
});

    $(document).on('click', '.status-btn', function () {
        $('#assign_tab').addClass('d-none');
        if ($("#user_id").data('select2') !== undefined) {
            $("#user_id").select2('destroy');
        }
        if ($("#qcer_id").data('select2') !== undefined) {
            $("#qcer_id").select2('destroy');
        }
        if ($("#typist_id").data('select2') !== undefined) {
            $("#typist_id").select2('destroy');
        }
        if ($("#typist_qc_id").data('select2') !== undefined) {
            $("#typist_qc_id").select2('destroy');
        }

        if ($("#cover_prep_id").data('select2') !== undefined) {
            $("#cover_prep_id").select2('destroy');
        }
        $('#user_id').prop('selectedIndex',0);
        $("#user_id").select2();
        $('#qcer_id').prop('selectedIndex',0);
        $("#qcer_id").select2();

        $('#typist_id').prop('selectedIndex',0);
        $("#typist_id").select2();
        $('#typist_qc_id').prop('selectedIndex',0);
        $("#typist_qc_id").select2();

        $('#cover_prep_id').prop('selectedIndex',0);
        $("#cover_prep_id").select2();
        $('input.check-all').prop('checked', false);
        $('.status-btn').removeClass('btn-primary').addClass('text-white');
        $(this).removeClass('btn-info');
        $(this).addClass('btn-primary');
        let status = $(this).attr('id').replace("status_", "");
        localStorage.setItem("lastOrderStatus", $(this).attr('id'));
        datatable.settings()[0].ajax.data.status = status;
        datatable.ajax.reload();
    });

    function page_reload() {
        $('#assign_tab').addClass('d-none');
        if ($("#user_id").data('select2') !== undefined) {
            $("#user_id").select2('destroy');
        }
        if ($("#qcer_id").data('select2') !== undefined) {
            $("#qcer_id").select2('destroy');
        }

        if ($("#typist_id").data('select2') !== undefined) {
            $("#typist_id").select2('destroy');
        }

        if ($("#typist_qc_id").data('select2') !== undefined) {
            $("#typist_qc_id").select2('destroy');
        }

        if ($("#cover_prep_id").data('select2') !== undefined) {
            $("#cover_prep_id").select2('destroy');
        }
        $('#user_id').prop('selectedIndex',0);
        $("#user_id").select2();
        $('#qcer_id').prop('selectedIndex',0);
        $("#qcer_id").select2();

        $('#typist_id').prop('selectedIndex',0);
        $("#typist_id").select2();

        $('#typist_qc_id').prop('selectedIndex',0);
        $("#typist_qc_id").select2();
        
        $('#cover_prep_id').prop('selectedIndex',0);
        $("#cover_prep_id").select2();
        $('input.check-all').prop('checked', false);
        task_status = $('#statusButtons').find('.btn-primary').attr('id');
        let status = task_status.replace("status_", "");
        if (task_status !== undefined) {
            let status = task_status.replace("status_", "");
            datatable.settings()[0].ajax.data.status = status;
            datatable.ajax.reload();
            updateStatusCounts();
        }
    };

    $('#order_datatable').on('draw.dt', function () {
        task_status = $('#statusButtons').find('.btn-primary').attr('id');
        let status = task_status.replace("status_", "");
        if (status == 6 || status == 7 || status == 5) {
            $('.status-dropdown').prop('disabled', true);
            if (status == 6 || status == 7) {
            // Make column 15 visible
            datatable.column(15).visible(true);

            // Disable the dropdown
                $('.status-dropdown').prop('disabled', true);

            // Hide column 17
            datatable.column(17).visible(false);
            }
        } else {
            $('.status-dropdown').prop('disabled', false);
            datatable.column(15).visible(false);
        }
        // //
        @if(Auth::user()->hasRole('Super Admin') || Auth::user()->hasRole('Business Head') ||Auth::user()->hasRole('PM/TL') ||Auth::user()->hasRole('SPOC') || Auth::user()->hasRole('AVP') || Auth::user()->hasRole('Admin') || Auth::user()->hasRole('VP'))
            if(status == 13){
                $('.status-dropdown').prop('disabled', false);
                datatable.column(15).visible(true);
            }
            else if(status == 6){
                $('.status-dropdown').prop('disabled', false);
                datatable.column(15).visible(true);
                $('.status-dropdown').prop('disabled', true);
            } else {
                datatable.column(15).visible(true);
            }

            if(status == 5 || status == 'All'){
                $('.status-dropdown').prop('disabled', false);
                datatable.column(15).visible(false);
            }

            if (status == 13) {
            document.getElementById('coversheetPrepDropdown').style.display = 'block';
            } else {
                document.getElementById('coversheetPrepDropdown').style.display = 'none';
            }
        @endif
        // //

        @if(Auth::user()->hasRole('Process/Qcer'))
        if(status == 6){
            datatable.column(15).visible(true);////
        }else{
            datatable.column(15).visible(false);
        }
        @endif


        if(status == 13){
            $('.status-dropdown').prop('disabled', false);
            datatable.column(17).visible(true);
        } else {
            datatable.column(17).visible(false);
        }
        @if(Auth::user()->hasRole('Qcer'))
        if(status == 1 || status == 2 || status == 5) {
            $('.status-dropdown').prop('disabled', true);
        }

        var allStatusValues = [];
        $('.status-dropdown').each(function() {
            var value = $(this).val();
            if (value =='1' || value =='5' || value =='2') {
                    $(this).prop('disabled', true);
            }
            allStatusValues.push(value);
        });
        @endif

        if(status == 5) {
            @if(Auth::user()->hasRole('Super Admin') || Auth::user()->hasRole('Business Head') ||Auth::user()->hasRole('PM/TL') || Auth::user()->hasRole('SPOC') || Auth::user()->hasRole('AVP') || Auth::user()->hasRole('Admin') || Auth::user()->hasRole('VP'))
            $('.status-dropdown').prop('disabled', false);
            @endif
        }

        @if(Auth::user()->hasRole('Process'))
        var allStatusValues = [];
        $('.status-dropdown').each(function() {
            var value = $(this).val();
            if (value !== '1' && value !== '3') {
                    $(this).prop('disabled', true);
            }
            allStatusValues.push(value);
        });
        @endif

        @if(Auth::user()->hasRole('Process/Qcer'))
        var allStatusValues = [];
        $('.status-dropdown').each(function() {
            var value = $(this).val();
            if (value !== '1' && value !== '3' && value !== '4') {
                    $(this).prop('disabled', true);
            }
            allStatusValues.push(value);
        });
        @endif


        if (status == 4 || status == 2) {
            @if(Auth::user()->hasRole('Process'))
                $('.status-dropdown').prop('disabled', true);
            @endif
        }
        if (status == 2) {
            @if(Auth::user()->hasRole('Process/Qcer'))
                $('.status-dropdown').prop('disabled', true);
            @endif
        }

        if(status == 'tax'){
            $('.status-dropdown').prop('disabled', true);
            datatable.column(15).visible(false);
            datatable.column(16).visible(false);
        }

        if(status == 15){
            $('.status-dropdown').prop('disabled', true);
        }

        @if(Auth::user()->hasRole('Typist/Typist_Qcer'))
            if(status == 4){
            $('.status-dropdown').prop('disabled', true);
        }
        @endif


        @if(Auth::user()->hasRole('Process/Qcer') || Auth::user()->hasRole('Process') || Auth::user()->hasRole('Qcer'))
            if(status == 14){
            $('.status-dropdown').prop('disabled', false);
        }
        @endif
    });


    // $(document).on('click', '.status-btn', function () {
    //     let status = $(this).attr('id').replace("status_", "");
    //     updateStatusCounts(status);
    // })

function updateStatusCounts() {
  $.ajax({
    url: "{{ route('getStatusCount') }}",
    type: 'POST',
    data: {
        _token: '{{ csrf_token() }}'
    },
    success: function(response) {
      if (response.StatusCounts !== undefined) {
        let taxCount = response.tax_bucket_count;
        let statusCounts = response.StatusCounts;
        let assign = response.AssignCoverSheet;
        let total = 0;
        @if(!Auth::user()->hasRole('Typist/Typist_Qcer'))
        for (let status = 1; status <= 20; status++) {
          if (status !== 6) { // Exclude status 6
            let count = statusCounts[status] || 0;
            total += count;
            $('#status_' + status + '_count').text(' (' + count + ')');

          }
        }
        @endif

        @if(Auth::user()->hasRole('Typist/Typist_Qcer'))
        for (let status = 1; status <= 21; status++) {
            if (status == 18 || status == 14 || status == 16 || status == 17 || status == 2 || status == 5 || status == 3 || status == 4 || status == 20) {
                let count = statusCounts[status] || 0;
                total += count;
                $('#status_' + status + '_count').text(' (' + count + ')');
            }
            }
        @endif


        @if(Auth::user()->hasRole('Process') || Auth::user()->hasRole('Process/Qcer'))
            $('#status_13_count').text("(" + statusCounts[13] + "+" + assign + ")");
        @endif

        let count6 = statusCounts[6] || 0;
        let count7 = statusCounts[7] || 0;
        $('#status_6_count').text(' (' + count6 + ')');
        $('#status_7_count').text(' (' + count7 + ')');

        // Calculate yetToAssignTotal
        let yetToAssignQa = response.yetToAssignCounts.yetToAssignQa ?? 0;
        let yetToAssignTypist = response.yetToAssignCounts.yetToAssignTypist ?? 0;
        let yetToAssignTypistQa = response.yetToAssignCounts.yetToAssignTypistQa ?? 0;

        let yetToAssignTotal = yetToAssignQa + yetToAssignTypist + yetToAssignTypistQa;

        // Update the UI
       
        @if(Auth::user()->hasRole('Process'))
            $('#status_All_count').html(' (' + total + ')');
            $('#status_4_count').html('(' + (statusCounts[4] ?? 0) + ')');
            $('#status_16_count').html('(' + (statusCounts[16] ?? 0) + ')');
            $('#status_17_count').html('(' + (statusCounts[17] ?? 0) + ')');
        @else
            $('#status_All_count').html(' (' + total + 
                "+<span class='black-text'>" + yetToAssignTotal + "</span>)");
            $('#status_4_count').html('(' + (statusCounts[4] ?? 0) + 
                "+<span class='black-text'>" + yetToAssignQa + "</span>)");
            $('#status_16_count').html('(' + (statusCounts[16] ?? 0) + 
                "+<span class='black-text'>" + yetToAssignTypist + "</span>)");
            $('#status_17_count').html('(' + (statusCounts[17] ?? 0) + 
                "+<span class='black-text'>" + yetToAssignTypistQa + "</span>)");
        @endif





        // Initialize sums for third and fourth counts
        let allThirdCount = 0;
        let allFourthCount = 0;

        // Update TatStatusResults and calculate total counts
        if (response.TatStatusResults) {
          for (let key in response.TatStatusResults) {
            if (response.TatStatusResults.hasOwnProperty(key)) {
              let thirdCount = response.TatStatusResults[key].orderReachthird || 0;
              let fourthCount = response.TatStatusResults[key].orderReachfourth || 0;

              // Update individual third and fourth count displays
              $('#tat_status_' + key + '_third_count').text(thirdCount);
              $('#tat_status_' + key + '_fourth_count').text(fourthCount);

              // Sum up third and fourth counts for All
              allThirdCount += thirdCount;
              allFourthCount += fourthCount;
            }
          }
        }

        // Display total third and fourth counts in the All sections
        $('#tat_status_All_third_count').text(allThirdCount);
        $('#tat_status_All_fourth_count').text(allFourthCount);

        $('#status_tax_count').text(' (' + taxCount + ')');
      }
    },
    error: function(error) {
      console.error('Error updating status count:', error);
    }
  });
}
$(document).ready(function() {
    var table = $('#order_datatable').DataTable();     

    table.on('draw', function() {
        $('.check-all').prop('checked', false);
    });
});

$(document).ready(function() {
  updateStatusCounts();
});


    $(document).on('change', 'input.check-all', function() {
        var isChecked = $(this).prop('checked');
        $('input.check-one').prop('checked', isChecked);

        if (isChecked) {
            $('#assign_tab').removeClass('d-none');
            task_status = $('#statusButtons').find('.btn-primary').attr('id');
            let status = task_status.replace("status_", "");
            if (status != 13 && status != 6 ) {
            var userlist = (status != status != 13 && status != 6) ? <?php echo json_encode($processors); ?> : <?php echo json_encode($qcers); ?>;
            } else if (status == 13 || status == 6) {
                userlist = <?php echo json_encode($processors); ?>;
            }  
            $('#user_id').empty().append('<option selected disabled value="">Select User</option>');
            $.each(userlist, function(index, user) {
                $('#user_id').append('<option value="' + user.id + '">' + user.emp_id + ' (' + user.username + ')' + '</option>');
            });
        } else {
            $('#assign_tab').addClass('d-none');
            $('#user_id').empty();
    }
    });

    $(document).on('change', 'input.check-all', function() {
        var isChecked = $(this).prop('checked');
        $('input.check-one').prop('checked', isChecked);

        if (isChecked) {
            $('#assign_tab').removeClass('d-none');
            task_status = $('#statusButtons').find('.btn-primary').attr('id');
            let status = task_status.replace("status_", "");
            if (status != 13 && status != 6 ) {
            var userlist = (status != status != 13 && status != 6) ? <?php echo json_encode($typists); ?> : <?php echo json_encode($typists); ?>;
            } else if (status == 13 || status == 6) {
                userlist = <?php echo json_encode($typists); ?>;
            }  
            $('#typist_id').empty().append('<option selected disabled value="">Select Typist</option>');
            $.each(userlist, function(index, user) {
                $('#typist_id').append('<option value="' + user.id + '">' + user.emp_id + ' (' + user.username + ')' + '</option>');
            });
        } else {
            $('#assign_tab').addClass('d-none');
            $('#typist_id').empty().append('<option selected disabled value="">Select Typist</option>');
    }
    });


    $(document).on('change', 'input.check-all', function() {
        var isChecked = $(this).prop('checked');
        $('input.check-one').prop('checked', isChecked);

        if (isChecked) {
            $('#assign_tab').removeClass('d-none');
            task_status = $('#statusButtons').find('.btn-primary').attr('id');
            let status = task_status.replace("status_", "");
            if (status != 13 && status != 6 ) {
            var userlist = (status != status != 13 && status != 6) ? <?php echo json_encode($typists_qcs); ?> : <?php echo json_encode($typists_qcs); ?>;
            } else if (status == 13 || status == 6) {
                userlist = <?php echo json_encode($typists_qcs); ?>;
            }  
            $('#typist_qc_id').empty().append('<option selected disabled value="">Select Typist_QC</option>');
            $.each(userlist, function(index, user) {
                $('#typist_qc_id').append('<option value="' + user.id + '">' + user.emp_id + ' (' + user.username + ')' + '</option>');
            });
        } else {
            $('#assign_tab').addClass('d-none');
            $('#typist_qc_id').empty().append('<option selected disabled value="">Select Typist_QC</option>');
    }
    });

    $(document).on('change', 'input.check-all', function() {
        var isChecked = $(this).prop('checked');
        $('input.check-one').prop('checked', isChecked);

        if (isChecked) {
            $('#assign_tab').removeClass('d-none');
            task_status = $('#statusButtons').find('.btn-primary').attr('id');
            let status = task_status.replace("status_", "");
            if (status != 13) {
                var userlist = (status != 13) ? <?php echo json_encode($qcers); ?> : [];
            } else if (status == 13) {
                userlist = <?php echo json_encode($qcers); ?>;
            }
            $('#qcer_id').empty().append('<option selected disabled value="">Select Qcer</option>');
            $.each(userlist, function(index, user) {
                $('#qcer_id').append('<option value="' + user.id + '">' + user.emp_id + ' (' + user.username + ')' + '</option>');
            });
        } else {
            $('#assign_tab').addClass('d-none');
            $('#qcer_id').empty();
    }
    });

    $(document).on('change', 'input.check-all', function() {
        var isChecked = $(this).prop('checked');
        $('input.check-one').prop('checked', isChecked);

        if (isChecked) {
            $('#assign_tab').removeClass('d-none');
            task_status = $('#statusButtons').find('.btn-primary').attr('id');
            let status = task_status.replace("status_", "");
            if (status != 13) {
                var userlist = (status != 13) ? <?php echo json_encode($qcers); ?> : [];
            } else if (status == 13) {
                userlist = <?php echo json_encode($qcers); ?>;
            }
            $('#qcer_id').empty().append('<option selected disabled value="">Select Qcer</option>');
            $.each(userlist, function(index, user) {
                $('#qcer_id').append('<option value="' + user.id + '">' + user.emp_id + ' (' + user.username + ')' + '</option>');
            });
        } else {
            $('#assign_tab').addClass('d-none');
            $('#qcer_id').empty();
    }
    });

    $(document).on('change', 'input.check-all', function() {
        var isChecked = $(this).prop('checked');
        $('input.check-one').prop('checked', isChecked);

        if (isChecked) {
            $('#assign_tab').removeClass('d-none');
            task_status = $('#statusButtons').find('.btn-primary').attr('id');
            let status = task_status.replace("status_", "");
          
            if (status == 13) {
                userlist = <?php echo json_encode($processors); ?>;
            }
           

            $('#cover_prep_id').empty().append('<option selected disabled value="">Select Coversheet-preperor</option>');
            $.each(userlist, function(index, user) {
                $('#cover_prep_id').append('<option value="' + user.id + '">' + user.emp_id + ' (' + user.username + ')' + '</option>');
            });
        } else {
            $('#assign_tab').addClass('d-none');
            $('#cover_prep_id').empty();
    }
    });


    $(document).on('change', 'input.check-one', function() {
    var allChecked = $('input.check-one').length === $('input.check-one:checked').length;
    $('input.check-all').prop('checked', allChecked);
    });

    $(document).on('change', 'input.check-one', function() {
        var anyCheckboxChecked = $('input.check-one:checked').length > 0;
        if (anyCheckboxChecked) {
            $('#assign_tab').removeClass('d-none');
        var task_status = $('#statusButtons').find('.btn-primary').attr('id');
            let status = task_status.replace("status_", "");
            var userlist = [];

       if (status == 13) {
                userlist = <?php echo json_encode($processors); ?>;
        }

        $('#cover_prep_id').empty();
        $('#cover_prep_id').append('<option selected disabled value="">Select Coversheet-prep</option>');
        $.each(userlist, function(index, user) {
            $('#cover_prep_id').append('<option value="' + user.id + '">' + user.emp_id + ' (' + user.username + ')' + '</option>');
        });
    } else {
        $('#assign_tab').addClass('d-none');
        $('#cover_prep_id').empty();
    }
});

//mine

$(document).on('change', 'input.check-one', function() {
    var anyCheckboxChecked = $('input.check-one:checked').length > 0;
    if (anyCheckboxChecked) {
        $('#assign_tab').removeClass('d-none');
        var task_status = $('#statusButtons').find('.btn-primary').attr('id');
        let status = task_status.replace("status_", "");
        var userlist = [];

        if (status != null) {
                userlist = <?php echo json_encode($processors); ?>;
            }

            $('#user_id').empty();
            $('#user_id').append('<option selected disabled value="">Select User</option>');
            $.each(userlist, function(index, user) {
                $('#user_id').append('<option value="' + user.id + '">' + user.emp_id + ' (' + user.username + ')' + '</option>');
            });
        } else {
            $('#assign_tab').addClass('d-none');
            $('#user_id').empty();
        }
});

$(document).on('change', 'input.check-one', function() {
    var anyCheckboxChecked = $('input.check-one:checked').length > 0;

    if (anyCheckboxChecked) {   

        if ($(this).data('id') == 82 || $(this).data('id') == 86 || $(this).data('id') == 84 || $(this).data('id') == 85 || $(this).data('id') == 87 || $(this).data('id') == 89 || $(this).data('id') == 91) {
                $('#assign_tab').removeClass('d-none');
                $('#typist_div').removeClass('d-none');
                var task_status = $('#statusButtons').find('.btn-primary').attr('id');
                let status = task_status.replace("status_", "");
                var userlist = [];

                if (status != null) {
                        userlist = <?php echo json_encode($typists); ?>;
                }
                $.each(userlist, function(index, user) {
                    $('#typist_id').append('<option value="' + user.id + '">' + user.emp_id + ' (' + user.username + ')' + '</option>');
                });
        }else{
            $('#assign_tab').addClass('d-none');
                $.each(userlist, function(index, user) {
                    $('#typist_id').append('<option value="' + user.id + '">' + user.emp_id + ' (' + user.username + ')' + '</option>');
                });

        }

        } else {
            $('#assign_tab').addClass('d-none');
                $.each(userlist, function(index, user) {
                    $('#typist_id').append('<option value="' + user.id + '">' + user.emp_id + ' (' + user.username + ')' + '</option>');
                });
        }
});


$(document).on('change', 'input.check-one', function() {
    var anyCheckboxChecked = $('input.check-one:checked').length > 0;
    if (anyCheckboxChecked) {
        if ($(this).data('id') == 82 || $(this).data('id') == 86 || $(this).data('id') == 84 || $(this).data('id') == 85 || $(this).data('id') == 87 || $(this).data('id') == 89 || $(this).data('id') == 91) {
        $('#assign_tab').removeClass('d-none');
        $('#typist_qc_div').removeClass('d-none');

        var task_status = $('#statusButtons').find('.btn-primary').attr('id');
        let status = task_status.replace("status_", "");
        var userlist = [];

        if (status != null) {
                userlist = <?php echo json_encode($typists_qcs); ?>;
            }

            $('#typist_qc_id').empty();
            $('#typist_qc_id').append('<option selected disabled value="">Select Typist_QC</option>');
            $.each(userlist, function(index, user) {
                $('#typist_qc_id').append('<option value="' + user.id + '">' + user.emp_id + ' (' + user.username + ')' + '</option>');
            });
        }else{
            $('#assign_tab').addClass('d-none');
            $.each(userlist, function(index, user) {
                $('#typist_qc_id').append('<option value="' + user.id + '">' + user.emp_id + ' (' + user.username + ')' + '</option>');
            });

        }
        } else {
            $('#assign_tab').addClass('d-none');
            $.each(userlist, function(index, user) {
                $('#typist_qc_id').append('<option value="' + user.id + '">' + user.emp_id + ' (' + user.username + ')' + '</option>');
            });

        }
});



$(document).on('change', 'input.check-one', function() {
    var anyCheckboxChecked = $('input.check-one:checked').length > 0;
    if (anyCheckboxChecked) {
        $('#assign_tab').removeClass('d-none');
        var task_status = $('#statusButtons').find('.btn-primary').attr('id');
        let status = task_status.replace("status_", "");
        var userlist = [];

        if (status != 7 && status != 13) {
            userlist = <?php echo json_encode($processors); ?>;
        }
            userlist = <?php echo json_encode($qcers); ?>;


        $('#qcer_id').empty();
        $('#qcer_id').append('<option selected disabled value="">Select Qcer</option>');
        $.each(userlist, function(index, user) {
            $('#qcer_id').append('<option value="' + user.id + '">' + user.emp_id + ' (' + user.username + ')' + '</option>');
    });
    } else {
        $('#assign_tab').addClass('d-none');
        $('#qcer_id').empty();
    }
});

$(document).on('click', '.status-dropdown', function() {
    var selectedStatus = $(this).val();
    var rowId = $(this).data('row-id');

    $.ajax({
        url: "{{ route('updateClickTime') }}",
        type: 'POST',
        data: {
            order_id: rowId,
            status: selectedStatus,
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
    });
});

var previousValue;

$(document).on('focus', '.status-dropdown', function() {
    previousValue = $(this).val();
});
    $(document).on('change', '.status-dropdown', function() {
    var selectedStatus = $(this).val();
    var rowId = $(this).data('row-id');


    Swal.fire({
        title: 'Are you sure?',
        text: 'You are about to update the status.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, update it!',
        cancelButtonText: 'No, cancel!',
    }).then((result) => {
        if (result.value) {
            $.ajax({
                url: "{{route('update_order_status')}}",
                type: 'POST',
                data: {
                    rowId: rowId,
                    currentValue: previousValue,
                    selectedStatus: selectedStatus,
                    _token: '{{csrf_token()}}',
                },
                success: function(response) {
                    if(response.success != undefined) {
                        Swal.fire({
                            text: "Status Updated Successfully",
                            icon: "success",
                            timer: 1000
                        });
                        // page_reload();

                        var table = $('#order_datatable').DataTable();
                        var currentPage = table.page();
                        var row = table.row($(this).closest('tr'));
                        row.remove().draw();
                        table.page(currentPage).draw(false);
                        updateStatusCounts();
                    } else {
                        Swal.fire({
                            text: response.error,
                            icon: "error",
                            timer: 1500
                        });
                        
                        var table = $('#order_datatable').DataTable();
                        var currentPage = table.page();
                        table.page(currentPage).draw(false);

                    }
                },
                error: function(error) {
                    console.error('Error updating status:', error);
                }
            });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            page_reload();
        }
    });
});


    $(document).ready(function() {
        $("#user_id").select2();
        $('.content-loaded').show();
        $('.frame').addClass('d-none');
    });

    $(document).ready(function() {
        $("#typist_id").select2();
        $('.content-loaded').show();
        $('.frame').addClass('d-none');
    });
    
    $(document).ready(function() {
        $("#typist_qc_id").select2();
        $('.content-loaded').show();
        $('.frame').addClass('d-none');
    });


    // Edit company
     $('#order_datatable').on('click','.edit_order',function () {
        $('#re_assign').prop('checked', false);
        $('#hide_user, #hide_qa').hide();
        $('#unassign_user, #unassign_qcer').hide();

		var id = $(this).data('id');
		var url = '{{ route("edit_order") }}';
		$.ajax({
			type: "post",
			url: url,
			data: { id:id , _token: '{{csrf_token()}}'},
			success: function(response)
			{
				var res = response;

                $('#id_ed').val(res['id']);
                $('#order_id_ed').val(res['order_id']);
                var orderDate = res['order_date'].split(' ')[0];
                $("#order_date_ed").val(orderDate);
                $("#process_code_ed").val(res['process_id']);
                $("#property_state_ed").val(res['state_id']);
				$("#property_county_ed").val(res['county_id']);
                $("#assign_user_ed").val(res['assignee_user_id']);
                $("#assign_qa_ed").val(res['assignee_qa_id']);

                var orderDate = res['order_date'].replace(' ', 'T');
                $("#order_date_ed").val(orderDate);

                if (res['status_id'] === 5) {
                $("#hide_reasign").hide();
                } else {
                    $("#hide_reasign").show();
                }

				$("#myModalEdit").modal('show');
			}
		});
	});


    $('.btn.btn-danger').click(function(){
            $('#myModalEdit').modal('hide');
        });

    $(document).ready(function() {
        $("#hide_user").hide();
        $("#hide_qa").hide();
        $('#re_assign').change(function() {
            if(this.checked) {
                $("#hide_user").show();
                $("#unassign_user").show();

                console.log($("#assign_qa_ed").val());
                if ($("#assign_qa_ed").val() != null || $("#assign_qa_ed").val() != undefined) {
                    $("#hide_qa").show();
                    $("#unassign_qcer").show();

                } else {
                    $("#hide_qa").hide();
                    $("#unassign_qcer").hide();

                }
                $('.select2dropdown').select2();
            } else {
                $('#hide_user, #hide_qa').hide();
                $('#unassign_user, #unassign_qcer').hide();

            }
        });
        $("#re_assign").show();
    });



    $('#order_datatable').on('click','.delete_order',function () {
		var id = $(this).data('id');
		var url = '{{ route("delete_order") }}';
		$.ajax({
			type: "post",
			url: url,
			data: { id:id , _token: '{{csrf_token()}}'},
                success: function(response) {
					Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, delete it!"
                    }).then((result) => {
                    if (result.value) {
                        Swal.fire({
                        title: "Deleted!",
                        text: "Your file has been deleted.",
                        icon: "success"
                        });
                        page_reload();
                    }
                    });
			}
		});
	});


    $('#property_state_ed').on('change', function () {
        $("#property_county_ed").select2();
        var state_id = $("#property_state_ed").val();
        $("#property_county_ed").html('');
        $.ajax({
            url: "{{url('getCounty')}}",
            type: "POST",
            data: {
                state_id: state_id,
                _token: '{{csrf_token()}}'
            },
            dataType: 'json',
            success: function (result) {
                $('#property_county_ed').html('<option value="">Select County</option>');
                $.each(result.county, function (key, value) {
                    $("#property_county_ed").append('<option value="' + value
                        .id + '">' + value.county_name + '</option>');
                });
            }
        });
    });

    $('#updateorderfrm').on('submit', function(event){
		event.preventDefault();

        var orderDate = new Date($('#order_date_ed').val());
        var currentDate = new Date();

        if (orderDate > currentDate) {
        Swal.fire({
            title: "Error",
            text: "Order Received Date cannot be in the future.",
            icon: "error"
        });
        return; 
    }


    if ($('#updateorderfrm').parsley().isValid()) {
			var url = '{{ route("updateOrder") }}';
			var formData = new FormData(this);
			$.ajax({
				type: "post",
				url: url,
				data: formData,
                contentType: false,
                processData: false,
				success: function(response) {
                    $("#myModalEdit").modal('hide');
                    Swal.fire({
                        title: "Success",
                        text:  response.msg,
                        icon: "success"
                    });
                    page_reload();
				},
				error:function(response) {
					var err = "";
					$.each(response.responseJSON.errors,function(field_name,error){
						err = err +'<br>' + error;
					});
					Swal.fire({
                        title: "Error",
                        text:  "Can't able to Updating Order Details",
                        icon: "error"
                    });
				}
			});
		}
	});



    $('#unassign_user').on('click', function(event) {
    event.preventDefault();
    const orderId = $('#id_ed').val();

    Swal.fire({
        title: 'Are you sure?',
        text: "Do you want to Unassign the order? This action cannot be undone.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Unassign it!',
        cancelButtonText: 'No, cancel!'
    }).then((result) => {
        if (result.value) {
            $.ajax({
                method: 'POST',
                url: "{{ route('unassign_user') }}",
                data: {
                    _token: '{{ csrf_token() }}',
                    order_id: orderId,
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire(
                            'Unassigned!',
                            'The user has been unassigned.',
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire(
                            'Error!',
                            'Failed to unassign the user. Please try again.',
                            'error'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire(
                        'Error!',
                        'There was a problem unassigning the user.',
                        'error'
                    );
                }
            });
        }
    });
});



        $('#unassign_qcer').on('click', function() {
            const orderId = $('#id_ed').val();
            event.preventDefault();
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to Unassign the order? This action cannot be undone.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Unassign it!',
                    cancelButtonText: 'No, cancel!'
                }).then((result) => {
                    if (result.value) {
                    $.ajax({
                        method: 'POST',
                        url: "{{ route('unassign_qcer') }}",
                        data: {
                            _token: '{{ csrf_token() }}',
                            order_id: orderId,
                        },
                        success: function(response) {
                    if (response.success) {
                        Swal.fire(
                            'Unassigned!',
                            'The Qcer has been unassigned.',
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire(
                            'Error!',
                            'Failed to unassign the user. Please try again.',
                            'error'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire(
                        'Error!',
                        'There was a problem unassigning the user.',
                        'error'
                    );
                }
            });
        }
    });
});


$(document).ready(function() {
    // Handle change event on the select element
    $('#status_change').on('change', function() {
        // Get the selected value from the select element
        var selectedStatus = $(this).val();
        let checkedOrdersArray = $('input[name="orders[]"]:checked');

        // Check if a valid status is selected
        if (selectedStatus) {
            // Trigger SweetAlert for confirmation
            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to change the status.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'OK',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.value) {
                    // Send the selected status to your server via AJAX
                    $.ajax({
                        url: "{{ route('status_change') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}', // CSRF token for Laravel
                            status_id: selectedStatus,
                            orders: checkedOrdersArray.map(function() {
                                return this.value;
                            }).get(),
                        },
                        success: function(response) {
                            // Handle the response (optional)
                            if (response.success) {
                                Swal.fire(
                                    'Success!',
                                    'Status has been updated.',
                                    'success'
                                ).then(() => {
                                    // Reload the page after a successful update
                                    location.reload();
                                });
                            }else {
                                Swal.fire(
                                    'Error!',
                                    response.message || 'Something went wrong.',
                                    'error'
                                ).then(() => {
                                    // Reset the dropdown after the error message
                                    $('#status_change').val('');
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            // Handle error (optional)
                            Swal.fire(
                                'Error!',
                                'Something went wrong, please try again.',
                                'error'
                            );
                        }
                    });
                } else {
                    // If the user cancels, reset the select box to the default value
                    $('#status_change').val('');
                }
            });
        } else {
            // If no status is selected, show the warning
            Swal.fire(
                'Warning!',
                'Please select a status before proceeding.',
                'warning'
            );
        }
    });
});


    $(document).on("click", "#assignBtn", function (event) {
        event.preventDefault();
        if ($("#assignmentForm").parsley().isValid()) {
            let task_status = $('#statusButtons').find('.btn-primary').attr('id');
            let status = task_status.replace("status_", "");
            let checkedOrdersArray = $('input[name="orders[]"]:checked');
            let user_id = $('#user_id').val();
            let qcer_id = $('#qcer_id').val();
            let typist_id = $('#typist_id').val();
            let typist_qc_id = $('#typist_qc_id').val();
            let cover_prep_id = $('#cover_prep_id').val();

            $.ajax({
                type: "POST",
                url: "{{ route('assignment_update') }}",
                data: {
                    type_id: status,
                    orders: checkedOrdersArray.map(function() {
                        return this.value;
                    }).get(),
                    user_id: user_id,
                    qcer_id: qcer_id,
                    typist_id: typist_id,
                    typist_qc_id: typist_qc_id,
                    cover_prep_id: cover_prep_id,
                    _token: '{{csrf_token()}}'
                },
                success: function (response) {
                    Swal.fire({
                        title: "Success",
                        text: response.msg,
                        icon: "success",
                        timer: 1000
                    });
                    page_reload();
                },
                error: function (error) {
                    var err = "";
                    $.each(error.responseJSON.errors, function (field_name, error) {
                        err = err + '<br>' + error;
                    });
                    Swal.fire({
                        title: "Error",
                        text: err,
                        icon: "error",
                        timer: 2000
                    });
                }
            });
        }
    });


$('#deleteBtn').click(function (event) {
    event.preventDefault();
    Swal.fire({
        title: 'Are you sure?',
        text: "Do you really want to delete the selected order(s)? This action cannot be undone.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'No, cancel!'
    }).then((result) => {
        if (result.value) {
            if ($("#assignmentForm").parsley().isValid()) {
                
                let task_status = $('#statusButtons').find('.btn-primary').attr('id');
                let status = task_status.replace("status_", ""); 
                let checkedOrdersArray = $('input[name="orders[]"]:checked').map(function () {
                    return $(this).val();
                }).get();

                $.ajax({
                    type: "POST",
                    url: "{{ route('delete_order') }}", 
                    data: {
                        type_id: status,                 
                        orders: checkedOrdersArray,     
                        _token: '{{ csrf_token() }}'    
                    },
                    success: function (response) {
                        Swal.fire({
                            title: "Deleted!",
                            text: response.msg,
                            icon: "success",
                            timer: 1000
                        });

                        page_reload();
                    },
                    error: function (error) {
                        var err = Object.values(error.responseJSON.errors).map(function (errorArray) {
                            return errorArray.join('<br>');
                        }).join('<br>');

                        Swal.fire({
                            title: "Error",
                            html: err, 
                            icon: "error",
                            timer: 2000
                        });
                    }
                });
            }
        } else {
            Swal.fire({
                title: "Cancelled",
                text: "Your order(s) are safe!",
                icon: "info",
                timer: 1500
            });
        }
    });
});

    $(document).on("click", ".assign-me", function (event) {
        let task_status = $('#statusButtons').find('.btn-primary').attr('id');
        let status = task_status.replace("status_", "");
        var elementId = $(this).attr('id');
        let order_id = elementId.split('_')[2];
        let user_id = "{!! Auth::id() !!}";
        let qcer_id = "{!! Auth::id() !!}";
        let typist_id = "{!! Auth::id() !!}";
        let typist_qc_id = "{!! Auth::id() !!}";

        let cover_prep_id = "{!! Auth::id() !!}";

        let orders = [order_id];
        $.ajax({
            type: "POST",
            url: "{{ route('assignment_update') }}",
            data: {
                type_id: status,
                orders: orders,
                user_id: user_id,
                qcer_id: qcer_id,
                typist_id: typist_id,
                typist_qc_id: typist_qc_id,
                cover_prep_id: cover_prep_id,
                _token: '{{csrf_token()}}'
            },
            success: function (response) {
                Swal.fire({
                    title: "Success",
                    text: response.msg,
                    icon: "success",
                    timer: 1500
                });
                page_reload();
            },
            error: function (error) {
                var err = "";
                $.each(error.responseJSON.errors, function (field_name, error) {
                    err = err + '<br>' + error;
                });
                Swal.fire({
                    title: "Error",
                    text: err,
                    icon: "error",
                    timer: 2000
                });
            }
        });
    });

    $(document).on("click", ".goto-order", function (event) {
        let task_status = $('#statusButtons').find('.btn-primary').attr('id');
        let status = task_status.replace("status_", "");
        var elementId = $(this).attr('id');
        let order_id = elementId.split('_')[1];
        localStorage.setItem("lastOrderStatus", task_status);
    $.ajax({
        url: "{{ route('updateClickTime') }}",
        type: 'POST',
        data: {
            order_id: order_id,
            status: status,
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
    });

    if (status == 13) {
            window.location.href = "{{ url('coversheet-prep/') }}/" + order_id;
        }else if(status == "tax"){
            window.location.href = "{{ url('orderform') }}/" +  order_id  + "/tax";
        }
         else {
            window.location.href = "{{ url('orderform/') }}/" + order_id;
        }
});



</script>

@endsection

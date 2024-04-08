@extends('layouts.app')
@section('title', 'Stellar-OMS | Dashboard')
@section('content')


    <style>

        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

        .text-center {
            font-family: "Poppins", sans-serif;
            font-weight: 500;
            font-style: normal;
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
                <h5 class="modal-title" id="exampleModalLabel">Process Wise Detail</h5>
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

<div class="container mt-2 mb-1 p-1">
    <section id="minimal-statistics">
                <div class="row justify-content-center mb-0 mt-2 ml-1">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="fromDate_dcf">From Date</label>
                            <input type="date" class="form-control" id="fromDate_dcf" name="fromDate_dcf">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="toDate_dcf">To Date</label>
                            <input type="date" class="form-control" id="toDate_dcf" name="toDate_dcf">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="client"> Client </label>
                            <select class="form-control select2-basic-multiple" name="dcf_client_id[]" id="client_id_dcf" multiple="multiple">
                                <option selected value="All">All</option>
                                @forelse($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->client_no }} ({{ $client->client_name }})</option>
                                @empty
                                @endforelse
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <label for="project">Project</label>
                        <Select class="form-control select2-basic-multiple" name="dcf_project_id[]" id="project_id_dcf" multiple="multiple">
                            <option selected value="All">All Projects</option>
                        </Select>
                    </div>
                    <div class="col-1"><label for="project">Billing Type</label>
                        <Select class="form-control select_role float-end" name="" id="billing_id_dcf">
                            <option selected value="All">All</option>
                            <option value="FTE">FTE</option>
                            <option value="TXN">TXN</option>
                        </Select>
                    </div>
                    <div class="col-1 col-md-1 mt-4">
                        <button type="submit" id="filterButton" class="btn btn-primary">Filter</button>
                    </div>
                </div>
                <!-- <div class="row ml-1 mt-0 mb-4">
                    
                </div> -->
            <div class="col-12">
                <div class="row my-2">
                    @if(Auth::user()->hasRole('Super Admin') || Auth::user()->hasRole('Business Head') ||Auth::user()->hasRole('PM/TL') || Auth::user()->hasRole('SPOC'))
                        <div class="col-xl-4 col-sm-6 col-12" onclick="window.location.href = '/orders_status/6'">
                            <div class="card">
                                <div class="card-content">
                                    <div class="card-body">
                                        <div class="media d-flex">
                                            <div class="media-body">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h3 class="icon-dual-warning mb-0" id="yet_to_assign_cnt">0</h3>
                                                    <!-- <h3 class="icon-dual-warning mb-0" id="yet_cost">$0.00</h3> -->
                                                </div>
                                                <div class="justify-content-between align-items-center mt-2">
                                                    <span>Yet to Assign</span>
                                                    <i class="icon-dual-warning font-large-2 float-right" data-feather="book-open"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if(!Auth::user()->hasRole('Qcer'))
                    <div class="col-xl-4 col-sm-6 col-12" onclick="window.location.href = '/orders_status/1'">
                        <div class="card">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="media d-flex">
                                        <div class="media-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h3 class="icon-dual-pink mb-0" id="wip_cnt">0</h3>
                                                <!-- <h3 class="icon-dual-pink mb-0" id="wip_cost">$0.00</h3> -->
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
                    @endif
                    <div class="col-xl-4 col-sm-6 col-12" onclick="window.location.href = '/orders_status/4'">
                        <div class="card">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="media d-flex">
                                        <div class="media-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h3 class="icon-dual-danger mb-0" id="Qu_cnt">0</h3>
                                                <!-- <h3 class="icon-dual-danger mb-0" id="qu_cost">$0.00</h3> -->
                                            </div>
                                            <div class="justify-content-between align-items-center mt-2">
                                                <span>Send For Qc</span>
                                                <i class="icon-dual-danger font-large-2 float-right" data-feather="chevrons-right"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-sm-6 col-12" onclick="window.location.href = '/orders_status/2'">
                        <div class="card">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="media d-flex">
                                        <div class="media-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h3 class="icon-dual-purple mb-0" id="hold_cnt">0</h3>
                                                <!-- <h3 class="icon-dual-purple mb-0" id="hold_cost">$0.00</h3> -->
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
                        <div class="col-xl-4 col-sm-6 col-12" onclick="window.location.href = '/orders_status/3'">
                            <div class="card">
                                <div class="card-content">
                                    <div class="card-body">
                                        <div class="media d-flex">
                                            <div class="media-body">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h3 class="icon-dual-danger mb-0" id="cancelled_cnt">0</h3>
                                                    <!-- <h3 class="icon-dual-danger mb-0" id="cancelled_cost">$0.00</h3> -->
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
                        <div class="col-xl-4 col-sm-6 col-12" onclick="window.location.href = '/orders_status/5'">
                            <div class="card">
                                <div class="card-content">
                                    <div class="card-body">
                                        <div class="media d-flex">
                                            <div class="media-body">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h3 class="icon-dual-success mb-0" id="completed_cnt">0</h3>
                                                    <!-- <h3 class="icon-dual-success mb-0" id="completed_cost">$0.00</h3> -->
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
                    </div>
                </div>
            </section>

        @if(Auth::user()->hasRole('Business Head'))
        <div class="card mt-5 tabledetails">
            <h4 class="text-center mt-3">Revenue Details - Transaction Billing</h4>
            <div class="card-body">
                <!-- <form id="dateRangeForm" class="mt-4">
                    <div class="row justify-content-center">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fromDate">From Date</label>
                                <input type="date" class="form-control" id="fromDate" name="from_date">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="toDate">To Date</label>
                                <input type="date" class="form-control" id="toDate" name="to_date">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="client"> Client </label>
                                <select class="form-control select2-basic-multiple" name="client_id[]" id="client_id" multiple="multiple">
                                    <option selected value="All">All</option>
                                    @forelse($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->client_no }} ({{ $client->client_name }})</option>
                                    @empty
                                    @endforelse
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 align-self-center">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </div>
                </form> -->
                <div class="p-0">
                    <h5 class="text-center"> Client Wise Details </h5>
                    <table id="revenueClientTable" class="table table-bordered nowrap mt-0 d-none" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead class="text-center">
                            <tr>
                                {{-- <th width="14%">Date</th> --}}
                                <th width="14%">Client</th>
                                <th width="14%">No of orders completed</th>
                                <th width="14%">Total</th>
                            </tr>
                        </thead>
                        <tbody class="text-center"></tbody>
                    </table>
                </div>
                <div class="p-0 process_wise">
                    <h5 class="text-center"> Process Wise Details </h5>
                    <table id="revenueTable" class="table table-bordered nowrap mt-0 d-none" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead class="text-center">
                            <tr>
                                <th width="14%">Project Code</th>
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
                                <th width="14%">Grand Total Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            <td id="grantCount"></td>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mt-5 ftetabledetails">
            <h4 class="text-center mt-3">Revenue Details - FTE</h4>
            <div class="card-body">
                <!-- <form id="ftedateRangeForm" class="mt-4">
                    <div class="row justify-content-center">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fromDate">From Date</label>
                                <input type="date" class="form-control" id="ftefromDate" name="from_date">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="toDate">To Date</label>
                                <input type="date" class="form-control" id="ftetoDate" name="to_date">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="client"> Client </label>
                                <select class="form-control select2-basic-multiple" name="fteclient_id[]" id="fteclient_id" multiple="multiple">
                                    <option selected value="All">All</option>
                                    @forelse($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->client_no }} ({{ $client->client_name }})</option>
                                    @empty
                                    @endforelse
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 align-self-center">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </div>
                </form> -->
                <div class="p-0 w-100 mx-auto" id="fteClientTable">
                    <h5 class="text-center"> Project Wise Details </h5>
                    <table id="fterevenueProjectTable" class="table table-bordered nowrap mt-0" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                    <thead class="text-center">
                    <tr>
                    <th width="14%">Client</th>
                    <th width="20%">Project Code</th> <!-- Adjusted width for the merged column -->
                    <th width="14%">Pricing</th>
                    <th width="14%">FTE Count</th>
                    <th width="14%">Expected Revenue</th>
                    <th width="14%">Start Date</th>
                    <th width="14%">End Date</th>
                    <th width="14%">Days</th>
                    <th width="10%">Revenue as Selected Date</th>
                    </tr>
                    </thead>
                    <tbody class="text-center"></tbody>
                    </table>
                </div>
                <div class="p-0 process_wise d-none">
                    <h5 class="text-center"> Process Wise Details </h5>
                    <table id="revenueFTETable" class="table table-bordered nowrap mt-0" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead class="text-center">
                            <tr>
                                <th width="14%">Project Code</th>
                                <th width="14%">No of orders completed</th>
                                <th width="14%">Unit cost</th>
                                <th width="14%">Total</th>
                            </tr>
                        </thead>
                        <tbody class="text-center"></tbody>
                    </table>
                </div>
                <div class="p-0 d-flex d-none justify-content-center">
                    <table id="totalFTETable" class="table table-bordered nowrap mt-3 w-50" style="border-collapse: collapse; border-spacing: 0;">
                        <thead class="text-center">
                            <tr>
                                <th width="14%">Grand Total Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            <td id="grantCountFTE"></td>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        @if(Auth::user()->hasRole(['Super Admin', 'AVP/VP','PM/TL']))
        <div class="card mt-5 tabledetails d-none" id="userwise_table">
            <h4 class="text-center mt-3">Userwise Details</h4>
            <div class="card-body">
                <div class="p-0">
                    <table id="userwise_datatable" class="table table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead class="text-center">
                            <tr>
                                <th width="16%" class="text-center">Users</th>
                                <th width="14%">WIP</th>
                                <th width="14%">Send For Qc</th>
                                <th width="14%">Hold</th>
                                <th width="14%">Cancelled</th>
                                <th width="14%">Completed</th>
                                <th width="14%">All</th>
                            </tr>
                        </thead>
                        <tbody class="text-center"></tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        @if(Auth::user()->hasRole(['Super Admin', 'AVP/VP','PM/TL','Process','Qcer','Process/Qcer','SPOC']))
        <div class="card mt-5 tabledetails d-none" id="datewise_table">
            <h4 class="text-center mt-3">Datewise Details</h4>
            <div class="card-body">
                <div class="p-0">
                    <table id="datewise_datatable" class="table table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead class="text-center">
                            <tr>
                                <th width="16%">Received Date</th>
                                <th width="14%">WIP</th>
                                <th width="14%">Send For Qc</th>
                                <th width="14%">Hold</th>
                                <th width="14%">Cancelled</th>
                                <th width="14%">Completed</th>
                                <th width="14%">All</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="text-center"></tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
</div>

{{-- Js --}}
<script>

$(document).ready(function() {
        $('#billing_id_dcf').on('change', function() {
            var value = $(this).val();
            if (value === 'All') {
                $('.ftetabledetails, .tabledetails').show();
            } else if (value === 'FTE') {
                $('.ftetabledetails').show();
                $('.tabledetails').hide();
            } else if (value === 'TXN') {
                $('.ftetabledetails').hide();
                $('.tabledetails').show();
            }
        });
    });

  

    // $(document).ready(function () {
    //     fetchOrderData('All');
    //     $("#project_id").select2();
    //     $("#project_id_dcf").select2();
    //     $("#client_id_dcf").select2();
    // });


    // function fetchOrderData(projectId) {
    //     $.ajax({
    //         url: "{{ route('dashboard_count') }}",
    //         type: "POST",
    //         data: {
    //             project_id: projectId,
    //             _token: '{{csrf_token()}}'
    //         },
    //         dataType: 'json',
    //         success: function (response) {
    //             let statusCounts = response.StatusCounts;
    //             $('#yet_to_assign_cnt').text(statusCounts[6] || 0);
    //             $('#wip_cnt').text(statusCounts[1] || 0);
    //             $('#hold_cnt').text(statusCounts[2] || 0);
    //             $('#Qu_cnt').text(statusCounts[4] || 0);
    //             $('#cancelled_cnt').text(statusCounts[3] || 0); 
    //             $('#completed_cnt').text(statusCounts[5] || 0);
    //         }
    //     });
    // }

     


    let datatable = null;

    $(document).ready(function () {
        fetchOrderData('All');
        fetchProData('All');
        $("#project_id").select2();
        $("#project_id_dcf").select2();
        $("#client_id_dcf").select2();
        $("#billing_id_dcf").select2();
        
        $("#filterButton").on('click', function() {
            let projectId = $("#project_id_dcf").val();
            let clientId = $("#client_id_dcf").val();
            let fromDate = $("#fromDate_dcf").val();
            let toDate = $("#toDate_dcf").val();
            
            fetchOrderData(projectId, clientId, fromDate, toDate);
            getGrandTotal(fromDate, toDate, client_id);
        });

    $('#client_id_dcf').on('change', function () {
        let getproject_id = $("#client_id_dcf").val();
        $("#project_id_dcf").html('All');
        fetchProData(getproject_id);
    });
   

});

function fetchOrderData(projectId, clientId, fromDate, toDate) {
    $.ajax({
        url: "{{ route('dashboard_count') }}",
        type: "POST",
        data: {
            project_id: projectId,
            client_id: clientId,
            from_date: fromDate,
            to_date: toDate,
            _token: '{{csrf_token()}}'
        },
        dataType: 'json',
        success: function (response) {
            let statusCounts = response.StatusCounts;
            $('#yet_to_assign_cnt').text(statusCounts[6] || 0);
            $('#wip_cnt').text(statusCounts[1] || 0);
            $('#hold_cnt').text(statusCounts[2] || 0);
            $('#Qu_cnt').text(statusCounts[4] || 0);
            $('#cancelled_cnt').text(statusCounts[3] || 0);
            $('#completed_cnt').text(statusCounts[5] || 0);
        }
    });
}

// $(document).on('change', '#project_id_dcf', function() {
//         fetchOrderData($(this).val());
//         datatable.settings()[0].ajax.data.project_id = $(this).val();
//         datatable.ajax.reload();
//     });

function fetchProData(client_id) {
    $.ajax({
        url: "{{ url('dashboard_dropdown') }}",
        type: "POST",
        data: {
            client_id: client_id,
            _token: '{{ csrf_token() }}'
        },
        dataType: 'json',
        success: function (response) {
            $('#project_id_dcf').html('<option value="All">All Projects</option>');
            $.each(response, function (index, item) {
                $("#project_id_dcf").append('<option value="' + item.id + '">(' + item.project_code + ') - ' + item.process_name + '</option>');
            });
        }
    });
}




    $(document).ready(function () {
        datatable = $('#datewise_datatable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('dashboard_datewise_count') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    project_id: 'All',
                }
            },
            columns: [
                { data: 'order_date', name: 'order_date' },
                { data: 'status_1', name: 'status_1', visible:@if(Auth::user()->hasRole('Qcer')) false @else true @endif},
                { data: 'status_4', name: 'status_4' },
                { data: 'status_2', name: 'status_2' },
                { data: 'status_3', name: 'status_3' },
                { data: 'status_5', name: 'status_5' },
                { data: 'status_6', name: 'status_6' },
                { data: 'order_date_unformatted', name: 'order_date_unformatted', visible: false },
            ],
            order: [
                [7, 'desc']
            ],
        });
    });

    $('#datewise_datatable').on('draw.dt', function () {
        $('#datewise_table').removeClass('d-none');
    });

    @if(Auth::user()->hasRole(['Super Admin', 'AVP/VP', 'Business Head', 'PM/TL']))
    $(document).ready(function () {
        datatable = $('#userwise_datatable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('dashboard_userwise_count') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    project_id: 'All',
                }
            },
            columns: [
                { data: 'userinfo', name: 'userinfo', class: 'text-left' },
                { data: 'status_1', name: 'status_1', visible:@if(Auth::user()->hasRole('Qcer')) false @else true @endif},
                { data: 'status_4', name: 'status_4' },
                { data: 'status_2', name: 'status_2' },
                { data: 'status_3', name: 'status_3' },
                { data: 'status_5', name: 'status_5' },
                { data: 'status_6', name: 'status_6' },
            ],
        });
    });

    $('#userwise_datatable').on('draw.dt', function () {
        $('#userwise_table').removeClass('d-none');
    });
    @endif


 function getGrandTotal(fromDate, toDate, client_id) {
        $.ajax({
            url: "{{ route('getTotalData') }}",
            type: "POST",
            data: {
                from_date: fromDate,
                to_date: toDate,
                client_id: client_id,
                _token: '{{csrf_token()}}',
            },
            dataType: 'json',
            success: function (response) {
                let GrandTotal = response.GrandTotal;
                $('#grantCount').text(GrandTotal || 0.00000);
            }
        });
    }





    $(document).ready(function () {

        var fromDate = '';
        var toDate = '';
        var client_id = '';

        $('#filterButton').on('click', function (e) {
            e.preventDefault();
            fromDate = $('#fromDate_dcf').val();
            toDate = $('#toDate_dcf').val();
            client_id = $('#client_id_dcf').val();
            datatable.ajax.reload();
            getGrandTotal(fromDate, toDate,client_id);
            revenueClientWise(fromDate, toDate,client_id);
            processwiseDetail(fromDate, toDate,client_id);
        });

        getGrandTotal(fromDate, toDate,client_id);
        revenueClientWise(fromDate, toDate,client_id);
        processwiseDetail(fromDate, toDate,client_id);


        $(document).on('click', '.project-link', function(event) {
            event.preventDefault(); // Prevent default behavior of the link
            var projectId = $(this).attr('id');
            var processName = $(this).text();
            orderWiseDetail(fromDate, toDate,projectId,processName);
        });



        function processwiseDetail(fromDate, toDate,client_id){


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
                    },
                    dataSrc: function (data) {
                        var rows = [];
                        var grandTotalRevenue = data['Grand Total Revenue'];
                        $.each(data.data, function (index, value) {
                            var date = moment(value['Date']).format('MM/DD/YYYY');
                            var row = {

                                'Project Code': '<a href="#" id="' + value['id'] + '" class="project-link">' + value['Project Code'] + ' (' + value['Process Name'] + ')</a>',
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
                    { data: 'Project Code', name: 'Project Code' },
                    { data: 'No of orders completed', name: 'No of orders completed' },
                    { data: 'Unit cost', name: 'Unit cost' },
                    { data: 'Total', name: 'Total' }
                ],
            });

        }
        $('#revenueTable').on('draw.dt', function () {
            $('#revenueTable').removeClass('d-none');
        });

        function orderWiseDetail(fromDate, toDate,projectId,processName){
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

        function revenueClientWise(fromDate, toDate,client_id){

            var toDate = toDate;
            var fromDate = fromDate;
            var client_id = client_id;

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
                    { data: 'Client', name: 'Client' },
                    { data: 'No of orders completed', name: 'No of orders completed' },
                    { data: 'Total', name: 'Total' },
                ],
                createdRow: function (row, data, dataIndex) {
                    $(row).find('.client-link').on('click', function () {
                        var clientId = $(this).attr('id');
                        processwiseDetail(fromDate, toDate,clientId);
                    });
                }
            });

        }
        $('#revenueClientTable').on('draw.dt', function () {
            $('#revenueClientTable').removeClass('d-none');
        });


        });


        $(document).ready(function() {
            $('.select2-basic-multiple').select2();
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
    });

    // $(document).ready(function() {
    //         $('.select2-basic-multiple').select2();
    //     });

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
    });


    // FTE

var fromDate  = "";
var toDate = "";
var client_id  = "";

    $(document).ready(function() {

        $('#filterButton').on('click', function (e) {
            e.preventDefault();
            fromDate = $('#fromDate_dcf').val();
            toDate = $('#toDate_dcf').val();
            client_id = $('#client_id_dcf').val();
            datatable.ajax.reload();
            fterevenueProjectWise(fromDate, toDate,client_id);
        });

        $(document).on('click', '.project-link-fte', function(event) {
            event.preventDefault();
            var project_id = $(this).data('id');
            var processName = $(this).text();
            fterevenueProject(fromDate, toDate,processName,project_id);
            ftetotalProccessWise(fromDate, toDate,project_id);
        });


        function ftetotalProccessWise(fromDate, toDate, project_id) {
            $.ajax({
                url: "{{ route('revenue_detail_process_total_fte') }}",
                type: "POST",
                data: {
                    from_date: fromDate,
                    to_date: toDate,
                    project_id: project_id,
                    _token: '{{csrf_token()}}',
                },
                dataType: 'json',
                success: function (response) {

                    let ProcessCountFTE = response.Total;
                    $('#ProcessCountFTE').text(ProcessCountFTE);
                }
            });
        }


        fterevenueProjectWise(fromDate, toDate,client_id);


        function fterevenueProjectWise(fromDate, toDate, client_id) {
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
                        d._token = '{{csrf_token()}}';
                    },
                    dataSrc: 'data',
                },
                columns: [
                    { data: 'client_name', name: 'client_name' },
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

                    name: 'project_code',
                     },
                    { data: 'unit_cost', name: 'unit_cost' },
                    { data: 'no_of_resources', name: 'no_of_resources' },
                    { data: 'expected_revenue', name: 'expected_revenue' },
                    { data: 'start_date', name: 'start_date' },
                    { data: 'end_date', name: 'end_date' },
                    { data: 'days', name: 'days' },
                    { data: 'revenue_selected', name: 'revenue_selected' },
                ],
         });
    }

  $(document).on('click', '.project-link-fte', function(event) {
            event.preventDefault();
            var project_id = $(this).data('id');
            var processName = $(this).text();
            console.log(project_id);
            fterevenueProject(fromDate, toDate,processName,project_id);
            ftetotalProccessWise(fromDate, toDate,project_id);
        });



            $('#fterevenueProjectTable').on('draw.dt', function () {
                $('#fteClientTable').removeClass('d-none');
            });


          function fterevenueProject(fromDate, toDate,processName,project_id){
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
                                { data: 'Unit cost', name: 'Unit cost' },
                                { data: 'Total', name: 'Total' }
                            ],
                        });

                    }
        });

//date script-start

    document.getElementById('toDate_dcf').valueAsDate = new Date();

    var currentDate = new Date();
        
    var firstDayOfMonth = new Date(currentDate.getFullYear(), currentDate.getMonth(), 2);
    
    var formattedDate = firstDayOfMonth.toISOString().split('T')[0];
    
    document.getElementById('fromDate_dcf').value = formattedDate;

    // future date script 
function isFutureDate(date) {
    var currentDate = new Date();
    return date > currentDate;
}

// Event listener for toDate_dcf
document.getElementById('toDate_dcf').addEventListener('change', function() {
    var selectedDate = new Date(this.value);
    
    if (isFutureDate(selectedDate)) {
        alert("You cannot select a future date.");
        this.valueAsDate = new Date();
    }
});

// Event listener for fromDate_dcf
document.getElementById('fromDate_dcf').addEventListener('change', function() {
    var selectedDate = new Date(this.value);
        if (isFutureDate(selectedDate)) {
        alert("You cannot select a future date.");
        this.valueAsDate = new Date();
    }
});

//date script-end






</script>
@endsection

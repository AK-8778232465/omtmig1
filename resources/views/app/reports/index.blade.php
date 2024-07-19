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
</style>
<div class="container-fluid d-flex reports">
    <div class="col-md-2 text-center left-menu">
        <h6 class="mt-2">List of Reports</h6>
        <ul>
            <li id="userwise-reports" class="report-item active">Userwise Reports</li>
            <li id="new-reports" class="report-item">New Reports</li>
            <li id="clientwise-reports" class="report-item">Clientwise Reports</li>
            <li id="txn-revenue-details" class="report-item">TXN Revenue Details</li>
            <li id="fte-revenue-details" class="report-item">FTE Revenue Details</li>
        </ul>
    </div>

    <div class="col-md-12">
        <div class="col-md-9 row justify-content-center">
            <div class="card">
                <div class="card-body" id="reports-content mt-1">
                    <h5 id="reports-heading">Reports - Userwise Reports</h5>
                </div>
            </div>
        </div>
        <div class="row">
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
                    <label for="project">Product</label>
                    <select class="form-control select2-basic-multiple" style="width:100%" name="dcf_project_id[]" id="project_id_dcf" multiple="multiple">
                        <option selected value="All">All Products</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2 mt-4">
                <button type="submit" id="filterButton" class="btn btn-primary">Filter</button>
            </div>
            <div class="card col-md-9 mt-5 tabledetails " id="userwise_table" style="font-size: 12px;">
                <h4 class="text-center mt-3" >Userwise Details</h4>
                <div class="card-body">
                    <div class="p-0">
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
                <h4 class="text-center mt-3">Report Details</h4>
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table id="newreports_datatable" class="table table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead class="text-center" style="font-size: 12px;">
                                <tr>
                                    <th width="12%">S.No</th>
                                    <th width="11%">Product Type</th>
                                    <th width="11%">Order Received</th>
                                    <th width="11%">Production Date</th>
                                    <th width="11%">Order Number</th>
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
        </div>
    </div>
</div>

<script src="{{asset('./assets/js/jquery.min.js')}}"></script>
<script>

    //new reports

    function newreports() {
        var fromDate = $('#fromDate_dcf').val();
        var toDate = $('#toDate_dcf').val();
        var client_id = $('#client_id_dcf').val();
        var project_id = $('#project_id_dcf').val();

        $('#newreports_datatable').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('newreports') }}",
            type: 'POST',
            data: function(d) {
                d.to_date = toDate;
                d.from_date = fromDate;
                d.client_id = client_id;
                d.project_id = project_id;
                d._token = '{{ csrf_token() }}';
            },
            dataSrc: 'data'
        },
        columns: [
            {
                data: null,
                name: 's_no',
                orderable: false,
                searchable: false,
                render: function(data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            { data: 'process', name: 'process' },
            { data: 'order_date', name: 'order_date' },
            { data: 'completion_date', name: 'completion_date' },
            { data: 'order_id', name: 'order_id' },
            { data: 'short_code', name: 'short_code' },
            { data: 'county_name', name: 'county_name' },
            { data: 'status', name: 'status' },
             { data: 'status_comment', name: 'status_comment' },
            { data: 'primary_source', name: 'primary_source' }
        ]
    });
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


function userwise_datatable(fromDate, toDate, client_id, projectId){
        fromDate = $('#fromDate_dcf').val();
        toDate = $('#toDate_dcf').val();
        client_id = $('#client_id_dcf').val();
        project_id = $('#project_id_dcf').val();


        datatable = $('#userwise_datatable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('userwise_count') }}",
                type: 'POST',
                data: function(d) {
                        d.to_date = toDate;
                        d.from_date = fromDate;
                        d.client_id = client_id;
                        d.project_id = project_id;
                        d._token = '{{csrf_token()}}';
                    },
                dataSrc: 'data'
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
                { data: 'All', name: 'All' },
            ],
            dom: 'l<"toolbar">Bfrtip',
        buttons: [
            'excel'
        ],
        });
    }


    $('#userwise_datatable').on('draw.dt', function () {
        $('#userwise_table').removeClass('d-none');
    });


$("#filterButton").on('click', function() {
    let fromDate = $("#fromDate_dcf").val();
    let toDate = $("#toDate_dcf").val();
    let client_id = $("#client_id_dcf").val();
    let project_id = $("#project_id_dcf").val();
    userwise_datatable(fromDate, toDate, client_id, project_id);
    newreports(fromDate, toDate, client_id, project_id);

    $('#client_id_dcf').on('change', function () {
        console.log('2');
       let getproject_id = $("#client_id_dcf").val();
       $("#project_id_dcf").html('All');
        fetchProData(getproject_id);
    });
});

$(document).ready(function() {
    fetchProData('All');
    console.log('1');
        $("#project_id").select2();
        $("#project_id_dcf").select2();
        $("#client_id_dcf").select2();

    $('.select2-basic-multiple').select2();
    userwise_datatable();
    newreports();


    $('#client_id_dcf').on('change', function () {
        console.log('2');
       let getproject_id = $("#client_id_dcf").val();
       $("#project_id_dcf").html('All');
        fetchProData(getproject_id);
    });
});



$(document).ready(function() {
    function showReport(reportType) {
        $('#reports-heading').text('Reports - ' + reportType);
        $('.report-item').removeClass('active');
        $('#' + reportType.toLowerCase().replace(/ /g, '-')).addClass('active');
        $('#userwise_table').hide();
        $('#newreports_table').hide();
        if (reportType === 'Userwise Reports') {
            $('#userwise_table').show();
        } else if (reportType === 'New Reports') {
            $('#newreports_table').show();
        }
    }
    showReport('Userwise Reports');
    $('.report-item').click(function() {
        var reportType = $(this).text();
        showReport(reportType);
    });
});



    let currentDate12 = new Date('<?php echo $currentDate; ?>');
    document.getElementById('toDate_dcf').valueAsDate = currentDate12;
    var currentDate = new Date('<?php echo $currentDate; ?>');
    var firstDayOfMonth = new Date(currentDate.getFullYear(), currentDate.getMonth(), 2);
    var formattedDate = firstDayOfMonth.toISOString().split('T')[0];
    document.getElementById('fromDate_dcf').value = formattedDate;
    function isFutureDate(date) {
    var currentDate = new Date('<?php echo $currentDate; ?>');
    return date > currentDate;
}
document.getElementById('toDate_dcf').addEventListener('change', function() {
    var selectedDate = new Date(this.value);
    if (isFutureDate(selectedDate)) {
        alert("You cannot select a future date.");
        this.valueAsDate = currentDate12;
    }
});
document.getElementById('fromDate_dcf').addEventListener('change', function() {
    var selectedDate = new Date(this.value);
        if (isFutureDate(selectedDate)) {
        alert("You cannot select a future date.");
        this.valueAsDate = currentDate12;
    }
});

</script>

@endsection


@extends('layouts.app')
@section('title', config('app.name') . ' | Reports')
@section('content')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="shortcut icon" href="{{asset('assets/images/favicon.ico')}}">
<link href="{{asset('plugins/daterangepicker/daterangepicker.css')}}" rel="stylesheet" />
<link href="{{asset('plugins/select2/select2.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('plugins/bootstrap-touchspin/css/jquery.bootstrap-touchspin.min.css')}}" rel="stylesheet" />
<link href="{{asset('plugins/nestable/jquery.nestable.min.css')}}" rel="stylesheet" />
<link href="{{asset('plugins/sweet-alert2/sweetalert2.min.css')}}" rel="stylesheet" type="text/css">
<link href="{{asset('plugins/animate/animate.css')}}" rel="stylesheet" type="text/css">
<link href="{{asset('plugins/dropify/css/dropify.min.css')}}" rel="stylesheet">
<link href="{{asset('plugins/jvectormap/jquery-jvectormap-2.0.2.css')}}" rel="stylesheet">
<link href="{{asset('plugins/jquery-steps/jquery.steps.css')}}">
<link href="{{asset('plugins/sweet-alert2/sweetalert2.min.css')}}" rel="stylesheet" type="text/css">
<link href="{{asset('plugins/datatables/dataTables.bootstrap4.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('plugins/datatables/buttons.bootstrap4.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('plugins/datatables/responsive.bootstrap4.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/css/jquery-ui.min.css')}}" rel="stylesheet">
<link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/css/icons.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/css/metisMenu.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/css/app.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/css/custom.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/css/style.css')}}" rel="stylesheet" type="text/css" />
<script src="{{asset('assets/js/jquery.min.js')}}"></script>
<script src="{{asset('assets/js/jquery-ui.min.js')}}"></script>
<link rel="stylesheet" type="text/css" href="{{asset('assets/css/pnotify.custom.css')}}">
<script type="text/javascript" src="{{asset('assets/js/pnotify.custom.js')}}"></script>
<link href="{{asset('assets/css/client_style.css')}}" rel="stylesheet" type="text/css" />
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
@php
    $currentDate = \Carbon\Carbon::now()->toDateString();
@endphp
<style>
.reports{
    padding-top: 7%;
}
.reports-content {
            display: none;
            margin-top: 10px;
        }
        * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .left-menu {
        position: fixed;
        top: 10%;
        height: 100%;
        padding: 20px;
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
            <li id="clientwise-reports" class="report-item">Clientwise Reports</li>
            <li id="txn-revenue-details" class="report-item">TXN Revenue Details</li>
            <li id="fte-revenue-details" class="report-item">FTE Revenue Details</li>
        </ul>
    </div>

    <div class="col-md-12">
        <div class="col-md-9 row justify-content-center">
            <div class="card">
                <div class="card-body" id="reports-content">
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
        </div>
    </div>
</div>
<script>

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
        $("#billing_id_dcf").select2();
    $('.select2-basic-multiple').select2();
    userwise_datatable();

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
        if (reportType === 'Userwise Reports') {
            $('#userwise_table').show();
        } else {
            $('#userwise_table').hide();
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

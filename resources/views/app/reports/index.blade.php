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
            <li id="userwise-details" class="report-item active">Userwise Details</li>
            <li id="orderwise-details" class="report-item">Orderwise Details</li>
            <li id="clientwise-details" class="report-item">Clientwise Details</li>
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
            <div class="col-md-4" style="width: 350px!important;">
                <div class="form-group" >
                    <label for="dateFilter" required>Select Date Range:</label>
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
                <h4 class="text-center mt-3">Orderwise Details</h4>
                <div class="card-body">
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
        </div>
    </div>
</div>

<script src="{{asset('./assets/js/jquery.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>

<script>


     








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
    return `[${formatDate(StartDate)} to ${formatDate(EndDate)}]`;
}

function getCurrentMonthDate() {
    let today = new Date();
    let StartDate = new Date(today.getFullYear(), today.getMonth(), 1);
    let EndDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
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
return `[${formatDate(StartDate)} to ${formatDate(EndDate)}]`;
}


function getYearlyDate() {
    let today = new Date();
    let startOfYear = new Date(today.getFullYear(), 0, 1);
    let endOfYear = new Date(today.getFullYear() + 1, 0, 0);

    return `Selected: ${formatDate(startOfYear)} to ${formatDate(endOfYear)}`;
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
        var fromDate = $('#fromDate_dcf').val();
        var toDate = $('#toDate_dcf').val();
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
                            to_date: toDate,
                            from_date: fromDate,
                            client_id: client_id,
                            project_id: project_id,
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


// function userwise_datatable(fromDate, toDate, client_id, projectId){
//         fromDate = $('#fromDate_dcf').val();
//         toDate = $('#toDate_dcf').val();
//         client_id = $('#client_id_dcf').val();
//         project_id = $('#project_id_dcf').val();


//         datatable = $('#userwise_datatable').DataTable({
//             destroy: true,
//             processing: true,
//             serverSide: true,
//             ajax: {
//                 url: "{{ route('userwise_count') }}",
//                 type: 'POST',
//                 data: function(d) {
//                         d.to_date = toDate;
//                         d.from_date = fromDate;
//                         d.client_id = client_id;
//                         d.project_id = project_id;
//                         d._token = '{{csrf_token()}}';
//                     },
//                 dataSrc: 'data'
//             },
//             columns: [
//                 { data: 'userinfo', name: 'userinfo', class: 'text-left' },
//                 { data: 'status_1', name: 'status_1', visible:@if(Auth::user()->hasRole('Qcer')) false @else true @endif},
//                 { data: 'status_13', name: 'status_13' },
//                 { data: 'status_14', name: 'status_14' },
//                 { data: 'status_4', name: 'status_4' },
//                 { data: 'status_2', name: 'status_2' },
//                 { data: 'status_3', name: 'status_3' },
//                 { data: 'status_5', name: 'status_5' },
//                 { data: 'All', name: 'All' },
//             ],
//             dom: 'l<"toolbar">Bfrtip',
//         buttons: [
//             'excel'
//         ],
//         });
//     }

function userwise_datatable() {
    var fromDate = $('#fromDate_dcf').val();
    var toDate = $('#toDate_dcf').val();
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
                        d.to_date = toDate;
                        d.from_date = fromDate;
                        d.client_id = client_id;
                        d.project_id = project_id;
                d._token = '{{ csrf_token() }}';
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
                            to_date: toDate,
                            from_date: fromDate,
                            client_id: client_id,
                            project_id: project_id,
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

        // Hide all tables initially
        $('#userwise_table').hide();
        $('#newreports_table').hide(); // Fixed table ID
        $('#clientwise_table').hide();
        $('#txn_revenue_table').hide();
        $('#fte_revenue_table').hide();

        // Show the selected table
        if (reportType === 'Userwise Details') {
            $('#userwise_table').show();
        } else if (reportType === 'Orderwise Details') {
            $('#newreports_table').show();
        } else if (reportType === 'Clientwise Details') {
            $('#clientwise_table').show();
        } else if (reportType === 'TXN Revenue Details') {
            $('#txn_revenue_table').show();
        } else if (reportType === 'FTE Revenue Details') {
            $('#fte_revenue_table').show();
        }
    }

    // Set default report
    showReport('Userwise Details');

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
    var fromDate = new Date(document.getElementById('fromDate_dcf').value);

    if (isFutureDate(selectedDate)) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'You cannot select a future date.'
        });
        this.valueAsDate = currentDate12;
    } else if (selectedDate < fromDate) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'According to your "From Date", you cannot choose a oldestdate  than the "From Date".'
        });
        this.valueAsDate = fromDate  ;
    }
});
document.getElementById('fromDate_dcf').addEventListener('change', function() {
    var selectedDate = new Date(this.value);
        if (isFutureDate(selectedDate)) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'You cannot select a future date.'
        });
        this.valueAsDate = currentDate12;
    } else {
        var toDate = new Date(document.getElementById('toDate_dcf').value);
        if (toDate < selectedDate) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'According to your "From Date", you cannot choose a "To Date" earlier than the "From Date".'
            });
            document.getElementById('toDate_dcf').valueAsDate = selectedDate;
        }
    }
});

</script>

@endsection


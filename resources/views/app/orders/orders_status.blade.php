@extends('layouts.app')
@section('title', config('app.name') . ' | Orders')
@section('content')
@include('app.orders.style')

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
                            <input type="date" id="order_date_ed" value="" class="form-control" name="order_date">
                        </div>
                        <div class="col-lg-4">
                            <label class="font-weight-bold">Project Code<span style="color:red;">*</span></label><br>
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
                        <div class="col-lg-4 mt-4">
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
                        <div class="col-lg-4 mb-4" id="hide_qa">
                            <label class="font-weight-bold text-right">Assign QA</label>
                            <select id="assign_qa_ed" name="assign_qa" type="text" class="select2dropdown form-control">
                                <option selected disabled value="">Select Assign QA</option>
                                @foreach ($qcers as $qcer)
                                    <option value="{{ $qcer->id }}">{{ $qcer->username }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" class="btn btn-danger mr-2">Close</button>
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
                    <button id="status_6"  class="btn btn-info status-btn @if(Auth::user()->hasRole('Qcer')) d-none @endif">Yet to Assign User<span id="status_6_count"></span></button>
                    <button id="status_7"  class="btn btn-info status-btn d-none">Yet to Assign QA<span id="status_7_count"></span></button>
                    <button id="status_1" class="btn btn-info status-btn @if(Auth::user()->hasRole('Qcer')) d-none @endif">WIP<span id="status_1_count"></span></button>
                    <button id="status_13" class="btn btn-info status-btn @if(Auth::user()->hasRole('Qcer')) d-none @endif">Coversheet Prep<span id="status_13_count"></span></button>
                    <button id="status_14" class="btn btn-info status-btn">Clarification<span id="status_14_count"></span></button>
                    <button id="status_4" class="btn btn-info status-btn">Send For Qc<span id="status_4_count"></span></button>
                    <button id="status_2" class="btn btn-info status-btn">Hold<span id="status_2_count"></span></button>
                    <button id="status_5" class="btn btn-info status-btn">Completed<span id="status_5_count"></span></button>
                    <button id="status_3" class="btn btn-info status-btn">Cancelled<span id="status_3_count"></span></button>
                    <button id="status_All" class="btn btn-info status-btn" >All<span id="status_All_count"></span></button>
                </div>
            </div>
            <div class="p-0 mx-2">
                <form id="assignmentForm" method="POST" data-parsley-validate>
                    <table id="order_datatable" class="table table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                        <tr>
                            <th style="width:10%">Order ID</th>
                            <th style="width:10%">Received Date</th>
                            <th style="width:10%">Product Code @for($i = 0; $i < 5; $i++) &nbsp; @endfor</th>
                            <th style="width:10%">Lob</th>
                            <th style="width:10%">Type</th>
                            <th style="width:15%">State @for($i = 0; $i < 5; $i++) &nbsp; @endfor</th>
                            <th style="width:15%">County @for($i = 0; $i < 5; $i++) &nbsp; @endfor</th>
                            <th style="width:20%">Status @for($i = 0; $i < 17; $i++) &nbsp; @endfor</th>
                            <th style="width:10%">User</th>
                            <th style="width:10%">QA</th>
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
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>


                    <div class="form-group">
                        <div class="row d-none" id="assign_tab">
                            <div class="col-12 row">
                                <div class="col-3"></div>
                                <div class="col-4">
                                        <select style="width: 100%;" required class="form-control form-control-sm" id="user_id" name="user_id">
                                        <option selected disabled value=""> Select User</option>
                                    </select>
                                </div>
                                <div class="col-2">
                                    <button type="submit" class="btn btn-sm btn-primary" id="assignBtn" name="assign">Assign</button>
                                </div>
                                <div class="col-3"></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">

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
        var currentURI = window.location.href;
        var match = currentURI.match(/\/orders_status\/(\d+|All)/);
        if (match) {
            var statusID = match[1];
            defaultStatus = statusID;
            @if(Session::has('dashboardfilters') && Session::get('dashboardfilters') == true)
                sessionfilter = true;
                $('#statusButtons').hide();
            @endif
        } else {
            defaultStatus = @if(Auth::user()->hasRole('Process') || Auth::user()->hasRole('Qcer') || Auth::user()->hasRole('Process/Qcer')) 1 @else 6 @endif;
        }

        datatable = $('#order_datatable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            scrollX: true,
            lengthMenu: [10, 25, 50, 100, 200, 500],
            ajax: {
                url: '{{ route("getOrderData") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: defaultStatus,
                    sessionfilter : sessionfilter
                }
            },
            "columns": [
                { "data": "order_id", "name": "order_id" },
                { "data": "order_date", "name": "order_date" },
                { "data": "project_code", "name": "project_code" },
                { "data": "lob_name", "name": "lob_name" },
                { "data": "process_name", "name": "process_name" },
                { "data": "short_code", "name": "short_code" },
                { "data": "county_name", "name": "county_name" },
                { "data": "status", "name": "status" },
                { "data": "assignee_user", "name": "assignee_user", "visible": @if(Auth::user()->hasRole('Process')) false @else true @endif },
                { "data": "assignee_qa", "name": "assignee_qa", "visible": @if(Auth::user()->hasRole('Qcer')) false @else true @endif },
                {
                    "data": "checkbox",
                    "name": "checkbox",
                    "visible": @if(Auth::user()->hasRole('Process') || Auth::user()->hasRole('Qcer') || Auth::user()->hasRole('Process/Qcer')) false @else true @endif,
                    "orderable": false,
                },
                {
                    "data": "action",
                    "name": "action",
                    "visible": @if(Auth::user()->hasRole('Process') || Auth::user()->hasRole('Qcer') || Auth::user()->hasRole('Process/Qcer')) false @else true @endif,
                    "orderable": false,
                },
                { "data": "associate_name", "name": "associate_name", "visible": true},
            ],
            createdRow: function (row, data, dataIndex) {
                    let status = data.status_id; // Assuming the status_id field exists in the returned data
                    let tat_value = data.tat_value;
                    let orderDate = new Date(data.order_date);
                    let currentDate = new Date();
                    let timeDiff = Math.abs(currentDate - orderDate);
                    let diffHours = Math.floor((timeDiff / (1000 * 60 * 60)) % 24);
                    if(!tat_value == 0){
                        if (status == 1 || status == 4 || status == 13) {
                            if (diffHours > tat_value) {
                                $(row).addClass('text-danger'); // Apply red color to the row
                            }
                        }
                    }
                }
            });

        $('.status-btn').removeClass('btn-primary').addClass('text-white');
        $('#status_' + defaultStatus).removeClass('btn-info').addClass('btn-primary');
        $('.status-dropdown').prop('disabled', true);
        updateStatusCounts();
        
        var lastOrderStatus = localStorage.getItem("lastOrderStatus");

        if(lastOrderStatus) {
            $('#'+lastOrderStatus).click();
        }

        lastOrderStatus = null;
    });

    $(document).on('click', '.status-btn', function () {
        $('#assign_tab').addClass('d-none');
        if ($("#user_id").data('select2') !== undefined) {
            $("#user_id").select2('destroy');
        }
        $('#user_id').prop('selectedIndex',0);
        $("#user_id").select2();
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
        $('#user_id').prop('selectedIndex',0);
        $("#user_id").select2();
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
                datatable.column(8).visible(true);
                $('.status-dropdown').prop('disabled', true);
            }
        } else {
            $('.status-dropdown').prop('disabled', false);
            datatable.column(8).visible(false);
        }
        // //
        @if(Auth::user()->hasRole('Super Admin') || Auth::user()->hasRole('Business Head') ||Auth::user()->hasRole('PM/TL') )
            if(status == 13){
                $('.status-dropdown').prop('disabled', false);
                datatable.column(8).visible(true);
            }
            else if(status == 6){
                $('.status-dropdown').prop('disabled', false);
                datatable.column(8).visible(true);
                $('.status-dropdown').prop('disabled', true);
            } else {
                datatable.column(8).visible(false);
            }
        @endif
        // //
        if(status == 13){
            $('.status-dropdown').prop('disabled', false);
            datatable.column(10).visible(true);
        } else {
            datatable.column(10).visible(false);
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
            @if(Auth::user()->hasRole('Super Admin') || Auth::user()->hasRole('Business Head') ||Auth::user()->hasRole('PM/TL') || Auth::user()->hasRole('SPOC'))
            $('.status-dropdown').prop('disabled', false);
            @endif
        }

        @if(Auth::user()->hasRole('Process') || Auth::user()->hasRole('Process/Qcer'))
        var allStatusValues = [];
        $('.status-dropdown').each(function() {
            var value = $(this).val();
            if (value !== '1' && value !== '3') {
                    $(this).prop('disabled', true);
            }
            allStatusValues.push(value);
        });
        @endif


        if (status == 4 || status == 2) {
            @if(Auth::user()->hasRole('Process') || Auth::user()->hasRole('Process/Qcer'))
                $('.status-dropdown').prop('disabled', true);
            @endif
        }

    });

function updateStatusCounts() {
  $.ajax({
    url: "{{ route('getStatusCount') }}",
    type: 'POST',
    data: {
      _token: '{{ csrf_token() }}'
    },
    success: function(response) {
      if (response.StatusCounts !== undefined) {
        let statusCounts = response.StatusCounts;
        let assign = response.AssignCoverSheet;
        let total = 0;
        for (let status = 1; status <= 14; status++) {
          if (status !== 6) { // Exclude status 6
            let count = statusCounts[status] || 0;
            total += count;
            $('#status_' + status + '_count').text(' (' + count + ')');
          }
        }
        @if(Auth::user()->hasRole('Process') || Auth::user()->hasRole('Process/Qcer'))
        $('#status_13_count').text("(" + statusCounts[13] + "+" + assign + ")");
        @endif
        let count6 = statusCounts[6] || 0;
        let count7 = statusCounts[7] || 0;
        $('#status_6_count').text(' (' + count6 + ')');
        $('#status_7_count').text(' (' + count7 + ')');
        $('#status_All_count').text(' (' + total + ')');
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
            if (status == 6) {
            var userlist = (status == 6) ? <?php echo json_encode($processors); ?> : <?php echo json_encode($qcers); ?>;
            } else if (status == 13) {
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

    $(document).on('change', 'input.check-one', function() {
    var allChecked = $('input.check-one').length === $('input.check-one:checked').length;
    $('input.check-all').prop('checked', allChecked);
    });

    $(document).on('change', 'input.check-one', function() {
        var anyCheckboxChecked = $('input.check-one:checked').length > 0;
        if (anyCheckboxChecked) {
            $('#assign_tab').removeClass('d-none');
            task_status = $('#statusButtons').find('.btn-primary').attr('id');
            let status = task_status.replace("status_", "");
            var userlist = [];

            if (status == 6) {
                userlist = <?php echo json_encode($processors); ?>;
            } else if (status == 7) {
                userlist = <?php echo json_encode($qcers); ?>;
            }else if (status == 13) {
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
                    selectedStatus: selectedStatus,
                    _token: '{{csrf_token()}}',
                },
                success: function(response) {
                    if(response.data != undefined) {
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
                            text: "Can't able to Update Status",
                            icon: "error",
                            timer: 1500
                        });
                        page_reload();
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


    // Edit company
     $('#order_datatable').on('click','.edit_order',function () {
        $('#re_assign').prop('checked', false);
        $('#hide_user, #hide_qa').hide();
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
                console.log($("#assign_qa_ed").val());
                if ($("#assign_qa_ed").val() != null || $("#assign_qa_ed").val() != undefined) {
                    $("#hide_qa").show();
                } else {
                    $("#hide_qa").hide();
                }
                $('.select2dropdown').select2();
            } else {
                $('#hide_user, #hide_qa').hide();
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
		if($('#updateorderfrm').parsley().isValid()){
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


    $(document).on("click", "#assignBtn", function (event) {
        event.preventDefault();
        if ($("#assignmentForm").parsley().isValid()) {
            let task_status = $('#statusButtons').find('.btn-primary').attr('id');
            let status = task_status.replace("status_", "");
            let checkedOrdersArray = $('input[name="orders[]"]:checked');
            let user_id = $('#user_id').val();

            $.ajax({
                type: "POST",
                url: "{{ route('assignment_update') }}",
                data: {
                    type_id: status,
                    orders: checkedOrdersArray.map(function() {
                        return this.value;
                    }).get(),
                    user_id: user_id,
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

    $(document).on("click", ".assign-me", function (event) {
        let task_status = $('#statusButtons').find('.btn-primary').attr('id');
        let status = task_status.replace("status_", "");
        var elementId = $(this).attr('id');
        let order_id = elementId.split('_')[2];
        let user_id = "{!! Auth::id() !!}";
        let orders = [order_id];
        $.ajax({
            type: "POST",
            url: "{{ route('assignment_update') }}",
            data: {
                type_id: status,
                orders: orders,
                user_id: user_id,
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
    if (status == 13) {
            window.location.href = "{{ url('coversheet-prep/') }}/" + order_id;
        } else {
            window.location.href = "{{ url('orderform/') }}/" + order_id;
        }
    });


</script>

@endsection

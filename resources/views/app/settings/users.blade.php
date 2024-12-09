@extends('layouts.app')
@section('title', config('app.name') . ' | Users')
@section('content')
<style>
    .modal-body {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .scrollable-col {
        height: calc(100vh - 10px); /* Adjust based on header height */
        overflow-y: auto;
        padding: 0 15px; /* Optional: for better padding */
    }

    .scroll-content {
        height: 100%;
    }

    .scrollable-col::-webkit-scrollbar-track
        {
            -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
            background-color: #F5F5F5;
            border-radius: 10px;
        }

        .scrollable-col::-webkit-scrollbar
        {
            width: 10px;
            background-color: #F5F5F5;
        }

        .scrollable-col::-webkit-scrollbar-thumb
        {
            background-color: #AAA;
            border-radius: 10px;
            background-image: -webkit-linear-gradient(90deg,
                                                    rgba(0, 0, 0, .2) 25%,
                                                    transparent 25%,
                                                    transparent 50%,
                                                    rgba(0, 0, 0, .2) 50%,
                                                    rgba(0, 0, 0, .2) 75%,
                                                    transparent 75%,
                                                    transparent)
        }

   .text-center {
        text-align: center;
    }
    /* Style for checkbox */
    .checkbox-container {
        display: flex;
        align-items: center;
    }
    .checkbox-container input[type="checkbox"] {
        margin-right: 10px; /* Adjust spacing between checkbox and text */
    }
</style>
<div class="container-fluid mt-2">
@include('app.settings.index')

{{-- Assigned User Modal --}}
<div class="modal fade" id="assigneeModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Assignee User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div>
                <form id="import" class="p-2"  enctype="multipart/form-data">
                @csrf
                <div class="form-group row mb-0 pb-0 pl-3 pr-3 align-items-center" style="flex-direction: column;">
                    <div class="form-group col-lg-6 mb-2 pb-0">

                        <input type="file" id="file" name="file"  data-height="75" height="100px" class="form-controlfile suppdoc dropify" accept=".csv,.xlsx,.ods">
                    </div>

                    <div class="form-group col-lg-6 mb-2 mt-2 pb-0 text-center" style="margin-top: auto;">
                        <button type="submit" class="btn btn-sm btn-primary" maxlength="100">
                            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="13" height="14" x="0" y="0" viewBox="0 0 459.904 459.904" style="enable-background:new 0 0 512 512" xml:space="preserve" class="">
                                <g>
                                    <path d="M123.465 168.28h46.543v138.07c0 14.008 11.358 25.352 25.352 25.352h69.2c13.993 0 25.352-11.343 25.352-25.352V168.28h46.527c7.708 0 14.637-4.641 17.601-11.764 2.933-7.094 1.301-15.295-4.145-20.741L243.413 29.28c-7.437-7.422-19.485-7.422-26.938 0L110.011 135.775a19.023 19.023 0 0 0-4.13 20.741c2.962 7.109 9.876 11.764 17.584 11.764z" fill="#ffffff" opacity="1" data-original="#ffffff" class=""></path>
                                    <path d="M437.036 220.029c-12.617 0-22.852 10.237-22.852 22.867v95.615c0 28.643-23.317 51.944-51.961 51.944H97.679c-28.644 0-51.945-23.301-51.945-51.944v-95.615c0-12.63-10.251-22.867-22.867-22.867C10.236 220.029 0 230.266 0 242.897v95.615c0 53.859 43.818 97.679 97.679 97.679h264.544c53.861 0 97.681-43.819 97.681-97.679v-95.615c0-12.631-10.237-22.868-22.868-22.868z" fill="#ffffff" opacity="1" data-original="#ffffff" class=""></path>
                                </g>
                            </svg> Upload File
                        </button>
                        @if($exportCount > 0)
                        <a href="{{ route('export') }}" class="btn btn-sm text-white"; style="background-color: #FFA726;">Export Data</a>
                        @endif
                    </div>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- End --}}
    <div class="col-lg-12">
        <div class="card ">
            <div class="card-body">
                <div class="p-0">
                    <table id="datatable" class="table table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>Sl No</th>
                                <th>Emp ID</th>
                                <th>User Name</th>
                                <th>Role</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($usersData as $users)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $users->emp_id }}</td>
                                <td>{{ $users->username }}</td>
                                <td>{{ isset($users->roles) ? $users->roles : ""}}</td>
                                <td>{{ $users->email}}</td>
                                <td class="text-center">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox"  class="custom-control-input" id="customSwitch{{ $users->id }}"  value="{{ $users->id }}" onclick="userStatus(this.value)" @if($users->is_active==1) checked @endif>
                                        <label class="custom-control-label" for="customSwitch{{ $users->id }}">@if($users->is_active==1) Active @else Inactive @endif</label>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @role('Super Admin|Business Head|SPOC|PM/TL|VP|Admin|AVP')
                                    <a href="#"><span class="edit_user ml-2"  data-id="{{ $users->id }}">
                                        <img class="menuicon tbl_editbtn" src="{{asset('assets/images/edit.svg')}}" >&nbsp;
                                    </span></a>
                                    @endrole
                                    <span class="assignService ml-2" onclick="assignService({{$users->id}})" data-id="{{ $users->id }}">
                                        <i class="fas fa-user-plus" style="cursor: pointer"></i>&nbsp;
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</div>
{{-- AddingUsers --}}
<div class="modal fade" id="myModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add user</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" id="usersForm" name="usersForm"  data-parsley-validate>
                @csrf
                <input  name="user_id" value="" id="ed_user_id" type="hidden"  />
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-4">
                            <label for="example-email-input" class="col-form-label"> User Role <span class="text-danger"> * </span></label>
                            <Select required class="form-control select_role" name="user_type_id" id="user_type_id">
                                <option selected disabled value="">Select Role</option>
                                @forelse($userTypes as $userType)
                                    <option value="{{$userType->id}}"> {{$userType->usertype}}</option>
                                @empty
                                @endforelse
                            </Select>
                        </div>
                        <div class="col-lg-4">
                            <label for="example-firstname-input" class="col-form-label">Emp ID <span class="text-danger"> * </span></label>
                            <input required name="emp_id" value="" id="emp_id" type="text" class="form-control" placeholder="Enter Emp ID" />
                        </div>
                        <div class="col-lg-4">
                            <label for="example-firstname-input" class="col-form-label">User Name</label>
                            <input autocomplete="off"  value="" name="username" id="username" type="text" class="form-control" placeholder="Enter User Name" />
                        </div>
                    </div>
                    <div class="row mt-1">
                        <div class="col-lg-4">
                            <label for="example-email-input" class="col-form-label">Email <span class="text-danger"> * </span></label>
                            <input autocomplete="off" required value="" name="email" id="email" type="email" class="form-control" placeholder="Enter Email" data-parsley-type="email" data-parsley-trigger="keyup" placeholder="Enter a valid e-mail"/>
                        </div>
                        <div class="col-lg-4">
                            <label for="example-email-input" class="col-form-label"> Password </label>

                            <input id="password" name="password" type="text" class="form-control" autocomplete="off" 	placeholder="Enter Password"   data-parsley-minlength="8"
                            data-parsley-errors-container=".errorspannewpassinput1"
                            data-parsley-required-message="Please enter your new password."
                            data-parsley-uppercase="1"
                            data-parsley-lowercase="1"
                            data-parsley-number="1"
                            data-parsley-special="1"
                            >
                            <span class="errorspannewpassinput1" ></span>
                        </div>
                        <div class="col-lg-4" id="add_reporting_to">
                            <label for="example-contact-input" class="col-form-label">Reporting To <span class="text-danger"> * </span></label>
                            <Select class="form-control select_role" name="reporting_to" id="reporting_to">

                            </Select>
                        </div>
                        <div class="col-lg-2">
                            <br>
                            <div class="mt-3">
                                <input type="checkbox" class="mx-1"  value="1" id="is_active" name="is_active" />
                                <label for="" class="">Is Active ?</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" onclick="usersVal();">Create</button>
                </div>
            </form>
        </div>
    </div>
  </div>
    <div class="modal fade" id="userEditModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Edit user</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form method="post" id="updateuserfrm" name="updateuserfrm"  data-parsley-validate>
					@csrf
                    <input  name="user_id" value="" id="user_id_ed" type="hidden"  />
					<div class="modal-body">
						<div class="row">
							<div class="col-lg-4">
								<label for="example-email-input" class="col-form-label"> User Role <span class="text-danger"> * </span></label>
                                <Select required class="form-control select_role" name="user_type_id" id="user_type_id_ed">
                                    <option selected disabled value="">Select Role</option>
                                        @forelse($userTypes as $userType)
                                        <option value="{{$userType->id}}"> {{$userType->usertype}}</option>
                                        @empty
                                    @endforelse
                                </Select>
							</div>
							<div class="col-lg-4">
								<label for="example-firstname-input" class="col-form-label">Emp ID <span class="text-danger"> * </span></label>
								<input required name="emp_id" value="" id="emp_id_ed" type="text" class="form-control"  placeholder="Enter Emp ID" />
							</div>
							<div class="col-lg-4">
								<label for="example-firstname-input" class="col-form-label">User Name</label>
								<input autocomplete="off"  value="" name="username" id="username_ed" type="text" class="form-control" placeholder="Enter User Name" />
							</div>
						</div>
						<div class="row mt-1">
                            <div class="col-lg-4">
                                <label for="example-email-input" class="col-form-label">Email <span class="text-danger"> * </span></label>
                                <input autocomplete="off" required value="" name="email" id="email_ed" type="email" class="form-control" placeholder="Enter Email" data-parsley-type="email" data-parsley-trigger="keyup" placeholder="Enter a valid e-mail"/>
                            </div>
							<div class="col-lg-4">
								<label for="example-email-input" class="col-form-label"> Password </label>

								<input id="password_ed" name="password" type="password" class="form-control" autocomplete="off" 	placeholder="Enter Password"   data-parsley-minlength="8"
								data-parsley-errors-container=".errorspannewpassinput1"
								data-parsley-required-message="Please enter your new password."
								data-parsley-uppercase="1"
								data-parsley-lowercase="1"
								data-parsley-number="1"
								data-parsley-special="1"
								>
								<span class="errorspannewpassinput1" ></span>
							</div>
                            <div class="col-lg-4" id="edit_reporting_to">
                            <label for="example-email-input" class="col-form-label"> Reporting To <span class="text-danger"> * </span></label>
                            <Select class="form-control select_role" name="reporting_to" id="reporting_to_ed">
                            </Select>
                        </div>
                            <div class="col-lg-2">
                                <br>
                                <div class="mt-3">
                                    <input type="checkbox" class="mx-1"  value="1" id="is_active_ed" name="is_active" />
                                    <label for="" class="">Is Active ?</label>
                                </div>
                            </div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
						<button type="submit" class="btn btn-primary">Update</button>
					</div>
				</form>
			</div>
		</div>
	</div>

    {{-- Assign Service Modal --}}
    <div class="modal fade vh-75" id="assignServiceModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-xl" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Project Mapping</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
					</button>
				</div>
                <div class="modal-body">
                    <div class="row d-flex assigned_row">
                        <div class="col-6">
                            <div class="d-flex justify-content-end mb-3">
                                <button type="button" class="btn btn-primary" id="confirmButton">Confirm</button>
                            </div>
                        <div id="jstree"  class="scrollable-col"></div>
                        </div>
                        <div class="col-6">
                            <div id="jstreestatic" class="scrollable-col mt-5"></div>
                        </div>
                    </div>
                </div>
                </div>
			</div>
		</div>
	</div>

{{-- Js --}}
<script type="text/javascript">
    $(function () {
        $('#add_reporting_to').hide();
        $('#edit_reporting_to').hide();
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

    function userStatus(value)
    {
        window.location.href = '/userStatus/' + value;
    }
    $(document).ready(function()
        {
            var $tabid=  'tab3';
            $(".odtabs").not("#tab3").addClass('btn-outline-secondary');
            $("#tab3").addClass('btn-secondary');

            $('.modal').on('hidden.bs.modal', function() {
                $(this).find('form')[0].reset();
                $(this).find('form').parsley().reset();
            });
        $("form").parsley();
            var table = $('#datatable').DataTable({
                responsive: true,
                dom: 'l<"toolbar">Bfrtip',
                buttons: [
                    'excel'
                ],
                initComplete: function(){
                @role('Super Admin')
                $("div.toolbar").html('<button id="addUsers" type="button" class="ml-2 btn btn-primary" data-toggle="modal" data-target="#myModal"><img class="menuicon" src="{{asset("assets/images/add.svg")}}">&nbsp;Add User</button>' +
                '<button id="assigneeUsers" type="button" class="ml-2 btn btn-primary" data-toggle="modal" data-target="#assigneeModal"><img class="menuicon" src="{{asset("assets/images/add.svg")}}">&nbsp;Assignee User</button><br />');
                @endrole
                @role('PM/TL')
                $("div.toolbar").html('<button id="addUsers" type="button" class="ml-2 btn btn-primary" data-toggle="modal" data-target="#myModal"><img class="menuicon" src="{{asset("assets/images/add.svg")}}">&nbsp;Add User</button>');
                @endrole
                @role('Business Head')
                $("div.toolbar").html('<button id="addUsers" type="button" class="ml-2 btn btn-primary" data-toggle="modal" data-target="#myModal"><img class="menuicon" src="{{asset("assets/images/add.svg")}}">&nbsp;Add User</button>');
                @endrole
                @role('VP')
                $("div.toolbar").html('<button id="addUsers" type="button" class="ml-2 btn btn-primary" data-toggle="modal" data-target="#myModal"><img class="menuicon" src="{{asset("assets/images/add.svg")}}">&nbsp;Add User</button>');
                @endrole
                @role('AVP')
                $("div.toolbar").html('<button id="addUsers" type="button" class="ml-2 btn btn-primary" data-toggle="modal" data-target="#myModal"><img class="menuicon" src="{{asset("assets/images/add.svg")}}">&nbsp;Add User</button>');
                @endrole
                @role('Admin')
                $("div.toolbar").html('<button id="addUsers" type="button" class="ml-2 btn btn-primary" data-toggle="modal" data-target="#myModal"><img class="menuicon" src="{{asset("assets/images/add.svg")}}">&nbsp;Add User</button>');
                @endrole
            }
            });
        });
        function getUsers(){
            window.location.href = "/users";
        }
        function usersVal(){
            if ($("#usersForm").parsley()) {

                if ($("#usersForm").parsley().validate()) {
                    event.preventDefault();
                    if ($("#usersForm").parsley().isValid()) {
                        $.ajax({
                        type: "POST",
                        cache:false,
                        async: false,
                        url: "{{ url('/usersInsert') }}",
                        data: new FormData($("#usersForm")[0]),
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if(response.msg=="User Already Exists!"){

                                new PNotify({
                                title: 'Error',
                                text:  response.msg,
                                type: 'error'
                                });
                                return false;
                            } else if(response.msg=="Only Image Allowed!"){

                                new PNotify({
                                title: 'Error',
                                text:  response.msg,
                                type: 'error'
                                });
                                return false;
                            }else if(response.msg=="User Added Successfully!"){

                                new PNotify({
                                title: 'Success',
                                text:  response.msg,
                                type: 'success'
                                });
                                setTimeout(function(){  location.reload(); }, 1000);
                            }
                        },
                        error:function(response) {

                            var err = "";
                            $.each(response.responseJSON.errors,function(field_name,error){
                                err = err+error;
                            });
                            new PNotify({
                                title: 'Error',
                                text:err,
                                type: 'error',
                                delay: 1000
                            });
                        }
                        });
                    }
                }
            }
        }
    function usersStatus(value) {
        window.location.href = '/usersStatus/' + value;
    }

    $('#updateuserfrm').on('submit', function(event){
		event.preventDefault();
        $('#department_ed').prop('disabled', false);
		if($('#updateuserfrm').parsley().isValid()){
			var url = '{{ route("updateUsers") }}';
			var data = $("#updateuserfrm").serialize();
			$.ajax({
				type: "post",
				url: url,
				data: data,
				success: function(response) {
					new PNotify({
						title: 'Success',
						text:  response.msg,
						type: 'success'
					});
					setTimeout(function(){  location.reload(); }, 800);
				},
				error:function(response) {
					var err = "";
					$.each(response.responseJSON.errors,function(field_name,error){
						err = err +'<br>' + error;
					});
					new PNotify({
						title: 'Error',
						text:err,
						type: 'error',
						delay: 2000
					});
				}
			});
		}
	});

    // Edit users
    $('#datatable').on('click','.edit_user',function () {
		var id = $(this).data('id');
		var url = '{{ route("edit_user") }}';
		$.ajax({
			type: "post",
			url: url,
			data: { id:id , _token: '{{csrf_token()}}'},
			success: function(response)
			{
				var res = response;

                $("#user_id_ed").val(res['id']);
				$("#username_ed").val(res['username']);
				$("#emp_id_ed").val(res['emp_id']);
				$("#email_ed").val(res['email']);
                $("#contact_no_ed").val(res['contact_no']);
                $("#password_ed").val(res['password']);
				$("#user_type_id_ed").val(res['user_type_id']);


				if(res['is_active'] == 1) {
					$( "#is_active_ed" ).attr('checked', 'checked');
				} else {
					$("#is_active_ed").removeAttr('checked', 'checked');
				}

                let role = res['user_type_id'];
                let reporting_users;
                if(role == 1) {
                    $('#edit_reporting_to').hide();
                } else if (role == 3) {
                    $('#edit_reporting_to').show();
                    reporting_users = 'getAVps';
                } else if (role == 5) {
                    $('#edit_reporting_to').show();
                    reporting_users = 'getBussinessHeads';
                } else if (role == 9) {
                    $('#edit_reporting_to').show();
                    reporting_users = 'getPM_TL';
                }else if (role == 6 || role == 7 || role == 8  || role == 10 || role == 11 || role == 22 ) {
                    $('#edit_reporting_to').show();
                    reporting_users = 'getSOPC';
                }else if (role == 2 ) {
                    $('#edit_reporting_to').show();
                    reporting_users = 'getVp';
                }else if (role == 24 ) {
                    $('#edit_reporting_to').show();
                    reporting_users = 'getAdmin';
                }
                $.ajax({
                    url: "{{ route('getUserList') }}",
                    type: "POST",
                    data: {
                        reviewer_type: reporting_users,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(html) {
                        $('#reporting_to_ed').html(html);
                        let reporting_to = res['reporting_to'];
                        if (reporting_to && $.isNumeric(reporting_to)) {
                            $("#reporting_to_ed").val(res['reporting_to']);
                            $('#edit_reporting_to').show();
                        } else {
                            $('#edit_reporting_to').hide();
                        }
                    },
                    error: function(error) {
                        // Handle error if needed
                    }
                });

				$("#userEditModal").modal('show');
			}
		});
	});




function assignService(userID) {

    function fetchMappingData() {
        $.ajax({
            url: '{{ route("mappingData") }}',
            type: 'POST',
            data: {
                id: userID,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                var allServicesData = response.allService;
                var assignedServices = response.assignedServiceData;
                var assignedServicesData = response.assignedService;
                var unassignedServicesData = response.assignedService;

                var treeData = allServicesData.map(function(lob) {
                    return {
                        'text': lob.lob_name,
                        'children': lob.items.map(function(item) {
                            return {
                                'text': item.project_code + ': ' + item.process_name,
                                'id': item.id,
                                'checkbox': true,
                                'state': {
                                    'selected': assignedServicesData.includes(item.id) // Check if the item is in the assigned services
                                }
                            };
                        }),
                        'state': {
                            'opened': true // Optionally open the parent nodes
                        }
                    };
                });

                var statictreeData = assignedServices.map(function(lob) {
                    return {
                        'text': lob.lob_name,
                        'children': lob.items.map(function(item) {
                            return {
                                'text': item.project_code + ': ' + item.process_name,
                                'id': item.id,
                            };
                        }),
                        'state': {
                            'opened': true // Optionally open the parent nodes
                        }
                    };
                });

                var jsTreeInstance = $('#jstree').jstree(true);
                if (jsTreeInstance) {
                    jsTreeInstance.destroy(); // Destroy the existing tree
                }

                var jsTreeInstancestatic = $('#jstreestatic').jstree(true);
                if (jsTreeInstancestatic) {
                    jsTreeInstancestatic.destroy(); // Destroy the existing tree
                }


                $('#jstree').jstree({
                    'core': {
                        'data': treeData
                    },
                    'plugins': ["checkbox"]
                });

                $('#jstreestatic').jstree({
                    'core': {
                        'data': statictreeData
                },
                    'plugins': [] // No checkboxes
                    });

                // Show the modal
                },
                error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to fetch assignment data. Please try again.',
                });
                }
            });
    }

    $('#assignServiceModal').modal('show');
    fetchMappingData();

    $('#confirmButton').on('click', function() {
        var selectedNodes = $('#jstree').jstree('get_checked'); // Get the IDs of selected nodes
        var selectedIDs = selectedNodes
            .map(function(id) { return parseInt(id, 10); })
            .filter(function(id) { return !isNaN(id); });

        // Fetch previously assigned IDs from the server or another source
            $.ajax({
            url: '{{ route("getPreviouslyAssignedIDs") }}',
            type: 'GET',
                data: {
                    userID: userID,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {

                console.log(response);

                var previouslyAssignedIDs = response.assignedServiceIDs; // Make sure this matches the response structure

                // Determine which services need to be added and which need to be removed
                var toAdd = selectedIDs.filter(id => !previouslyAssignedIDs.includes(id));
                var toRemove = previouslyAssignedIDs.filter(id => !selectedIDs.includes(id));

                // Optionally, ensure that previously assigned services are reactivated if they are unchecked
                var toReactivate = previouslyAssignedIDs.filter(id => selectedIDs.includes(id) && id in previouslyAssignedIDs);

                    $.ajax({
                    url: '{{ route("updateMapping") }}',
                        type: 'POST',
                        data: {
                            userID: userID,
                        add: toAdd,
                        remove: toRemove,
                        reactivate: toReactivate,
                            _token: "{{ csrf_token() }}"
                        },
                    success: function(response) {
                        if (response.data === 'success') {
                        new PNotify({
                            title: 'Success',
                            text: 'Product Updated Successfully',
                            type: 'success',
                            delay: 1000
                        });
                            fetchMappingData();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to update services. Please try again.',
                    });
                    }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while updating services. Please try again.',
                });
                    }
                });
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to fetch previously assigned services. Please try again.',
                });
            }
        });
    });



}



    $(document).on('click', '#addUsers', function() {
        $('#add_reporting_to').hide();
    });


    $(document).on('change', '#user_type_id', function() {
        let role = $(this).val();
        let reporting_users;
        if(role == 1 ) {
            $('#add_reporting_to').hide();
        } else if (role == 3 ) {
            $('#add_reporting_to').show();
            reporting_users = 'getAVps';
        } else if (role == 5) {
            $('#add_reporting_to').show();
            reporting_users = 'getBussinessHeads';
        } else if (role == 9) {
            $('#add_reporting_to').show();
            reporting_users = 'getPM_TL';
        }
        else if (role ==  6 || role == 7 || role == 8 || role == 10 ||role == 11 || role == 22  ) {
            $('#add_reporting_to').show();
            reporting_users = 'getSOPC';
        }else if (role == 2 ) {
            $('#add_reporting_to').show();
            reporting_users = 'getVps';
        }else if (role == 24 ) {
            $('#add_reporting_to').hide();
           
        }else if (role == 23) {
            $('#add_reporting_to').hide();
        }

            $.ajax({
                url: "{{ route('getUserList') }}",
                type: "POST",
                data: {
                    reviewer_type: reporting_users,
                    _token: '{{ csrf_token() }}'
                },
                success: function(html) {
                    $('#reporting_to').html(html);
                },
                error: function(error) {
                    // Handle error if needed
                }
            });

    });

    $(document).on('change', '#user_type_id_ed', function() {
        let role = $(this).val();
        let reporting_users;
        if(role == 1 ) {
            $('#edit_reporting_to').hide();
        } else if (role == 3) {
            $('#edit_reporting_to').show();
            reporting_users = 'getAVps';
        } else if (role == 5) {
            $('#edit_reporting_to').show();
            reporting_users = 'getBussinessHeads';
        } else if (role == 9) {
            $('#edit_reporting_to').show(); 
            reporting_users = 'getPM_TL';
        }else if (role == 2 ) {
            $('#edit_reporting_to').show();
            reporting_users = 'getVps';
        }else if (role ==  6 || role == 7 || role == 8 || role == 10 ||role == 11 || role == 22  ) {
            $('#edit_reporting_to').show();
            reporting_users = 'getSOPC';
        }else if (role == 24 ) {
            $('#edit_reporting_to').hide();
        }else if (role == 23 ) {
            $('#edit_reporting_to').hide();
        }

            $.ajax({
                url: "{{ route('getUserList') }}",
                type: "POST",
                data: {
                    reviewer_type: reporting_users,
                    _token: '{{ csrf_token() }}'
                },
                success: function(html) {
                    $('#reporting_to_ed').html(html);
                },
                error: function(error) {
                    // Handle error if needed
                }
            });

    });


    $(document).ready(function() {
        $('.content-loaded').show();
        $('.frame').addClass('d-none');
    });


    $('#import').on('submit', function(event){
            event.preventDefault();
            $('.content-loaded').hide();
            $('.frame').removeClass('d-none');
            if($('#import').parsley().isValid()){
                $.ajax({
                    type: "POST",
                    url: "{{ route('import') }}",
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        $('.content-loaded').show();
                         $('.frame').addClass('d-none');
                        Swal.fire({
                            text: "File Uploaded Successfully",
                            icon: "success",
                            confirmButtonText: "OK"
                        }).then((result) => {
                            if (result.value) {
                                location.reload();
                            }
                        });

                    },
                    error: function (response) {
                        $('.content-loaded').show();
                         $('.frame').addClass('d-none');
                        Swal.fire({
                            text: "File Upload Failed",
                            icon: "error",
                            confirmButtonText: "OK"
                        });
                    }
                });
            }
        });
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/themes/default/style.min.css" />
    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- jsTree JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/jstree.min.js"></script>
@endsection

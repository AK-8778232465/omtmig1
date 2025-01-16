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
                                <th>Created Date</th>
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
                                <td>{{ $users->created_date}}</td>
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
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" id="usersForm" name="usersForm" data-parsley-validate>
    @csrf
    <input name="user_id" value="" id="ed_user_id" type="hidden" />
    <div class="modal-body">
        <div class="row">
            <div class="col-lg-3">
                <label for="emp_id" class="col-form-label">Emp ID <span class="text-danger"> * </span></label>
                <input required name="emp_id" value="" id="emp_id" type="text" class="form-control" placeholder="Enter Emp ID" />
            </div>
            <div class="col-lg-3">
                <label for="username" class="col-form-label">User Name</label>
                <input autocomplete="off" value="" name="username" id="username" type="text" class="form-control" placeholder="Enter User Name" />
            </div>
            <div class="col-lg-3">
                <label for="email" class="col-form-label">Email <span class="text-danger"> * </span></label>
                <input autocomplete="off" required value="" name="email" id="email" type="email" class="form-control" placeholder="Enter Email" data-parsley-type="email" data-parsley-trigger="keyup" />
            </div>
            <div class="col-lg-3">
                <label for="password" class="col-form-label">Password</label>
                <input id="password" name="password" type="text" class="form-control" autocomplete="off" placeholder="Enter Password" data-parsley-minlength="8" data-parsley-uppercase="1" data-parsley-lowercase="1" data-parsley-number="1" data-parsley-special="1" />
                <span class="errorspannewpassinput1"></span>
            </div>
        </div>
        <div class="row " id="user_div">
            <div class="col-lg-3 row ml-1  align-items-center d-flex justify-space-between " >
                <div class="" >
                    <input type="checkbox" class="mx-1" value="1" id="is_active" name="is_active" />
                    <label for="is_active">Is Active?</label>
                </div>
                <div class=" p-2">
                    <input type="checkbox" class="mx-1" value="1" id="can_assign_order" name="can_assign_order" />
                    <label for="can_assign_order">Can Assign Order?</label>
                </div>
            </div>
            <div class="col-lg-3 ml-1  align-items-center d-flex" >
                <div class="">
                    <input type="checkbox" class="mx-1" value="1" id="is_multirole" name="is_multirole" />
                    <label for="can_assign_role">Assign Multiple Role</label>
                </div>
            </div>
            <div class="col-lg-3" id="user_role_div">
                <label for="example-email-input" class="col-form-label"> User Role <span class="text-danger"> * </span></label>
                <select  class="form-control select_role" name="user_type_id" id="user_type_id">
                    <option selected disabled value="">Select Role</option>
                    @forelse($userTypes as $userType)
                        <option value="{{$userType->id}}"> {{$userType->usertype}}</option>
                    @empty
                    @endforelse
                </select>
            </div>
            <div class="col-lg-3" id="add_reporting_to">
                <label for="example-contact-input" class="col-form-label">Reporting To <span class="text-danger"> * </span></label>
                <Select class="form-control select_role" name="reportingto" id="reportingto">
                </Select>
            </div>
        </div>
        <div class="row " id="multipleRole" style="display: none;">
            <div id="existing_subcategories">
                <input type="hidden" name="subcat_id[]" value="" id="">
                <div class=" mt-1 " id="subcategory_row_0">
                    <div class="d-flex ">
                        <div class="col-lg-3">
                            <label class="mt-2">Client</label>
                            <select name="client_name[]" id="client_0" class="form-control client-select" data-index="0">
                                <option value="" selected disabled>Select a Client</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->client_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="mt-2">LOB & Process</label>
                            <select id="lob_process_0" name="lob_process[]" class="form-control lob-process" data-index="0" >
                                <option value="">Select LOB & Process</option>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="mt-2">User Role</label>
                            <select name="userRole[]" id="user_role_0" class="form-control userRole" data-index="0">
                                <option value="" selected disabled>Select a User Role</option>
                                @foreach ($userTypes as $usertype)
                                    <option value="{{ $usertype->id }}">{{ $usertype->usertype }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3 row" >  
                            <div class="col-lg-11 ">
                                <label class="mt-2">Reporting to</label>                                  
                                <select class="form-control reporting-to" name="reporting_to[]" id="reporting_to_0">
                                    <option value="">Select User Role first</option>
                                </select>
                            </div>                          
                            <div class="d-flex justify-content-center align-items-center mt-4 col-lg-1" >
                                <button type="button" class="btn btn-success btn-sm text-white add-row">+</button>
                            </div>

                        </div>
                    </div>
                    <div class=" d-flex justify-item-center align-items-center mt-4">
                    </div>                
                </div>
            </div>
        </div>
            <div id="new_subcategory" ></div>
  
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-danger" id="close_modals" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary" onclick="usersVal(event);">Create</button>
    </div>
</form>
        </div>
    </div>
  </div>
    <div class="modal fade" id="userEditModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-xl" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Edit user</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form method="post" id="updateuserfrm" name="updateuserfrm" data-parsley-validate>
                    @csrf
                    <input name="user_id" value="" id="user_id_ed" type="hidden" />
                    <div class="modal-body">
                        <!-- Basic User Info -->
                        <div class="row">
                            <div class="col-lg-3">
                                <label for="emp_id_ed" class="col-form-label">Emp ID <span class="text-danger">*</span></label>
                                <input required name="emp_id" value="" id="emp_id_ed" type="text" class="form-control" placeholder="Enter Emp ID" />
                            </div>
                            <div class="col-lg-3">
                                <label for="username_ed" class="col-form-label">User Name</label>
                                <input autocomplete="off" value="" name="username" id="username_ed" type="text" class="form-control" placeholder="Enter User Name" />
                            </div>
                            <div class="col-lg-3">
                                <label for="email_ed" class="col-form-label">Email <span class="text-danger">*</span></label>
                                <input autocomplete="off" required value="" name="email" id="email_ed" type="email" class="form-control" placeholder="Enter Email" />
                            </div>
                            <div class="col-lg-3">
                                <label for="password_ed" class="col-form-label">Password</label>
                                <input id="password_ed" name="password" type="password" class="form-control" autocomplete="off" placeholder="Enter Password" />
                            </div>
                        </div>
                        <div class="row mt-1">
                            <div class="col-lg-3 row ml-1  align-items-center d-flex justify-space-between " >
                                <div class="mt-3">
                                    <input type="checkbox" class="mx-1" value="1" id="is_active_ed" name="is_active_ed" />
                                    <label for="is_active">Is Active?</label>
                                </div>
                                <div class="mt-3">
                                    <input type="checkbox" class="mx-1" value="1" id="can_assign_order" name="can_assign_order" />
                                    <label for="can_assign_order">Can Assign Order?</label>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="mt-3">
                                    <input type="checkbox" class="mx-1" value="1" id="is_edit_multierole" name="is_edit_multierole" />
                                    <label for="can_assign_order"> Assign Multiple Role?</label>
                                </div>
                            </div>
                            <div class="col-lg-3 " id="user_role_div_ed">
								<label for="example-email-input" class="col-form-label"> User Role <span class="text-danger"> * </span></label>
                                <Select  class="form-control select_role" name="user_type_id" id="user_type_id_ed">
                                    <option selected  value="">Select Role</option>
                                        @forelse($userTypes as $userType)
                                        <option value="{{$userType->id}}"> {{$userType->usertype}}</option>
                                        @empty
                                    @endforelse
                                </Select>
							</div>
                            <div class="col-lg-3 " id="edit_reporting_to">
                                <label for="example-email-input" class="col-form-label"> Reporting To <span class="text-danger"> * </span></label>
                                <Select class="form-control select_role" name="reporting_to" id="reporting_to_ed"> </Select>
                            </div>

                        </div>
                        
                        <div id="edit_existing_subcategories"  style="display: none;">
                            
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" id="close_modals" data-dismiss="modal">Close</button>
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
        function usersVal(event) {
        event.preventDefault(); // Prevent the default form submission
    
        // Validate the form using Parsley.js
        if ($("#usersForm").parsley().validate()) {
            // If the form is valid, proceed with the AJAX request
            $.ajax({
                type: "POST",
                cache: false,
                async: false,
                url: "{{ url('/usersInsert') }}",
                data: new FormData($("#usersForm")[0]),
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.msg === "User Already Exists!") {
                        new PNotify({
                            title: 'Error',
                            text: response.msg,
                            type: 'error'
                        });
                    } else if (response.msg === "Only Image Allowed!") {
                        new PNotify({
                            title: 'Error',
                            text: response.msg,
                            type: 'error'
                        });
                    } else if (response.msg === "User Added Successfully!") {
                        new PNotify({
                            title: 'Success',
                            text: response.msg,
                            type: 'success'
                        });
                        setTimeout(function () {
                            location.reload();
                        }, 1000);
                    }
                },
                error: function (response) {
                    var err = "";
                    $.each(response.responseJSON.errors, function (field_name, error) {
                        err += error + "<br>";
                    });
                    new PNotify({
                        title: 'Error',
                        text: err,
                        type: 'error',
                        delay: 1000
                    });
                }
            });
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
                    $('#reportingto').html(html);
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

<!-- <script>
  // Cache for storing reporting users by role
  var reportingCache = {};

  // Listen for role change using event delegation
  $('#new_subcategory').on('change', '.userRole', function() {
    var $this = $(this);  // Reference to the userRole dropdown in the current row
    var index = $this.data('index');  // Get the index of the current row
    var role = $this.val();  // Get the selected value from the current row's userRole dropdown
    var reporting_users = null;

    // Clear the reporting_to dropdown before populating
    $(`#reporting_to_${index}`).html('<option value="" disabled selected>Select a Reporting User</option>');

    if (role == 1) {
        // No reporting users for this role, just clear the field
        return;
    } else if (role == 3) {
        reporting_users = 'getAVps';
    } else if (role == 5) {
        reporting_users = 'getBussinessHeads';
    } else if (role == 9) {
        reporting_users = 'getPM_TL';
    } else if (role == 6 || role == 7 || role == 8 || role == 10 || role == 11 || role == 22) {
        reporting_users = 'getSOPC';
    } else if (role == 2) {
        reporting_users = 'getVps';
    }

    // Check if data for this role is already cached
    if (reporting_users && reportingCache[reporting_users]) {
        // Use cached reporting users
        $(`#reporting_to_${index}`).html(reportingCache[reporting_users]);
    } else if (reporting_users) {
        // Fetch reporting users for the subcategory if not cached
        $.ajax({
            url: "{{ route('getUserList') }}",
            type: "POST",
            data: {
                reviewer_type: reporting_users,
                _token: '{{ csrf_token() }}'
            },
            success: function(html) {
                // Cache the fetched reporting users for future use
                reportingCache[reporting_users] = html;
                $(`#reporting_to_${index}`).html(html); // Populate the reporting_to dropdown for the current row
            },
            error: function(error) {
                console.log(error); // Handle error if needed
            }
        });
    }
  });


  // Cache for storing reporting users by role
  var reportingCache = {};

  // Listen for role change using event delegation
  $('#new_subcategory').on('change', '.userRole', function() {
    var $this = $(this);  // Reference to the userRole dropdown in the current row
    var index = $this.data('index');  // Get the index of the current row
    var role = $this.val();  // Get the selected value from the current row's userRole dropdown
    var reporting_users = null;

    // Clear the reporting_to dropdown before populating
    $(`#reporting_to_${index}`).html('<option value="" disabled selected>Select a Reporting User</option>');

    if (role == 1) {
        // No reporting users for this role, just clear the field
        return;
    } else if (role == 3) {
        reporting_users = 'getAVps';
    } else if (role == 5) {
        reporting_users = 'getBussinessHeads';
    } else if (role == 9) {
        reporting_users = 'getPM_TL';
    } else if (role == 6 || role == 7 || role == 8 || role == 10 || role == 11 || role == 22) {
        reporting_users = 'getSOPC';
    } else if (role == 2) {
        reporting_users = 'getVps';
    }

    // Check if data for this role is already cached
    if (reporting_users && reportingCache[reporting_users]) {
        // Use cached reporting users
        $(`#reporting_to_${index}`).html(reportingCache[reporting_users]);
    } else if (reporting_users) {
        // Fetch reporting users for the subcategory if not cached
        $.ajax({
            url: "{{ route('getUserList') }}",
            type: "POST",
            data: {
                reviewer_type: reporting_users,
                _token: '{{ csrf_token() }}'
            },
            success: function(html) {
                // Cache the fetched reporting users for future use
                reportingCache[reporting_users] = html;
                $(`#reporting_to_${index}`).html(html); // Populate the reporting_to dropdown for the current row
            },
            error: function(error) {
                console.log(error); // Handle error if needed
            }
        });
    }
  });

  function addnewsubcategory() {
    var clients = @json($clients);
    var userTypes = @json($userTypes);

    // Calculate the next index based on the number of rows in the container
    var index = $('#new_subcategory .row').length;

    // Generate options for Clients and User Roles
    var clientOptions = '<option value="" disabled selected>Select a Client</option>';
    clients.forEach(client => {
        clientOptions += `<option value="${client.id}">${client.client_name}</option>`;
    });

    var userRoleOptions = '<option value="" disabled selected>Select a User Role</option>';
    userTypes.forEach(userType => {
        userRoleOptions += `<option value="${userType.id}">${userType.usertype}</option>`;
    });

    // Generate the Reporting To dropdown for the subcategory (initially empty)
    var reportingToOptions = '<option value="" disabled selected>Select User Role first</option>';

    // Append new subcategory to the container
    $('#new_subcategory').append(`
        <div class="row mt-1" id="addedsubcategory_${index}">
            <div class="col-lg-3">
                <label class="mt-2">Client:</label>
                <select class="form-control" name="client_name[]" id="subcategoryname_${index}" required>
                    ${clientOptions}
                </select>
            </div>
            <div class="col-lg-3">
                <label class="mt-2">LOB & Process:</label>
               <select id="lob-process" name="lob-process" class="form-control">
                            <option value="">Select LOB & Process</option>
                </select>
            </div>
            <div class="col-lg-3">
                <label class="mt-2">User Role:</label>
                <select class="form-control userRole" name="userRole[]" id="subcategorydesc_${index}" data-index="${index}">
                    ${userRoleOptions}
                </select>
            </div>
            <div class="col-lg-3">
                <div class="row">
                    <label class="mt-2">Reporting to:</label>
                    <div class="col-lg-12 d-flex justify-content-between">
                        <select class="form-control select_role" name="reporting_to[]" id="reporting_to_${index}">
                            ${reportingToOptions}
                        </select>
                        <div class="d-flex">
                            <button type="button" class="btn btn-success btn-sm text-white pb-0 pt-0 m-1 pr-2 pl-2 addnewsubcategory"
                                    onclick="addnewsubcategory()">+</button>
                            <button type="button" onclick="removesubcategory(${index})" class="btn btn-danger m-1 ml-0 btn-sm pb-0 pt-0 pr-2 pl-2" id="delsubcategory_${index}">-</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `);
  }
  
  function removesubcategory(index) {
      $('#addedsubcategory_' + index).remove();
  }
</script> -->

<script>
    $(document).ready(function () {
        $('#client').on('change', function () {
            const clientId = $(this).val();
            if (clientId) {
                $.ajax({
                    url: "{{ route('get-lob-process') }}",
                    method: 'POST',
                    data: {
                        client_id: clientId,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        $('#lob-process').html('<option value="">Select LOB & Process</option>'); // Reset LOB & Process dropdown
                        console.log(response); // Log the response to check its structure

                        // Make sure response is an array
                        if (Array.isArray(response)) {
                            response.forEach(function (lob) { // Correctly iterate over each lob object
                                // Check if processes are available in this lob object
                                if (lob.processes && Array.isArray(lob.processes)) {
                                    lob.processes.forEach(function (process) {
                                        // Add options in the format `${lob.name} (${process.name})`
                                        $('#lob-process').append(`<option value="${process.id}">${lob.name} (${process.name})</option>`);
                                    });
                                }
                            });

                            // Enable the LOB & Process dropdown after adding options
                            $('#lob-process').prop('disabled', false);
                        } else {
                            console.error('Unexpected response format:', response);
                            $('#lob-process').prop('disabled', true);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX error:', error);
                        $('#lob-process').prop('disabled', true);
                    }
                });
            } else {
                // Reset the dropdown if no client is selected
                $('#lob-process').html('<option value="">Select LOB & Process</option>').prop('disabled', true);
            }
        });
    });
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/themes/default/style.min.css" />
    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- jsTree JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/jstree.min.js"></script>

    <script>
    // Cache for storing reporting users by role
var reportingCache = {};

// Listen for role change using event delegation
// Use a common parent container to handle both existing and dynamically added rows
$('#existing_subcategories, #new_subcategory').on('change', '.userRole', function () {
    var $this = $(this); // Reference to the userRole dropdown in the current row
    var index = $this.data('index'); // Get the index of the current row
    var role = $this.val(); // Get the selected value from the current row's userRole dropdown
    var reporting_users = null;

    // Clear the reporting_to dropdown before populating
    $(`#reporting_to_${index}`).html('<option value="" disabled selected>Select a Reporting User</option>');

    // Determine the type of reporting users based on the selected role
    if (role == 1) {
        // No reporting users for this role, just clear the field
        return;
    } else if (role == 3) {
        reporting_users = 'getAVps';
    } else if (role == 5) {
        reporting_users = 'getBussinessHeads';
    } else if (role == 9) {
        reporting_users = 'getPM_TL';
    } else if (role == 6 || role == 7 || role == 8 || role == 10 || role == 11 || role == 22) {
        reporting_users = 'getSOPC';
    } else if (role == 2) {
        reporting_users = 'getVps';
    }

    // Check if data for this role is already cached
    if (reporting_users && reportingCache[reporting_users]) {
        // Use cached reporting users
        $(`#reporting_to_${index}`).html(reportingCache[reporting_users]);
    } else if (reporting_users) {
        // Fetch reporting users for the subcategory if not cached
        $.ajax({
            url: "{{ route('getUserList') }}",
            type: "POST",
            data: {
                reviewer_type: reporting_users,
                _token: '{{ csrf_token() }}'
            },
            success: function (html) {
                // Cache the fetched reporting users for future use
                reportingCache[reporting_users] = html;
                $(`#reporting_to_${index}`).html(html); // Populate the reporting_to dropdown for the current row
            },
            error: function (error) {
                console.error("Error fetching reporting users:", error); // Handle error if needed
            }
        });
    }
});

// Function to add a new subcategory row
function addNewSubcategory() {
    var clients = @json($clients);
    var userTypes = @json($userTypes);

    // Calculate the next index based on the number of rows in the container
    var index = $('#existing_subcategories .row').length + $('#new_subcategory .row').length;

    // Generate options for Clients and User Roles
    var clientOptions = '<option value="" disabled selected>Select a Client</option>';
    clients.forEach(client => {
        clientOptions += `<option value="${client.id}">${client.client_name}</option>`;
    });

    var userRoleOptions = '<option value="" disabled selected>Select a User Role</option>';
    userTypes.forEach(userType => {
        userRoleOptions += `<option value="${userType.id}">${userType.usertype}</option>`;
    });

    // Append a new subcategory row to the container
    $('#new_subcategory').append(`
    <div class=" mt-1" id="subcategory_row_${index}">
        <div class=" row">
            <div class="col-lg-3">
                <label class="mt-2">Client:</label>
                <select class="form-control client-select" name="client_name[]" id="client_${index}" data-index="${index}" >
                    ${clientOptions}
                </select>
            </div>
            <div class="col-lg-3">
                <label class="mt-2">LOB & Process:</label>
                <select id="lob_process_${index}" name="lob_process[]" class="form-control lob-process" data-index="${index}" disabled>
                    <option value="">Select LOB & Process</option>
                </select>
            </div>
            <div class="col-lg-3">
                <label class="mt-2">User Role:</label>
                <select class="form-control userRole" name="userRole[]" id="user_role_${index}" data-index="${index}">
                    ${userRoleOptions}
                </select>
            </div>
            <div class="col-lg-3 d-flex">
                <div class="col-lg-11 ">                
                    <label class="mt-2">Reporting to:</label>                
                    <select class="form-control reporting-to" name="reporting_to[]" id="reporting_to_${index}">
                        <option value="" disabled selected>Select User Role first</option>
                    </select>
                </div>
                <div class=" d-flex justify-content-center align-items-center mt-4 col-lg-1" >
                    <button type="button" class="btn btn-danger btn-sm text-white remove-row " data-index="${index}">-</button>
                </div>
            </div>
        </div>
       
    </div>
    `);
}



    // Function to remove a subcategory row
    function removeSubcategory(index) {
        $('#subcategory_row_' + index).remove();
    }

    $(document).ready(function () {
        // Handle client change to populate LOB & Process dropdown
        $(document).on('change', '.client-select', function () {
            const clientId = $(this).val();
            const index = $(this).data('index');
            const lobProcessDropdown = $(`#lob_process_${index}`);

            if (clientId) {
                $.ajax({
                    url: "{{ route('get-lob-process') }}",
                    method: 'POST',
                    data: {
                        client_id: clientId,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        lobProcessDropdown.html('<option value="">Select LOB & Process</option>');
                        response.forEach(function (lob) {
                            lob.processes.forEach(function (process) {
                                lobProcessDropdown.append(`<option value="${lob.id},${process.id}">${lob.name} (${process.name})</option>`);
                            });
                        });
                        lobProcessDropdown.prop('disabled', false);
                    }
                });
            } else {
                lobProcessDropdown.html('<option value="">Select LOB & Process</option>').prop('disabled', true);
            }
        });

        // Handle "+" button click to add a new row
        $(document).on('click', '.add-row', function () {
            addNewSubcategory();
        });

        // Handle "-" button click to remove a row
        $(document).on('click', '.remove-row', function () {
            const index = $(this).data('index');
            removeSubcategory(index);
        });
    });

// EDIT

$(document).ready(function () {
    // When the Edit button is clicked
    $('#datatable').on('click', '.edit_user', function () {
        var id = $(this).data('id');  // Get the user ID
        // console.log(id);
        var url = '{{ route("edit_user") }}';  // Route to fetch user data
        $.ajax({
            type: "POST",
            url: url,
            data: { id: id, _token: '{{csrf_token()}}' },
            success: function(response) {
                var res = response;  // Response from the backend

                // Pre-fill the fields in the modal
                $("#user_id_ed").val(res['id']);
                $("#username_ed").val(res['username']);
                $("#emp_id_ed").val(res['emp_id']);
                $("#email_ed").val(res['email']);
                $("#contact_no_ed").val(res['contact_no']);
                $("#password_ed").val(res['password']);
                $("#user_type_id_ed").val(res['user_type_id']);

                // Handle the "is_active" checkbox
                if (res['is_active'] == 1) {
                    $("#is_active_ed").prop('checked', true);
                } else {
                    $("#is_active_ed").prop('checked', false);
                }

                if (res['is_multirole'] == 1) {
                    $("#is_edit_multierole").prop('checked', true);
                    var userRoleDiv = document.getElementById('user_role_div_ed');
                    var userRoleMulti = document.getElementById('edit_existing_subcategories');
                    // var userDiv = document.getElementById('user_div');
                    var roleDiv = document.getElementById('edit_reporting_to');
                    userRoleDiv.style.display = 'none';  
                    userRoleMulti.style.display = 'block';  
                    roleDiv.style.display = 'none';  
                } else {
                    $("#is_edit_multierole").prop('checked', false);
                }

                // Handle the profiles and dynamically add them to the modal
                populateEditSubcategories(res.profile);

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
                    reporting_users = 'getVps';
                    console.log(reporting_users);
                }else if (role == 24 ) {
                    $('#edit_reporting_to').show();
                    // reporting_users = 'getAdmin';
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
                        console.log(reporting_to)
                    },
                    error: function(error) {
                        // Handle error if needed
                    }
                });


                // Show the modal
                $("#userEditModal").modal('show');
            }
        });
    });

    $(document).on('change', '.edit_client', function () {
            const clientId = $(this).val();
            const index = $(this).data('index');
            const lobProcessDropdown = $(`#edit_lob_process_${index}`);

            if (clientId) {
                $.ajax({
                    url: "{{ route('get-lob-process') }}",
                    method: 'POST',
                    data: {
                        client_id: clientId,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        lobProcessDropdown.html('<option value="">Select LOB & Process</option>');
                        response.forEach(function (lob) {
                            lob.processes.forEach(function (process) {
                                lobProcessDropdown.append(`<option value="${lob.id},${process.id}">${lob.name} (${process.name})</option>`);
                            });
                        });
                        lobProcessDropdown.prop('disabled', false);
                    }
                });
            } else {
                lobProcessDropdown.html('<option value="">Select LOB & Process</option>').prop('disabled', true);
            }
        });


        var ediReporting = {};

// Listen for role change using event delegation
// Use a common parent container to handle both existing and dynamically added rows
$('#edit_existing_subcategories').on('change', '.edit_user_role', function () {
    var $this = $(this); // Reference to the userRole dropdown in the current row
    var index = $this.data('index'); // Get the index of the current row
    var role = $this.val(); // Get the selected value from the current row's userRole dropdown
    var reporting_users = null;

   // Clear the reporting_to dropdown before populating
    $(`#edit_reporting_to_${index}`).html('<option value="" disabled selected>Select a Reporting User</option>');

    // Determine the type of reporting users based on the selected role
    if (role == 1) {
        // No reporting users for this role, just clear the field
        return;
    } else if (role == 3) {
        reporting_users = 'getAVps';
    } else if (role == 5) {
        reporting_users = 'getBussinessHeads';
    } else if (role == 9) {
        reporting_users = 'getPM_TL';
    } else if (role == 6 || role == 7 || role == 8 || role == 10 || role == 11 || role == 22) {
        reporting_users = 'getSOPC';
    } else if (role == 2) {
        reporting_users = 'getVps';
    }

    // Check if data for this role is already cached
    if (reporting_users && ediReporting[reporting_users]) {
        // Use cached reporting users
        $(`#edit_reporting_to_${index}`).html(ediReporting[reporting_users]);
    } else if (reporting_users) {
        // Fetch reporting users for the subcategory if not cached
        $.ajax({
            url: "{{ route('getUserList') }}",
            type: "POST",
            data: {
                reviewer_type: reporting_users,
                _token: '{{ csrf_token() }}'
            },
            success: function (html) {
                // Cache the fetched reporting users for future use
                ediReporting[reporting_users] = html;
                $(`#edit_reporting_to_${index}`).html(html); // Populate the reporting_to dropdown for the current row
            },
            error: function (error) {
                console.error("Error fetching reporting users:", error); // Handle error if needed
            }
        });
    }
});

function populateEditSubcategories(profiles) {
    const clients = @json($clients);  // List of clients from your server
    const userTypes = @json($userTypes);  // List of user types from your server
    const reportingUsers = @json($reportingUsers);  // Reporting users (if available)
    const lobs = @json($lobs);
    const processes = @json($processes);

    $('#edit_existing_subcategories').html('');  // Clear existing subcategories

    // If profiles is null or empty, add a single empty row
    if (!profiles || profiles.length === 0) {
        addNewSubcategoryRow();
    } else {
        profiles.forEach((profile, index) => {
            // Generate client options
            let clientOptions = '<option value="" disabled>Select a Client</option>';
            clients.forEach(client => {
                clientOptions += `<option value="${client.id}" ${client.id === profile.client_id ? 'selected' : ''}>${client.client_name}</option>`;
            });

            let lopProcessOption = '<option value="" disabled>Select a Client</option>';

            // Assuming you have an array `processes` and `lob` is an array of lobs
            lobs.forEach(lob => {
                processes.forEach(process => {
                    console.log(lob.id, profile.process_id);
                    lopProcessOption += `<option value="${lob.id},${process.id}" 
                        ${lob.id === profile.lob_id && process.id === profile.process_id ? 'selected' : ''}>
                        ${lob.name} (${process.name})
                    </option>`;
                });
            });

            // Generate user role options
            let userRoleOptions = '<option value="" disabled>Select a User Role</option>';
            userTypes.forEach(userType => {
                userRoleOptions += `<option value="${userType.id}" ${userType.id === profile.user_type_id ? 'selected' : ''}>${userType.usertype}</option>`;
            });

            // Generate reporting-to options
            let reportingOptions = '<option value="" disabled selected>Select Reporting User</option>';
            reportingUsers.forEach(reportingUser => {
                reportingOptions += `<option value="${reportingUser.id}" ${reportingUser.id === profile.reporting_to ? 'selected' : ''}>${reportingUser.username}</option>`;
            });

            // Append the profile data to the modal
            $('#edit_existing_subcategories').append(`
            <div class=" mt-1" id="edit_subcategory_row_${index}">
                <div class="row ">
                    <div class="col-lg-3">
                        <label class="mt-2">Client:</label>
                        <select class="form-control edit_client" name="client_name[]" id="edit_client_${index}" data-index="${index}" >
                            ${clientOptions}
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <label class="mt-2">LOB & Process:</label>
                        <select id="edit_lob_process_${index}" name="lob_process[]" class="form-control" data-index="${index}" disabled>
                            ${lopProcessOption}
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <label class="mt-2">User Role:</label>
                        <select class="form-control edit_user_role" name="userRole[]" id="edit_user_role_${index}" data-index="${index}" >
                            ${userRoleOptions}
                        </select>
                    </div>
                    <div class="col-lg-3 row">
                    <div class="col-lg-11">
                        <label class="mt-2">Reporting to:</label>
                        <select class="form-control" name="reporting_to[]" id="edit_reporting_to_${index}" data-index="${index}"disabled>
                            ${reportingOptions}
                        </select>
                    </div>
                    <div class="d-flex justify-content-center align-items-center mt-4 col-lg-1">
                          ${index === 0 ? 
                    `<button id="add_new_row_btn" type="button" class="btn btn-success btn-sm text-white">+</button>` : 
                    `<button type="button" class="btn btn-danger remove-row btn-sm text-white " data-index="${index}">-</button>`
                }
                    </div>
                </div>
            </div>
            `);
        });
    }

    // Function to add a new row
    function addNewSubcategoryRow() {
        const newIndex = $('#edit_existing_subcategories .row').length;

        let clientOptions = '<option value="" >Select a Client</option>';
        clients.forEach(client => {
            clientOptions += `<option value="${client.id}">${client.client_name}</option>`;
        });

        let lopProcessOption = '<option value="" >Select a Client</option>';
        // lobs.forEach(lob => {
        //     processes.forEach(process => {
        //         lopProcessOption += `<option value="${process.id},${lob.id}">${lob.name} (${process.name})</option>`;
        //     });
        // });

        let userRoleOptions = '<option value="">Select a User Role</option>';
        userTypes.forEach(userType => {
            userRoleOptions += `<option value="${userType.id}">${userType.usertype}</option>`;
        });

        let reportingOptions = '<option value="" disabled selected>Select Reporting User</option>';
        reportingUsers.forEach(reportingUser => {
            reportingOptions += `<option value="${reportingUser.id}">${reportingUser.username}</option>`;
        });

        // Append a new empty row with the "+" and "-" buttons
        $('#edit_existing_subcategories').append(`
            <div class=" mt-1" id="edit_subcategory_row_${newIndex}">
            <div class=" row">
                <div class="col-lg-3">
                    <label class="mt-2">Client:</label>
                    <select class="form-control edit_client" name="client_name[]" id="edit_client_${newIndex}" data-index="${newIndex}" >
                        ${clientOptions}
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="mt-2">LOB & Process:</label>
                    <select id="edit_lob_process_${newIndex}" name="lob_process[]" class="form-control" data-index="${newIndex}" >
                        ${lopProcessOption}
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="mt-2">User Role:</label>
                    <select class="form-control edit_user_role" name="userRole[]" id="edit_user_role_${newIndex}" data-index="${newIndex}">
                        ${userRoleOptions}
                    </select>
                </div>
                <div class="col-lg-3 row">
                <div class="col-lg-11">
                    <label class="mt-2">Reporting to:</label>
                    <select class="form-control" name="reporting_to[]" id="edit_reporting_to_${newIndex}" data-index="${newIndex}" >
                        ${reportingOptions}
                    </select>
                </div>
                <div class="d-flex justify-content-center align-items-center mt-4 col-lg-1">
                   
                     ${newIndex === 0 ? 
                    `<button id="add_new_row_btn" type="button" class="btn btn-success btn-sm text-white">+</button>` : 
                    `<button type="button" class="btn btn-danger remove-row btn-sm text-white " data-index="${newIndex}">-</button>`
                }
                </div>
            </div>
            </div>
        `);
        if (newIndex > 0) {
    $(`#edit_subcategory_row_${newIndex}`).find('#add_new_row_btn').hide();
}
        // // Append the add button to the last row after the new row is added
        // $('#edit_existing_subcategories').append(`
        //     <div class="row mt-1">
                
        //     </div>
        // `);
    }

    // Event listener for adding a new row
    $(document).on('click', '#add_new_row_btn', function() {
        addNewSubcategoryRow();
    });

    // Event listener for removing a row
    $(document).on('click', '.remove-row', function() {
        const rowIndex = $(this).data('index');
        $(`#edit_subcategory_row_${rowIndex}`).remove();
    });
}

});

</script>
<script>
    document.getElementById('is_multirole').addEventListener('change', function() {
        var userRoleDiv = document.getElementById('user_role_div');
        var userRoleMulti = document.getElementById('multipleRole');
        var userDiv = document.getElementById('user_div');
        var roleDiv = document.getElementById('new_subcategory');
      
        if (this.checked) {
            userRoleDiv.style.display = 'none';  
            userRoleMulti.style.display = 'block';  
            roleDiv.style.display = 'block';  
            userDiv.classList.add('mt-3', 'mb-3');
        } else {
            userRoleDiv.style.display = 'block';  
            userRoleMulti.style.display = 'none';  
            $('#add_reporting_to').hide(); 
            userDiv.classList.remove('mt-3', 'mb-3');
            roleDiv.style.display = 'none'; 
        }
    });

    document.getElementById('is_edit_multierole').addEventListener('change', function() {
        var userRoleDiv = document.getElementById('user_role_div_ed');
        var userRoleMulti = document.getElementById('edit_existing_subcategories');
        // var userDiv = document.getElementById('user_div');
        var roleDiv = document.getElementById('edit_reporting_to');
      
        if (this.checked) {
            userRoleDiv.style.display = 'none';  
            userRoleMulti.style.display = 'block';  
            roleDiv.style.display = 'none';  
            // userDiv.classList.add('mt-3', 'mb-3');
        } else {
            userRoleDiv.style.display = 'block';  
            userRoleMulti.style.display = 'none';  
            $('#edit_reporting_to').hide(); 
            // userDiv.classList.remove('mt-3', 'mb-3');
            roleDiv.style.display = 'block'; 
        }
    });
    document.getElementById('close_modals').addEventListener('click', function(event) {
  // Prevent the default modal dismissal behavior
  event.preventDefault();
  // Reload the page
  location.reload();
});
</script>
@endsection

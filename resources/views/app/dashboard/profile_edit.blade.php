@extends('layouts.app')
@section('title', config('app.name') . ' | Users')
@section('content')
<style>
    .col-sm-3{
        align-items: middle;
        padding-top: 4px;
    }

    .input-container {
        position: relative;
    }
    .input-container input[type="password"], .input-container input[type="text"] {
        width: 100%;
        padding-right: 10px; /* Make space for the eye icon */
    }
    .input-container .toggle-password {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
    }

</style>
<div class="container-fluid">
    <div class="row justify-content-center"> <!-- Center the content horizontally -->
        <div class="col-md-6">
            <div class="card mt-2">

                <div class="card-body mr-4">
                    <h4 class="ml-1 mb-4">User Details:</h4>
                    <form action="{{ route("profile_Update") }}" method="POST" id="reset_password_form" name="reset_password_form">
                        @csrf
                        <input name="user_id" value="" id="ed_user_id" type="hidden" />
                        <div class="form-group row">
                            <label for="username" class="col-sm-3 text-right">User Name</label>
                            <div class="col-sm-9">
                                <input readonly class="form-control" type="text" id="username" name="username" value="{{ old('username', Auth::user()->username) }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="email" class="col-sm-3 text-right">Email</label>
                            <div class="col-sm-9">
                                <input class="form-control" type="email" id="email" name="email" value="{{ old('email', Auth::user()->email) }}" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="contact_no" class="col-sm-3 text-right">Contact No</label>
                            <div class="col-sm-9">
                                <input readonly class="form-control" type="text" id="contact_no" name="contact_no"
                                       value="{{ old('contact_no', Auth::user()->contact_no ?: 'NA') }}">
                            </div>
                        </div>
                        <hr>
                        <h4 class="ml-1 mb-4">Change Password:</h4>
                        <div class="form-group row">
                            <label for="new_password" class="col-sm-3 text-right">New Password</label>
                           <div class="col-sm-9 input-container">
                                <input class="form-control" type="password" id="new_password" name="new_password" value="">
                                <span class="toggle-password" id="togglePassword1" style="display: none;">
                          <i class="fas fa-eye"></i>
                          </span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="new_password_confirmation" class="col-sm-3 text-right">Confirm Password</label>
                    <div class="col-sm-9 input-container">
                                <input class="form-control" type="password" id="new_password_confirmation" name="new_password_confirmation">
                                <span class="toggle-password" id="togglePassword2" style="display: none;">
                                    <i class="fas fa-eye"></i>
                      </span>
                            </div>
                        </div>
                    <div id="passwordLengthValidation" style="color: red;"></div>
                        <div class="text-center mb-4">
                            <button class="btn btn-sm btn-primary" type="submit" id="reset_password" name="reset_password">Change Password</button>
                            <a href="{{route("home")}}" class="btn btn-sm btn-danger ml-1">Go Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- <div class="container-fluid">
    <div class="row justify-content-center"> <!-- Center the content horizontally -->
        <div class="col-md-6">
            <div class="card mt-2">
                <div class="card-body mr-4">
                    <h4 class="ml-4 mb-4">API Token:</h4>
                    <form action="{{ route("api_token") }}" method="POST" id="api_form" name="api_form">
                        @csrf
                        <input name="user_id" value="" id="ed_user_id" type="hidden" />
                        <div class="form-group row">
                            <div class="col-sm-1">
                            </div>
                            <div class="col-sm-7">
                                @if(isset($token))
                                    <input readonly class="form-control" type="text" id="first_name" name="first_name" placeholder="Generating New Token will make old token expired" value="{{ $token }}">
                                @else
                                    <input readonly class="form-control" type="text" id="first_name" name="first_name" placeholder="Generating New Token will make old token expired">
                                @endif
                            </div>
                            <div class="col-sm-4">
                                <button class="btn btn-sm btn-warning" type="submit" id="get_token" name="get_token">Get New Token</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> --}}

{{-- Js --}}
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

        window.ParsleyValidator
        .addValidator('fileextension', function (value, requirement) {
        var tagslistarr = requirement.split(',');
        var fileExtension = value.split('.').pop();
        var arr=[];
        $.each(tagslistarr,function(i,val){
        arr.push(val);
        });
        if(jQuery.inArray(fileExtension, arr)!='-1') {
        return true;
        } else {
        return false;
        }
    }, 32)
    .addMessage('en', 'fileextension', 'The extension doesn\'t match the required');
    $("form").parsley();
        var table = $('#datatable').DataTable({
            responsive: true,
            dom: 'l<"toolbar">Bfrtip',
            buttons: [
                'excel'
            ],
            initComplete: function(){
                $("div.toolbar").html('<button id="addUsers" type="button" class="ml-2 btn btn-primary" data-toggle="modal" data-target="#myModal"><img class="menuicon" src="{{asset("assets/images/add.svg")}}">&nbsp;Add User</button><br />');
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
                        if(response.msg=="Users Name Already Exists!"){

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
                        }else if(response.msg=="Users Added Successfully!"){

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

//new


document.getElementById('new_password').addEventListener('input', function () {
    var togglePassword1 = document.getElementById('togglePassword1');
    if (this.value.length > 0) {
        togglePassword1.style.display = 'inline';
    } else {
        togglePassword1.style.display = 'none';
    }
});

document.getElementById('new_password_confirmation').addEventListener('input', function () {
    var togglePassword2 = document.getElementById('togglePassword2');
    if (this.value.length > 0) {
        togglePassword2.style.display = 'inline';
    } else {
        togglePassword2.style.display = 'none';
    }
});






document.getElementById('togglePassword1').addEventListener('click', function () {
    var passwordField = document.getElementById('new_password');
    var passwordFieldType = passwordField.getAttribute('type');
    if (passwordFieldType === 'password') {

        passwordField.setAttribute('type', 'text');

        this.innerHTML = '<i class="fas fa-eye-slash"></i>';
    } else {

        passwordField.setAttribute('type', 'password');

        this.innerHTML = '<i class="fas fa-eye"></i>';
    }
});


document.getElementById('togglePassword2').addEventListener('click', function () {
    var passwordField = document.getElementById('new_password_confirmation');
    var passwordFieldType = passwordField.getAttribute('type');
    if (passwordFieldType === 'password') {

        passwordField.setAttribute('type', 'text');

        this.innerHTML = '<i class="fas fa-eye-slash"></i>';
    } else {

        passwordField.setAttribute('type', 'password');

        this.innerHTML = '<i class="fas fa-eye"></i>';
    }
});

function checkPasswordsMatch(event) {
    var newPassword = document.getElementById('new_password').value;
    var confirmPassword = document.getElementById('new_password_confirmation').value;

    if (newPassword.trim() === '' || confirmPassword.trim() === '') {
        alert('Password cannot be empty.');
        event.preventDefault();
        return;
    }

    if (newPassword.length < 8 || confirmPassword.length < 8) {
        alert('Password must be at least 8 characters long.');
        event.preventDefault();
        return;
    }

    if (newPassword !== confirmPassword) {
        alert('Passwords do not match!');
        event.preventDefault();
        return;
    }
}

document.getElementById('reset_password').addEventListener('click', checkPasswordsMatch);

///

$(document).ready(function() {
        $('#reset_password_form').submit(function(e) {
            e.preventDefault();
            var form = $(this);
            $.ajax({
                type: form.attr('method'),
                url: form.attr('action'),
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.success,
                        showConfirmButton: false,
                        timer: 2000
                    }).then(function() {
                        location.reload(); // Reload the page or redirect as per your requirement
                    });
                },
                error: function(xhr) {
                    // Handle errors if any
                }
            });
        });
    });
///


    $('#updateuserfrm').on('submit', function(event){
		event.preventDefault();
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
				$("#first_name_ed").val(res['first_name']);
				$("#last_name_ed").val(res['last_name']);
				$("#email_ed").val(res['email']);
                $("#contact_no_ed").val(res['contact_no']);
                $("#password_ed").val(res['password']);
				$("#user_type_id_ed").val(res['user_type_id']);

				if(res['is_active'] == 1) {
					$( "#is_active_ed" ).attr('checked', 'checked');
				} else {
					$( "#is_active_ed" ).removeAttr('checked', 'checked');
				}
				$("#userEditModal").modal('show');
			}
		});
	});

</script>
@endsection

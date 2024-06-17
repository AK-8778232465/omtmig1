@extends('layouts.app')
@section('title', 'Stellar OMS | Products')
@section('content')

<div class="container-fluid mt-2">
    @include('app.settings.index')
    <div class="col-lg-12">
        <div class="card ">
            <div class="card-body">
                <div class="p-0">
                    <table id="datatable" class="table table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>Sl No</th>
                                <th>Client Name</th>
                                <th>Lob</th>
                                <th>Product Name</th>
                                <th>Comments</th>
                                <th>Is Active</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $product->client->client_name }}</td>
                                    <td>{{ $product->lob->name }}</td>
                                    <td>{{ $product->product_name }}</td>
                                    <td>{{ $product->comments}}</td>
                                    <td class="text-center">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox"  class="custom-control-input" id="customSwitch{{ $product->id }}"  value="{{ $product->id }}" onclick="productStatus(this.value)" @if($product->is_active==1) checked @endif>
                                            <label class="custom-control-label" for="customSwitch{{ $product->id }}">@if($product->is_active==1) Active @else Inactive @endif</label>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <a href="#"><span class="edit_product ml-2"  onclick="editproduct(this.value)" data-id="{{ $product->id }}">
                                            <img class="menuicon tbl_editbtn" src="{{asset('assets/images/edit.svg')}}" >&nbsp;
                                        </span></a>
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
{{-- Addingservice --}}
<div class="modal fade" id="myModal">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
      <form id="productForm" name="productForm" method="post" role="form" enctype="multipart/form-data">
        @csrf
        <!-- Modal Header -->
            <div class="modal-header">
            <h5 class="modal-title">Add Product</h5>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

        <!-- Modal body -->
        <div class="modal-body">
          <div class="row">
            <div class="col-lg-4">
                <label for="name-input" class="col-form-label">Client Name<span class="text-danger"> * <span></label>
                <select name="client_id" id="client_id" class="form-control">
                    <option value="">Select Client</option>
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->client_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-4">
                <label for="name-input" class="col-form-label">Lob<span class="text-danger"> * <span></label>
                <select name="lob_id" id="lob_id" class="form-control">
                    <option value="">Select Lob</option>
                    @foreach($lobData as $lob)
                    <option value="{{ $lob->id }}">{{ $lob->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-4">
                <label for="name-input" class="col-form-label">Product Name<span class="text-danger"> * <span></label>
                <input id="product_name" name="product_name" type="text" class="form-control" autocomplete="off" placeholder="Enter Product Name">
            </div>
          </div>
            <div class="row mt-1">
                <div class="col-lg-6">
                    <label for="name-input" class="col-form-label">Comments<span class="text-danger"> <span></label>
                    <textarea id="comments" name="comments" class="form-control" rows="4" autocomplete="off" placeholder="Enter Comments here!"></textarea>
                </div>
                <div class="text-center col-lg-2">
                    <br>
                    <div class="mt-4">
                        <input type="checkbox" class="mx-1"  value="1" id="is_active" name="is_active" />
                        <label for="" class="">Is Active ?</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal footer -->
        <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" name="submit" id="submit" onclick="serviceVal();">Submit</button>

        </div>
        </form>
      </div>
    </div>
  </div>
{{-- Edit Model service --}}
    <div class="modal fade" id="myModalEdit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Edit Product</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
					</button>
				</div>
				    <form method="post" id="updateservicefrm" name="updateservicefrm"  enctype="multipart/form-data">
                        @csrf
                            <input  name="id_ed" value="" id="id_ed" type="hidden"  />
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <label for="name-input" class="col-form-label">Client Name<span class="text-danger"> * <span></label>
                                        <select name="client_id_ed" id="client_id_ed" class="form-control" required>
                                            <option value="">Select Client</option>
                                            @foreach($clients as $client)
                                            <option value="{{ $client->id }}">{{ $client->client_name }}</option>
                                            @endforeach
                                        </select>                          
                                    </div>
                                    <div class="col-lg-4">
                                        <label for="name-input" class="col-form-label">Lob<span class="text-danger">  <span></label>
                                        <select name="lob_id_ed" id="lob_id_ed" class="form-control" required>
                                            <option value="">Select Lob</option>
                                            @foreach($lobData as $lob)
                                            <option value="{{ $lob->id }}">{{ $lob->name }}</option>
                                            @endforeach
                                        </select>  
                                    </div>
                                    <div class="col-lg-4">
                                        <label for="name-input" class="col-form-label">Product Name<span class="text-danger">  <span></label>
                                        <input id="product_name_ed" name="product_name_ed" type="text" class="form-control" autocomplete="off" placeholder="Enter Product Name" required>
                                    </div>
                                </div>
                                <div class="row mt-1">
                                    <div class="col-lg-6">
                                        <label for="name-input" class="col-form-label">Comments<span class="text-danger"> <span></label>
                                        <textarea id="comments_ed" name="comments_ed" class="form-control" rows="4" autocomplete="off" placeholder="Enter Comments here!"></textarea>
                                    </div>
                                    <div class="text-center col-lg-2">
                                        <br>
                                        <div class="mt-4">
                                            <input type="checkbox" class="mx-1"  value="1" id="is_active_ed" name="is_active_ed" />
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
</div>

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
                    $("div.toolbar").html('<button id="addproduct" type="button" class="ml-2 btn btn-primary" data-toggle="modal" data-target="#myModal"><img class="menuicon" src="{{asset("assets/images/add.svg")}}">&nbsp;Add Product</button><br />');
                }
            });
        });
        function serviceVal(){
            if ($("#productForm").parsley()) {
                if ($("#productForm").parsley().validate()) {
                    event.preventDefault();
                    if ($("#productForm").parsley().isValid()) {
                        $.ajax({
                        type: "POST",
                        cache:false,
                        async: false,
                        url: "{{ url('/productInsert') }}",
                        data: new FormData($("#productForm")[0]),
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if(response.msg=="Product Name Already Exists!"){

                                new PNotify({
                                title: 'Error',
                                text:  response.msg,
                                type: 'error'
                                });
                                return false;
                            }
                            else if(response.msg=="Product Added Successfully!"){

                                new PNotify({
                                title: 'Success',
                                text:  response.msg,
                                type: 'success'
                                });
                                setTimeout(function() {
                                    window.location.reload();
                                }, 1000);                            }
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

        function productStatus(value)
    {
        window.location.href = '/productStatus/' + value;
    }

    function serviceUpdate(){
        if ($("#serviceUpdateForm").parsley()) {
            if ($("#serviceUpdateForm").parsley().validate()) {
                event.preventDefault();
                if ($(serviceUpdateForm).parsley().isValid()) {
                    $.ajax({
                        type: "POST",
                        cache:false,
                        async: false,
                        url: "",
                        data: new FormData($("#serviceUpdateForm")[0]),
                        processData: false,
                        contentType: false,
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
            }
        }
        return false;
    }

    // Edit service
     $('#datatable').on('click','.edit_product',function () {
		var id = $(this).data('id');
		var url = "{{ url('/edit_product') }}";
		$.ajax({
			type: "post",
			url: url,
			data: { id:id , _token: '{{csrf_token()}}'},
			success: function(response)
			{
				var res = response;
                console.log(res);
                $("#id_ed").val(res['id']);
                $('#client_id_ed').val(res['client_id']);
                $('#product_name_ed').val(res['product_name']);
                $("#lob_id_ed").val(res['lob_id']);
                $("#comments_ed").val(res['comments']);

				if(res['is_active'] == 1) {
					$( "#is_active_ed" ).attr('checked', 'checked');
				} else {
					$( "#is_active_ed" ).removeAttr('checked', 'checked');
				}
				$("#myModalEdit").modal('show');
			}
		});
	});


    $('#updateservicefrm').on('submit', function(event){
		event.preventDefault();
		if($('#updateservicefrm').parsley().isValid()){
			var url = "{{ url('/update_product') }}";
			var formData = new FormData(this);
			$.ajax({
				type: "post",
				url: url,
				data: formData,
                contentType: false,
                processData: false,
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
</script>

@endsection

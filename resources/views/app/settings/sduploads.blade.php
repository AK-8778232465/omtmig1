@extends('layouts.app')

@section('title', config('app.name') . ' | Supporting Doc\'s Upload')

@section('content')
<style>

  #filename-display {
            margin-top: 10px;
            font-size: 14px; 
            color: #333; 
            font-weight: bold;
            background-color: #f9f9f9; 
            padding: 5px 10px; 
            border-radius: 5px; 
            border: 1px solid #ddd; 
            display: inline-block; 
        }

  #uploading-spinner {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(255, 255, 255, 0.8);
    z-index: 9999;
}

.spinner-border {
    width: 6rem; 
    height: 6rem; 
}

p {
    font-size: 2rem;
}

</style>
<div class="container-fluid mt-2">
    <div class="col-md-12 pl-1 mt-2 p-3 content-loaded">
        <form id="sdupload_id" enctype="multipart/form-data" method="post" name="sdupload_id">
            @csrf
            <div class="card">
                <div class="card-body rounded shadow-sm" style="border-top: 3px solid #0e7c31">
                    <div class="form-group row mb-4 pl-3 pr-3 mt-3">
                        <div class="col-lg-3">
                            <label class="font-weight-bold">Client<span style="color: red;">*</span></label>
                            <select class="form-control select2dropdown" style="width: 100%" name="client_id" id="client_id">
                                <option selected disabled value="">Select Client</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}">{!! $client->client_no.' ('.$client->client_name.')' !!}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="font-weight-bold">Lob<span style="color: red;">*</span></label>
                            <select id="lob_id" name="lob_id" class="form-control select2dropdown" style="width: 100%" autocomplete="off" placeholder="Select Lob" data-parsley-trigger="focusout keyup">
                                <option selected disabled value="">Select Lob</option>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="font-weight-bold">Process<span style="color: red;">*</span></label>
                            <select id="process_id" name="process_id" class="form-control select2dropdown" style="width: 100%" autocomplete="off" placeholder="Select Lob" data-parsley-trigger="focusout keyup">
                                <option selected disabled value="">Select process</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="form-group row mb-0 mt-3 pl-3 pr-3 align-items-center">
                    <div class="col-3"></div>
                    <div class="col-6 mb-2">
                        <input type="file" id="file" name="file" class="form-control-file dropify" accept=".csv,.xlsx,.ods" required>
                        <p id="filename-display" style="margin-top: 10px;"></p> 
                    </div>
                    <div class="col-3"></div>
                    <div class="col-lg-12 mb-2 mt-3 text-center">
                        <a class="btn btn-sm btn-info mx-2" href="{{ asset('/template/sample_template_for_sduploads.xlsx') }}"><i class="fas fa-download"></i> Sample Format</a>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="13" height="14" x="0" y="0" viewBox="0 0 459.904 459.904" style="enable-background: new 0 0 512 512" xml:space="preserve">
                                <g>
                                    <path d="M123.465 168.28h46.543v138.07c0 14.008 11.358 25.352 25.352 25.352h69.2c13.993 0 25.352-11.343 25.352-25.352V168.28h46.527c7.708 0 14.637-4.641 17.601-11.764 2.933-7.094 1.301-15.295-4.145-20.741L243.413 29.28c-7.437-7.422-19.485-7.422-26.938 0L110.011 135.775a19.023 19.023 0 0 0-4.13 20.741c2.962 7.109 9.876 11.764 17.584 11.764z" fill="#ffffff" opacity="1" data-original="#ffffff"></path>
                                    <path d="M437.036 220.029c-12.617 0-22.852 10.237-22.852 22.867v95.615c0 28.643-23.317 51.944-51.961 51.944H97.679c-28.644 0-51.945-23.301-51.945-51.944v-95.615c0-12.63-10.251-22.867-22.867-22.867C10.236 220.029 0 230.266 0 242.897v95.615c0 53.859 43.818 97.679 97.679 97.679h264.544c53.861 0 97.681-43.819 97.681-97.679v-95.615c0-12.631-10.237-22.868-22.868-22.868z" fill="#ffffff" opacity="1" data-original="#ffffff"></path>
                                </g>
                            </svg> Upload File
                        </button>
                    </div>
                </div>
            </div>
        </form>
        <table id="datatable" class="table table-striped table-bordered p-2">
            <thead>
                <tr>
                    <th>S.NO</th>
                    <th>File Name</th>
                    <th>Total</th>
                    <th>Completed</th>
                    <th>Failed</th>
                    <th>Uploaded At</th>
                    <th>Uploaded By</th>
                </tr>
        </thead>
            <tbody>
            @php $j = 1 @endphp
                    @foreach($exceldetail as $detail)
                    @if(!empty($detail->unsuccessfull_rows) || !empty($detail->successfull_rows))
                        <tr>
                            <td>{{ $j++ }}</td>
                            <td>{{ $detail->file_name}}</td>
                            <td>{{ $detail->total_rows}}</td>
                            <td>{{ $detail->successfull_rows}}</td>
                            <td style="font-weight:600">
                                @if ($detail->unsuccessfull_rows > 0 && $detail->unsuccessfull_rows != "")
                                    <a href="{{ route('exportCIFailedOrders', ['id' => $detail->id]) }}">
                                        {{ $detail->unsuccessfull_rows }}
                                        <i class="fas fa-download"></i>
                                    </a>
                                @endif
                            </td>
                            <td>{{ date('m/d/Y H:i:s', strtotime($detail->created_at)) }}</td>
                            <td>{!! optional($detail->users)->emp_id . " (" . optional($detail->users)->username. ")" !!}</td>
                        </tr>
                    @endif
                    @endforeach
            </tbody>
        </table>
    </div>
</div>

<div id="uploading-spinner" class="text-center d-none">
    <div class="d-flex flex-column justify-content-center align-items-center" style="height: 100vh;">
        <div class="spinner-border text-primary mb-2" role="status" style="width: 4rem; height: 4rem;"> 
            <span class="sr-only">Excel Uploading...</span>
        </div>
        <p class="mb-0" style="font-size: 1.5rem;">Excel Uploading...</p> 
    </div>
</div>

<script>
$(document).ready(function() {
      $('.select2dropdown').select2();
  });

  
  $(document).ready(function() {
        $('.content-loaded').show();
        $('.frame').addClass('d-none');
    });

$(document).ready(function() {
        
        $('.dropify').dropify();
       
        $('.dropify').on('change', function(event) {
            var input = event.target;
            if (input.files && input.files[0]) {
                var fileName = input.files[0].name;
            $('#filename-display').text('Selected file: ' + fileName).show(); 
            } else {
            $('#filename-display').text('').hide(); 
            }
        });

        var drEvent = $('.dropify').dropify();

        drEvent.on('dropify.afterClear', function(event, element){
        $('#filename-display').text('').hide(); 
    });

    if ($('#filename-display').text().trim() === '') {
        $('#filename-display').hide();
    }
});

$('#client_id').on('change', function () {
    var client_id = $(this).val();
    $("#lob_id").html('');
    if (client_id) {
        $.ajax({
            url: "{{ route('getlobId') }}",
            type: "POST",
            data: {
                 client_id: client_id,
                _token: '{{ csrf_token() }}'
            },
            dataType: 'json',
            success: function (result) {
                $('#lob_id').html('<option value="">Select lob</option>');
                $.each(result, function (key, value) {
                    $("#lob_id").append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            },
            error: function (xhr, status, error) {
            }
        });
    } else {
        $('#lob_id').html('<option value="">Select lob</option>');
    }
});


$('#lob_id').on('change', function () {
    var lob_id = $(this).val();
    $("#process_id").html('');
    if (lob_id) {
        $.ajax({
            url: "{{ route('getprocessId') }}",
            type: "POST",
            data: {
                 lob_id: lob_id,
                _token: '{{ csrf_token() }}'
            },
            dataType: 'json',
            success: function (result) {
                $('#process_id').html('<option value="">Select process</option>');
                $.each(result, function (key, value) {
                    $("#process_id").append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            },
            error: function (xhr, status, error) {
            }
        });
    } else {
        $('#process_id').html('<option value="">Select process</option>');
    }
});

$('#sdupload_id').on('submit', function(event){
    event.preventDefault();
    $('#uploading-spinner').removeClass('d-none');
    $('.content-loaded').hide();
    $('.frame').removeClass('d-none');
    
    if ($('#sdupload_id').parsley().isValid()) {
        $.ajax({
            type: "POST",
            url: "{{ route('sduploadfileImport') }}",
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function (response) {
                $('#uploading-spinner').addClass('d-none');
                $('.content-loaded').show();
                $('.frame').addClass('d-none');

                if (response.success) {
                    Swal.fire({
                        title: 'Success',
                        text: response.success,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else if (response.error) {
                    Swal.fire({
                        title: 'Fill the Required Fields',
                        text: response.error,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function (response) {
                $('#uploading-spinner').addClass('d-none');
                $('.content-loaded').show();
                $('.frame').addClass('d-none');

                Swal.fire({
                    title: 'Uploading Failed',
                    text: 'Please fill the required fields OR Is not an XLSX file',
                    icon: "error",
                    confirmButtonText: "OK"
                }).then(() => {
                    location.reload();
                });
            }
        });
    }
});

</script>

@endsection

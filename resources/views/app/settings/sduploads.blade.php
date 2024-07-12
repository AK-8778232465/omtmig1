@extends('layouts.app')

@section('title', config('app.name') . ' | Supporting Doc\'s Upload')

@section('content')
<style>

  #filename-display {
            margin-top: 10px;
            font-size: 14px; /* Adjust the font size as needed */
            color: #333; /* Change to the desired color */
            font-weight: bold; /* Makes the text bold */
            background-color: #f9f9f9; /* Light grey background */
            padding: 5px 10px; /* Adds some padding */
            border-radius: 5px; /* Rounds the corners */
            border: 1px solid #ddd; /* Adds a light border */
            display: inline-block; /* Makes the element fit the content */
        }

  #uploading-spinner {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(255, 255, 255, 0.8); /* Semi-transparent background */
    z-index: 9999; /* Ensure it's on top of other content */
}

.spinner-border {
    width: 6rem; /* Increase spinner size */
    height: 6rem; /* Increase spinner size */
}

p {
    font-size: 2rem; /* Increase text size */
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
                            <label class="font-weight-bold">Lob</label>
                            <select id="lob_id" name="lob_id" class="form-control select2dropdown" style="width: 100%" autocomplete="off" placeholder="Select Lob" data-parsley-trigger="focusout keyup">
                                <option selected disabled value="">Select Lob</option>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="font-weight-bold">Process</label>
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
                        <p id="filename-display" style="margin-top: 10px;"></p> <!-- Element to display the filename -->
                    </div>
                    <div class="col-3"></div>
                    <div class="col-lg-12 mb-2 mt-3 text-center">
                        <a class="btn btn-sm btn-info mx-2" href="{{ asset('/template/sample_template.xlsx') }}"><i class="fas fa-download"></i> Sample Format</a>
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
    </div>
</div>

<div id="uploading-spinner" class="text-center d-none">
    <div class="d-flex flex-column justify-content-center align-items-center" style="height: 100vh;">
        <div class="spinner-border text-primary mb-2" role="status" style="width: 4rem; height: 4rem;"> <!-- Adjust size as needed -->
            <span class="sr-only">Excel Uploading...</span>
        </div>
        <p class="mb-0" style="font-size: 1.5rem;">Excel Uploading...</p> <!-- Adjust font size as needed -->
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

// Js Dropify
$(document).ready(function() {
        // Initialize Dropify
        $('.dropify').dropify();
        // Event listener for file change
        $('.dropify').on('change', function(event) {
            var input = event.target;
            if (input.files && input.files[0]) {
                var fileName = input.files[0].name;
            $('#filename-display').text('Selected file: ' + fileName).show(); // Show the element and set text
            } else {
            $('#filename-display').text('').hide(); // Clear text and hide the element
            }
        });

        // Event listener for Dropify's events (for example, file clear event)
        var drEvent = $('.dropify').dropify();

        drEvent.on('dropify.afterClear', function(event, element){
        $('#filename-display').text('').hide(); // Clear text and hide the element
    });

    // Hide initially if it's empty
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
                console.error(xhr.responseText);
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
                console.error(xhr.responseText);
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
                      if (response.success == "Excel Uploaded Successfully!") {
                          new PNotify({
                              title: 'Success',
                              text: response.success,
                              type: 'success'
                          });
                          setTimeout(function() {
                              location.reload();
                          }, 1000);
                      } else if (response.error == "The file does not exist, is not readable, or is not an XLSX file") {
                          new PNotify({
                              title: 'Error',
                              text: response.error,
                          });
                      }
                  },
                error: function (response) {
                    $('#uploading-spinner').addClass('d-none'); // Hide the spinner and text on error
                    $('#progressId').addClass('d-none');
                    $('.content-loaded').show();
                    $('.frame').addClass('d-none');
                    Swal.fire({
                        text: "File Upload Failed",
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

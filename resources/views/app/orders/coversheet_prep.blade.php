@extends('layouts.app')
@section('title', config('app.name') . ' | Coversheet Prep')
@section('content')
<div class="card shadow shadow-md rounded showdow-grey mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-center">
            <div class="col-lg-3">
                <div class="font-weight-bold">Status :</div>
                <select style="width:100%" class="form-control mx-2" name="order_status" id="order_status" @if(!isset($orderData->assignee_user)) disabled @endif>
                    <option value="1" @if($orderData->status_id == 1) selected @endif>WIP</option>
                    <option value="2" @if($orderData->status_id == 2) selected @endif>Hold</option>
                    <option value="3" @if($orderData->status_id == 3) selected @endif>Cancelled</option>
                    <option value="4" @if($orderData->status_id == 4) selected @endif>Send for QC</option>
                    <option value="5" @if($orderData->status_id == 5) selected @endif>Completed</option>
                    <option value="13" @if($orderData->status_id == 13) selected @endif>Coversheet Prep</option>
                    <option value="14" @if($orderData->status_id == 14) selected @endif>Clarification</option>
                </select>
            </div>
        </div>
        <div class="d-flex justify-content-center my-4">
            <button class="btn btn-primary btn-sm mx-2" onclick="status_submition({{$orderData->id}})" type="submit">Submit</button>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#order_status').select2();
    });

    function status_submition(orderId) {
        var orderStatus = $("#order_status").val();

        var data = {
            orderId: orderId,
            orderStatus: orderStatus,
            _token: '{{ csrf_token() }}'
        };

        // Perform AJAX request
        $.ajax({
            url: '{{url("coversheet_submit")}}',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    Swal.fire({
                        title: "Success",
                        text: response.success,
                        icon: "success"
                    }).then((result) => {
                        if (result.value) {
                            window.location.href = '{{ url("orders_status") }}';
                        }
                    });
                }
                else if(response.error) {
                    Swal.fire({
                        title: "Error",
                        text: response.error,
                        icon: "error"
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                Swal.fire({
                    title: "Error",
                    text: 'Error submitting order. Please try again later.',
                    icon: "error"
                });
            }
        });
    }
</script>

@endsection

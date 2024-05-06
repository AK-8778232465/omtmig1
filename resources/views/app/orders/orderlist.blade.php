@extends('layouts.app')
@section('title', config('app.name') . ' | Orders')
@section('content')

<style>
    .status-btn {
        font-size: .7rem !important;
    }
    .content-loaded{
        display: none;
    }
    .frame {
      position: fixed;
      top: 50%;
      left: 50%;
      width: 400px;
      height: 400px;
      margin-top: -200px;
      margin-left: -200px;
      border-radius: 2px;
      /* background: #ffffff; */
      color: #fff;
    }

    .center {
      position: absolute;
      width: 220px;
      height: 220px;
      top: 90px;
      left: 90px;
    }

    .dot-1 {
      position: absolute;
      z-index: 3;
      width: 30px;
      height: 30px;
      top: 95px;
      left: 95px;
      background: #fff;
      border-radius: 50%;
      -webkit-animation-fill-mode: both;
              animation-fill-mode: both;
      -webkit-animation: jump-jump-1 2s cubic-bezier(0.21, 0.98, 0.6, 0.99) infinite alternate;
              animation: jump-jump-1 2s cubic-bezier(0.21, 0.98, 0.6, 0.99) infinite alternate;
    }

    .dot-2 {
      position: absolute;
      z-index: 2;
      width: 60px;
      height: 60px;
      top: 80px;
      left: 80px;
      background: #fff;
      border-radius: 50%;
      -webkit-animation-fill-mode: both;
              animation-fill-mode: both;
      -webkit-animation: jump-jump-2 2s cubic-bezier(0.21, 0.98, 0.6, 0.99) infinite alternate;
              animation: jump-jump-2 2s cubic-bezier(0.21, 0.98, 0.6, 0.99) infinite alternate;
    }

    .dot-3 {
      position: absolute;
      z-index: 1;
      width: 90px;
      height: 90px;
      top: 65px;
      left: 65px;
      background: #fff;
      border-radius: 50%;
      -webkit-animation-fill-mode: both;
              animation-fill-mode: both;
      -webkit-animation: jump-jump-3 2s cubic-bezier(0.21, 0.98, 0.6, 0.99) infinite alternate;
              animation: jump-jump-3 2s cubic-bezier(0.21, 0.98, 0.6, 0.99) infinite alternate;
    }

    @-webkit-keyframes jump-jump-1 {
      0%, 70% {
        box-shadow: 2px 2px 3px 2px rgba(0, 0, 0, 0.2);
        -webkit-transform: scale(0);
                transform: scale(0);
      }
      100% {
        box-shadow: 10px 10px 15px 0 rgba(0, 0, 0, 0.3);
        -webkit-transform: scale(1);
                transform: scale(1);
      }
    }

    @keyframes jump-jump-1 {
      0%, 70% {
        box-shadow: 2px 2px 3px 2px rgba(0, 0, 0, 0.2);
        -webkit-transform: scale(0);
                transform: scale(0);
      }
      100% {
        box-shadow: 10px 10px 15px 0 rgba(0, 0, 0, 0.3);
        -webkit-transform: scale(1);
                transform: scale(1);
      }
    }
    @-webkit-keyframes jump-jump-2 {
      0%, 40% {
        box-shadow: 2px 2px 3px 2px rgba(0, 0, 0, 0.2);
        -webkit-transform: scale(0);
                transform: scale(0);
      }
      100% {
        box-shadow: 10px 10px 15px 0 rgba(0, 0, 0, 0.3);
        -webkit-transform: scale(1);
                transform: scale(1);
      }
    }
    @keyframes jump-jump-2 {
      0%, 40% {
        box-shadow: 2px 2px 3px 2px rgba(0, 0, 0, 0.2);
        -webkit-transform: scale(0);
                transform: scale(0);
      }
      100% {
        box-shadow: 10px 10px 15px 0 rgba(0, 0, 0, 0.3);
        -webkit-transform: scale(1);
                transform: scale(1);
      }
    }
    @-webkit-keyframes jump-jump-3 {
      0%, 10% {
        box-shadow: 2px 2px 3px 2px rgba(0, 0, 0, 0.2);
        -webkit-transform: scale(0);
                transform: scale(0);
      }
      100% {
        box-shadow: 10px 10px 15px 0 rgba(0, 0, 0, 0.3);
        -webkit-transform: scale(1);
                transform: scale(1);
      }
    }
    @keyframes jump-jump-3 {
      0%, 10% {
        box-shadow: 2px 2px 3px 2px rgba(0, 0, 0, 0.2);
        -webkit-transform: scale(0);
                transform: scale(0);
      }
      100% {
        box-shadow: 10px 10px 15px 0 rgba(0, 0, 0, 0.3);
        -webkit-transform: scale(1);
                transform: scale(1);
      }
    }
</style>

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
            <div class="row justify-content-center mt-2 mb-4" id="statusButtons">
                <div class="bg-info shadow-lg p-0 rounded text-white " style="text-decoration: none; font-size:0.7rem">
                    <button id="status_1"  class="btn btn-info status-btn">Order Received<span id="status_1_count"></span></button>
                    <button id="status_2"  class="btn btn-info status-btn">Open<span id="status_2_count"></span></button>
                    <button id="status_3" class="btn btn-info status-btn">WIP<span id="status_3_count"></span></button>
                    <button id="status_4" class="btn btn-info status-btn">Follow Up<span id="status_4_count"></span></button>
                    <button id="status_5" class="btn btn-info status-btn">Clarification Req<span id="status_5_count"></span></button>
                    <button id="status_6" class="btn btn-info status-btn">Clarification Rec<span id="status_6_count"></span></button>
                    <button id="status_7" class="btn btn-info status-btn">Qc Queue<span id="status_7_count"></span></button>
                    <button id="status_8" class="btn btn-info status-btn">Completed<span id="status_8_count"></span></button>
                    <button id="status_9" class="btn btn-info status-btn">Closed<span id="status_9_count"></span></button>
                    <button id="status_10" class="btn btn-info status-btn">Cancelled<span id="status_10_count"></span></button>
                    <button id="status_11" class="btn btn-info status-btn">Pending<span id="status_11_count"></span></button>
                    <button id="status_12" class="btn btn-info status-btn">On Hold<span id="status_12_count"></span></button>
                    <button id="status_All" class="btn btn-info status-btn @role('Lead') d-none  @endrole @role('User') d-none  @endrole" >All<span id="status_All_count"></span></button>
                    <button id="status_13" class="btn btn-info status-btn">Coversheet Prep<span id="status_13_count"></span></button>
                    <button id="status_14" class="btn btn-info status-btn">Clarification<span id="status_14_count"></span></button>
                </div>
            </div>
            <div class="p-0 mx-2">
                <table id="order_datatable" class="table table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                    <thead>
                        <tr>
                            {{-- <th width="5%">Sl No</th> --}}
                            <th width="10%">Client</th>
                            <th width="10%">Service</th>
                            <th width="10%">Assigned To</th>
                            <th width="8%">Status</th>
                            <th width="20%">Property Address</th>
                            <th width="10%">Created By</th>
                            <th width="8%">Order#  </th>
                            <th>Order Received</th>
                            <th width="20%">Comments</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
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


            function serviceStatus(value) {
    window.location.href = '/serviceStatus/' + value;
    }

    let task_status = 1;
    let status_change;
    let first_load = 1;

    $(document).on('click', '.status-btn', function () {
        $('.status-btn').removeClass('btn-primary').addClass('text-white');
        $(this).removeClass('btn-info');
        $(this).addClass('btn-primary');
        status_change();
    });

    $(function () {
        status_change();
    });

    status_change = function () {
        let task_status = $('#statusButtons').find('.btn-primary').attr('id');
        let status = null;

        if (first_load === 1) {
            first_load = 2;
            @if(isset($begin))
            status = "{{$begin}}";
            @endif
            if (!status || status === undefined) {
                status = "1";
                $('.status-btn').removeClass('btn-primary').addClass('text-white');
                $('#status_' + status).removeClass('btn-info').addClass('btn-primary');
            } else {
                $('.status-btn').removeClass('btn-primary').addClass('text-white');
                $('#status_' + status).removeClass('btn-info').addClass('btn-primary');
            }
        } else {
            status = task_status.replace("status_", "");
        }

        var statusColumnVisible = (status === 'All') ? true : false;
        console.log(status);
        var otherinfo = ["4", "5", "6", "10", "11", "12"].includes(status) ? true : false;

        $.ajax({
        url: "{{url('getOrderData')}}",
        type: "POST",
        data: {
            status: status,
            _token: '{{csrf_token()}}'
        },
        dataType: 'json',
        success: function (response) {
            var OrderData = response.OrderData;
            updateStatusCounts(response.StatusCounts);
            var table = $('#order_datatable').DataTable();
            table.destroy();
            var table = $('#order_datatable').DataTable({
                data: OrderData,
                // scrollX: true,
                lengthMenu: [10, 50, 100, 200, 500],
                columns: [
                    // { data: null, render: function (data, type, row, meta) { return meta.row + 1; } },
                    {
                            data: null,
                            render: function (data, type, row) {
                                if(row.order_id != null && row.order_id != undefined) {
                                    return row.order_id;
                                } else {
                                    return '';
                                }
                            }
                        },
                        {
                            data: null,
                            render: function (data, type, row) {
                                if(row.order_date != null && row.order_date != undefined) {
                                    return row.order_date ? new Date(row.order_date).toLocaleDateString('en-US', {month: '2-digit', day: '2-digit', year: 'numeric'}) : '';
                                } else {
                                    return '';
                                }
                            }
                        },
                    {
                        data: null,
                        render: function (data, type, row) {
                            return (row.process.process_code != null && row.process.process_code != undefined) ?
                                (row.process.process_code + ' ' + row.process.process_code) : '';
                        }
                    },
                    {
                        data: null,

                        render: function (data, type, row) {
                            return (row.state.state_name != null && row.state.state_name != undefined) ? row.state.state_name : '';
                        }
                    },
                    {
                        data: null,

                        render: function (data, type, row) {
                            return (row.county.county_name != null && row.county.county_name != undefined) ? row.county.county_name : '';
                        }
                    },
                    {
                        data: null,
                        visible: statusColumnVisible,
                        render: function (data, type, row) {
                            return (row.status.status != null && row.status.status != undefined) ? row.status.status : '';
                        }
                    },
                    {
                        data: null,

                        render: function (data, type, row) {
                            return (row.created_by != null && row.created_by != undefined) ? row.created_by : '';

                        }
                    },

                ],
                // order: [8, (status == 1) ? 'asc' : 'desc']
                order: [8, 'desc']
            });

            updateStatusCounts(response.StatusCounts);
        }
    });


    };


    function updateStatusCounts(statusCounts) {
        let total = 0;

        for (let status = 1; status <= 14; status++) {
            let count = statusCounts[status] || 0;
            total += count;
            $('#status_' + status + '_count').text(' (' + count + ')');
        }

        // Update the count for the "All" button
        $('#status_All_count').text(' (' + total + ')');
    }

    // assign order
    $(document).on('click', '.assign-order', function (e) {
    e.preventDefault();
    var orderId = $(this).data('order-id');
    $('.content-loaded').hide();
    $('.frame').removeClass('d-none');
    $.ajax({
        url: '/assignOrder',
        type: 'POST',
        data: {
            orderId: orderId,
            _token: '{{ csrf_token() }}'
        },
        dataType: 'json',
        success: function (response) {
            if (response.error) {
                $('.frame').addClass('d-none');
                Swal.fire({
                        title: 'Unavailable',
                        text: 'Order is already being processed',
                        icon: 'warning',
                        showConfirmButton: false,
                        timer: 1000
                    });
                    setTimeout(function () {
                        window.location.href = '/orders';
                    }, 1000);
            } else {
                if (response.message === 'Order assigned successfully' || response.message === 'OK') {
                    window.location.href = '/orders/' + orderId;
                } else {
                    $('.frame').addClass('d-none');
                    Swal.fire({
                        title: 'Unavailable',
                        text: 'Order is already being processed',
                        icon: 'warning',
                        showConfirmButton: false,
                        timer: 1000
                    });
                    setTimeout(function () {
                        window.location.href = '/orders';
                    }, 1000);
                }
            }
        },
        error: function (xhr, textStatus, errorThrown) {
            console.error('Error assigning order: ' + errorThrown);
        }
    });
    });

    $(document).ready(function() {
        $('.content-loaded').show();
        $('.frame').addClass('d-none');
    });

</script>
@endsection

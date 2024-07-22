@extends('layouts.app')
@section('title', 'Stellar OMS | Geo Informations')
@section('content')
<div class="container-fluid mt-2">
    @include('app.settings.index')
    <div class="form-group row mb-4 pb-0 pl-3 pr-3">
        <div class="form-group col-lg-3 mb-0 pb-0">
            <label class="font-weight-bold">State Code</label><br>
            <select class="form-control select2dropdown" style="width:100%" name="property_state" id="property_state" aria-hidden="true">
                <option selected="" disabled="" value="">Select State Code</option>
                @foreach ($stateList as $state)
                        <option value="{{ $state->id }}">{{ $state->short_code }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-lg-3 mb-0 pb-0">
            <label class="font-weight-bold">County</label>
            <select id="property_county" name="property_county" type="text" class="form-control select2dropdown" style="width:100%" autocomplete="off" placeholder="Enter Property County"  data-parsley-trigger="focusout" data-parsley-trigger="keyup">
                <option selected="" disabled="" value="">Select County</option>
            </select>
        </div>
    </div>
    <table id="datatable" class="table table-striped table-bordered p-2">
                <thead>
                <tr>
                    <th class="text-center" style="width:20%;">S.NO</th>
                    <th class="text-center">County</th>
                    <th class="text-center Mhide">Municipality</th>
                </tr>
                </thead>
                <tbody>
                   
                </tbody>
            </table>
</div>

<script type="text/javascript">
     $(document).ready(function() {
        $('.select2dropdown').select2();

        var datatable = $('#datatable').DataTable({
            "paging": true,
            "searching": true,
            "info": true,
            "lengthChange": true,
            "pageLength": 10,
            "language": {
                "paginate": {
                    "previous": "Previous",
                    "next": "Next"
                }
            },
            "columns": [
                { 
                    "data": null, 
                    "class": "text-center",
                    "render": function (data, type, row, meta) {
                        return meta.row + 1; // Serial number
                    }
                }, // S.NO
                { "data": "county_name", "class": "text-center" }, // County
                { "data": "municipality", "class": "text-center Mhide" } // Municipality
            ]
    });

    $('#property_state').on('change', function () {
        var state_id = $("#property_state").val();
        $("#property_county").html('');
        $.ajax({
                url: "{{ url('getGeoCounty') }}",
            type: "POST",
            data: {
                state_id: state_id,
                    _token: '{{ csrf_token() }}'
            },
            dataType: 'json',
            success: function (result) {
                $('#property_county').html('<option value="">Select County</option>');
                    var tableData = [];
                $.each(result.county, function (key, value) {
                    $("#property_county").append('<option value="' + value.id + '">' + value.county_name + '</option>');
                        tableData.push({
                            "county_name": value.county_name,
                            "municipality": value.municipality || '-'
                });
                    });
                    datatable.clear().rows.add(tableData).draw();
            }
        });
    });

    $('#property_county').on('change', function () {
        var county_id = $(this).val();
            $("#city").html('');
            var tableData = [];

        if (county_id) {
            $.ajax({
                url: "{{ route('getGeoCities') }}",
                type: "POST",
                data: {
                    county_id: county_id,
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json',
                success: function (result) {
                    if (result.length > 0 && result[0].id !== null) {
                        $('#city').html('<option value="">Select City</option>');
                        $.each(result, function (key, value) {
                            $("#city").append('<option value="' + value.id + '">' + value.city + '</option>');
                                tableData.push({
                                    "county_name": $('#property_county option:selected').text(),
                                    "municipality": value.city || '-'
                                });
                        });
                    } else {
                        $('#city').html('<option value="">No Municipality Found</option>');
                            tableData.push({
                                "county_name": $('#property_county option:selected').text(),
                                "municipality": 'No Municipality Found'
                            });
                            swal.fire({
                                title: 'No Municipality Found',
                                text: 'No municipality from selected county',
                                icon: 'warning',
                                confirmButtonText: 'OK'
                            });
                    }
                        datatable.clear().rows.add(tableData).draw();
                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        } else {
            $('#city').html('<option value="">Select City</option>');
                tableData.push({
                    "county_name": '',
                    "municipality": 'No Cities Found'
                });
                datatable.clear().rows.add(tableData).draw();
        }
        });
    });
</script>
@endsection

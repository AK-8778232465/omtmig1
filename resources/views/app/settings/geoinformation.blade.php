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
    });

    $('#property_state').on('change', function () {
        var state_id = $("#property_state").val();
        $("#property_county").html('');
        $.ajax({
            url: "{{url('getGeoCounty')}}",
            type: "POST",
            data: {
                state_id: state_id,
                _token: '{{csrf_token()}}'
            },
            dataType: 'json',
            success: function (result) {
                $('#property_county').html('<option value="">Select County</option>');
                var tableRows = '';
                $.each(result.county, function (key, value) {
                    $("#property_county").append('<option value="' + value.id + '">' + value.county_name + '</option>');
                    tableRows += '<tr>';
                    tableRows += '<td class="text-center">' + (key + 1) + '</td>';
                    tableRows += '<td class="text-center">' + value.county_name + '</td>';
                    tableRows += '<td class="text-center">' + (value.municipality || '-') + '</td>';
                    tableRows += '</tr>';
                });
                $('#datatable tbody').html(tableRows);
            }
        });
    });

    $('#property_county').on('change', function () {
        var county_id = $(this).val();
        $("#city").html(''); // Clear previous options
        var tableRows = ''; // Initialize table rows

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
                            tableRows += '<tr>';
                            tableRows += '<td class="text-center">' + (key + 1) + '</td>';
                            tableRows += '<td class="text-center">' + $('#property_county option:selected').text() + '</td>';
                            tableRows += '<td class="text-center">' + (value.city || '-') + '</td>';
                            tableRows += '</tr>';
                        });
                    } else {
                        $('#city').html('<option value="">No Cities Found</option>');
                        tableRows += '<tr>';
                        tableRows += '<td class="text-center" colspan="3">No Cities Found</td>';
                        tableRows += '</tr>';
                    }
                    $('#datatable tbody').html(tableRows); // Update table body
                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        } else {
            $('#city').html('<option value="">Select City</option>');
            tableRows += '<tr>';
            tableRows += '<td class="text-center" colspan="3">No Cities Found</td>';
            tableRows += '</tr>';
            $('#datatable tbody').html(tableRows); // Update table body
        }
    });
</script>
@endsection

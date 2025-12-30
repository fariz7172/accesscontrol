@extends('layout_background.app_layouts')

@section('content')
<div class="container-fluid">
    @if(session('success'))
    <div class="alert alert-success">
        {!! session('success') !!}
    </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <h1 class="h3 mb-2 text-gray-800">Dayzone Management</h1>

    <!-- Tab Bar Menu -->
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('dayzone.index') ? 'active' : '' }}"
                href="{{ route('dayzone.index') }}">Dayzone</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('dayzonedetail.index') ? 'active' : '' }}"
                href="{{ route('dayzonedetail.index') }}">Dayzone Detail</a>
        </li>
    </ul>

    <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#pushModal">Push to Mechine</button>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Dayzone</th>
                            <th>Start Time 1</th>
                            <th>End Time 1</th>
                            <th>Start Time 2</th>
                            <th>End Time 2</th>
                            <th>Start Time 3</th>
                            <th>End Time 3</th>
                            <th>Start Time 4</th>
                            <th>End Time 4</th>
                            <th>Start Time 5</th>
                            <th>End Time 5</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dayzoneDetails as $dayzoneDetail)
                        <tr>
                            <td>{{ $dayzoneDetail->ID }}</td>
                            <td>{{ $dayzoneDetail->dayzone ? $dayzoneDetail->dayzone->Name : 'N/A' }}</td>
                            <td>{{ $dayzoneDetail->Stz1 }}</td>
                            <td>{{ $dayzoneDetail->Etz1 }}</td>
                            <td>{{ $dayzoneDetail->Stz2 }}</td>
                            <td>{{ $dayzoneDetail->Etz2 }}</td>
                            <td>{{ $dayzoneDetail->Stz3 }}</td>
                            <td>{{ $dayzoneDetail->Etz3 }}</td>
                            <td>{{ $dayzoneDetail->Stz4 }}</td>
                            <td>{{ $dayzoneDetail->Etz4 }}</td>
                            <td>{{ $dayzoneDetail->Stz5 }}</td>
                            <td>{{ $dayzoneDetail->Etz5 }}</td>
                            <td>
                                <a href="{{ route('dayzonedetail.edit', $dayzoneDetail->ID) }}"
                                    class="btn btn-warning btn-sm">Edit</a>
                                {{-- <form action="{{ route('dayzonedetail.destroy', $dayzoneDetail->ID) }}"
                                method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm"
                                    onclick="return confirm('Are you sure you want to delete this dayzone detail?')">Delete</button>
                                </form> --}}

                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $dayzoneDetails->links() }}
            </div>
        </div>
    </div>


    <div class="modal fade" id="pushModal" tabindex="-1" role="dialog" aria-labelledby="pushModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form action="{{ route('dayzonedetail.push-to-api') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="pushModalLabel">Push Dayzone to Device</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Device Gate Selection -->
                        <div class="form-group">
                            <label>Select Devices</label>
                            <div class="row">
                                @foreach(\App\Models\deviceGateModel::all() as $device)
                                @continue($device->type == 1) {{-- Lewati seluruh iterasi jika type == 1 --}}

                                @php
                                $typeLabel = match($device->type) {
                                0 => 'Fingerprint',
                                50 => 'Face',
                                default => null,
                                };
                                @endphp
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="sn[]"
                                            value="{{ $device->sn }}" id="device_{{ $device->sn }}">
                                        <label class="form-check-label" for="device_{{ $device->sn }}">
                                            {{ $device->name }} (SN: {{ $device->sn }})
                                            @if($typeLabel)
                                            <span class="text-muted">- {{ $typeLabel }}</span>
                                            @endif
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @error('sn')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Dayzone Detail Selection -->
                        <div class="form-group">
                            <label>Select Dayzone Details (ID 1-8)</label>
                            <div class="row">
                                @foreach(\App\Models\DayzoneDetail::whereBetween('ID', [1, 8])->get() as $detail)
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="dayzone_id[]"
                                            value="{{ $detail->ID }}" id="dayzone_{{ $detail->ID }}">
                                        <label class="form-check-label" for="dayzone_{{ $detail->ID }}">
                                            Dayzone ID: {{ $detail->ID }}
                                            ({{ $detail->dayzone ? $detail->dayzone->Name : 'N/A' }})
                                            <br>

                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Push to API</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<!-- Initialize DataTable for Device Gate Modal -->
<script>
    $(document).ready(function() {
        $('#deviceGateTable').DataTable({
            "pageLength": 10,
            "ordering": true,
            "searching": true,
            "paging": true,
            "info": true
        });

        // Pass dayzoneDetail ID to modal
        $('#deviceGateModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var dayzoneId = button.data('dayzone-id'); // Extract dayzoneDetail ID
            var modal = $(this);
            modal.find('#dayzoneDetailId').text(dayzoneId); // Update modal title with ID
        });

        // Handle Save Selection button click
        $('#saveDeviceSelection').on('click', function() {
            let selectedDevices = [];
            $('input[name="device[]"]:checked').each(function() {
                selectedDevices.push($(this).val());
            });
            let dayzoneId = $('#dayzoneDetailId').text(); // Get dayzoneDetail ID from modal
            console.log('Dayzone Detail ID:', dayzoneId, 'Selected Devices:', selectedDevices);
            // Add logic to handle selected devices (e.g., send to server via AJAX)
            $.ajax({
                url: '/dayzonedetail/save-devices', // Replace with your route
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    dayzone_id: dayzoneId,
                    devices: selectedDevices
                },
                success: function(response) {
                    console.log('Devices saved successfully:', response);
                    $('#deviceGateModal').modal('hide');
                },
                error: function(xhr) {
                    console.error('Error saving devices:', xhr);
                }
            });
        });
    });
</script>

@endsection
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

    <h1 class="h3 mb-2 text-gray-800">Weekzone Management</h1>

    <!-- Tab Bar Menu -->
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('weekzone.index') ? 'active' : '' }}"
                href="{{ route('weekzone.index') }}">
                Weekzone
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('weekzonedetail.index') ? 'active' : '' }}"
                href="{{ route('weekzonedetail.index') }}">
                Weekzone Detail
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('setusertimeone.index') ? 'active' : '' }}"
                href="{{ route('setusertimeone.index') }}">
                User Profiles
            </a>
        </li>
    </ul>

    <!-- Button to trigger the push modal -->
    <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#pushModal">Push to Mechine</button>

    <!-- Weekzone Detail Content -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Weekzone</th>
                            <th>Mon</th>
                            <th>Tue</th>
                            <th>Wed</th>
                            <th>Thu</th>
                            <th>Fri</th>
                            <th>Sat</th>
                            <th>Sun</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($weekzoneDetails as $weekzoneDetail)
                        <tr>
                            <td>{{ $weekzoneDetail->ID }}</td>
                            <td>{{ $weekzoneDetail->weekzone ? $weekzoneDetail->weekzone->Name : 'N/A' }}</td>
                            <td>{{ $weekzoneDetail->day1 ? \App\Models\Dayzone::where('ID', $weekzoneDetail->day1)->value('Name') ?? 'None' : 'None' }}
                            </td>
                            <td>{{ $weekzoneDetail->day2 ? \App\Models\Dayzone::where('ID', $weekzoneDetail->day2)->value('Name') ?? 'None' : 'None' }}
                            </td>
                            <td>{{ $weekzoneDetail->day3 ? \App\Models\Dayzone::where('ID', $weekzoneDetail->day3)->value('Name') ?? 'None' : 'None' }}
                            </td>
                            <td>{{ $weekzoneDetail->day4 ? \App\Models\Dayzone::where('ID', $weekzoneDetail->day4)->value('Name') ?? 'None' : 'None' }}
                            </td>
                            <td>{{ $weekzoneDetail->day5 ? \App\Models\Dayzone::where('ID', $weekzoneDetail->day5)->value('Name') ?? 'None' : 'None' }}
                            </td>
                            <td>{{ $weekzoneDetail->day6 ? \App\Models\Dayzone::where('ID', $weekzoneDetail->day6)->value('Name') ?? 'None' : 'None' }}
                            </td>
                            <td>{{ $weekzoneDetail->day7 ? \App\Models\Dayzone::where('ID', $weekzoneDetail->day7)->value('Name') ?? 'None' : 'None' }}
                            </td>
                            <td>
                                <a href="{{ route('weekzonedetail.edit', $weekzoneDetail->ID) }}"
                                    class="btn btn-warning btn-sm">Edit</a>
                                {{-- <form action="{{ route('weekzonedetail.destroy', $weekzoneDetail->ID) }}"
                                method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm"
                                    onclick="return confirm('Are you sure you want to delete this weekzone detail?')">Delete</button>
                                </form> --}}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $weekzoneDetails->links() }}
            </div>
        </div>
    </div>

    <!-- Push to API Modal -->
    <!-- Push to API Modal -->
    <div class="modal fade" id="pushModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form action="{{ route('weekzonedetail.push-to-api') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Push Weekzone to Multiple Devices</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">

                        <!-- Pilih Device (Checkbox) -->
                        <div class="form-group">
                            <label class="font-weight-bold text-danger">Pilih Device (wajib)</label>
                            <div class="border rounded p-3"
                                style="max-height: 250px; overflow-y: auto; background:#f8f9fa;">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="selectAllDevices">
                                    <label class="form-check-label font-weight-bold text-primary"
                                        for="selectAllDevices">
                                        Pilih Semua Device
                                    </label>
                                </div>
                                <hr class="my-2">
                                @foreach($deviceGates as $device)
                                <div class="form-check">
                                    <input class="form-check-input device-checkbox" type="checkbox" name="sn[]"
                                        value="{{ $device->sn }}" id="device_{{ $device->sn }}">
                                    <label class="form-check-label" for="device_{{ $device->sn }}">
                                        <strong>{{ $device->name }}</strong>
                                        <small class="text-muted d-block">SN: {{ $device->sn }} | Type:
                                            {{ $device->type }}</small>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                            @error('sn')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <hr>

                        <!-- Pilih Weekzone Detail (ID 1-8) -->
                        <div class="form-group">
                            <label class="font-weight-bold text-danger">Pilih Weekzone (ID 1-8)</label>
                            <div class="row">
                                @foreach($weekzoneDetailsForApi as $detail)
                                <div class="col-md-6 mb-3">
                                    <div class="border rounded p-3 bg-light shadow-sm">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="weekzone_id[]"
                                                value="{{ $detail->ID }}" id="week_{{ $detail->ID }}">
                                            <label class="form-check-label font-weight-bold"
                                                for="week_{{ $detail->ID }}">
                                                Week {{ $detail->ID }}: {{ $detail->weekzone->Name ?? 'N/A' }}
                                            </label>
                                        </div>
                                        <small class="d-block text-muted mt-2">
                                            @php
                                            $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                                            $fields = ['day1', 'day2', 'day3', 'day4', 'day5', 'day6', 'day7'];
                                            @endphp
                                            @foreach($days as $i => $day)
                                            @php
                                            $field = $fields[$i];
                                            $dayzone = $detail->{$field} ? $detail->{"day" . ($i + 1)} : null;
                                            $name = $dayzone?->Name ?? ($detail->{$field} ? 'ID:' . $detail->{$field} :
                                            'None');
                                            $class = $dayzone ? 'badge-success' : 'badge-secondary';
                                            @endphp
                                            <span class="badge {{ $class }} mr-1 mb-1">
                                                {{ $day }}: {{ $name }}
                                            </span>
                                            @endforeach
                                        </small>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @error('weekzone_id')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Push ke API
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
<!-- jQuery + Bootstrap JS (WAJIB) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Select All Devices
document.getElementById('selectAllDevices')?.addEventListener('change', function() {
    document.querySelectorAll('.device-checkbox').forEach(cb => {
        cb.checked = this.checked;
    });
});

// DataTable
$(document).ready(function() {
    $('#dataTable').DataTable({
        pageLength: 10,
        ordering: true,
        searching: true,
        paging: true,
        info: true
    });
});
</script>
@endsection
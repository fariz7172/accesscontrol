@extends('layout_background.app_layouts')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-slash text-primary"></i> Management Invalid User Log
        </h1>
        <a href="#" id="exportExcel" class="btn btn-success btn-icon-split shadow-sm">
            <span class="icon text-white-50"><i class="fas fa-file-excel"></i></span>
            <span class="text">Export Excel</span>
        </a>
    </div>

    <!-- Filter -->
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-header py-3 bg-gradient-primary text-white">
            <h6 class="m-0 font-weight-bold"><i class="fas fa-filter"></i> Filter Data</h6>
        </div>
        <div class="card-body">
            <form id="dateFilterForm" action="{{ route('logInvalid') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label text-gray-700"><i class="far fa-calendar-alt"></i> From Date</label>
                        <input type="date" class="form-control form-control-sm border-primary" name="date_from" id="date_from"
                               value="{{ old('date_from', request('date_from', today()->format('Y-m-d'))) }}">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label text-gray-700"><i class="far fa-calendar-alt"></i> TO Date</label>
                        <input type="date" class="form-control form-control-sm border-primary" name="date_to" id="date_to"
                               value="{{ old('date_to', request('date_to', today()->format('Y-m-d'))) }}">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label text-gray-700"><i class="fas fa-microchip"></i> Device Name</label>
                        <select name="device_name" id="device_name" class="form-control form-control-sm border-primary">
                            <option value="">All Device</option>
                            @foreach($devices as $device)
                                <option value="{{ $device }}" {{ request('device_name') === $device ? 'selected' : '' }}>
                                    {{ $device }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <button type="submit" class="btn btn-primary btn-block shadow-sm">
                            <i class="fas fa-search"></i> View Data
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Notifikasi Update -->
    <div id="updateNotification" class="alert alert-info alert-dismissible fade show d-none shadow-sm" role="alert">
        <i class="fas fa-bell"></i> <strong>Update!</strong> New log invalid .
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>

    <!-- Tabel -->
    <div class="card shadow mb-4 border-left-danger">
        <div class="card-header py-3 bg-gradient-dark text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-table"></i> List Invalid Log
                <span class="badge badge-light float-right">Total: {{ $logData->total() }} data</span>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="logTable">
                    <thead class="thead-dark">
                        <tr>
                            <th width="5%">No</th>
                            <th>User Name</th>
                            <th>Event Time</th>
                            <th>Status</th>
                            <th>Device Name</th>
                            <th>Card</th>
                            <th width="12%">Foto</th>
                        </tr>
                    </thead>
                    <tbody id="logTableBody">
                     <!-- Di dalam tbody (bagian awal load) -->
@forelse($logData as $key => $log)
    <tr data-timestamp="{{ $log->TM_EVENT }}">
        <td>{{ $logData->firstItem() + $key }}</td>
        <td><strong class="{{ $log->userProfile?->NAME ? '' : 'text-danger' }}">{{ $log->userProfile?->NAME ?? 'NOT REGISTERED' }}</strong></td>
        <td>{{ \Carbon\Carbon::parse($log->TM_EVENT)->format('d/m/Y H:i:s') }}</td>
        <td><span class="badge badge-danger badge-pill px-3 py-2">INVALID ACCESS</span></td>
        <td><span class="text-primary font-weight-bold">{{ $log->deviceGate?->name ?? $log->DEVICESN ?? 'Unknown Device' }}</span></td>
        <td><code>{{ $log->card ?? '-' }}</code></td>
        <td class="text-center">
            @if($log->userProfile?->photo)
                @php
                    $cleanBase64 = preg_replace('#^data:image/\w+;base64,#i', '', $log->userProfile->photo);
                @endphp
                <img src="data:image/jpeg;base64,{{ $cleanBase64 }}"
                     class="img-thumbnail rounded"
                     style="width:60px;height:60px;object-fit:cover;">
            @else
                <span class="text-muted">No Image</span>
            @endif
        </td>
    </tr>
@empty
    <tr><td colspan="7" class="text-center text-muted py-5">No Data invalid.</td></tr>
@endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $logData->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const $ = jQuery;
    const todayStr = new Date().toISOString().split('T')[0];

    // Simpan filter ke localStorage
    function saveFilters() {
        localStorage.setItem('invalid_date_from', $('#date_from').val() || '');
        localStorage.setItem('invalid_date_to', $('#date_to').val() || '');
        localStorage.setItem('invalid_device_name', $('#device_name').val() || '');
    }
    function loadFilters() {
        $('#date_from').val(localStorage.getItem('invalid_date_from') || todayStr);
        $('#date_to').val(localStorage.getItem('invalid_date_to') || todayStr);
        $('#device_name').val(localStorage.getItem('invalid_device_name') || '');
    }
    loadFilters();

    $('#dateFilterForm').on('submit', function(e) {
        e.preventDefault();
        saveFilters();
        window.location = '{{ route("logInvalid") }}?' + $(this).serialize();
    });

    $('#exportExcel').on('click', function(e) {
        e.preventDefault();
        saveFilters();
        const params = new URLSearchParams({
            date_from: $('#date_from').val() || '',
            date_to: $('#date_to').val() || '',
            device_name: $('#device_name').val() || ''
        });
        window.location = '{{ route("exportInvalidUserLogs") }}?' + params;
    });

    // Real-time Polling
    function pollLogs() {
        const lastTimestamp = $('#logTableBody tr:first').data('timestamp') || '';

        const params = new URLSearchParams({
            last_timestamp: lastTimestamp,
            date_from: $('#date_from').val() || '',
            date_to: $('#date_to').val() || '',
            device_name: $('#device_name').val() || ''
        });

        fetch('{{ route("getLatestLogsInvalid") }}?' + params, {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(logs => {
            if (logs.length > 0) {
                logs.forEach(log => {
                    const photoHtml = log.photo_base64
                        ? `<img src="data:image/jpeg;base64,${log.photo_base64}" class="img-thumbnail rounded" style="width:60px;height:60px;object-fit:cover;">`
                        : '<span class="text-muted">No Image</span>';

                    const userClass = log.is_registered ? '' : 'text-danger';

                    const row = `
                        <tr data-timestamp="${log.TM_EVENT_RAW}">
                            <td></td>
                            <td><strong class="${userClass}">${log.user_name}</strong></td>
                            <td>${log.TM_EVENT}</td>
                            <td><span class="badge badge-danger badge-pill px-3 py-2">INVALID ACCESS</span></td>
                            <td><span class="text-primary font-weight-bold">${log.device_name}</span></td>
                            <td><code>${log.card}</code></td>
                            <td class="text-center">${photoHtml}</td>
                        </tr>`;

                    $('#logTableBody').prepend(row);
                });

                // Hapus baris lama jika lebih dari 50
                while ($('#logTableBody tr').length > 50) {
                    $('#logTableBody tr:last').remove();
                }

                // Update nomor urut
                $('#logTableBody tr').each(function(i) {
                    $(this).find('td:first').text(i + 1);
                });

                // Tampilkan notifikasi
                $('#updateNotification').removeClass('d-none').addClass('show');
                setTimeout(() => $('#updateNotification').alert('close'), 5000);
            }
        })
        .catch(err => console.error('Polling error:', err))
        .finally(() => setTimeout(pollLogs, 8000));
    }

    // Mulai polling
    pollLogs();
});
</script>
@endsection
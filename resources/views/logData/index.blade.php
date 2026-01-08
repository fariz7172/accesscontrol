@extends('layout_background.app_layouts')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Management User Log</h1>

    <!-- Filter Form dan Excel Button -->
    <div class="d-flex py-2 px-2 sm-3" id="filterForm">
        <form id="dateFilterForm" action="{{ route('logUser') }}" method="GET" class="d-flex md-3 sm-3">
           <input type="date" class="form-control mr-2" name="date_from" id="date_from"
    value="{{ old('date_from', request('date_from', today()->format('Y-m-d'))) }}">

<input type="date" class="form-control mr-2" name="date_to" id="date_to"
    value="{{ old('date_to', request('date_to', today()->format('Y-m-d'))) }}">
            <select name="branch_id" id="branch_id" class="form-control mr-2">
                <option value="">All Branches</option>
                @foreach($branches as $branch)
                <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                    {{ $branch->name }}
                </option>
                @endforeach
            </select>
            <select name="dep_id" id="dep_id" class="form-control mr-2">
                <option value="">All Departments</option>
                @foreach($departments as $department)
                <option value="{{ $department->id }}" {{ request('dep_id') == $department->id ? 'selected' : '' }}>
                    {{ $department->name }}
                </option>
                @endforeach
            </select>
            
            <select name="device_name" id="device_name" class="form-control">
                    <option value="">All Devices</option>
                    @foreach($devices as $deviceName)
                        <option value="{{ $deviceName }}" {{ request('device_name') == $deviceName ? 'selected' : '' }}>
                            {{ $deviceName }}
                        </option>
                    @endforeach
            </select>

            <button type="submit" class="btn btn-primary mb-3 sm-3" style="height:40px;">Filter</button>
        </form>
        <div class="row">
            <div class="col mb-3"></div>
            <a href="#" id="exportExcel" class="btn btn-success mb-3" style="height:40px;">Excel</a>
        </div>
    </div>

    <!-- Notifikasi untuk pembaruan data -->
    <div id="updateNotification" class="alert alert-success alert-dismissible fade show d-none" role="alert">
        New logs have been added!
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="logTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>User Name</th>
                            <th>Branch</th>
                            <th>Department</th>
                            <th>Event Time</th>
                            <th>Status</th>
                            <th>Device Name</th>
                            <th>Card</th>
                            <th>User Picture</th>
                        </tr>
                    </thead>
                    <tbody id="logTableBody">
                        @foreach($logData as $key => $index)
                        <tr data-timestamp="{{ $index->TM_EVENT }}" data-user-addr="{{ $index->USER_ADDR }}">
                            <td>{{ $logData->firstItem() + $key }}</td>
                            <td>{{ $index->userProfile->NAME ?? 'N/A' }}</td>
                            <td>{{ $index->userProfile->branch->name ?? 'N/A' }}</td>
                            <td>{{ $index->userProfile->department->name ?? 'N/A' }}</td>
                            <td>{{ $index->TM_EVENT }}</td>
                            <td class="status-cell">
                                @php
                                    $status = $index->stat == '1' ? 'CARD EXPIRED' : ($index->stat == '0' ? 'CARD ACTIVE' : 'N/A');
                                    // Simpan status ke localStorage
                                    echo "<script>localStorage.setItem('status_{$index->USER_ADDR}_{$index->TM_EVENT}', '$status');</script>";
                                @endphp
                                <span class="{{ $status == 'CARD EXPIRED' ? 'text-danger' : ($status == 'CARD ACTIVE' ? 'text-success' : '') }}">{{ $status }}</span>
                            </td>
                            <td>{{ $index->device_name }}</td>
                            <td>{{ $index->userProfile->Card ?? 'N/A' }}</td>
                            <td>
                                @if($index->userProfile && $index->userProfile->photo)
                                <img src="{{ strpos($index->userProfile->photo, 'data:image') === 0 ? $index->userProfile->photo : 'data:image/jpeg;base64,' . $index->userProfile->photo }}"
                                    alt="User Photo"
                                    style="max-width: 100px; max-height: 100px;">
                                @else
                                No Image
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $logData->appends(['date_from' => request('date_from'), 'date_to' => request('date_to'), 'branch_id' => request('branch_id'), 'dep_id' => request('dep_id')])->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Di head atau sebelum </body> --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- Inject semua route yang dibutuhkan JS --}}
<script>
    window.logDataRoutes = {
        index: "{{ route('logUser') }}",
        export: "{{ route('exportUserLogs') }}",
        latestLogs: "{{ route('getLatestLogs') }}",
        getCounts: "{{ route('getCounts') }}"
    };
</script>

{{-- Include JS eksternal --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('js/logData/logData.js') }}?v={{ time() }}"></script>

@endsection
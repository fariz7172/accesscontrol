@extends('layout_background.app_layouts')

@section('content')
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

    <ul class="nav nav-tabs mb-4" id="statusTab" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('user-status.index') }}">
            <i class="fas fa-users"></i> Real-Time Status
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('user-status.hourly') }}">
            <i class="fas fa-clock"></i> Hourly
        </a>
    </li>
</ul>

    <h1>Summary Status (IN/OUT)</h1>

    <!-- Form Filter -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Filter Data</h5>
        </div>
        <div class="card-body">
            <form method="GET" id="filterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label fw-bold text-primary">Start Date</label>
                        <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label fw-bold text-primary">End Date</label>
                        <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label fw-bold text-primary">Department</label>
                        <select name="department" id="department" class="form-control form-control-sm">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->name }}" {{ $department == $dept->name ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label fw-bold text-primary">Branch</label>
                        <select name="branch" id="branch" class="form-control form-control-sm">
                            <option value="">All Branches</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->name }}" {{ $branch == $b->name ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label fw-bold text-primary">Gate / Device</label>
                        <select name="gate" id="gate" class="form-control form-control-sm">
                            <option value="">All Gates</option>
                            @foreach($gates as $g)
                                <option value="{{ $g->name }}" {{ $gate == $g->name ? 'selected' : '' }}>{{ $g->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    
                    <div class="col-12 col-lg-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                        <a href="{{ route('user-status.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </div>
               
               <div class="mt-3">
    <button type="button" class="btn btn-success shadow" id="exportExcelBtn">
        Export Excel
    </button>
</div>

            </form>
        </div>
    </div>

    <!-- Cards -->
    <div class="row mb-4">
        <div class="col-md-3"><div class="card text-center bg-success text-white"><div class="card-body"><h5>Total User IN ROOM</h5><h2 id="total-in">{{ $userStatuses->where('status_display', 'IN')->count() }}</h2></div></div></div>
        <div class="col-md-3"><div class="card text-center bg-info text-white"><div class="card-body"><h5>Total Entry IN</h5><h2 id="total-entry-in">{{ $totalEntryIn }}</h2></div></div></div>
        <div class="col-md-3"><div class="card text-center bg-warning text-dark"><div class="card-body"><h5>Total User OUT</h5><h2 id="total-out">{{ $userStatuses->where('status_display', 'OUT')->count() }}</h2></div></div></div>
        <div class="col-md-3"><div class="card text-center bg-danger text-white"><div class="card-body"><h5>Total Entry OUT</h5><h2 id="total-entry-out">{{ $totalEntryOut }}</h2></div></div></div>
    </div>

    <div class="row">
        <!-- Tabel Status Terakhir -->
        <div class="col-lg-6">
            <p><strong>User Last Checked</strong></p>
            <div class="table-responsive">
                <table class="table table-striped table-sm" id="status-table">
                    <thead class="table-dark"><tr><th>Name</th><th>Last Event</th><th>Status</th><th>Dept</th></tr></thead>
                    <tbody id="table-body">
                        @forelse($userStatuses as $status)
                            <tr>
                                <td>{{ $status->user_name ?? 'N/A' }}</td>
                                <td>{{ $status->last_event_formatted }}</td>
                                <td>
                                    @php
                                        $class = $status->status_display === 'IN' ? 'bg-success' : ($status->status_display === 'OUT' ? 'bg-danger' : 'bg-warning text-dark');
                                    @endphp
                                    <span class="badge {{ $class }}">{{ $status->status_display }}</span>
                                </td>
                                <td>{{ $status->dept_name ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center">No data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tabel Log -->
        <div class="col-lg-6">
            <p><strong>Log Data</strong></p>
            <div class="table-responsive">
                <table class="table table-striped table-sm" id="log-table">
                    <thead class="table-dark"><tr><th>User</th><th>Time</th><th>Device</th><th>Status</th><th>Dept</th></tr></thead>
                    <tbody id="log-body">
                        @forelse($allUserLogs as $log)
                            <tr>
                                <td>{{ $log->user_name }}</td>
                                <td>{{ $log->tm_event_formatted }}</td>
                                <td>{{ $log->device_name }}</td>
                                <td>
                                    @php
                                        $class = $log->status_display === 'IN' ? 'bg-success' : ($log->status_display === 'OUT' ? 'bg-danger' : 'bg-warning text-dark');
                                    @endphp
                                    <span class="badge {{ $class }}">{{ $log->status_display }}</span>
                                </td>
                                <td>{{ $log->dept_name ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center">No log</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div id="log-pagination">{{ $allUserLogs->links() }}</div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


{{-- Kirim URL route ke JavaScript --}}
<script>
    window.userStatusConfig = {
        latestUrl: '{{ route("user-status.latest") }}'
    };
</script>

<script src="{{ asset('js/userStatus/userStatus.js') }}"></script>


<script>
document.getElementById('exportExcelBtn')?.addEventListener('click', function () {
    if (confirm('Export semua data log sesuai filter saat ini ke Excel?')) {
        const params = new URLSearchParams({
            start_date: document.getElementById('start_date').value,
            end_date:   document.getElementById('end_date').value,
            department: document.getElementById('department').value || '',
            branch:     document.getElementById('branch').value     || '',
            gate:       document.getElementById('gate').value       || '',
            search:     document.querySelector('input[name="search"]')?.value || ''
        });

        window.location.href = '{{ route("user-status.export") }}?' + params.toString();
    }
});
</script>

@endsection
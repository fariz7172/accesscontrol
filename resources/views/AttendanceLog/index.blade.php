@extends('layout_background.app_layouts')

@section('content')
<div class="container-fluid">

    <!-- Header + Export Button -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-clipboard-list text-primary"></i> Attendance Report
        </h1>
        <a href="{{ route('attendanceLog.export', request()->query()) }}" 
           class="btn btn-success btn-icon-split shadow-sm">
            <span class="icon text-white-50"><i class="fas fa-file-excel"></i></span>
            <span class="text">Export to Excel</span>
        </a>
    </div>

    <!-- Filter Card -->
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-header py-3 bg-gradient-primary text-white">
            <h6 class="m-0 font-weight-bold"><i class="fas fa-filter"></i> Filter Report</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('attendanceLog') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                        <label class="form-label"><i class="far fa-calendar-alt"></i> From Date</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}" required>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                        <label class="form-label"><i class="far fa-calendar-alt"></i> To Date</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}" required>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                        <label class="form-label"><i class="fas fa-building"></i> Branch</label>
                        <select name="branch_id" class="form-control form-control-sm">
                            <option value="">All Branches</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                        <label class="form-label"><i class="fas fa-users"></i> Department</label>
                        <select name="dep_id" class="form-control form-control-sm">
                            <option value="">All Departments</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}" {{ $depId == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                        <label class="form-label"><i class="fas fa-microchip"></i> Device</label>
                        <select name="device_name" class="form-control form-control-sm">
                            <option value="">All Devices</option>
                            @foreach($devices as $name)
                                <option value="{{ $name }}" {{ $deviceName == $name ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3 col-lg-1">
                        <label class="form-label"><i class="fas fa-check-circle"></i> Status</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">All</option>
                            <option value="Present" {{ $status === 'Present' ? 'selected' : '' }}>Present</option>
                            <option value="Not Present" {{ $status === 'Not Present' ? 'selected' : '' }}>Absent</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3 col-lg-1">
                        <button type="submit" class="btn btn-primary btn-block shadow-sm">
                            <i class="fas fa-search"></i> Apply
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <!-- Detail Table -->
        <div class="col-lg-7 mb-4">
            <div class="card shadow border-left-success h-100">
                <div class="card-header py-3 bg-gradient-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-list-ul"></i> Attendance Details
                        <span class="badge badge-light float-right">Total: {{ $attendanceData->total() }} records</span>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm">
                            <thead class="thead-light text-center">
                                <tr>
                                    <th>No</th>
                                    <th>Name</th>
                                    <th>Card No</th>
                                    <th>Attendance Time</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendanceData as $data)
                                    <tr>
                                        <td class="text-center">{{ $data['no'] }}</td>
                                        <td><strong>{{ $data['name'] }}</strong></td>
                                        <td><code>{{ $data['member'] }}</code></td>
                                        <td class="text-center">
                                            @if($data['attend'] !== '-')
                                                <span class="text-success">{{ $data['attend'] }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>{{ $data['department'] }}</td>
                                        <td class="text-center">
                                            @if($data['desc'] === 'Present')
                                                <span class="badge badge-success px-3 py-2">Present</span>
                                            @else
                                                <span class="badge badge-danger px-3 py-2">Absent</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-5">No data found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $attendanceData->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Table -->
        <div class="col-lg-5">
            <div class="card shadow border-left-info h-100">
                <div class="card-header py-3 bg-gradient-info text-white">
                    <h6 class="m-0 font-weight-bold"><i class="fas fa-chart-pie"></i> Daily Summary</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="thead-light text-center">
                                <tr>
                                    <th>Day & Date</th>
                                    <th>Present</th>
                                    <th>Absent</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($summaryData as $s)
                                    <tr class="text-center">
                                        <td><strong>{{ $s['day'] }}</strong></td>
                                        <td class="text-success font-weight-bold">{{ $s['total_present'] }}</td>
                                        <td class="text-danger font-weight-bold">{{ $s['total_not_present'] }}</td>
                                        <td>
                                            <span class="badge badge-{{ $s['total_present'] >= $s['total_not_present'] ? 'success' : 'warning' }} px-3 py-2">
                                                {{ $s['attendance_percentage'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
@endsection
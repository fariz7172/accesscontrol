@extends('layout_background.app_layouts')

@section('content')
<ul class="nav nav-tabs mb-4" id="statusTab" role="tablist">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('user-status.index') ? 'active' : '' }}"
           href="{{ route('user-status.index') }}">
            <i class="fas fa-users"></i> Real-Time Status
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('user-status.hourly') ? 'active' : '' }}"
           href="{{ route('user-status.hourly') }}">
            <i class="fas fa-clock"></i> Hourly
        </a>
    </li>
</ul>

<h1>Hourly Access Report</h1>

<!-- Filter -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Filter Hourly Data</h5>
    </div>
    <div class="card-body">
        <form method="GET" id="hourlyFilterForm">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label fw-bold text-primary">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="form-control form-control-sm" required>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label fw-bold text-primary">End Date</label>
                    <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="form-control form-control-sm" required>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label fw-bold text-primary">Gate / Device</label>
                    <select name="gate" id="gate" class="form-control form-control-sm">
                        <option value="">All Gates</option>
                        @foreach($gates as $g)
                            <option value="{{ $g->name }}" {{ $gate == $g->name ? 'selected' : '' }}>{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-lg-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                    <a href="{{ route('user-status.hourly') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Hourly -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">Hourly Access Data</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Hour</th>
                        <th>Total Access</th>
                        <th>Valid (IN)</th>
                        <th>Invalid (OUT)</th>
                        <th>Device</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fullHours as $row)
                        <tr @if($row['total_access'] == 0) class="table-secondary" @endif>
                            <td><strong>{{ $row['hour_display'] }}</strong></td>
                            <td>{{ $row['total_access'] }}</td>
                            <td><span class="badge bg-success text-white">{{ $row['valid_in'] }}</span></td>
                            <td><span class="badge bg-danger text-white">{{ $row['invalid_out'] }}</span></td>
                            <td><small>{{ $row['devices'] }}</small></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">No data available</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="table-dark">
                    <tr>
                        <th>Total</th>
                        <th>{{ $fullHours->sum('total_access') }}</th>
                        <th>{{ $fullHours->sum('valid_in') }}</th>
                        <th>{{ $fullHours->sum('invalid_out') }}</th>
                        <th>-</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        // Validasi tanggal sebelum submit
        $('#hourlyFilterForm').on('submit', function (e) {
            e.preventDefault();
            const start = $('#start_date').val();
            const end = $('#end_date').val();
            if (new Date(end) < new Date(start)) {
                alert('End date must be greater than or equal to Start date');
                return;
            }
            this.submit(); // submit biasa (reload)
        });

        // Auto submit saat ganti input (dengan debounce 500ms)
        $('#start_date, #end_date, #gate').on('change', function () {
            clearTimeout(window.hourlyTimeout);
            window.hourlyTimeout = setTimeout(() => {
                $('#hourlyFilterForm').submit();
            }, 500);
        });
    });
</script>
@endsection
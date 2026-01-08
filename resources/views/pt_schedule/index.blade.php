@extends('layout_background.app_layouts')

@section('content')
<div class="container-fluid py-4">
    <!-- Success and Error Alerts -->
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {!! session('success') !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Page Header -->
    <h1 class="h3 mb-4 text-gray-800">Personal Trainer Schedules</h1>

    <!-- Filter Form -->
    <form method="GET" class="mb-3">
        <div class="row align-items-end">
            <div class="col-md-3">
                <label for="start_date">Start Date:</label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="form-control d-inline w-auto">
            </div>
            <div class="col-md-3">
                <label for="end_date">End Date:</label>
                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="form-control d-inline w-auto">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-4" id="scheduleTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="schedules-tab" data-bs-toggle="tab" href="#schedules" role="tab" aria-controls="schedules" aria-selected="true">Schedules</a>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="scheduleTabsContent">
        <!-- Schedules Tab -->
        <div class="tab-pane fade show active" id="schedules" role="tabpanel" aria-labelledby="schedules-tab">
            <div class="card-header py-3 d-flex justify-content-between">
                <a href="{{ route('pt_schedule.create') }}" class="btn btn-primary btn-sm">Add Schedule</a>
                <a href="{{ route('pt_schedule.export', request()->only(['start_date', 'end_date'])) }}" class="btn btn-success btn-sm">
                     Export to Excel
                </a>

            </div>

            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Personal Trainer</th>
                                    <th>Member</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Booked At</th>
                                    <th>Status</th>
                                    <th>Note</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($schedules as $schedule)
                                <tr>
                                    <td>{{ $schedule->ID ?? 'N/A' }}</td>
                                    <td>{{ $schedule->personalTrainer->NAME ?? 'N/A' }}</td>
                                    <td>{{ $schedule->member->NAME ?? 'N/A' }}</td>
                                    <td>{{ $schedule->START_TIME ?? '-' }}</td>
                                    <td>{{ $schedule->END_TIME ?? '-' }}</td>
                                    <td>{{ $schedule->BOOKED_AT ?? '-' }}</td>
                                    <td>{{ $schedule->getStatusTextAttribute() }}</td>
                                    <td>{{ $schedule->NOTE ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('pt_schedule.edit', $schedule->ID) }}" class="btn btn-warning btn-sm">Edit</a>
                                        <form action="{{ route('pt_schedule.destroy', $schedule->ID) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this schedule?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination Links -->
                    <div class="mt-3">
                        {{ $schedules->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection
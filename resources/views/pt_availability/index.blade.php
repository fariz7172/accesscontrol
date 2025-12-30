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
    <h1 class="h3 mb-4 text-gray-800">Personal Trainer Availability</h1>

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-4" id="availabilityTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="availabilities-tab" data-bs-toggle="tab" href="#availabilities" role="tab" aria-controls="availabilities" aria-selected="true">Availabilities</a>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="availabilityTabsContent">
        <!-- Availabilities Tab -->
        <div class="tab-pane fade show active" id="availabilities" role="tabpanel" aria-labelledby="availabilities-tab">
            <div class="card-header py-3 d-flex">
                <a href="{{ route('pt_availability.create') }}" class="btn btn-primary btn-sm float-end mr-3">Add Availability</a>
            </div>

            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Personal Trainer</th>
                                    <th>Day of Week</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($availabilities as $availability)
                                <tr>
                                    <td>{{ $availability->ID ?? 'N/A' }}</td>
                                    <td>{{ $availability->personalTrainer->NAME ?? 'N/A' }}</td>
                                    <td>{{ $availability->day_name ?? 'N/A' }}</td>
                                    <td>{{ $availability->START_TIME ?? '-' }}</td>
                                    <td>{{ $availability->END_TIME ?? '-' }}</td>
                                    <td>{{ $availability->IS_ACTIVE ? 'Active' : 'Inactive' }}</td>
                                    <td>
                                        <a href="{{ route('pt_availability.edit', $availability->ID) }}" class="btn btn-warning btn-sm">Edit</a>
                                        <form action="{{ route('pt_availability.destroy', $availability->ID) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this availability?')">Delete</button>
                                        </form>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection
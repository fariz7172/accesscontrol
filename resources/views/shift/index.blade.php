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
    <h1 class="h3 mb-4 text-gray-800">Shift Management</h1>

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-4" id="shiftTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="shifts-tab" data-bs-toggle="tab" href="#shifts" role="tab" aria-controls="shifts" aria-selected="true">Shifts</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="shift-summary-tab" data-bs-toggle="tab" href="#shift-summary" role="tab" aria-controls="shift-summary" aria-selected="false">Shift Patterns</a>
        </li>
        <!-- <li class="nav-item">
            <a class="nav-link" id="add-shift-user-tab" data-bs-toggle="tab" href="#add-shift-user" role="tab" aria-controls="add-shift-user" aria-selected="false">Add Shift User</a>
        </li> -->
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="shiftTabsContent">
        <!-- Shifts Tab -->
        <div class="tab-pane fade show active" id="shifts" role="tabpanel" aria-labelledby="shifts-tab">
            <div class="card-header py-3  d-flex">

                <a href="{{ route('shift.create') }}" class="btn btn-primary btn-sm float-end mr-3">Add Shift</a>
            </div>

            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>No Id</th>
                                    <th>Shift No</th>
                                    <th>Shift Name</th>
                                    <th>Begin Time</th>
                                    <th>Break Time</th>
                                    <th>Resume Time</th>
                                    <th>Out Time</th>
                                    <th>Type</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($shifts as $shift)
                                <tr>
                                    <td>{{ $shift->id ?? 'N/A' }}</td>
                                    <td>{{ $shift->ShiftNo ?? 'N/A' }}</td>
                                    <td>{{ $shift->ShiftName ?? 'N/A' }}</td>
                                    <td>{{ $shift->Begin_Time ?? '-' }}</td>
                                    <td>{{ $shift->Break_Time ?? '-' }}</td>
                                    <td>{{ $shift->Resume_Time ?? '-' }}</td>
                                    <td>{{ $shift->Out_time ?? '-' }}</td>
                                    <td>{{ $shift->Tipe == 1 ? 'Working Days' : ($shift->Tipe == 2 ? 'Off Days' : '-') }}</td>
                                    <td>
                                        <a href="{{ route('shift.edit', $shift->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                        <form action="{{ route('shift.destroy', $shift->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this shift?')">Delete</button>
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

        <!-- Shift Patterns Tab -->
        <div class="tab-pane fade" id="shift-summary" role="tabpanel" aria-labelledby="shift-summary-tab">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Shift Pattern List</h6>
                    <a href="{{ route('shiftpattern.create') }}" class="btn btn-primary btn-sm float-end">Create New Pattern</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="shiftPatternTable">
                            <thead>
                                <tr>
                                    <th>Pattern Name</th>
                                    <th>Pattern Type</th>
                                    <th>Day 1</th>
                                    <th>Day 2</th>
                                    <th>Day 3</th>
                                    <th>Day 4</th>
                                    <th>Day 5</th>
                                    <th>Day 6</th>
                                    <th>Day 7</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($patterns as $pattern)
                                <tr>
                                    <td>{{ $pattern->PatternName ?? '-' }}</td>
                                    <td>{{ $pattern->PatternType ?? '-' }}</td>
                                    <td>{{ $pattern->shiftDay1->ShiftName ?? '-' }}</td>
                                    <td>{{ $pattern->shiftDay2->ShiftName ?? '-' }}</td>
                                    <td>{{ $pattern->shiftDay3->ShiftName ?? '-' }}</td>
                                    <td>{{ $pattern->shiftDay4->ShiftName ?? '-' }}</td>
                                    <td>{{ $pattern->shiftDay5->ShiftName ?? '-' }}</td>
                                    <td>{{ $pattern->shiftDay6->ShiftName ?? '-' }}</td>
                                    <td>{{ $pattern->shiftDay7->ShiftName ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('shiftpattern.edit', $pattern->Id) }}" class="btn btn-warning btn-sm">Edit</a>
                                        <form action="{{ route('shiftpattern.destroy', $pattern->Id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
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

        <!-- Add Shift User Tab -->
        <div class="tab-pane fade" id="add-shift-user" role="tabpanel" aria-labelledby="add-shift-user-tab">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">User Shift Patterns</h6>
                    <a href="{{ route('addshiftuser.create') }}" class="btn btn-primary btn-sm float-end">Assign New Pattern</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="addShiftUserTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Shift Pattern</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                <tr>
                                    <td>{{ $user->NAME ?? '-' }}</td>
                                    <td>{{ $user->shiftPattern ? $user->shiftPattern->PatternName : 'No Pattern' }}</td>
                                    <td>
                                        <a href="{{ route('addshiftuser.edit', $user->ID) }}" class="btn btn-warning btn-sm">Edit Pattern</a>
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
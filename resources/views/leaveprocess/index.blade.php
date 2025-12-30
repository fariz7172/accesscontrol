@extends('layout_background.app_layouts')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Leave Management</h1>

    <!-- Tab Bar -->
    <ul class="nav nav-tabs mb-4" id="leaveTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="leave-requests-tab" data-toggle="tab" href="#leave-requests" role="tab" aria-controls="leave-requests" aria-selected="true">Leave Requests</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="leave-types-tab" data-toggle="tab" href="#leave-types" role="tab" aria-controls="leave-types" aria-selected="false">Leave Types</a>
        </li>
    </ul>

    <div class="tab-content" id="leaveTabsContent">
        <!-- Leave Requests Tab -->
        <div class="tab-pane fade show active" id="leave-requests" role="tabpanel" aria-labelledby="leave-requests-tab">
            <!-- Filter Form -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <form action="{{ route('leaveprocess.index') }}" method="GET" class="form-inline mb-3">
                        <div class="form-group mr-2">
                            <label for="start_date" class="mr-2">Start Date:</label>
                            <input type="date" class="form-control" name="start_date" value="{{ request()->input('start_date', now()->format('Y-m-d')) }}">
                        </div>
                        <div class="form-group mr-2">
                            <label for="end_date" class="mr-2">End Date:</label>
                            <input type="date" class="form-control" name="end_date" value="{{ request()->input('end_date', now()->format('Y-m-d')) }}">
                        </div>
                        <button type="submit" class="btn btn-primary mr-2">Filter</button>
                        <button type="submit" class="btn btn-success" formaction="{{ route('leaveprocess.export') }}">Export to Excel</button>
                    </form>
                </div>
            </div>

            <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addModal">Add Leave Request</button>

            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>From Date</th>
                                    <th>To Date</th>
                                    <th>Notes</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leaveProcesses as $leaveProcess)
                                <tr>
                                    <td>{{ $leaveProcess->userProfile->NAME ?? 'N/A' }}</td>
                                    <td>{{ $leaveProcess->leaveType->Name ?? 'N/A' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($leaveProcess->FromDate)->format('Y-m-d') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($leaveProcess->ToDate)->format('Y-m-d') }}</td>
                                    <td>{{ $leaveProcess->Notes ?? '-' }}</td>
                                    <td>
                                        <form class="update-status-form" action="{{ route('leaveprocess.updateStatus', $leaveProcess->Id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" class="form-control status-select" data-id="{{ $leaveProcess->Id }}">
                                                <option value="0" {{ $leaveProcess->STATUS == 0 ? 'selected' : '' }}>Pending</option>
                                                <option value="1" {{ $leaveProcess->STATUS == 1 ? 'selected' : '' }}>Approved</option>
                                                <option value="2" {{ $leaveProcess->STATUS == 2 ? 'selected' : '' }}>Rejected</option>
                                                <option value="3" {{ $leaveProcess->STATUS == 3 ? 'selected' : '' }}>Cancelled</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        <a href="{{ route('leaveprocess.edit', $leaveProcess->Id) }}" class="btn btn-warning btn-sm">Edit</a>
                                        <form action="{{ route('leaveprocess.destroy', $leaveProcess->Id) }}" method="POST" class="delete-form" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{ $leaveProcesses->links() }}
                    </div>
                </div>
            </div>

            <!-- Modal Tambah Data -->
            <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form action="{{ route('leaveprocess.store') }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="addModalLabel">Add Leave Request</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="EmplID">Employee</label>
                                    <select class="form-control" name="EmplID" required>
                                        <option value="">Select Employee</option>
                                        @if($users->isNotEmpty())
                                        @foreach($users as $user)
                                        <option value="{{ $user->ID }}">{{ $user->NAME ?? 'N/A' }}</option>
                                        @endforeach
                                        @else
                                        <option value="" disabled>No employees available</option>
                                        @endif
                                    </select>
                                    @error('EmplID')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="leaveid">Leave Type</label>
                                    <select class="form-control" name="leaveid" required>
                                        <option value="">Select Leave Type</option>
                                        @if($leaveTypes->isNotEmpty())
                                        @foreach($leaveTypes as $leaveType)
                                        <option value="{{ $leaveType->Id }}">{{ $leaveType->Name ?? 'Unnamed Leave Type' }}</option>
                                        @endforeach
                                        @else
                                        <option value="" disabled>No leave types available</option>
                                        @endif
                                    </select>
                                    @error('leaveid')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="FromDate">From Date</label>
                                    <input type="date" class="form-control" name="FromDate" value="{{ old('FromDate', \Carbon\Carbon::today()->format('Y-m-d')) }}" required>
                                    @error('FromDate')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="ToDate">To Date</label>
                                    <input type="date" class="form-control" name="ToDate" value="{{ old('ToDate', \Carbon\Carbon::today()->format('Y-m-d')) }}" required>
                                    @error('ToDate')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="Notes">Notes</label>
                                    <textarea class="form-control" name="Notes" rows="4">{{ old('Notes') }}</textarea>
                                    @error('Notes')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                              
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Add Leave Request</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Leave Types Tab -->
        <div class="tab-pane fade" id="leave-types" role="tabpanel" aria-labelledby="leave-types-tab">
            <a href="{{ route('leavetype.create') }}" class="btn btn-primary mb-3">Add Leave Type</a>

            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="leaveTypeTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leaveTypes as $leaveType)
                                <tr>
                                    <td>{{ $leaveType->Name ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('leavetype.edit', $leaveType->Id) }}" class="btn btn-warning btn-sm">Edit</a>
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

<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- Kirim session message ke JS (aman, tanpa Blade di JS) --}}
<script>
    window.leaveProcessMessages = {
        success: {{ session('success') ? "'".addslashes(session('success'))."'" : 'null' }},
        error: {{ session('error') ? "'".addslashes(session('error'))."'" : 'null' }},
        validationErrors: @json($errors->all())
    };
</script>

{{-- Include JS eksternal --}}
<script src="{{ asset('js/leaveProccess/leaveProccess.js') }}?v={{ time() }}"></script>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



@endsection
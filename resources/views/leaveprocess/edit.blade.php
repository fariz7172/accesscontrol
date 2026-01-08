@extends('layout_background.app_layouts')

@section('content')
<div class="container-fluid">
    @if(session('success'))
    <div class="alert alert-success">
        {!! session('success') !!}
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
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

    <h1 class="h3 mb-2 text-gray-800">Edit Leave Request</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('leaveprocess.update', $leaveProcess->Id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="EmplID">Employee</label>
                    <select class="form-control" name="EmplID" required>
                        <option value="">Select Employee</option>
                        @if($users->isNotEmpty())
                        @foreach($users as $user)
                        <option value="{{ $user->ID }}" {{ old('EmplID', $leaveProcess->EmplID) == $user->ID ? 'selected' : '' }}>{{ $user->NAME }}</option>
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
                        <option value="{{ $leaveType->Id }}" {{ old('leaveid', $leaveProcess->leaveid) == $leaveType->Id ? 'selected' : '' }}>{{ $leaveType->Name }}</option>
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
                    <input type="date" class="form-control" name="FromDate" value="{{ old('FromDate', $leaveProcess->FromDate) }}" required>
                    @error('FromDate')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="ToDate">To Date</label>
                    <input type="date" class="form-control" name="ToDate" value="{{ old('ToDate', $leaveProcess->ToDate) }}" required>
                    @error('ToDate')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="Notes">Notes</label>
                    <textarea class="form-control" name="Notes" rows="4">{{ old('Notes', $leaveProcess->Notes) }}</textarea>
                    @error('Notes')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
              
                <button type="submit" class="btn btn-primary">Update Leave Request</button>
                <a href="{{ route('leaveprocess.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
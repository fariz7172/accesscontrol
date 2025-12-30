@extends('layout_background.app_layouts')

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4 text-gray-800">Assign Shift Pattern to User</h1>

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

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('addshiftuser.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="user_id" class="form-label">Select User</label>
                    <select name="user_id" class="form-control" id="user_id" required>
                        <option value="">Select a User</option>
                        @foreach ($users as $user)
                        <option value="{{ $user->ID }}" {{ old('user_id') == $user->ID ? 'selected' : '' }}>{{ $user->NAME }}</option>
                        @endforeach
                    </select>
                    @error('user_id')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="PatternID" class="form-label">Shift Pattern</label>
                    <select name="PatternID" class="form-control" id="PatternID">
                        <option value="">No Pattern</option>
                        @foreach ($patterns as $pattern)
                        <option value="{{ $pattern->Id }}" {{ old('PatternID') == $pattern->Id ? 'selected' : '' }}>{{ $pattern->PatternName }}</option>
                        @endforeach
                    </select>
                    @error('PatternID')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Assign Pattern</button>
                    <a href="{{ route('shift.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
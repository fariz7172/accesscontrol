@extends('layout_background.app_layouts')

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4 text-gray-800">Assign Shift Pattern for {{ $user->NAME }}</h1>

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
            <form action="{{ route('addshiftuser.update', $user->ID) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="PatternID" class="form-label">Shift Pattern</label>
                    <select name="PatternID" class="form-control" id="PatternID">
                        <option value="">No Pattern</option>
                        @foreach ($patterns as $pattern)
                        <option value="{{ $pattern->Id }}" {{ old('PatternID', $user->PatternID) == $pattern->Id ? 'selected' : '' }}>{{ $pattern->PatternName }}</option>
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
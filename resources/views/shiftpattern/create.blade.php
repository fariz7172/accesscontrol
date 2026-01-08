@extends('layout_background.app_layouts')

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4 text-gray-800">Create Shift Pattern</h1>

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
            <form action="{{ route('shiftpattern.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="PatternName" class="form-label">Pattern Name</label>
                            <input type="text" name="PatternName" class="form-control" id="PatternName" value="{{ old('PatternName') }}" required>
                            @error('PatternName')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="PatternType" class="form-label">Pattern Type</label>
                            <input type="number" name="PatternType" class="form-control" id="PatternType" value="{{ old('PatternType') }}">
                            @error('PatternType')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <h5 class="card-header bg-primary text-white">Set Shifts for Each Day</h5>
                    <div class="card-body">
                        <div class="row">
                            @for ($i = 1; $i <= 7; $i++)
                                <div class="col-md-4 mb-3">
                                <label for="pola{{ $i }}" class="form-label">Day {{ $i }}</label>
                                <select name="pola{{ $i }}" class="form-control" id="pola{{ $i }}">

                                    @foreach ($shifts as $shift)
                                    <option value="{{ $shift->id }}" {{ old('pola' . $i) == $shift->id ? 'selected' : '' }}>{{ $shift->ShiftName }}</option>
                                    @endforeach
                                </select>
                                @error('pola' . $i)
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                        </div>
                        @endfor
                    </div>
                </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Create Pattern</button>
            <a href="{{ route('shift.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
        </form>
    </div>
</div>
</div>
@endsection
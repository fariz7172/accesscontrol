@extends('layout_background.app_layouts')

@section('content')
<div class="container-fluid">
    @if(session('success'))
    <div class="alert alert-success">
        {!! session('success') !!}
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

    <h1 class="h3 mb-2 text-gray-800">Add Weekzone Detail</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('weekzonedetail.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="ID">ID</label>
                    <input type="number" class="form-control @error('ID') is-invalid @enderror" name="ID" value="{{ old('ID') }}" required>
                    @error('ID')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="wz">Weekzone</label>
                    <select class="form-control @error('wz') is-invalid @enderror" name="wz" required>
                        <option value="">Select Weekzone</option>
                        @foreach($weekzones as $weekzone)
                        <option value="{{ $weekzone->ID }}" {{ old('wz') == $weekzone->ID ? 'selected' : '' }}>{{ $weekzone->Name }}</option>
                        @endforeach
                    </select>
                    @error('wz')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Urutan baru: Sunday → Monday → Saturday -->
                <div class="form-group">
                    <label for="day1">Sunday </label>
                    <select class="form-control @error('day1') is-invalid @enderror" name="day1">
                        <option value="">None</option>
                        @foreach($dayzones as $dayzone)
                        <option value="{{ $dayzone->ID }}" {{ old('day1') == $dayzone->ID ? 'selected' : '' }}>{{ $dayzone->Name }}</option>
                        @endforeach
                    </select>
                    @error('day1')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="day2">Monday </label>
                    <select class="form-control @error('day2') is-invalid @enderror" name="day2">
                        <option value="">None</option>
                        @foreach($dayzones as $dayzone)
                        <option value="{{ $dayzone->ID }}" {{ old('day2') == $dayzone->ID ? 'selected' : '' }}>{{ $dayzone->Name }}</option>
                        @endforeach
                    </select>
                    @error('day2')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="day3">Tuesday </label>
                    <select class="form-control @error('day3') is-invalid @enderror" name="day3">
                        <option value="">None</option>
                        @foreach($dayzones as $dayzone)
                        <option value="{{ $dayzone->ID }}" {{ old('day3') == $dayzone->ID ? 'selected' : '' }}>{{ $dayzone->Name }}</option>
                        @endforeach
                    </select>
                    @error('day3')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="day4">Wednesday </label>
                    <select class="form-control @error('day4') is-invalid @enderror" name="day4">
                        <option value="">None</option>
                        @foreach($dayzones as $dayzone)
                        <option value="{{ $dayzone->ID }}" {{ old('day4') == $dayzone->ID ? 'selected' : '' }}>{{ $dayzone->Name }}</option>
                        @endforeach
                    </select>
                    @error('day4')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="day5">Thursday </label>
                    <select class="form-control @error('day5') is-invalid @enderror" name="day5">
                        <option value="">None</option>
                        @foreach($dayzones as $dayzone)
                        <option value="{{ $dayzone->ID }}" {{ old('day5') == $dayzone->ID ? 'selected' : '' }}>{{ $dayzone->Name }}</option>
                        @endforeach
                    </select>
                    @error('day5')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="day6">Friday </label>
                    <select class="form-control @error('day6') is-invalid @enderror" name="day6">
                        <option value="">None</option>
                        @foreach($dayzones as $dayzone)
                        <option value="{{ $dayzone->ID }}" {{ old('day6') == $dayzone->ID ? 'selected' : '' }}>{{ $dayzone->Name }}</option>
                        @endforeach
                    </select>
                    @error('day6')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="day7">Saturday </label>
                    <select class="form-control @error('day7') is-invalid @enderror" name="day7">
                        <option value="">None</option>
                        @foreach($dayzones as $dayzone)
                        <option value="{{ $dayzone->ID }}" {{ old('day7') == $dayzone->ID ? 'selected' : '' }}>{{ $dayzone->Name }}</option>
                        @endforeach
                    </select>
                    @error('day7')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary mt-3">Add Weekzone Detail</button>
                <a href="{{ route('weekzone.index') }}" class="btn btn-secondary mt-3">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
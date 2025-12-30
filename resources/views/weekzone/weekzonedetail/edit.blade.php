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

    <h1 class="h3 mb-2 text-gray-800">Edit Weekzone Detail</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('weekzonedetail.update', $weekzoneDetail->ID) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- FIELD ID: Disabled + Readonly + Hidden Input -->
                <div class="form-group">
                    <label for="ID">ID</label>
                    <div class="form-control bg-light" style="pointer-events: none;">
                        <input type="number" 
                               class="border-0 bg-transparent w-100" 
                               value="{{ old('ID', $weekzoneDetail->ID) }}" 
                               disabled>
                    </div>
                    <!-- Hidden input agar tetap terkirim -->
                    <input type="hidden" name="ID" value="{{ old('ID', $weekzoneDetail->ID) }}">
                    @error('ID')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- FIELD WEEKZONE: Disabled + Readonly + Hidden Input -->
                <div class="form-group">
                    <label for="wz">Weekzone</label>
                    <div class="form-control bg-light" style="pointer-events: none;">
                        <select class="border-0 bg-transparent w-100" disabled>
                            <option value="">Select Weekzone</option>
                            @foreach($weekzones as $weekzone)
                            <option value="{{ $weekzone->ID }}" 
                                    {{ old('wz', $weekzoneDetail->wz) == $weekzone->ID ? 'selected' : '' }}>
                                {{ $weekzone->Name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Hidden input agar tetap terkirim -->
                    <input type="hidden" name="wz" value="{{ old('wz', $weekzoneDetail->wz) }}">
                    @error('wz')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Urutan baru: Sunday → Monday → Saturday -->
                <div class="form-group">
                    <label for="day1">Sunday </label>
                    <select class="form-control @error('day1') is-invalid @enderror" name="day1">
                        <option value="">None</option>
                        @foreach($dayzones as $dayzone)
                        <option value="{{ $dayzone->ID }}" 
                                {{ old('day1', $weekzoneDetail->day1) == $dayzone->ID ? 'selected' : '' }}>
                            {{ $dayzone->Name }}
                        </option>
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
                        <option value="{{ $dayzone->ID }}" 
                                {{ old('day2', $weekzoneDetail->day2) == $dayzone->ID ? 'selected' : '' }}>
                            {{ $dayzone->Name }}
                        </option>
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
                        <option value="{{ $dayzone->ID }}" 
                                {{ old('day3', $weekzoneDetail->day3) == $dayzone->ID ? 'selected' : '' }}>
                            {{ $dayzone->Name }}
                        </option>
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
                        <option value="{{ $dayzone->ID }}" 
                                {{ old('day4', $weekzoneDetail->day4) == $dayzone->ID ? 'selected' : '' }}>
                            {{ $dayzone->Name }}
                        </option>
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
                        <option value="{{ $dayzone->ID }}" 
                                {{ old('day5', $weekzoneDetail->day5) == $dayzone->ID ? 'selected' : '' }}>
                            {{ $dayzone->Name }}
                        </option>
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
                        <option value="{{ $dayzone->ID }}" 
                                {{ old('day6', $weekzoneDetail->day6) == $dayzone->ID ? 'selected' : '' }}>
                            {{ $dayzone->Name }}
                        </option>
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
                        <option value="{{ $dayzone->ID }}" 
                                {{ old('day7', $weekzoneDetail->day7) == $dayzone->ID ? 'selected' : '' }}>
                            {{ $dayzone->Name }}
                        </option>
                        @endforeach
                    </select>
                    @error('day7')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary mt-3">Update Weekzone Detail</button>
                <a href="{{ route('weekzone.index') }}" class="btn btn-secondary mt-3">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
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
    <h1 class="h3 mb-4 text-gray-800">Add New Availability</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('pt_availability.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="PT_ID" class="form-label">Personal Trainer</label>
                    <select class="form-control" id="PT_ID" name="PT_ID" required>
                        <option value="">Select Personal Trainer</option>
                        @if($personalTrainers->isEmpty())
                        <option value="" disabled>No trainers available</option>
                        @else
                        @foreach($personalTrainers as $pt)
                        <option value="{{ $pt->ID }}" {{ old('PT_ID') == $pt->ID ? 'selected' : '' }}>{{ $pt->NAME }} (Trainer)</option>
                        @endforeach
                        @endif
                    </select>
                </div>
                <div class="mb-3">
                    <label for="DOW" class="form-label">Day of Week</label>
                    <select class="form-control" id="DOW" name="DOW" required>
                        <option value="">Select Day</option>
                        <option value="1" {{ old('DOW') == '1' ? 'selected' : '' }}>Senin</option>
                        <option value="2" {{ old('DOW') == '2' ? 'selected' : '' }}>Selasa</option>
                        <option value="3" {{ old('DOW') == '3' ? 'selected' : '' }}>Rabu</option>
                        <option value="4" {{ old('DOW') == '4' ? 'selected' : '' }}>Kamis</option>
                        <option value="5" {{ old('DOW') == '5' ? 'selected' : '' }}>Jumat</option>
                        <option value="6" {{ old('DOW') == '6' ? 'selected' : '' }}>Sabtu</option>
                        <option value="7" {{ old('DOW') == '7' ? 'selected' : '' }}>Minggu</option>
                    </select>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="mb-3">
                            <label for="START_TIME" class="form-label">Start Time</label>
                            <input type="datetime-local" class="form-control @error('START_TIME') is-invalid @enderror" id="START_TIME" name="START_TIME" value="{{ old('START_TIME') ? str_replace(' ', 'T', old('START_TIME')) : \Carbon\Carbon::today('Asia/Jakarta')->format('Y-m-d\T08:00') }}" required>
                            @error('START_TIME')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col">
                        <div class="mb-3">
                            <label for="END_TIME" class="form-label">End Time</label>
                            <input type="datetime-local" class="form-control @error('END_TIME') is-invalid @enderror" id="END_TIME" name="END_TIME" value="{{ old('END_TIME') ? str_replace(' ', 'T', old('END_TIME')) : \Carbon\Carbon::today('Asia/Jakarta')->format('Y-m-d\T09:00') }}" required>
                            @error('END_TIME')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="IS_ACTIVE" class="form-label">Status</label>
                    <select class="form-control" id="IS_ACTIVE" name="IS_ACTIVE" required>
                        <option value="1" {{ old('IS_ACTIVE') == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('IS_ACTIVE') == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('pt_availability.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection
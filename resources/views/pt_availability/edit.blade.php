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
    <h1 class="h3 mb-4 text-gray-800">Edit Availability</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('pt_availability.update', $availability->ID) }}" method="POST" id="availabilityForm">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="PT_ID" class="form-label">Personal Trainer</label>
                    <select class="form-control @error('PT_ID') is-invalid @enderror" id="PT_ID" name="PT_ID" required>
                        <option value="">Select Personal Trainer</option>
                        @foreach($personalTrainers as $pt)
                        <option value="{{ $pt->ID }}" {{ old('PT_ID', $availability->PT_ID) == $pt->ID ? 'selected' : '' }}>{{ $pt->NAME }}</option>
                        @endforeach
                    </select>
                    @error('PT_ID')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="DOW" class="form-label">Day of Week</label>
                    <select class="form-control @error('DOW') is-invalid @enderror" id="DOW" name="DOW" required>
                        <option value="">Select Day</option>
                        @foreach([
                            1 => 'Senin',
                            2 => 'Selasa',
                            3 => 'Rabu',
                            4 => 'Kamis',
                            5 => 'Jumat',
                            6 => 'Sabtu',
                            7 => 'Minggu'
                        ] as $value => $name)
                        <option value="{{ $value }}" {{ old('DOW', $availability->DOW) == $value ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('DOW')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="row">
                    <div class="col">
                        <div class="mb-3">
                            <label for="START_TIME" class="form-label">Start Time</label>
                            <input type="datetime-local" class="form-control @error('START_TIME') is-invalid @enderror" id="START_TIME" name="START_TIME" value="{{ old('START_TIME', \Carbon\Carbon::today('Asia/Jakarta')->format('Y-m-d') . 'T' . \Carbon\Carbon::parse($availability->START_TIME)->format('H:i')) }}" required>
                            @error('START_TIME')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col">
                        <div class="mb-3">
                            <label for="END_TIME" class="form-label">End Time</label>
                            <input type="datetime-local" class="form-control @error('END_TIME') is-invalid @enderror" id="END_TIME" name="END_TIME" value="{{ old('END_TIME', \Carbon\Carbon::today('Asia/Jakarta')->format('Y-m-d') . 'T' . \Carbon\Carbon::parse($availability->END_TIME)->format('H:i')) }}" required>
                            @error('END_TIME')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="IS_ACTIVE" class="form-label">Status</label>
                    <select class="form-control @error('IS_ACTIVE') is-invalid @enderror" id="IS_ACTIVE" name="IS_ACTIVE" required>
                        <option value="1" {{ old('IS_ACTIVE', $availability->IS_ACTIVE) ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('IS_ACTIVE', $availability->IS_ACTIVE) == 0 ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('IS_ACTIVE')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('pt_availability.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('availabilityForm').addEventListener('submit', function(event) {
        const startTime = document.getElementById('START_TIME').value;
        const endTime = document.getElementById('END_TIME').value;

        if (startTime && endTime && startTime >= endTime) {
            event.preventDefault();
            alert('Waktu Selesai harus setelah Waktu Mulai.');
            document.getElementById('END_TIME').classList.add('is-invalid');
            document.getElementById('END_TIME').nextElementSibling.textContent = 'Waktu Selesai harus setelah Waktu Mulai.';
        }
    });
</script>
@endsection
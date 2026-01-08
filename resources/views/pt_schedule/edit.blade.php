@extends('layout_background.app_layouts')

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4 text-gray-800">Edit Personal Trainer Schedule</h1>

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
            <form action="{{ route('pt_schedule.update', $schedule->ID) }}" method="POST" id="scheduleForm">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="PT_ID" class="form-label">Personal Trainer</label>
                    <select name="PT_ID" id="PT_ID" class="form-control @error('PT_ID') is-invalid @enderror" required>
                        <option value="">Select Personal Trainer</option>
                        @foreach($personalTrainers as $pt)
                        <option value="{{ $pt->ID }}" {{ $schedule->PT_ID == $pt->ID ? 'selected' : '' }}>{{ $pt->NAME }}</option>
                        @endforeach
                    </select>
                    @error('PT_ID')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="MEMBER_ID" class="form-label">Member</label>
                    <select name="MEMBER_ID" id="MEMBER_ID" class="form-control @error('MEMBER_ID') is-invalid @enderror" required>
                        <option value="">Select Member</option>
                        @foreach($members as $member)
                        <option value="{{ $member->ID }}" {{ $schedule->MEMBER_ID == $member->ID ? 'selected' : '' }}>{{ $member->NAME }}</option>
                        @endforeach
                    </select>
                    @error('MEMBER_ID')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="START_TIME" class="form-label">Start Time</label>
                    <input type="datetime-local" name="START_TIME" id="START_TIME" class="form-control @error('START_TIME_formatted') is-invalid @enderror" value="{{ old('START_TIME', \Carbon\Carbon::today('Asia/Jakarta')->format('Y-m-d') . 'T' . \Carbon\Carbon::parse($schedule->START_TIME)->format('H:i')) }}" required>
                    <input type="hidden" name="START_TIME_formatted" id="START_TIME_formatted">
                    @error('START_TIME_formatted')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="END_TIME" class="form-label">End Time</label>
                    <input type="datetime-local" name="END_TIME" id="END_TIME" class="form-control @error('END_TIME_formatted') is-invalid @enderror" value="{{ old('END_TIME', \Carbon\Carbon::today('Asia/Jakarta')->format('Y-m-d') . 'T' . \Carbon\Carbon::parse($schedule->END_TIME)->format('H:i')) }}" required>
                    <input type="hidden" name="END_TIME_formatted" id="END_TIME_formatted">
                    @error('END_TIME_formatted')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="BOOKED_AT" class="form-label">Booked At</label>
                    <input type="datetime-local" name="BOOKED_AT" id="BOOKED_AT" class="form-control @error('BOOKED_AT_formatted') is-invalid @enderror" value="{{ \Carbon\Carbon::parse($schedule->BOOKED_AT)->format('Y-m-d\TH:i') }}" required>
                    <input type="hidden" name="BOOKED_AT_formatted" id="BOOKED_AT_formatted">
                    @error('BOOKED_AT_formatted')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="STATUS" class="form-label">Status</label>
                    <select name="STATUS" id="STATUS" class="form-control @error('STATUS') is-invalid @enderror" required>
                        <option value="0" {{ $schedule->STATUS == 0 ? 'selected' : '' }}>Non Aktif</option>
                        <option value="1" {{ $schedule->STATUS == 1 ? 'selected' : '' }}>Aktif</option>
                    </select>
                    @error('STATUS')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="NOTE" class="form-label">Note</label>
                    <textarea name="NOTE" id="NOTE" class="form-control @error('NOTE') is-invalid @enderror">{{ $schedule->NOTE }}</textarea>
                    @error('NOTE')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('pt_schedule.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('scheduleForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevent form submission for validation

    const startTime = document.getElementById('START_TIME').value;
    const endTime = document.getElementById('END_TIME').value;
    const bookedAt = document.getElementById('BOOKED_AT').value;

    // Validate inputs
    if (!startTime || !endTime || !bookedAt) {
        alert('Start Time, End Time, and Booked At are required.');
        return;
    }

    if (startTime >= endTime) {
        alert('Waktu Selesai harus setelah Waktu Mulai.');
        document.getElementById('END_TIME').classList.add('is-invalid');
        return;
    }

    // Convert to Y-m-d H:i:s format
    if (startTime) {
        document.getElementById('START_TIME_formatted').value = startTime.replace('T', ' ') + ':00';
    }
    if (endTime) {
        document.getElementById('END_TIME_formatted').value = endTime.replace('T', ' ') + ':00';
    }
    if (bookedAt) {
        document.getElementById('BOOKED_AT_formatted').value = bookedAt.replace('T', ' ') + ':00';
    }

    // Submit form after validation
    this.submit();
});
</script>
@endsection
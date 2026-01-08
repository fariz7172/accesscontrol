@extends('layout_background.app_layouts')

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4 text-gray-800">Create Personal Trainer Schedule</h1>

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
            <form action="{{ route('pt_schedule.store') }}" method="POST" id="scheduleForm">
                @csrf
                <div class="mb-3">
                    <label for="PT_ID" class="form-label">Personal Trainer</label>
                    <select name="PT_ID" id="PT_ID" class="form-control @error('PT_ID') is-invalid @enderror" required>
                        <option value="">Select Personal Trainer</option>
                        @foreach($personalTrainers as $pt)
                        <option value="{{ $pt->ID }}" {{ old('PT_ID') == $pt->ID ? 'selected' : '' }}>{{ $pt->NAME }}</option>
                        @endforeach
                    </select>
                    @error('PT_ID')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Members</label>
                    <div class="checkbox-list" style="max-height: 200px; overflow-y: auto; border: 1px solid #ced4da; padding: 10px; border-radius: 4px;">
                        @if($members->isEmpty())
                        <p>No members available</p>
                        @else
                        @foreach($members as $member)
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="MEMBER_ID[]" id="member_{{ $member->ID }}" value="{{ $member->ID }}" {{ in_array($member->ID, old('MEMBER_ID', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="member_{{ $member->ID }}">{{ $member->NAME }}</label>
                        </div>
                        @endforeach
                        @endif
                    </div>
                    @error('MEMBER_ID')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="START_TIME" class="form-label">Start Time</label>
                    <input type="datetime-local" name="START_TIME" id="START_TIME" class="form-control @error('START_TIME_formatted') is-invalid @enderror" value="{{ old('START_TIME') ? \Carbon\Carbon::parse(old('START_TIME'))->format('Y-m-d\TH:i') : \Carbon\Carbon::today('Asia/Jakarta')->format('Y-m-d\T08:00') }}" required>
                    <input type="hidden" name="START_TIME_formatted" id="START_TIME_formatted">
                    @error('START_TIME_formatted')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="END_TIME" class="form-label">End Time</label>
                    <input type="datetime-local" name="END_TIME" id="END_TIME" class="form-control @error('END_TIME_formatted') is-invalid @enderror" value="{{ old('END_TIME') ? \Carbon\Carbon::parse(old('END_TIME'))->format('Y-m-d\TH:i') : \Carbon\Carbon::today('Asia/Jakarta')->format('Y-m-d\T09:00') }}" required>
                    <input type="hidden" name="END_TIME_formatted" id="END_TIME_formatted">
                    @error('END_TIME_formatted')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <input type="hidden" name="BOOKED_AT_formatted" id="BOOKED_AT_formatted">
                <div class="mb-3">
                    <label for="STATUS" class="form-label">Status</label>
                    <select name="STATUS" id="STATUS" class="form-control @error('STATUS') is-invalid @enderror" required>
                        <option value="0" {{ old('STATUS') == '0' ? 'selected' : '' }}>Booked</option>
                        <option value="1" {{ old('STATUS') == '1' ? 'selected' : '' }}>Selesai</option>
                    </select>
                    @error('STATUS')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="NOTE" class="form-label">Note</label>
                    <textarea name="NOTE" id="NOTE" class="form-control @error('NOTE') is-invalid @enderror">{{ old('NOTE') }}</textarea>
                    @error('NOTE')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Save</button>
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
    const memberCheckboxes = document.querySelectorAll('input[name="MEMBER_ID[]"]:checked');

    // Validate inputs
    if (!startTime || !endTime) {
        alert('Start Time and End Time are required.');
        return;
    }

    if (startTime >= endTime) {
        alert('Waktu Selesai harus setelah Waktu Mulai.');
        document.getElementById('END_TIME').classList.add('is-invalid');
        return;
    }

    if (memberCheckboxes.length === 0) {
        alert('At least one Member must be selected.');
        document.querySelector('.checkbox-list').style.borderColor = 'red';
        return;
    }

    // Convert to Y-m-d H:i:s format
    document.getElementById('START_TIME_formatted').value = startTime.replace('T', ' ') + ':00';
    document.getElementById('END_TIME_formatted').value = endTime.replace('T', ' ') + ':00';

    // Set BOOKED_AT_formatted to current date and time
    const now = new Date();
    const bookedAtFormatted = now.getFullYear() + '-' +
        String(now.getMonth() + 1).padStart(2, '0') + '-' +
        String(now.getDate()).padStart(2, '0') + ' ' +
        String(now.getHours()).padStart(2, '0') + ':' +
        String(now.getMinutes()).padStart(2, '0') + ':' +
        String(now.getSeconds()).padStart(2, '0');
    document.getElementById('BOOKED_AT_formatted').value = bookedAtFormatted;

    // Submit form after all fields are filled
    this.submit();
});
</script>
@endsection
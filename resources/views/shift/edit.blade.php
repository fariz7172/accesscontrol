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
    <h1 class="h3 mb-4 text-gray-800">Edit Shift</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('shift.update', $shift->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="mb-3">Shift Settings</h5>
                        <div class="mb-3">
                            <label for="ShiftNo" class="form-label">Shift No</label>
                            <input type="text" name="ShiftNo" class="form-control" id="ShiftNo" value="{{ old('ShiftNo', $shift->ShiftNo) }}" required>
                            @error('ShiftNo')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="ShiftName" class="form-label">Shift Name</label>
                            <input type="text" name="ShiftName" class="form-control" id="ShiftName" value="{{ old('ShiftName', $shift->ShiftName) }}" required>
                            @error('ShiftName')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h5 class="mb-3">Additional Settings</h5>
                        <div class="mb-3">
                            <label for="Tipe" class="form-label">Type</label>
                            <select name="Tipe" class="form-control" id="Tipe" required>
                                <option value="1" {{ old('Tipe', $shift->Tipe) == 1 ? 'selected' : '' }}>Working Days</option>
                                <option value="2" {{ old('Tipe', $shift->Tipe) == 2 ? 'selected' : '' }}>Off Days</option>
                            </select>
                            @error('Tipe')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="DDay" class="form-label">DDay</label>
                            <select name="DDay" class="form-control" id="DDay" required>
                                <option value="1" {{ old('DDay', $shift->DDay) == 1 ? 'selected' : '' }}>Normal Shift</option>
                                <option value="2" {{ old('DDay', $shift->DDay) == 2 ? 'selected' : '' }}>Over Night Shift</option>
                            </select>
                            @error('DDay')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Set Start Shift -->
                <div class="card mt-4">
                    <h5 class="card-header bg-success" style="color:white">Set Time of Entry</h5>
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label for="Begin_Time" class="form-label">Begin Time (Jam Masuk)</label>
                                    <input type="time" name="Begin_Time" class="form-control" id="Begin_Time" value="{{ old('Begin_Time', substr($shift->Begin_Time, 0, 5)) }}">
                                    @error('Begin_Time')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="Start_In" class="form-label">Start In (Batas Awal Masuk)</label>
                                    <input type="time" name="Start_In" class="form-control" id="Start_In" value="{{ old('Start_In', substr($shift->Start_In, 0, 5)) }}">
                                    @error('Start_In')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="Range_In" class="form-label">Range In (Batas Akhir Masuk)</label>
                                    <input type="time" name="Range_In" class="form-control" id="Range_In" value="{{ old('Range_In', substr($shift->Range_In, 0, 5)) }}">
                                    @error('Range_In')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col">
                                <div class="mb-3">
                                    <label for="Out_time" class="form-label">Out Time (Jam Pulang)</label>
                                    <input type="time" name="Out_time" class="form-control" id="Out_time" value="{{ old('Out_time', substr($shift->Out_time, 0, 5)) }}">
                                    @error('Out_time')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="Start_Out" class="form-label">Start Out (Batas Awal Pulang)</label>
                                    <input type="time" name="Start_Out" class="form-control" id="Start_Out" value="{{ old('Start_Out', substr($shift->Start_Out, 0, 5)) }}">
                                    @error('Start_Out')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="Range_Out" class="form-label">Range Out (Batas Akhir Pulang)</label>
                                    <input type="time" name="Range_Out" class="form-control" id="Range_Out" value="{{ old('Range_Out', substr($shift->Range_Out, 0, 5)) }}">
                                    @error('Range_Out')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Start In -->
                <div class="card mt-4">
                    <h5 class="card-header bg-warning" style="color:white">Start In</h5>
                    <div class="card-body">
                        <h5 class="card-title">Start In</h5>
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label for="Break_Time" class="form-label">Break Time (Jam Break)</label>
                                    <input type="time" name="Break_Time" class="form-control" id="Break_Time" value="{{ old('Break_Time', substr($shift->Break_Time, 0, 5)) }}">
                                    @error('Break_Time')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="Start_Break" class="form-label">Start Break (Batas Awal Break)</label>
                                    <input type="time" name="Start_Break" class="form-control" id="Start_Break" value="{{ old('Start_Break', substr($shift->Start_Break, 0, 5)) }}">
                                    @error('Start_Break')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="Range_Break" class="form-label">Range Break (Batas Akhir Break)</label>
                                    <input type="time" name="Range_Break" class="form-control" id="Range_Break" value="{{ old('Range_Break', substr($shift->Range_Break, 0, 5)) }}">
                                    @error('Range_Break')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col">
                                <div class="mb-3">
                                    <label for="Resume_Time" class="form-label">Resume Time (Jam Resume)</label>
                                    <input type="time" name="Resume_Time" class="form-control" id="Resume_Time" value="{{ old('Resume_Time', substr($shift->Resume_Time, 0, 5)) }}">
                                    @error('Resume_Time')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="Start_Resume" class="form-label">Start Resume (Batas Awal Resume)</label>
                                    <input type="time" name="Start_Resume" class="form-control" id="Start_Resume" value="{{ old('Start_Resume', substr($shift->Start_Resume, 0, 5)) }}">
                                    @error('Start_Resume')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="Range_Resume" class="form-label">Range Resume (Batas Akhir Resume)</label>
                                    <input type="time" name="Range_Resume" class="form-control" id="Range_Resume" value="{{ old('Range_Resume', substr($shift->Range_Resume, 0, 5)) }}">
                                    @error('Range_Resume')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Update Shift</button>
                    <a href="{{ route('shift.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
document.getElementById('Tipe').addEventListener('change', function() {
    const timeInputs = [
        'Begin_Time', 'Start_In', 'Range_In',
        'Out_time', 'Start_Out', 'Range_Out',
        'Break_Time', 'Start_Break', 'Range_Break',
        'Resume_Time', 'Start_Resume', 'Range_Resume'
    ];
    const isOffDay = this.value == '2';
    timeInputs.forEach(id => {
        const input = document.getElementById(id);
        input.disabled = isOffDay;
        if (isOffDay) {
            input.value = '';
        }
    });
});

// Trigger on page load to handle pre-selected Off Days
document.addEventListener('DOMContentLoaded', function() {
    const tipeSelect = document.getElementById('Tipe');
    tipeSelect.dispatchEvent(new Event('change'));
});
</script>
@endsection
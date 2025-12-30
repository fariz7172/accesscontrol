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
    <h1 class="h3 mb-4 text-gray-800">Create Shift</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('shift.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="mb-3">Shift Settings</h5>
                        <div class="mb-3">
                            <label for="ShiftNo" class="form-label">Shift No</label>
                            <input type="number" name="ShiftNo" class="form-control" id="ShiftNo" value="{{ old('ShiftNo') }}" required>
                            @error('ShiftNo')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="ShiftName" class="form-label">Shift Name</label>
                            <input type="text" name="ShiftName" class="form-control" id="ShiftName" value="{{ old('ShiftName') }}" required>
                            @error('ShiftName')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h5 class="mb-3">Additional Settings</h5>
                        <div class="mb-3">
                            <label for="Tipe" class="form-label">Type</label>
                            <select name="Tipe" class="form-control" id="Tipe">
                                <option value="1" {{ old('Tipe') == '1' ? 'selected' : '' }}>Working Days</option>
                                <option value="2" {{ old('Tipe') == '2' ? 'selected' : '' }}>Off Days</option>
                            </select>
                            @error('Tipe')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="DDay" class="form-label">DDay</label>

                            <select name="DDay" class="form-control" id="Tipe">
                                <option value="1" {{ old('Tipe') == '1' ? 'selected' : '' }}>Normal Shift</option>
                                <option value="2" {{ old('Tipe') == '2' ? 'selected' : '' }}>Over Nigth Shift</option>
                            </select>
                            @error('DDay')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Set Start Shift -->
                <div class="card mt-4">
                    <h5 class="card-header bg-success" style="color:white">Set Time of entry</h5>
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label for="Begin_Time" class="form-label">Begin Time (jam Masuk)</label>
                                    <input type="time" name="Begin_Time" class="form-control" id="Begin_Time" value="{{ old('Begin_Time') }}">
                                    @error('Begin_Time')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="Start_In" class="form-label">Start In (Batas awal masuk)</label>
                                    <input type="time" name="Start_In" class="form-control" id="Start_In" value="{{ old('Start_In') }}">
                                    @error('Start_In')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="Range_In" class="form-label">Range In (Batas akhir masuk)</label>
                                    <input type="time" name="Range_In" class="form-control" id="Range_In" value="{{ old('Range_In') }}">
                                    @error('Range_In')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>


                            </div>
                            <div class="col">
                                <div class="mb-3">
                                    <label for="Out_Time" class="form-label">Out Time (jam pulang)</label>
                                    <input type="time" name="Out_time" class="form-control" id="Out_time" value="{{ old('Out_time') }}">
                                    @error('Out_Time')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="Start_Out" class="form-label">Start Out (Batas awal Pulang)</label>
                                    <input type="time" name="Start_Out" class="form-control" id="Start_Out" value="{{ old('Start_Out') }}">
                                    @error('Start_Out')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="Range_Out" class="form-label">Range Out (Batas akhir Pulang)</label>
                                    <input type="time" name="Range_Out" class="form-control" id="Range_Out" value="{{ old('Range_Out') }}">
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
                                    <label for="Break_Time" class="form-label">Break Time (jam Break)</label>
                                    <input type="time" name="Break_Time" class="form-control" id="Break_Time" value="{{ old('Break_Time') }}">
                                    @error('Break_Time')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>


                                <div class="mb-3">
                                    <label for="Start_Break" class="form-label">Start Break (Batas awal Break)</label>
                                    <input type="time" name="Start_Break" class="form-control" id="Start_Break" value="{{ old('Start_Break') }}">
                                    @error('Start_Break')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="Range_Break" class="form-label">Range Break (Batas akhir Break)</label>
                                    <input type="time" name="Range_Break" class="form-control" id="Range_Break" value="{{ old('Range_Break') }}">
                                    @error('Range_Break')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>


                            </div>
                            <div class="col">
                                <div class="mb-3">
                                    <label for="Resume_Time" class="form-label">Resume Time (Jam resume)</label>
                                    <input type="time" name="Resume_Time" class="form-control" id="Resume_Time" value="{{ old('Resume_Time') }}">
                                    @error('Resume_Time')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="Start_Resume" class="form-label">Start Resume (Batas awal Resume)</label>
                                    <input type="time" name="Start_Resume" class="form-control" id="Start_Resume" value="{{ old('Start_Resume') }}">
                                    @error('Start_Resume')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>


                                <div class="mb-3">
                                    <label for="Range_Resume" class="form-label">Range Resume (Batas akhir Resume)</label>
                                    <input type="time" name="Range_Resume" class="form-control" id="Range_Resume" value="{{ old('Range_Resume') }}">
                                    @error('Range_Resume')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>



                            </div>
                        </div>
                    </div>
                </div>



                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Create Shift</button>
                    <a href="{{ route('shift.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('Tipe').addEventListener('change', function() {
        const timeFields = [
            'Begin_Time', 'Break_Time', 'Resume_Time', 'Out_time',
            'Start_In', 'Start_Break', 'Start_Resume', 'Start_Out',
            'Range_In', 'Range_Break', 'Range_Resume', 'Range_Out'
        ];
        const isOffDay = this.value === '2';

        timeFields.forEach(field => {
            const input = document.getElementById(field);
            input.value = isOffDay ? '00:00:00' : input.value;
            input.disabled = isOffDay;
        });
    });

    // Trigger change event on page load to handle old input values
    document.getElementById('Tipe').dispatchEvent(new Event('change'));
</script>
@endsection
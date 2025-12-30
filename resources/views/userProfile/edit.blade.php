@extends('layout_background.app_layouts')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Edit User Profile</h1>

    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
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

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edit User</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('userProfile.update', encryptId($user->ID)) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="NAME">Name</label>
                            <input type="text" id="NAME" name="NAME" class="form-control" value="{{ old('NAME', $user->NAME) }}" required>
                            @error('NAME')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="NoIdentitas">No Identity</label>
                            <input type="number" id="NoIdentitas" name="NoIdentitas" class="form-control" value="{{ old('NoIdentitas', $user->NoIdentitas) }}" required>
                            @error('NoIdentitas')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="PASSWORD">Password (leave blank to keep unchanged)</label>
                    <input type="text" id="PASSWORD" name="PASSWORD" class="form-control" value="{{ old('PASSWORD', $user->PASSWORD) }}">
                    @error('PASSWORD')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="BIRTHDAY">Birthday</label>
                    <input type="date" id="BIRTHDAY" name="BIRTHDAY" class="form-control" value="{{ old('BIRTHDAY', $user->BIRTHDAY) }}" required>
                    @error('BIRTHDAY')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="Depid">Departemen</label>
                            <select id="Depid" name="Depid" class="form-control" required>
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ old('Depid', $user->Depid) == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('Depid')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="Branchid">Branch</label>
                            <select id="Branchid" name="Branchid" class="form-control" required>
                                <option value="">Select Branch</option>
                                @foreach($branches as $branchItem)
                                <option value="{{ $branchItem->id }}" {{ old('Branchid', $user->Branchid) == $branchItem->id ? 'selected' : '' }}>
                                    {{ $branchItem->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('Branchid')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="user_type">User Type</label>
                    <select id="user_type" name="user_type" class="form-control" required>
                        <option value="">Select User Type</option>
                        <option value="0" {{ old('user_type', $user->user_type) == '0' ? 'selected' : '' }}>Staff/User</option>
                        <option value="1" {{ old('user_type', $user->user_type) == '1' ? 'selected' : '' }}>Member</option>
                        <option value="2" {{ old('user_type', $user->user_type) == '2' ? 'selected' : '' }}>Personal Trainer</option>
                    </select>
                    @error('user_type')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col">
                        <div class="mb-3">
                            <label for="BEGIN_DATE">Begin Date</label>
                            <input type="datetime-local" id="BEGIN_DATE" name="BEGIN_DATE" class="form-control"
                                value="{{ old('BEGIN_DATE', \Carbon\Carbon::parse($user->BEGIN_DATE)->format('Y-m-d\TH:i')) }}" required>
                            @error('BEGIN_DATE')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col">
                        <div class="mb-3">
                            <label for="END_DATE">End Date</label>
                            <input type="datetime-local" id="END_DATE" name="END_DATE" class="form-control"
                                value="{{ old('END_DATE', \Carbon\Carbon::parse($user->END_DATE)->format('Y-m-d\TH:i')) }}" required>
                            @error('END_DATE')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="mb-3">
                            <label for="formFile" class="form-label">Update Profile Picture</label>
                            <input class="form-control" type="file" id="formFile" name="photo">
                            @error('photo')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                            @if($user->photo)
                            <img src="{{ $user->photo }}" alt="Profile Picture" style="max-width: 100px; margin-top: 10px;">
                            @endif
                        </div>
                    </div>
                    <div class="col">
                        <div class="mb-3">
                            <label for="Card" class="form-label">Tag RFID</label>
                            <input type="text" id="Card" name="Card" class="form-control" value="{{ old('Card', $user->Card) }}" required>
                            @error('Card')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col">
                        <div class="mb-3">
                            <label>Data Fingerprint (Fp)</label>
                            @if($user->userData->isNotEmpty())
                            @if($user->userData->first()->Fp)
                            <p class="text-success">Finger Print Tersedia</p>
                            <div class="input-group mb-2">
                                <input type="text" name="fp" class="form-control" value="{{ old('fp', $user->userData->first()->Fp) }}" placeholder="Masukkan Fingerprint" hidden>
                                @error('fp')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            @else
                            <p class="text-danger">Finger Print Tidak Tersedia</p>
                            <div class="input-group mb-2">
                                <input type="text" name="fp" class="form-control" value="{{ old('fp', '') }}" placeholder="Masukkan Fingerprint" hidden>
                                @error('fp')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            @endif
                            @endif
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('userProfiles.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    flatpickr('#BEGIN_DATE', {
        enableTime: true,
        dateFormat: 'Y-m-d H:i',
        defaultDate: "{{ \Carbon\Carbon::parse($user->BEGIN_DATE)->format('Y-m-d H:i') }}"
    });
    flatpickr('#END_DATE', {
        enableTime: true,
        dateFormat: 'Y-m-d H:i',
        defaultDate: "{{ \Carbon\Carbon::parse($user->END_DATE)->format('Y-m-d H:i') }}"
    });
</script>
@endsection
@extends('layout_background.app_layouts')

@section('content')
<div class="container">
    <h1>Edit User</h1>

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Menampilkan SweetAlert jika ada pesan -->
    @if (session('success'))
    <script>
        Swal.fire({
            title: 'Success!',
            text: "{{ session('success') }}",
            icon: 'success',
            confirmButtonText: 'OK'
        });
    </script>
    @endif
    @if (session('error'))
    <script>
        Swal.fire({
            title: 'Error!',
            text: "{{ session('error') }}",
            icon: 'error',
            confirmButtonText: 'OK'
        });
    </script>
    @endif

    <form action="{{ route('userAdmin.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group mb-3">
            <label for="username">Username</label>
            <input type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}" required>
            @error('username')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group mb-3">
            <label for="password">Password (leave blank to keep current)</label>
            <input type="password" name="password" class="form-control" placeholder="******">
            @error('password')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group mb-3">
            <label for="priv">Privilege</label>
            <select name="priv" class="form-control" required>
                <option value="0" {{ old('priv', $user->priv) == '0' ? 'selected' : '' }}>Non-Admin (0)</option>
                <option value="1" {{ old('priv', $user->priv) == '1' ? 'selected' : '' }}>Admin (1)</option>
            </select>
            @error('priv')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group mb-3" hidden>
            <label>Access Permissions</label>
            <div class="form-check">
                <input type="checkbox" name="access_user" class="form-check-input" value="1" {{ old('access_user', $user->bactive['user'] ?? false) ? 'checked' : '' }}>
                <label class="form-check-label">User</label>
            </div>
            <div class="form-check">
                <input type="checkbox" name="access_device" class="form-check-input" value="1" {{ old('access_device', $user->bactive['device'] ?? false) ? 'checked' : '' }}>
                <label class="form-check-label">Device</label>
            </div>
            <div class="form-check">
                <input type="checkbox" name="access_log" class="form-check-input" value="1" {{
                old('access_log', $user->bactive['log'] ?? true) ? 'checked' : '' }}>
                <label class="form-check-label">Log</label>
            </div>
            <div class="form-check">
                <input type="checkbox" name="access_setting" class="form-check-input" value="1" {{ old('access_setting', $user->bactive['setting'] ?? false) ? 'checked' : '' }}>
                <label class="form-check-label">Setting</label>
            </div>
            <div class="form-check">
                <input type="checkbox" name="access_userAdmin" class="form-check-input" value="1" {{ old('access_userAdmin', $user->bactive['userAdmin'] ?? false) ? 'checked' : '' }}>
                <label class="form-check-label">User Admin</label>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('userAdmin.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection
@extends('layout_background.app_layouts')

@section('content')
<div class="container">
    <h1>Add New User</h1>

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Menampilkan SweetAlert jika ada pesan error -->
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

    <form action="{{ route('userAdmin.store') }}" method="POST">
        @csrf
        <div class="form-group mb-3">
            <label for="username">Username</label>
            <input type="text" name="username" class="form-control" value="{{ old('username') }}" required>
            @error('username')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group mb-3">
            <label for="password">Password</label>
            <input type="password" name="password" class="form-control" required>
            @error('password')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group mb-3">
            <label for="priv">Privilege</label>
            <select name="priv" class="form-control" required>
                <option value="0" {{ old('priv') == '0' ? 'selected' : '' }}>Non-Admin (0)</option>
                <option value="1" {{ old('priv') == '1' ? 'selected' : '' }}>Admin (1)</option>
            </select>
            @error('priv')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group mb-3">
            <label>Access Permissions</label>
            <div class="form-check">
                <input type="checkbox" name="access_user" class="form-check-input" value="1" {{ old('access_user') ? 'checked' : '' }}>
                <label class="form-check-label">User</label>
            </div>
            <div class="form-check">
                <input type="checkbox" name="access_device" class="form-check-input" value="1" {{ old('access_device') ? 'checked' : '' }}>
                <label class="form-check-label">Device</label>
            </div>
            <div class="form-check">
                <input type="checkbox" name="access_log" class="form-check-input" value="1" {{ old('access_log') ? 'checked' : '' }} checked>
                <label class="form-check-label">Log</label>
            </div>
            <div class="form-check">
                <input type="checkbox" name="access_setting" class="form-check-input" value="1" {{ old('access_setting') ? 'checked' : '' }}>
                <label class="form-check-label">Setting</label>
            </div>
            <div class="form-check">
                <input type="checkbox" name="access_userAdmin" class="form-check-input" value="1" {{ old('access_userAdmin') ? 'checked' : '' }}>
                <label class="form-check-label">User Admin</label>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Create User</button>
        <a href="{{ route('userAdmin.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection
@extends('layout_background.app_layouts')

@section('content')
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="{{ asset('css/icon.css') }}">
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container-fluid">
    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">User Access Control</h6>
        </div>
        @if(session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: "{{ session('success') }}",
                timer: 3000,
                showConfirmButton: false
            });
        </script>
        @endif

        <!-- //////////////////// -->
        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <button class="nav-link active" id="nav-home-tab" data-bs-toggle="tab" data-bs-target="#nav-home" type="button" role="tab" aria-controls="nav-home" aria-selected="true">Admin Control</button>
                <button class="nav-link" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile" aria-selected="false">Admin Access</button>
            </div>
        </nav>
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                <div class="card-body">
                    <a href="{{ route('userAdmin.create') }}" class="btn btn-primary mb-3">Add User</a>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->username }}</td>
                                    <td>
                                        <a href="{{ route('userAdmin.edit', ['id' => encryptId($user->id)]) }}" class="btn btn-warning">Edit</a>
                                        <form action="{{ route('userAdmin.destroy', $user->id) }}" method="POST" id="deleteForm-{{ $user->id }}" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                        <button type="button" class="btn btn-danger delete-btn" data-id="{{ $user->id }}">Delete</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                <div class="card-body">
                    <div class="table-responsive">
                        <div id="dataTable" class="dataTables_wrapper dt-bootstrap4">
                            <div class="row d-flex">
                                <div class="col-sm-12 col-md-6 d-flex">
                                    <div class="dataTables_length" id="dataTable_length d-flex">
                                        <form method="GET" action="">
                                            <label>Show
                                                <select name="per_page" aria-controls="dataTable" class="custom-select custom-select-sm form-control form-control-sm" onchange="this.form.submit()">
                                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                                    <option value="250" {{ request('per_page') == 250 ? 'selected' : '' }}>250</option>
                                                    <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500</option>
                                                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
                                                </select> entries
                                            </label>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <table class="table table-bordered dataTable" id="dataTable" width="100%" cellspacing="0" role="grid" aria-describedby="dataTable_info" style="width: 100%;">
                                        <thead>
                                            <tr role="row">
                                                <th class="sorting sorting_asc" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-sort="ascending" aria-label="ID: activate to sort column descending" style="width: 52px;">ID</th>
                                                <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Name: activate to sort column ascending" style="width: 235px;">Name</th>
                                                <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Access: activate to sort column ascending" style="width: 108px;">Access</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($users as $user)
                                            <tr id="userRow-{{ $user->id }}">
                                                <td>{{ $user->id }}</td>
                                                <td>{{ $user->username }}</td>
                                                <td>
                                                    <div class="access-container">

                                                        <div class="access-item">
                                                            <div class="access-icon">
                                                                <i class="fas fa-door-open"></i>
                                                            </div>

                                                            <div class="access-label">User
                                                                <input type="checkbox" class="flat user-checkbox"
                                                                    data-userid="{{ $user->id }}"
                                                                    onchange="toggleSidebar(this, 'userData')"
                                                                    id="userDataID-{{ $user->id }}"
                                                                    {{ $user->hasAccess('user') ? 'checked' : '' }}
                                                                    {{ auth()->user()->priv ? '' : 'disabled' }}>
                                                            </div>
                                                        </div>

                                                        <div class="access-item">
                                                            <div class="access-icon">
                                                                <i class="fas fa-desktop"></i>
                                                            </div>
                                                            <div class="access-label">Device
                                                                <input type="checkbox" class="flat user-checkbox"
                                                                    data-userid="{{ $user->id }}"
                                                                    onchange="toggleSidebar(this, 'deviceData')"
                                                                    id="deviceDataID-{{ $user->id }}"
                                                                    {{ $user->hasAccess('device') ? 'checked' : '' }}
                                                                    {{ auth()->user()->priv ? '' : 'disabled' }}>
                                                            </div>
                                                        </div>

                                                        <div class="access-item">
                                                            <div class="access-icon">
                                                                <i class="fas fa-file-alt"></i>
                                                            </div>
                                                            <div class="access-label">Log
                                                                <input type="checkbox" class="flat user-checkbox"
                                                                    data-userid="{{ $user->id }}"
                                                                    onchange="toggleSidebar(this, 'logData')"
                                                                    id="logDataID-{{ $user->id }}"
                                                                    {{ $user->hasAccess('log') ? 'checked' : '' }}
                                                                    {{ auth()->user()->priv ? '' : 'disabled' }}>
                                                            </div>
                                                        </div>
                                                        <div class="access-item">
                                                            <div class="access-icon">
                                                                <i class="fas fa-cog"></i>
                                                            </div>
                                                            <div class="access-label">Setting
                                                                <input type="checkbox" class="flat user-checkbox"
                                                                    data-userid="{{ $user->id }}"
                                                                    onchange="toggleSidebar(this, 'setting')"
                                                                    id="settingID-{{ $user->id }}"
                                                                    {{ $user->hasAccess('setting') ? 'checked' : '' }}
                                                                    {{ auth()->user()->priv ? '' : 'disabled' }}>
                                                            </div>
                                                        </div>
                                                        <div class="access-item">
                                                            <div class="access-icon">
                                                                <i class="fas fa-user"></i>
                                                            </div>
                                                            <div class="access-label">userAdmin
                                                                <input type="checkbox" class="flat user-checkbox"
                                                                    data-userid="{{ $user->id }}"
                                                                    onchange="toggleSidebar(this, 'userAdmin')"
                                                                    id="userAdminID-{{ $user->id }}"
                                                                    {{ $user->hasAccess('setting') ? 'checked' : '' }}
                                                                    {{ auth()->user()->priv ? '' : 'disabled' }}>
                                                            </div>
                                                        </div>

                                                        <div class="access-item">
                                                            <div class="access-icon">
                                                                <i class="far fa-edit"></i>
                                                            </div>
                                                            <div class="access-label">Attendance
                                                                <input type="checkbox" class="flat user-checkbox"
                                                                    data-userid="{{ $user->id }}"
                                                                    onchange="toggleSidebar(this, 'attendance')"
                                                                    id="AttendanceID-{{ $user->id }}"
                                                                    {{ $user->hasAccess('attendance') ? 'checked' : '' }}
                                                                    {{ auth()->user()->priv ? '' : 'disabled' }}>
                                                            </div>
                                                        </div>

                                                         <div class="access-item">
                                                            <div class="access-icon">
                                                                <i class="far fa-edit"></i>
                                                            </div>
                                                            <div class="access-label">Schedule
                                                                <input type="checkbox" class="flat user-checkbox"
                                                                    data-userid="{{ $user->id }}"
                                                                    onchange="toggleSidebar(this, 'schedule')"
                                                                    id="scheduleID-{{ $user->id }}"
                                                                    {{ $user->hasAccess('schedule') ? 'checked' : '' }}
                                                                    {{ auth()->user()->priv ? '' : 'disabled' }}>
                                                            </div>
                                                        </div>

                                                        
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 col-md-5">
                                    <div class="dataTables_info" id="dataTable_info" role="status" aria-live="polite">Showing 1 to of entries</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Di bagian head atau sebelum </body> --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- Inject Route untuk JavaScript --}}
<script>
    window.userAdminRoutes = {
        updateAccess: "{{ route('userAdmin.updateAccess') }}"
    };
</script>

{{-- Library Eksternal --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- JavaScript Utama (SUDAH DIPISAH!) --}}
<script src="{{ asset('js/userAdmin/userAdmin.js') }}?v={{ time() }}"></script>

@endsection
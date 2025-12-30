@extends('layout_background.app_layouts')

@section('content')
<div class="container-fluid">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">×</button>
    </div>
    @endif

    @if(session('details'))
    <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
        <strong>Detail Error:</strong>
        <ul class="mb-0">
            @foreach(session('details') as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert">×</button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert">×</button>
    </div>
    @endif

    <h1 class="h3 mb-2 text-gray-800">User Profiles & Weekzone Assignment</h1>

    <!-- Tab Bar Menu -->
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('weekzone.index') ? 'active' : '' }}"
                href="{{ route('weekzone.index') }}">Weekzone</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('weekzonedetail.index') ? 'active' : '' }}"
                href="{{ route('weekzonedetail.index') }}">Weekzone Detail</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('setusertimeone.index') }}">User Profiles</a>
        </li>
    </ul>

    <!-- 1. Pilih Device -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-primary text-white">
            <h6 class="m-0 font-weight-bold">
                Select Device (Machine)
                <span class="badge badge-light ml-2" id="selectedCount">0</span>
            </h6>
        </div>
        <div class="card-body">
            <div class="form-group">

                <!-- Checkbox "Tampilkan Semua User" -->
                <div class="mb-3 p-3 border rounded bg-light">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="showAllUsers">
                        <label class="form-check-label font-weight-bold text-primary" for="showAllUsers">
                            Show All Users (from all devices)
                        </label>
                    </div>
                </div>

                <!-- Daftar Device -->
                <div class="row" id="deviceList">
                    @foreach($devices as $device)
                    @if($device->type != 1)
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input device-checkbox" type="checkbox" value="{{ $device->id }}"
                                id="device_{{ $device->id }}">
                            <label class="form-check-label font-weight-medium" for="device_{{ $device->id }}">
                                <strong>{{ $device->name }}</strong>
                                <small class="text-muted d-block">
                                    SN: {{ $device->sn }}
                                    Type:
                                    @if($device->type == 0)
                                    <span class="text-info">Fingerprint</span>
                                    @elseif($device->type == 50)
                                    <span class="text-success">Face</span>
                                    @else
                                    {{ $device->type }}
                                    @endif
                                </small>
                            </label>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>

                <!-- Tombol Pilih Semua / Batal Semua -->
                <div class="mt-3">
                    <button type="button" id="selectAllDevices" class="btn btn-sm btn-outline-primary">
                        Select All Devices
                    </button>
                    <button type="button" id="deselectAllDevices" class="btn btn-sm btn-outline-secondary">
                        Clear Selection
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 2. Tabel User dengan Search & Pagination -->
        <div class="col-md-6">
            <div id="userTableContainer" style="display:none;">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">User List</h6>
                        <button type="button" class="btn btn-success btn-sm" id="assignWeekzoneBtn"
                            style="display:none;">
                            <i class="fas fa-calendar-alt"></i> Assign Weekzone
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Search Box -->
                        <div class="row mb-3">
                            <div class="col"> <input type="text" id="userSearchInput" class="form-control form-control-sm"
                                placeholder="Search by name, card, or department..."></div>
                            <div class="col">
                                  <select id="entriesPerPage" class="form-control form-control-sm">
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>
                      

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm" id="userTable">
                              
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%" class="text-center align-middle px-2 py-3">
                                            <div class="form-check m-0 d-inline-block">
                                                <input class="form-check-input position-static" type="checkbox" id="selectAllUsers"
                                                    title="Select All on This Page">
                                                <label class="form-check-label m-0" for="selectAllUsers"></label>
                                            </div>
                                        </th>
                                        <th>Name</th>
                                        <th>Card</th>
                                        <th>Department</th>
                                        <th>Begin Date</th>
                                        <th>End Date</th>
                                        <th>Photo</th>
                                    </tr>
                                </thead>

                                <tbody id="userTableBody">
                                    <!-- Diisi via JS -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <nav aria-label="User pagination">
                            <ul class="pagination justify-content-center pagination-sm" id="userPagination">
                                <!-- Diisi via JS -->
                            </ul>
                        </nav>
                        <div class="text-center text-muted small">
                            Showing <span id="pageInfo">0</span> of <span id="totalUsers">0</span> users
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Panel Weekzone (Preview Saat Ini) -->
        <div class="col-md-6">
            <div id="weekzonePanel" class="card shadow mb-4" style="display:none;">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Current Weekzone: <span id="selectedUserCount">0</span> Selected Users
                    </h6>
                </div>
                <div class="card-body" id="weekzoneContent">
                    <!-- Diisi via JS -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Assign Weekzone -->
<div class="modal fade" id="assignWeekzoneModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="assignWeekzoneForm" action="{{ route('setusertimeone.assignWeekzone') }}" method="POST">
                @csrf
                <input type="hidden" name="user_ids" id="modalUserIds" required>

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        Assign Weekzone to <span id="modalUserCount">0</span> Users
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">×</button>
                </div>

                <div class="modal-body">
                    <!-- Progress Bar -->
                    <div id="assignProgressContainer" class="mt-3" style="display:none;">
                        <div class="progress mb-2" style="height: 25px;">
                            <div id="assignProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" 
                                role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                0%
                            </div>
                        </div>
                        <div class="text-center small text-muted" id="assignProgressText">
                           Already Send Data To Device...
                        </div>
                    </div>
                    <!-- Alert Info Kuota -->
                    <div class="alert alert-info small mb-3" id="weekzoneLimitInfo" style="display:none;">
                        This user already has <strong id="usedCount">0</strong> weekzones.
                        Remaining quota: <strong id="remainingCount">3</strong>.
                    </div>

                    <div class="alert alert-info mb-3">
                        Weekzone will be assigned to <strong id="modalUserCount2">0</strong> selected users and
                        <strong id="modalDeviceCount">0</strong> devices.
                    </div>

                    <!-- Select Multiple -->
                    <div class="form-group">
                        <label class="form-label font-weight-bold">Select Weekzone</label>
                        <select class="form-control" id="weekzoneSelect" name="weekzone_ids[]" multiple size="8"
                            style="height: auto !important;">
                            @php
                            $weekzoneDetails = \App\Models\WeekzoneDetail::select('wz')
                                ->distinct()
                                ->orderBy('wz')
                                ->get();
                            @endphp
                            @forelse($weekzoneDetails as $wz)
                            <option value="{{ $wz->wz }}">Weekzone {{ $wz->wz }}</option>
                            @empty
                            <option disabled>No weekzone available.</option>
                            @endforelse
                        </select>
                        <small class="text-muted">Hold Ctrl + Click to select multiple</small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <span id="submitText">Save</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- CSS & JS -->
{{-- Di head atau sebelum </body > --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- Inject routes untuk JS --}}
<script>
    window.setUserTimeoneRoutes = {
        users: "{{ route('setusertimeone.users') }}",
        allusers: "{{ route('setusertimeone.allusers') }}",
        weekzones: "{{ url('setusertimeone/weekzones') }}",
    };
</script>

{{-- Library eksternal --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- JS Utama --}}
<script src="{{ asset('js/setUserTimeone/setUserTimeone.js') }}?v={{ time() }}"></script>

@endsection
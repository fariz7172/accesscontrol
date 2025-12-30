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
                Pilih Device (Machine)
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
                            Tampilkan Semua User (dari semua device)
                        </label>
                    </div>
                </div>

                <!-- Daftar Device -->
                <div class="row" id="deviceList">
                    @foreach($devices as $device)
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input device-checkbox" type="checkbox" value="{{ $device->id }}"
                                id="device_{{ $device->id }}">
                            <label class="form-check-label font-weight-medium" for="device_{{ $device->id }}">
                                <strong>{{ $device->name }}</strong>
                                <small class="text-muted d-block">SN: {{ $device->sn }}</small>
                            </label>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Tombol Pilih Semua / Batal Semua -->
                <div class="mt-3">
                    <button type="button" id="selectAllDevices" class="btn btn-sm btn-outline-primary">
                        Pilih Semua Device
                    </button>
                    <button type="button" id="deselectAllDevices" class="btn btn-sm btn-outline-secondary">
                        Batal Semua
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
                        <h6 class="m-0 font-weight-bold text-primary">Daftar User</h6>
                        <button type="button" class="btn btn-success btn-sm" id="assignWeekzoneBtn"
                            style="display:none;">
                            <i class="fas fa-calendar-alt"></i> Assign Weekzone
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Search Box -->
                        <div class="mb-3">
                            <input type="text" id="userSearchInput" class="form-control form-control-sm"
                                placeholder="Cari nama, card, atau department...">
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm" id="userTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">
                                            <input type="checkbox" id="selectAllUsers"
                                                title="Pilih Semua di Halaman Ini">
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
                            Menampilkan <span id="pageInfo">0</span> dari <span id="totalUsers">0</span> user
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
                        Weekzone Saat Ini: <span id="selectedUserCount">0</span> User Terpilih
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
                        Assign Weekzone ke <span id="modalUserCount">0</span> User
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">×</button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        Weekzone akan di-assign ke <strong id="modalUserCount2">0</strong> user terpilih dan
                        <strong id="modalDeviceCount">0</strong> device.
                    </div>

                    <div class="form-group"
                        style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                        @php
                        $weekzoneDetails = \App\Models\WeekzoneDetail::select('wz')
                        ->distinct()
                        ->orderBy('wz')
                        ->get();
                        @endphp

                        @forelse($weekzoneDetails as $wz)
                        <div class="form-check mb-2">
                            <input class="form-check-input weekzone-checkbox" type="checkbox" name="weekzone_ids[]"
                                value="{{ $wz->wz }}" id="wz_{{ $wz->wz }}">
                            <label class="form-check-label" for="wz_{{ $wz->wz }}">
                                <strong>Weekzone {{ $wz->wz }}</strong>
                            </label>
                        </div>
                        @empty
                        <p class="text-muted">Tidak ada weekzone tersedia.</p>
                        @endforelse
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <span id="submitText">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
    #userSearchInput {
        max-width: 100%;
    }

    #userTable img {
        border-radius: 4px;
        object-fit: cover;
    }

    .page-item.active .page-link {
        background-color: #4e73df;
        border-color: #4e73df;
    }

    .table th {
        font-size: 0.9rem;
        vertical-align: middle;
    }

    .table-sm th,
    .table-sm td {
        font-size: 0.85rem;
    }

    .badge-sm {
        font-size: 0.7rem;
    }

    .alert-dismissible .close {
        padding: 0.5rem 1rem;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deviceCheckboxes = document.querySelectorAll('.device-checkbox');
        const showAllUsersCheckbox = document.getElementById('showAllUsers');
        const userTableContainer = document.getElementById('userTableContainer');
        const userTableBody = document.getElementById('userTableBody');
        const weekzonePanel = document.getElementById('weekzonePanel');
        const weekzoneContent = document.getElementById('weekzoneContent');
        const selectedCount = document.getElementById('selectedCount');
        const assignWeekzoneBtn = document.getElementById('assignWeekzoneBtn');
        const modalUserCount = document.getElementById('modalUserCount');
        const modalUserCount2 = document.getElementById('modalUserCount2');
        const modalDeviceCount = document.getElementById('modalDeviceCount');
        const modalUserIds = document.getElementById('modalUserIds');
        const assignForm = document.getElementById('assignWeekzoneForm');
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');

        let selectedUserIds = new Set();
        let selectedDeviceIds = new Set();
        let allUsers = [];
        let filteredUsers = [];
        let currentPage = 1;
        const perPage = 10;

        // === Update Device Count ===
        function updateSelectedCount() {
            const checked = document.querySelectorAll('.device-checkbox:checked').length;
            selectedCount.textContent = checked;
            modalDeviceCount.textContent = checked;
        }

        // === Update Assign Button ===
        function updateAssignButton() {
            const userCount = selectedUserIds.size;
            const deviceCount = getSelectedDeviceIds().length;
            if (userCount > 0 && deviceCount > 0) {
                assignWeekzoneBtn.style.display = 'inline-block';
                modalUserCount.textContent = userCount;
                modalUserCount2.textContent = userCount;
                document.getElementById('selectedUserCount').textContent = userCount;
            } else {
                assignWeekzoneBtn.style.display = 'none';
            }
        }

        // === Get Selected Device IDs ===
        function getSelectedDeviceIds() {
            return Array.from(document.querySelectorAll('.device-checkbox:checked')).map(cb => cb.value);
        }

        // === DEVICE SELECTION ===
        document.getElementById('selectAllDevices').addEventListener('click', () => {
            if (!showAllUsersCheckbox.checked) {
                deviceCheckboxes.forEach(cb => cb.checked = true);
                updateSelectedCount();
                loadUsersFromSelectedDevices();
            }
        });

        document.getElementById('deselectAllDevices').addEventListener('click', () => {
            deviceCheckboxes.forEach(cb => cb.checked = false);
            updateSelectedCount();
            selectedUserIds.clear();
            selectedDeviceIds.clear();
            userTableContainer.style.display = 'none';
            weekzonePanel.style.display = 'none';
            updateAssignButton();
        });

        deviceCheckboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                updateSelectedCount();
                if (!showAllUsersCheckbox.checked) {
                    const anyChecked = getSelectedDeviceIds().length > 0;
                    if (anyChecked) {
                        loadUsersFromSelectedDevices();
                    } else {
                        userTableContainer.style.display = 'none';
                        weekzonePanel.style.display = 'none';
                        selectedUserIds.clear();
                        updateAssignButton();
                    }
                }
            });
        });

        showAllUsersCheckbox.addEventListener('change', function() {
            selectedUserIds.clear();
            if (this.checked) {
                loadAllUsers();
            } else {
                const anyChecked = getSelectedDeviceIds().length > 0;
                if (anyChecked) {
                    loadUsersFromSelectedDevices();
                } else {
                    userTableContainer.style.display = 'none';
                    weekzonePanel.style.display = 'none';
                    updateAssignButton();
                }
            }
        });

        // === LOAD USERS ===
        function loadUsersFromSelectedDevices() {
            const deviceIds = getSelectedDeviceIds();
            if (deviceIds.length === 0) return;

            userTableBody.innerHTML =
                '<tr><td colspan="7" class="text-center"><div class="spinner-border text-primary"></div></td></tr>';

            fetch(`{{ route('setusertimeone.users') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        device_ids: deviceIds
                    })
                })
                .then(r => r.json())
                .then(users => {
                    renderUserTable(users);
                    userTableContainer.style.display = 'block';
                });
        }

        function loadAllUsers() {
            userTableBody.innerHTML =
                '<tr><td colspan="7" class="text-center"><div class="spinner-border text-primary"></div></td></tr>';

            fetch(`{{ route('setusertimeone.allusers') }}`)
                .then(r => r.json())
                .then(users => {
                    renderUserTable(users);
                    userTableContainer.style.display = 'block';
                });
        }

        // === RENDER USER TABLE ===
        function renderUserTable(users) {
            allUsers = users;
            currentPage = 1;
            applySearchAndPagination();
        }

        function applySearchAndPagination() {
            const searchTerm = document.getElementById('userSearchInput').value.toLowerCase().trim();

            filteredUsers = allUsers.filter(user => {
                return (
                    user.NAME.toLowerCase().includes(searchTerm) ||
                    (user.Card && user.Card.toString().includes(searchTerm)) ||
                    (user.department?.name && user.department.name.toLowerCase().includes(searchTerm))
                );
            });

            const totalPages = Math.ceil(filteredUsers.length / perPage);
            currentPage = Math.min(currentPage, totalPages || 1);

            const start = (currentPage - 1) * perPage;
            const end = start + perPage;
            const pageUsers = filteredUsers.slice(start, end);

            const tbody = document.getElementById('userTableBody');
            tbody.innerHTML = '';

            if (pageUsers.length === 0) {
                tbody.innerHTML =
                    '<tr><td colspan="7" class="text-center text-muted py-4">Tidak ada user ditemukan.</td></tr>';
            } else {
                pageUsers.forEach(user => {
                    const checked = selectedUserIds.has(user.ID.toString()) ? 'checked' : '';
                    const row = `
                    <tr>
                        <td>
                            <div class="form-check">
                                <input class="form-check-input user-checkbox" type="checkbox"
                                       value="${user.ID}" id="user_${user.ID}" ${checked}>
                            </div>
                        </td>
                        <td><label for="user_${user.ID}" class="m-0 font-weight-medium">${user.NAME}</label></td>
                        <td>${user.Card || '—'}</td>
                        <td>${user.department?.name || '—'}</td>
                        <td>${user.BEGIN_DATE || '—'}</td>
                        <td>${user.END_DATE || '—'}</td>
                        <td>
                            ${user.photo 
                                ? `<img src="${user.photo}" class="img-thumbnail" style="width:40px;height:40px;object-fit:cover;">` 
                                : '<span class="text-muted small">No Photo</span>'
                            }
                        </td>
                    </tr>`;
                    tbody.innerHTML += row;
                });
            }

            renderPagination(totalPages);
            document.getElementById('totalUsers').textContent = filteredUsers.length;
            document.getElementById('pageInfo').textContent =
                `${start + 1} - ${Math.min(end, filteredUsers.length)}`;

            document.querySelectorAll('.user-checkbox').forEach(cb => {
                cb.removeEventListener('change', handleUserCheckboxChange);
                cb.addEventListener('change', handleUserCheckboxChange);
            });

            updateAssignButton();
        }

        function renderPagination(totalPages) {
            const pagination = document.getElementById('userPagination');
            pagination.innerHTML = '';

            if (totalPages <= 1) return;

            const prevLi = document.createElement('li');
            prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
            prevLi.innerHTML = `<a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>`;
            pagination.appendChild(prevLi);

            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                    const li = document.createElement('li');
                    li.className = `page-item ${i === currentPage ? 'active' : ''}`;
                    li.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
                    pagination.appendChild(li);
                } else if (i === currentPage - 2 || i === currentPage + 2) {
                    const li = document.createElement('li');
                    li.className = 'page-item disabled';
                    li.innerHTML = `<span class="page-link">...</span>`;
                    pagination.appendChild(li);
                }
            }

            const nextLi = document.createElement('li');
            nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
            nextLi.innerHTML = `<a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>`;
            pagination.appendChild(nextLi);

            pagination.querySelectorAll('.page-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const page = parseInt(this.getAttribute('data-page'));
                    if (page && page !== currentPage) {
                        currentPage = page;
                        applySearchAndPagination();
                    }
                });
            });
        }

        // === SEARCH & SELECT ALL ===
        document.getElementById('userSearchInput').addEventListener('input', function() {
            currentPage = 1;
            applySearchAndPagination();
        });

        document.getElementById('selectAllUsers').addEventListener('change', function() {
            const isChecked = this.checked;
            document.querySelectorAll('.user-checkbox').forEach(cb => {
                cb.checked = isChecked;
                const userId = cb.value;
                if (isChecked) selectedUserIds.add(userId);
                else selectedUserIds.delete(userId);
            });
            loadWeekzonesForSelectedUsers();
            updateAssignButton();
        });

        // === USER CHECKBOX CHANGE ===
        function handleUserCheckboxChange() {
            const userId = this.value;
            if (this.checked) {
                selectedUserIds.add(userId);
            } else {
                selectedUserIds.delete(userId);
            }
            loadWeekzonesForSelectedUsers();
            updateAssignButton();
        }

        // === LOAD WEEKZONES (Preview) ===
        function loadWeekzonesForSelectedUsers() {
            if (selectedUserIds.size === 0) {
                weekzonePanel.style.display = 'none';
                return;
            }

            weekzonePanel.style.display = 'block';
            weekzoneContent.innerHTML =
                '<div class="text-center py-3"><div class="spinner-border text-success"></div></div>';

            const promises = Array.from(selectedUserIds).map(userId =>
                fetch(`{{ route('setusertimeone.weekzones', '') }}/${userId}`).then(r => r.json())
            );

            Promise.all(promises)
                .then(results => {
                    let html = `<h6 class="text-primary mb-3">${selectedUserIds.size} User Terpilih</h6>`;
                    results.forEach(data => {
                        const user = data.user;
                        html += `<div class="mb-4 p-3 border rounded bg-light">`;
                        html +=
                            `<strong>${user.NAME}</strong> <small class="text-muted">(Card: ${user.Card || 'N/A'})</small><br>`;
                        html += `<small class="text-info">Device: ${data.device}</small>`;

                        const viaDevice = data.via_device || [];
                        if (viaDevice.length > 0) {
                            html +=
                                `<div class="mt-2"><small class="text-info font-weight-bold">Via Device:</small></div>`;
                            html += renderWeekzoneTable(viaDevice.map(i => i.weekzone));
                        } else {
                            html += `<p class="text-warning mt-2">Belum ada weekzone via device.</p>`;
                        }

                        const viaUser = data.via_user || [];
                        if (viaUser.length > 0) {
                            html +=
                                `<div class="mt-2"><small class="text-success font-weight-bold">Via User:</small></div>`;
                            html += renderWeekzoneTable(viaUser.map(i => i.weekzone));
                        }

                        html += `</div>`;
                    });
                    weekzoneContent.innerHTML = html;
                })
                .catch(() => {
                    weekzoneContent.innerHTML = '<p class="text-danger">Gagal memuat weekzone.</p>';
                });
        }

        function renderWeekzoneTable(weekzones) {
            if (!weekzones || weekzones.length === 0) return '<p class="text-muted">—</p>';
            let table =
                `<div class="table-responsive mt-2"><table class="table table-sm table-bordered"><thead class="thead-light"><tr><th>WZ</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th><th>Sun</th></tr></thead><tbody>`;
            weekzones.forEach(wz => {
                const details = wz.weekzone_details || [];
                const dayFields = ['day1', 'day2', 'day3', 'day4', 'day5', 'day6', 'day7'];
                const days = dayFields.map(f => {
                    const d = details.find(x => x[f]);
                    return d && d[f] ? d[f].Name : 'None';
                });
                table +=
                    `<tr><td><strong>${wz.Name || wz.ID}</strong></td>${days.map(d => `<td><span class="badge ${d==='None'?'badge-secondary':'badge-success'} badge-sm">${d}</span></td>`).join('')}</tr>`;
            });
            return table + `</tbody></table></div>`;
        }

        // === MODAL OPEN ===
        assignWeekzoneBtn.addEventListener('click', function() {
            // Bersihkan device_ids[] lama
            assignForm.querySelectorAll('input[name="device_ids[]"]').forEach(el => el.remove());

            // Set user_ids
            modalUserIds.value = Array.from(selectedUserIds).join(',');

            // Tambahkan device_ids[] sebagai array
            getSelectedDeviceIds().forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'device_ids[]';
                input.value = id;
                assignForm.appendChild(input);
            });

            $('#assignWeekzoneModal').modal('show');
        });

        // === FORM SUBMIT ===
        assignForm.addEventListener('submit', function(e) {
            const checkedWeekzones = document.querySelectorAll('.weekzone-checkbox:checked').length;
            const selectedDevices = getSelectedDeviceIds().length;

            if (checkedWeekzones === 0) {
                e.preventDefault();
                alert('Pilih minimal satu weekzone!');
                return;
            }
            if (selectedUserIds.size === 0) {
                e.preventDefault();
                alert('Tidak ada user yang dipilih!');
                return;
            }
            if (selectedDevices === 0) {
                e.preventDefault();
                alert('Pilih minimal satu device!');
                return;
            }

            submitBtn.disabled = true;
            submitText.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';
        });

        updateAssignButton();
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const MAX_WZ = 3;
        let selectedUserIds = new Set();
        let allUsers = [],
            filteredUsers = [],
            currentPage = 1,
            perPage = 10;

        // === Elements ===
        const els = {
            deviceCheckboxes: document.querySelectorAll('.device-checkbox'),
            showAll: document.getElementById('showAllUsers'),
            userTableContainer: document.getElementById('userTableContainer'),
            userTableBody: document.getElementById('userTableBody'),
            weekzonePanel: document.getElementById('weekzonePanel'),
            weekzoneContent: document.getElementById('weekzoneContent'),
            selectedCount: document.getElementById('selectedCount'),
            assignBtn: document.getElementById('assignWeekzoneBtn'),
            modalUserCount: document.getElementById('modalUserCount'),
            modalUserCount2: document.getElementById('modalUserCount2'),
            modalDeviceCount: document.getElementById('modalDeviceCount'),
            modalUserIds: document.getElementById('modalUserIds'),
            form: document.getElementById('assignWeekzoneForm'),
            submitBtn: document.getElementById('submitBtn'),
            submitText: document.getElementById('submitText'),
            limitAlert: document.getElementById('weekzoneLimitAlert'),
            usedCount: document.getElementById('usedCount'),
            remainingCount: document.getElementById('remainingCount')
        };

        // === Device Selection ===
        const getSelectedDeviceIds = () => Array.from(document.querySelectorAll('.device-checkbox:checked')).map(
            cb => cb.value);
        const updateCounts = () => {
            const checked = getSelectedDeviceIds().length;
            els.selectedCount.textContent = checked;
            els.modalDeviceCount.textContent = checked;
        };
        const updateAssignBtn = () => {
            const userCount = selectedUserIds.size;
            const deviceCount = getSelectedDeviceIds().length;
            if (userCount > 0 && deviceCount > 0) {
                els.assignBtn.style.display = 'inline-block';
                els.modalUserCount.textContent = els.modalUserCount2.textContent = userCount;
                document.getElementById('selectedUserCount').textContent = userCount;
            } else {
                els.assignBtn.style.display = 'none';
            }
        };

        document.getElementById('selectAllDevices').onclick = () => {
            if (!els.showAll.checked) {
                els.deviceCheckboxes.forEach(cb => cb.checked = true);
                updateCounts();
                loadUsersFromSelectedDevices();
            }
        };
        document.getElementById('deselectAllDevices').onclick = () => {
            els.deviceCheckboxes.forEach(cb => cb.checked = false);
            updateCounts();
            selectedUserIds.clear();
            els.userTableContainer.style.display = 'none';
            els.weekzonePanel.style.display = 'none';
            updateAssignBtn();
        };
        els.deviceCheckboxes.forEach(cb => cb.onchange = () => {
            updateCounts();
            if (!els.showAll.checked) {
                getSelectedDeviceIds().length ? loadUsersFromSelectedDevices() : clearUserTable();
            }
        });
        els.showAll.onchange = () => {
            selectedUserIds.clear();
            els.showAll.checked ? loadAllUsers() : (getSelectedDeviceIds().length ?
                loadUsersFromSelectedDevices() : clearUserTable());
        });

    // === Load Users ===
    const loadUsersFromSelectedDevices = () => {
        const ids = getSelectedDeviceIds();
        if (!ids.length) return;
        els.userTableBody.innerHTML =
            '<tr><td colspan="7" class="text-center"><div class="spinner-border text-primary"></div></td></tr>';
        fetch(`{{ route('setusertimeone.users') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                device_ids: ids
            })
        }).then(r => r.json()).then(renderUserTable).then(() => els.userTableContainer.style.display =
            'block');
    };
    const loadAllUsers = () => {
        els.userTableBody.innerHTML =
            '<tr><td colspan="7" class="text-center"><div class="spinner-border text-primary"></div></td></tr>';
        fetch(`{{ route('setusertimeone.allusers') }}`)
            .then(r => r.json()).then(renderUserTable).then(() => els.userTableContainer.style.display =
                'block');
    };
    const clearUserTable = () => {
        els.userTableContainer.style.display = 'none';
        els.weekzonePanel.style.display = 'none';
        updateAssignBtn();
    };

    // === Render Table ===
    const renderUserTable = users => {
        allUsers = users;
        currentPage = 1;
        applySearchAndPagination();
    };
    const applySearchAndPagination = () => {
        const term = document.getElementById('userSearchInput').value.toLowerCase();
        filteredUsers = allUsers.filter(u =>
            u.NAME.toLowerCase().includes(term) ||
            (u.Card && u.Card.toString().includes(term)) ||
            (u.department?.name && u.department.name.toLowerCase().includes(term))
        );
        const totalPages = Math.ceil(filteredUsers.length / perPage) || 1;
        currentPage = Math.min(currentPage, totalPages);
        const start = (currentPage - 1) * perPage;
        const end = start + perPage;
        const pageUsers = filteredUsers.slice(start, end);

        const tbody = els.userTableBody;
        tbody.innerHTML = pageUsers.length ? pageUsers.map(u => `
            <tr>
                <td><input class="form-check-input user-checkbox" type="checkbox" value="${u.ID}" id="user_${u.ID}" ${selectedUserIds.has(u.ID.toString()) ? 'checked' : ''}></td>
                <td><label for="user_${u.ID}" class="m-0">${u.NAME}</label></td>
                <td>${u.Card || '—'}</td>
                <td>${u.department?.name || '—'}</td>
                <td>${u.BEGIN_DATE || '—'}</td>
                <td>${u.END_DATE || '—'}</td>
                <td>${u.photo ? `<img src="${u.photo}" style="width:40px;height:40px;object-fit:cover;border-radius:4px;">` : '<small class="text-muted">No</small>'}</td>
            </tr>
        `).join('') : '<tr><td colspan="7" class="text-center text-muted py-4">Tidak ada user.</td></tr>';

        renderPagination(totalPages);
        document.getElementById('totalUsers').textContent = filteredUsers.length;
        document.getElementById('pageInfo').textContent =
            `${start + 1} - ${Math.min(end, filteredUsers.length)}`;
        document.querySelectorAll('.user-checkbox').forEach(cb => cb.onchange = handleUserChange);
        updateAssignBtn();
    };
    const renderPagination = totalPages => {
        const ul = document.getElementById('userPagination');
        ul.innerHTML = '';
        if (totalPages <= 1) return;
        const prev =
            `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}"><a class="page-link" data-page="${currentPage - 1}">Prev</a></li>`;
        ul.innerHTML += prev;
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                ul.innerHTML +=
                    `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" data-page="${i}">${i}</a></li>`;
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                ul.innerHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }
        ul.innerHTML +=
            `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}"><a class="page-link" data-page="${currentPage + 1}">Next</a></li>`;
        ul.querySelectorAll('.page-link').forEach(l => l.onclick = e => {
            e.preventDefault();
            const p = parseInt(l.dataset.page);
            if (p && p !== currentPage) {
                currentPage = p;
                applySearchAndPagination();
            }
        });
    };

    // === Search & Select All ===
    document.getElementById('userSearchInput').oninput = () => {
        currentPage = 1;
        applySearchAndPagination();
    }; document.getElementById('selectAllUsers').onchange = function() {
        document.querySelectorAll('.user-checkbox').forEach(cb => {
            cb.checked = this.checked;
            this.checked ? selectedUserIds.add(cb.value) : selectedUserIds.delete(cb.value);
        });
        loadWeekzonesForSelectedUsers();
        updateAssignBtn();
    };
    const handleUserChange = function() {
        this.checked ? selectedUserIds.add(this.value) : selectedUserIds.delete(this.value);
        loadWeekzonesForSelectedUsers();
        updateAssignBtn();
    };

    // === Preview Weekzone ===
    const loadWeekzonesForSelectedUsers = () => {
        if (!selectedUserIds.size) {
            els.weekzonePanel.style.display = 'none';
            return;
        }
        els.weekzonePanel.style.display = 'block';
        els.weekzoneContent.innerHTML =
            '<div class="text-center py-3"><div class="spinner-border text-success"></div></div>';
        Promise.all(Array.from(selectedUserIds).map(id =>
            fetch(`{{ route('setusertimeone.weekzones', '') }}/${id}`).then(r => r.json())
        )).then(results => {
            let html = `<h6 class="text-primary mb-3">${selectedUserIds.size} User</h6>`;
            results.forEach(d => {
                const u = d.user;
                html += `<div class="mb-4 p-3 border rounded bg-light">
                    <strong>${u.NAME}</strong> <small class="text-muted">(Card: ${u.Card || 'N/A'})</small><br>
                    <small class="text-info">Device: ${d.device}</small>`;
                const viaD = d.via_device || [],
                    viaU = d.via_user || [];
                if (viaD.length) html +=
                    `<div class="mt-2"><small class="text-info font-weight-bold">Via Device:</small></div>${renderTable(viaD.map(i => i.weekzone))}`;
                if (viaU.length) html +=
                    `<div class="mt-2"><small class="text-success font-weight-bold">Via User:</small></div>${renderTable(viaU.map(i => i.weekzone))}`;
                html += `</div>`;
            });
            els.weekzoneContent.innerHTML = html;
        }).catch(() => els.weekzoneContent.innerHTML = '<p class="text-danger">Gagal memuat.</p>');
    };
    const renderTable = wzs => {
        if (!wzs?.length) return '<p class="text-muted">—</p>';
        let t =
            `<div class="table-responsive mt-2"><table class="table table-sm table-bordered"><thead class="thead-light"><tr><th>WZ</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th><th>Sun</th></tr></thead><tbody>`;
        wzs.forEach(w => {
            const det = w.weekzone_details || [];
            const days = ['day1', 'day2', 'day3', 'day4', 'day5', 'day6', 'day7'].map(f => {
                const d = det.find(x => x[f]);
                return d?.[f]?.Name || 'None';
            });
            t +=
                `<tr><td><strong>${w.Name || w.ID}</strong></td>${days.map(d => `<td><span class="badge ${d==='None'?'badge-secondary':'badge-success'} badge-sm">${d}</span></td>`).join('')}</tr>`;
        });
        return t + `</tbody></table></div>`;
    };

    // === Modal ===
    els.assignBtn.onclick = () => {
        els.form.querySelectorAll('input[name="device_ids[]"]').forEach(el => el.remove());
        els.modalUserIds.value = Array.from(selectedUserIds).join(',');
        getSelectedDeviceIds().forEach(id => {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'device_ids[]';
            inp.value = id;
            els.form.appendChild(inp);
        });

        // Hitung batasan
        fetch(`{{ route('setusertimeone.weekzones', '') }}/${Array.from(selectedUserIds)[0]}`)
            .then(r => r.json())
            .then(data => {
                const used = (data.via_user || []).length;
                const remaining = MAX_WZ - used;
                els.usedCount.textContent = used;
                els.remainingCount.textContent = remaining > 0 ? remaining : 0;
                els.limitAlert.style.display = used > 0 ? 'block' : 'none';

                if (remaining <= 0) {
                    document.querySelectorAll('.weekzone-checkbox').forEach(cb => {
                        cb.disabled = true;
                        cb.checked = false;
                    });
                    alert('Maksimal 3 weekzone per user!');
                } else {
                    document.querySelectorAll('.weekzone-checkbox').forEach(cb => cb.disabled = false);
                }
            });

        $('#assignWeekzoneModal').modal('show');
    };

    els.form.onsubmit = e => {
        const checked = document.querySelectorAll('.weekzone-checkbox:checked').length;
        if (!checked || !selectedUserIds.size || !getSelectedDeviceIds().length) {
            e.preventDefault();
            alert('Pilih weekzone, user, dan device!');
            return;
        }
        els.submitBtn.disabled = true;
        els.submitText.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';
    };

    updateAssignBtn();
    });
</script>
@endsection
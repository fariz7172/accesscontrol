@extends('layout_background.header')

<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">
    <!-- Main Content -->
    <div id="content">
        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
            <!-- Sidebar Toggle (Topbar) -->
            <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                <i class="fa fa-bars"></i>
            </button>

            <form id="searchForm" action="{{ route('logUserFrontend') }}" method="GET" class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                <input type="hidden" name="model" value="{{ request('model') }}">
                <div class="input-group">
                    <input type="text" name="search2" class="form-control bg-light border-0 small" placeholder="Cari Data User" aria-label="Search" aria-describedby="basic-addon2" value="{{ request('search2') }}">
                    <div class="input-group-append">
                        <button id="searchButton2" class="btn btn-primary" type="submit">
                            <i class="fas fa-search fa-sm"></i>
                        </button>
                    </div>
                </div>
            </form>

            <!-- Topbar Navbar -->
            <ul class="navbar-nav ml-auto">
                <div class="topbar-divider d-none d-sm-block"></div>
                <!-- Nav Item - User Information -->
                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="/sesi/logout" id="userDropdown" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="mr-2 d-none d-lg-inline text-gray-600 small"></span>
                        <img class="img-profile rounded-circle" src="{{ asset('template') }}/img/undraw_profile.svg">
                    </a>
                    <!-- Dropdown - User Information -->
                    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                        aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="/setDatabase">
                            <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                            Settings
                        </a>
                        <a class="dropdown-item" href="/sesi/logout" data-toggle="modal" data-target="#logoutModal">
                            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                            Logout
                        </a>
                    </div>
                </li>
            </ul>
        </nav>

        <div class="container-fluid">
            <h1 class="h3 mb-2 text-gray-800">Management User Log</h1>

            <!-- Filter Form dan Back Button -->
            <div class="d-flex py-2 px-2 sm-3" id="filterForm">
                <form id="dateFilterForm" action="{{ route('logUserFrontend') }}" method="GET" class="d-flex md-3 sm-3">
                    <input type="date" class="form-control mr-2" name="date_from" id="date_from"
                        value="{{ $dateFrom ? $dateFrom->toDateString() : now()->toDateString() }}">
                    <input type="date" class="form-control mr-2" name="date_to" id="date_to"
                        value="{{ $dateTo ? $dateTo->toDateString() : now()->toDateString() }}">
                    <select name="branch_id" id="branch_id" class="form-control mr-2">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                        @endforeach
                    </select>
                    <select name="dep_id" id="dep_id" class="form-control mr-2">
                        <option value="">All Departments</option>
                        @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ $depId == $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary mb-3 sm-3" style="height:40px;">Filter</button>
                </form>
                <div class="row">
                    <div class="col mb-3"></div>
                    <a href="/" class="btn btn-success mb-3" style="height:40px;">Back</a>
                </div>
            </div>

            <!-- Notifikasi untuk pembaruan data -->
            <div id="updateNotification" class="alert alert-success alert-dismissible fade show d-none" role="alert">
                New logs have been added!
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="logTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>User Name</th>
                                    <th>Branch</th>
                                    <th>Department</th>
                                    <th>Event Time</th>
                                    <th>Status</th>
                                    <th>Device Name</th>
                                    <th>User Picture</th>
                                </tr>
                            </thead>
                            <tbody id="logTableBody">
                                @foreach($logData as $key => $index)
                                <tr data-timestamp="{{ $index->TM_EVENT }}" data-user-addr="{{ $index->USER_ADDR }}">
                                    <td>{{ $logData->firstItem() + $key }}</td>
                                    <td>{{ $index->userProfile->NAME ?? 'N/A' }}</td>
                                    <td>{{ $index->userProfile->branch->name ?? 'N/A' }}</td>
                                    <td>{{ $index->userProfile->department->name ?? 'N/A' }}</td>
                                    <td>{{ $index->TM_EVENT }}</td>
                                    <td class="status-cell">
                                        @php
                                            $status = $index->stat == '1' ? 'CARD EXPIRED' : ($index->stat == '0' ? 'CARD ACTIVE' : 'N/A');
                                            // Simpan status ke localStorage
                                            echo "<script>localStorage.setItem('status_{$index->USER_ADDR}_{$index->TM_EVENT}', '$status');</script>";
                                        @endphp
                                        <span class="{{ $status == 'CARD EXPIRED' ? 'text-danger' : ($status == 'CARD ACTIVE' ? 'text-success' : '') }}">{{ $status }}</span>
                                    </td>
                                    <td>{{ $index->device_name }}</td>
                                    <td>
                                        @if($index->userProfile && $index->userProfile->photo)
                                        <img src="{{ strpos($index->userProfile->photo, 'data:image') === 0 ? $index->userProfile->photo : 'data:image/jpeg;base64,' . $index->userProfile->photo }}"
                                            alt="User Photo"
                                            style="max-width: 100px; max-height: 100px;">
                                        @else
                                        No Image
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $logData->appends(['date_from' => request('date_from'), 'date_to' => request('date_to'), 'branch_id' => request('branch_id'), 'dep_id' => request('dep_id'), 'search2' => request('search2')])->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            $(document).ready(function() {
                // Muat filter dari localStorage saat halaman dimuat
                function loadFiltersFromStorage() {
                    let dateFrom = localStorage.getItem('filter_date_from');
                    let dateTo = localStorage.getItem('filter_date_to');
                    let branchId = localStorage.getItem('filter_branch_id');
                    let depId = localStorage.getItem('filter_dep_id');
                    let search2 = localStorage.getItem('filter_search2');

                    if (dateFrom) $('#date_from').val(dateFrom);
                    if (dateTo) $('#date_to').val(dateTo);
                    if (branchId) $('#branch_id').val(branchId);
                    if (depId) $('#dep_id').val(depId);
                    if (search2) $('#searchForm input[name="search2"]').val(search2);
                }

                // Simpan filter ke localStorage saat form disubmit
                $('#dateFilterForm').on('submit', function(e) {
                    e.preventDefault();
                    let dateFrom = $('#date_from').val();
                    let dateTo = $('#date_to').val();
                    let branchId = $('#branch_id').val();
                    let depId = $('#dep_id').val();
                    let search2 = $('#searchForm input[name="search2"]').val();

                    // Simpan filter ke localStorage
                    localStorage.setItem('filter_date_from', dateFrom);
                    localStorage.setItem('filter_date_to', dateTo);
                    localStorage.setItem('filter_branch_id', branchId);
                    localStorage.setItem('filter_dep_id', depId);
                    localStorage.setItem('filter_search2', search2);

                    // Perbarui URL dengan parameter filter
                    let url = '{{ route("logUserFrontend") }}' + '?date_from=' + encodeURIComponent(dateFrom) +
                              '&date_to=' + encodeURIComponent(dateTo) +
                              '&branch_id=' + encodeURIComponent(branchId) +
                              '&dep_id=' + encodeURIComponent(depId) +
                              '&search2=' + encodeURIComponent(search2);
                    window.location.href = url;
                });

                // Event listener untuk form pencarian
                $('#searchForm').on('submit', function(e) {
                    e.preventDefault();
                    let dateFrom = $('#date_from').val();
                    let dateTo = $('#date_to').val();
                    let branchId = $('#branch_id').val();
                    let depId = $('#dep_id').val();
                    let search2 = $(this).find('input[name="search2"]').val();

                    // Simpan filter ke localStorage
                    localStorage.setItem('filter_date_from', dateFrom);
                    localStorage.setItem('filter_date_to', dateTo);
                    localStorage.setItem('filter_branch_id', branchId);
                    localStorage.setItem('filter_dep_id', depId);
                    localStorage.setItem('filter_search2', search2);

                    // Perbarui URL dengan parameter filter
                    let url = '{{ route("logUserFrontend") }}' + '?date_from=' + encodeURIComponent(dateFrom) +
                              '&date_to=' + encodeURIComponent(dateTo) +
                              '&branch_id=' + encodeURIComponent(branchId) +
                              '&dep_id=' + encodeURIComponent(depId) +
                              '&search2=' + encodeURIComponent(search2);
                    window.location.href = url;
                });

                // Terapkan status dari localStorage ke baris yang ada
                $('#logTableBody tr').each(function() {
                    let userAddr = $(this).data('user-addr');
                    let tmEvent = $(this).data('timestamp');
                    let storedStatus = localStorage.getItem(`status_${userAddr}_${tmEvent}`);
                    if (storedStatus) {
                        let statusHtml = storedStatus === 'CARD EXPIRED' 
                            ? '<span class="text-danger">CARD EXPIRED</span>'
                            : storedStatus === 'CARD ACTIVE'
                            ? '<span class="text-success">CARD ACTIVE</span>'
                            : '<span>N/A</span>';
                        $(this).find('.status-cell').html(statusHtml);
                    }
                });

                // Fungsi untuk memuat ulang tabel berdasarkan filter
                function loadFilteredLogs(dateFrom, dateTo, branchId, depId, search2) {
                    $.ajax({
                        url: '{{ route("logUserFrontend") }}',
                        method: 'GET',
                        data: {
                            date_from: dateFrom,
                            date_to: dateTo,
                            branch_id: branchId,
                            dep_id: depId,
                            search2: search2
                        },
                        success: function(response) {
                            let tbody = $('#logTableBody');
                            tbody.empty();
                            let logData = $(response).find('#logTableBody').html();
                            tbody.html(logData);
                            let pagination = $(response).find('.pagination').html();
                            $('.d-flex.justify-content-center.mt-4').html(pagination);
                            console.log('Table updated with filtered data');

                            // Terapkan status dari localStorage ke baris baru
                            $('#logTableBody tr').each(function() {
                                let userAddr = $(this).data('user-addr');
                                let tmEvent = $(this).data('timestamp');
                                let storedStatus = localStorage.getItem(`status_${userAddr}_${tmEvent}`);
                                if (storedStatus) {
                                    let statusHtml = storedStatus === 'CARD EXPIRED' 
                                        ? '<span class="text-danger">CARD EXPIRED</span>'
                                        : storedStatus === 'CARD ACTIVE'
                                        ? '<span class="text-success">CARD ACTIVE</span>'
                                        : '<span>N/A</span>';
                                    $(this).find('.status-cell').html(statusHtml);
                                }
                            });
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading filtered logs:', error);
                        }
                    });
                }

                // Fungsi polling untuk log terbaru
                function pollLogs() {
                    let lastTimestamp = $('#logTableBody tr:first').data('timestamp') || '';
                    let dateFrom = $('#date_from').val() || localStorage.getItem('filter_date_from') || '{{ now()->toDateString() }}';
                    let dateTo = $('#date_to').val() || localStorage.getItem('filter_date_to') || '{{ now()->toDateString() }}';
                    let branchId = $('#branch_id').val() || localStorage.getItem('filter_branch_id') || '';
                    let depId = $('#dep_id').val() || localStorage.getItem('filter_dep_id') || '';
                    let search2 = $('#searchForm input[name="search2"]').val() || localStorage.getItem('filter_search2') || '';

                    console.log('Polling with Last Timestamp:', lastTimestamp);
                    console.log('Date From:', dateFrom, 'Date To:', dateTo, 'Branch ID:', branchId, 'Dep ID:', depId, 'Search:', search2);

                    $.ajax({
                        url: '{{ route("getLatestLogs1") }}',
                        method: 'GET',
                        data: {
                            last_timestamp: lastTimestamp,
                            date_from: dateFrom,
                            date_to: dateTo,
                            branch_id: branchId,
                            dep_id: depId,
                            search2: search2
                        },
                        success: function(response) {
                            console.log('New Logs Received:', response);
                            if (response.length > 0) {
                                response.forEach(function(log) {
                                    let status = log.stat == '1' 
                                        ? '<span class="text-danger">CARD EXPIRED</span>' 
                                        : (log.stat == '0' 
                                            ? '<span class="text-success">CARD ACTIVE</span>' 
                                            : '<span>N/A</span>');
                                    localStorage.setItem(`status_${log.USER_ADDR}_${log.TM_EVENT}`, log.stat == '1' ? 'CARD EXPIRED' : (log.stat == '0' ? 'CARD ACTIVE' : 'N/A'));

                                    let photo = log.user_profile && log.user_profile.photo ?
                                        `<img src="${log.user_profile.photo.startsWith('data:image') ? log.user_profile.photo : 'data:image/jpeg;base64,' + log.user_profile.photo}" 
                                              alt="User Photo" 
                                              style="max-width: 100px; max-height: 100px;">` :
                                        'No Image';

                                    let branchName = log.user_profile && log.user_profile.branch ? log.user_profile.branch.name : 'N/A';
                                    let departmentName = log.user_profile && log.user_profile.department ? log.user_profile.department.name : 'N/A';
                                    let deviceName = log.device_name || 'N/A';

                                    let rowCount = $('#logTableBody tr').length;
                                    let no = rowCount > 0 ? parseInt($('#logTableBody tr:first td:first').text()) + 1 : 1;

                                    let newRow = `
                                        <tr data-timestamp="${log.TM_EVENT}" data-user-addr="${log.USER_ADDR}">
                                            <td>${no}</td>
                                            <td>${log.user_profile?.NAME || 'N/A'}</td>
                                            <td>${branchName}</td>
                                            <td>${departmentName}</td>
                                            <td>${log.TM_EVENT}</td>
                                            <td class="status-cell">${status}</td>
                                            <td>${deviceName}</td>
                                            <td>${photo}</td>
                                        </tr>
                                    `;
                                    $('#logTableBody').prepend(newRow);

                                    // Tampilkan notifikasi pembaruan
                                    $('#updateNotification').removeClass('d-none').fadeIn();
                                    setTimeout(() => $('#updateNotification').fadeOut().addClass('d-none'), 3000);

                                    if ($('#logTableBody tr').length > 50) {
                                        $('#logTableBody tr:last').remove();
                                    }

                                    updateRowNumbers();
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.log('Error polling logs:', xhr.status, status, error);
                            console.log('Response:', xhr.responseText);
                        },
                        complete: function() {
                            setTimeout(pollLogs, 10000); // Interval polling 10 detik
                        }
                    });
                }

                function updateRowNumbers() {
                    $('#logTableBody tr').each(function(index) {
                        $(this).find('td:first').text(index + 1);
                    });
                }

                // Panggil fungsi untuk memuat filter dari localStorage
                loadFiltersFromStorage();

                // Mulai polling
                pollLogs();
            });
        </script>
        @extends('layout_background.footer')
@extends('layout_background.app_layouts')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Management User Log</h1>

    <!-- Filter Form dan Excel Button -->
    <div class="d-flex py-2 px-2 sm-3" id="filterForm">
        <form id="dateFilterForm" action="{{ route('logUser') }}" method="GET" class="d-flex md-3 sm-3">
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
            <a href="#" id="exportExcel" class="btn btn-success mb-3" style="height:40px;">Excel</a>
        </div>
    </div>

    <div class="">
        <div class="row">
            <div class="col">
                <div class="card mb-3 border border-primary ">
                    <div class="card-body d-flex align-items-center ml-3">
                        <i class="fas fa-user" style="font-size: 2.5em;"></i>
                        <h1 class="ms-2 ml-3">Member : {{ $memberCount }}</h1>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card mb-3 bg-primary text-white">
                    <div class="card-body d-flex align-items-center ml-3">
                        <i class="fas fa-address-card" style="font-size: 2.5em;"></i>
                        <h1 class="ms-2 ml-3">Employee : {{ $employeeCount }}</h1>
                    </div>
                </div>
            </div>
        </div>
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
                            <th>Card</th>
                            <th>User Picture</th>
                        </tr>
                    </thead>
                    <tbody id="logTableBody">
                        @foreach($logData as $key => $index)
                        <tr data-timestamp="{{ $index->TM_EVENT }}">
                            <td>{{ $logData->firstItem() + $key }}</td>
                            <td>{{ $index->userProfile->NAME ?? 'N/A' }}</td>
                            <td>{{ $index->userProfile->branch->name ?? 'N/A' }}</td>
                            <td>{{ $index->userProfile->department->name ?? 'N/A' }}</td>
                            <td>{{ $index->TM_EVENT }}</td>
                            <td>
                                @if($index->userProfile && $index->userProfile->END_DATE)
                                @if(Carbon\Carbon::parse($index->userProfile->END_DATE)->isPast())
                                <span class="text-danger">CARD EXPIRED</span>
                                @else
                                <span class="text-success">ACTIVE</span>
                                @endif
                                @else
                                <span>N/A</span>
                                @endif
                            </td>
                            <td>{{ $index->device_name }}</td>
                            <td>{{ $index->card ?? 'N/A' }}</td>
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
                    {{ $logData->appends(['date_from' => request('date_from'), 'date_to' => request('date_to'), 'branch_id' => request('branch_id'), 'dep_id' => request('dep_id')])->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Fungsi untuk memuat ulang tabel berdasarkan filter
        function loadFilteredLogs(dateFrom, dateTo, branchId, depId) {
            $.ajax({
                url: '{{ route("logUser") }}',
                method: 'GET',
                data: {
                    date_from: dateFrom,
                    date_to: dateTo,
                    branch_id: branchId,
                    dep_id: depId
                },
                success: function(response) {
                    let tbody = $('#logTableBody');
                    tbody.empty();
                    let logData = $(response).find('#logTableBody').html();
                    tbody.html(logData);
                    let pagination = $(response).find('.pagination').html();
                    $('.d-flex.justify-content-center.mt-4').html(pagination);
                    console.log('Table updated with filtered data');
                },
                error: function(xhr, status, error) {
                    console.error('Error loading filtered logs:', error);
                }
            });
        }

        // Event listener untuk submit form filter
        $('#dateFilterForm').on('submit', function(e) {
            e.preventDefault();
            let dateFrom = $('#date_from').val();
            let dateTo = $('#date_to').val();
            let branchId = $('#branch_id').val();
            let depId = $('#dep_id').val();
            console.log('Filtering logs - Date From:', dateFrom, 'Date To:', dateTo, 'Branch ID:', branchId, 'Dep ID:', depId);
            loadFilteredLogs(dateFrom, dateTo, branchId, depId);
        });

        // Fungsi polling untuk log terbaru dan counts
        function pollLogs() {
            let lastTimestamp = $('#logTableBody tr:first').data('timestamp') || '';
            let dateFrom = $('#date_from').val();
            let dateTo = $('#date_to').val();
            let branchId = $('#branch_id').val();
            let depId = $('#dep_id').val();

            console.log('Polling with Last Timestamp:', lastTimestamp);
            console.log('Date From:', dateFrom, 'Date To:', dateTo, 'Branch ID:', branchId, 'Dep ID:', depId);

            // Fetch new logs
            $.ajax({
                url: '{{ route("getLatestLogs") }}',
                method: 'GET',
                data: {
                    last_timestamp: lastTimestamp,
                    date_from: dateFrom,
                    date_to: dateTo,
                    branch_id: branchId,
                    dep_id: depId
                },
                success: function(response) {
                    console.log('New Logs Received:', response);
                    if (response.length > 0) {
                        response.every(function(log) {
                            let status = 'N/A';
                            if (log.user_profile && log.user_profile.END_DATE) {
                                let endDate = new Date(log.user_profile.END_DATE);
                                status = endDate < new Date() ?
                                    '<span class="text-danger">CARD EXPIRED</span>' :
                                    '<span class="text-success">ACTIVE</span>';
                            }

                            let photo = log.user_profile && log.user_profile.photo ?
                                `<img src="data:image/jpeg;base64,${log.user_profile.photo}" 
                                      alt="User Photo" 
                                      style="max-width: 100px; max-height: 100px;">` :
                                'No Image';

                            let branchName = log.user_profile && log.user_profile.branch ? log.user_profile.branch.name : 'N/A';
                            let departmentName = log.user_profile && log.user_profile.department ? log.user_profile.department.name : 'N/A';
                            let deviceName = log.device_name || 'N/A';
                            let card = log.card || 'N/A';

                            let rowCount = $('#logTableBody tr').length;
                            let no = rowCount > 0 ? parseInt($('#logTableBody tr:first td:first').text()) + 1 : 1;

                            let newRow = `
                                <tr data-timestamp="${log.TM_EVENT}">
                                    <td>${no}</td>
                                    <td>${log.user_profile?.NAME || 'N/A'}</td>
                                    <td>${branchName}</td>
                                    <td>${departmentName}</td>
                                    <td>${log.TM_EVENT}</td>
                                    <td>${status}</td>
                                    <td>${deviceName}</td>
                                    <td>${card}</td>
                                    <td>${photo}</td>
                                </tr>
                            `;
                            $('#logTableBody').prepend(newRow);

                            if ($('#logTableBody tr').length > 50) {
                                $('#logTableBody tr:last').remove();
                            }

                            updateRowNumbers();
                            return $('#logTableBody tr').length < 50;
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Error polling logs:', xhr.status, status, error);
                    console.log('Response:', xhr.responseText);
                }
            });

            // Fetch updated counts
            $.ajax({
                url: '{{ route("getCounts") }}',
                method: 'GET',
                success: function(response) {
                    $('h1:contains("Member :")').text('Member : ' + response.memberCount);
                    $('h1:contains("Employee :")').text('Employee : ' + response.employeeCount);
                    console.log('Counts updated:', response);
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching counts:', error);
                }
            });

            // Schedule the next poll
            setTimeout(pollLogs, 5000);
        }

        function updateRowNumbers() {
            $('#logTableBody tr').each(function(index) {
                $(this).find('td:first').text(index + 1);
            });
        }

        // Start polling
        pollLogs();

        // Initial count fetch
        $.ajax({
            url: '{{ route("getCounts") }}',
            method: 'GET',
            success: function(response) {
                $('h1:contains("Member :")').text('Member : ' + response.memberCount);
                $('h1:contains("Employee :")').text('Employee : ' + response.employeeCount);
            },
            error: function(xhr, status, error) {
                console.error('Error fetching counts:', error);
            }
        });

        $('#exportExcel').on('click', function(e) {
            e.preventDefault();
            let dateFrom = $('#date_from').val();
            let dateTo = $('#date_to').val();
            let branchId = $('#branch_id').val();
            let depId = $('#dep_id').val();
            console.log('Exporting to Excel - Date From:', dateFrom, 'Date To:', dateTo, 'Branch ID:', branchId, 'Dep ID:', depId);
            let url = '{{ route("exportUserLogs") }}' + '?date_from=' + dateFrom + '&date_to=' + dateTo + '&branch_id=' + branchId + '&dep_id=' + depId;
            window.location.href = url;
        });
    });
</script>
@endsection
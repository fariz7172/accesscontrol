@extends('layout_background.app_layouts')

@section('content')
<!-- Previous content remains unchanged -->
<div class="container-fluid py-4">
    <h1 class="h3 mb-4 text-gray-800">Add Report to Summary</h1>

    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
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
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <form method="POST" action="{{ route('summary.index') }}" id="attendanceForm">
                        @csrf
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-md-6 col-lg-3">
                                <label for="selected_month" class="form-label">Select Month</label>
                                <select class="form-control" name="selected_month" id="selected_month">
                                    @foreach (range(1, 12) as $month)
                                    <option value="{{ $month }}"
                                        {{ $month == old('selected_month', $selectedMonth) ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($month)->format('F') }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <label for="selected_year" class="form-label">Select Year</label>
                                <input type="number" class="form-control" name="selected_year" id="selected_year"
                                    value="{{ old('selected_year', $selectedYear) }}" min="1900" max="9999">
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <label for="StartPeriod" class="form-label">Start Date</label>
                                <input type="date" class="form-control" name="StartPeriod" id="StartPeriod"
                                    value="{{ old('StartPeriod', $startPeriod) }}">
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <label for="EndPeriod" class="form-label">End Date</label>
                                <input type="date" class="form-control" name="EndPeriod" id="EndPeriod"
                                    value="{{ old('EndPeriod', $endPeriod) }}">
                            </div>
                            <div class="col-12 col-lg-4 d-flex flex-wrap gap-2 mt-2">
                                <button type="submit" name="generate" value="1" class="btn btn-primary mr-2" formaction="{{ route('summary.generate') }}">Generate</button>
                                <button type="button" class="btn btn-secondary mr-2" data-bs-toggle="modal" data-bs-target="#employeeModal">Select Employees</button>
                                <button type="submit" name="export" value="1" class="btn btn-success" formaction="{{ route('summary.export') }}">Ekspor</button>
                            </div>
                            @foreach (old('selected_users', $selectedUsers) as $userId)
                            <input type="hidden" name="selected_users[]" value="{{ $userId }}">
                            @endforeach
                        </div>
                    </form>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>Name</th>
                                <th>Period</th>
                                <th>Working Days</th>
                                <th>Present</th>
                                <th>Absent</th>
                                <th>Late In</th>
                                <th>Early Out</th>
                                <th>Late In Minute</th>
                                <th>Early Out Minute</th>
                                <th>Total Work time</th>
                                <th>Total Work Hour</th>
                                <th>OT</th>
                                <th>OT minute</th>
                                <th>Early Work</th>
                                <th>Early Work Minute</th>
                                <th>No checkin</th>
                                <th>No checkout</th>
                                <th>Leave Taken</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (!empty($selectedUsers) && $monthlyData->isNotEmpty())
                            @foreach ($monthlyData as $summary)
                            @php
                            $user = $users->where('ID', $summary->EmployeeID)->first();
                            \Log::info('User for EmployeeID ' . $summary->EmployeeID . ': ', $user ? $user->toArray() : []);
                            @endphp
                            @if ($user && in_array($user->ID, $selectedUsers))
                            <tr>
                                <td><input type="checkbox" name="selected_users[]" value="{{ $user->ID }}" class="user-checkbox" {{ in_array($user->ID, old('selected_users', $selectedUsers)) ? 'checked' : '' }}></td>
                                <td>{{ $user->NAME }}</td>
                                <td>{{ \Carbon\Carbon::parse($summary->Period)->format('F Y') }}</td>
                                <td>{{ $summary->WorkingDays }}</td>
                                <td>{{ $summary->Present }}</td>
                                <td>{{ $summary->Absent }}</td>
                                <td>{{ $summary->LateIn }}</td>
                                <td>{{ $summary->EarlyOut }}</td>
                                <td>{{ \App\Helpers\TimeHelper::minutesToHoursMinutes($summary->LateInMinute) }}</td>
                                <td>{{ \App\Helpers\TimeHelper::minutesToHoursMinutes($summary->EarlyOutMinute) }}</td>
                                <td>{{ \App\Helpers\TimeHelper::minutesToHoursMinutes($summary->TotalWorktime) }}</td>
                                <td>{{ \App\Helpers\TimeHelper::minutesToHoursMinutes($summary->TotalWorkHour) }}</td>
                                <td>{{ $summary->OT }}</td>
                                <td>{{ \App\Helpers\TimeHelper::minutesToHoursMinutes($summary->OTMinute) }}</td>
                                <td>{{ $summary->EarlyWork }}</td>
                                <td>{{ \App\Helpers\TimeHelper::minutesToHoursMinutes($summary->EarlyWorkMinute) }}</td>
                                <td>{{ $summary->Ncheckin }}</td>
                                <td>{{ $summary->Ncheckout }}</td>
                                <td>{{ $summary->LeaveTaken }}</td>
                            </tr>
                            @endif
                            @endforeach
                            @else
                            <tr>
                                <td colspan="19">No employees selected or summary data is not available.</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Updated Modal for selecting employees -->
    <div class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="employeeModalLabel">Select Employees</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Department Filter Dropdown -->
                    <div class="form-group mb-3">
                        <label for="department_filter">Filter by Department</label>
                        <select class="form-control" id="department_filter">
                            <option value="">All Departments</option>
                            @foreach (\App\Models\departmentModel::all() as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="select_all_employees">
                        <label class="form-check-label" for="select_all_employees">Select All</label>
                    </div>

                    <div class="row" id="employee_list">
                        @foreach ($users as $user)
                        <div class="col-md-4 employee-item" data-department-id="{{ $user->Depid ?? '' }}">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input employee-checkbox" name="selected_users[]" value="{{ $user->ID }}"
                                    id="employee_{{ $user->ID }}" form="attendanceForm"
                                    {{ in_array($user->ID, old('selected_users', $selectedUsers)) ? 'checked' : '' }}>
                                <label class="form-check-label" for="employee_{{ $user->ID }}">{{ $user->NAME }}</label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('attendanceForm').submit();">Apply</button>
                </div>
            </div>
        </div>
    </div>
</div>
    <!-- Custom CSS for responsiveness -->
    <style>
        .table-responsive {
            overflow-x: auto;
        }

        @media (max-width: 576px) {
            .btn {
                width: 100%;
                margin-bottom: 10px;
            }

            .d-flex.flex-wrap.gap-2 {
                flex-direction: column;
            }

            .employee-item {
                margin-bottom: 10px;
            }
        }

        @media (min-width: 577px) and (max-width: 768px) {
            .btn {
                margin-bottom: 10px;
            }
        }
    </style>

{{-- Di head atau sebelum </body> --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- Inject route untuk JavaScript --}}
<script>
    window.attendSummaryRoutes = {
        getEmployeesByDepartment: "{{ route('attendantSheet.getEmployeesByDepartment') }}"
    };
</script>

{{-- Library Eksternal --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- JavaScript Utama (TERPISAH!) --}}
<script src="{{ asset('js/attendSummary/attendSummary.js') }}?v={{ time() }}"></script>

@endsection
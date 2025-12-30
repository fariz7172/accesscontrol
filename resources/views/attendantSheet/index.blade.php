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
        <ul class="alert mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Validation Error Message Container for Client-Side Validation -->
    <div id="validation-error" class="alert alert-danger alert-dismissible fade show d-none" role="alert">
        <span id="validation-error-message"></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <style>
        .red-text {
            color: white;
            background-color: red;
        }

        .green-text {
            color: white;
            background-color: green;
        }

        .hidden {
            display: none;
        }

        .text-danger {
            color: red;
        }
    </style>

    <!-- Page Header -->
    <h1 class="h3 mb-4 text-gray-800">Attendance Sheet Management</h1>

    <!-- Filter Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Attendance</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('attendantSheet.index') }}" id="attendanceForm">
                <div class="form-group mb-3">
                    <div class="row d-flex">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="start_date">Start Date</label>
                                <input type="date" class="form-control" name="start_date" id="start_date"
                                    value="{{ request()->input('start_date', now()->format('Y-m-d')) }}">
                            </div>

                            <div class="form-group mb-3">
                                <label for="end_date">End Date</label>
                                <input type="date" class="form-control" name="end_date" id="end_date"
                                    value="{{ request()->input('end_date', now()->format('Y-m-d')) }}">
                            </div>
                        </div>
                        <div class="col-md-12 col-lg-6 d-flex col-sm-12">
                            <div class="card border-left-primary shadow h-30 py-2 mr-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Present</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">Total Present: {{ $totalPresent }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fa fa-user fa-2x text-gray-300" aria-hidden="true"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-left-danger shadow h-30 py-2 mr-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                Absent</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">Total Absent: {{ $totalAbsent }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fa fa-user fa-2x text-gray-300" aria-hidden="true"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-left-warning shadow h-30 py-2 mr-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Leaves</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">Total Leaves: {{ $totalLeave }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fa fa-user fa-2x text-gray-300" aria-hidden="true"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#employeeModal">
                    Choose Employees
                </button>

                <button type="submit" name="generate" value="1" class="btn btn-primary">Generate</button>
                <button type="submit" name="export" value="1" class="btn btn-success">Export</button>
                <button type="submit" name="exportBreak" value="1" class="btn btn-success">Export with Break Schedule</button>
            </form>
        </div>
    </div>

    <!-- Employee Selection Modal -->
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
                        @foreach($userProfiles as $user)
                        <div class="col-md-4 employee-item" data-department-id="{{ $user->Depid }}">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input employee-checkbox" name="employee_ids[]" value="{{ $user->ID }}" id="employee_{{ $user->ID }}"
                                    {{ in_array($user->ID, request()->input('employee_ids', [])) ? 'checked' : '' }} form="attendanceForm">
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

    <!-- Tab Content -->
    <div class="tab-content" id="shiftTabsContent">
        <!-- Shifts Tab -->
        <div class="tab-pane fade show active" id="shifts" role="tabpanel" aria-labelledby="shifts-tab">
            @if($attendByDate->isEmpty())
            <div class="card shadow mb-4">
                <div class="card-body">
                    <p class="text-center">No attendance records found for the selected date range.</p>
                </div>
            </div>
            @else
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Attendance Records</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>No Id</th>
                                    <th style="width: 200px;">Employee</th>
                                    <th style="width: 150px;">Date</th>
                                    <th style="width: 100px;">Shift Name</th>
                                    <th>Schedule IN</th>
                                    <th>Schedule Out</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Status</th>
                                    <th>Late IN</th>
                                    <th>Early Out</th>
                                    <th>Scheduled Hours (Minute)</th>
                                    <th>Total Work Hours (Minute)</th>
                                    <th>OT</th>
                                    <th>Leave Type</th>
                                    <th>Remark</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attendByDate->flatMap(function($records) { return $records; }) as $item)
                                <tr class="{{ $item->DayType == 2 || $item->Remark === 'Holiday' ? 'red-text' : ($item->Remark ? 'green-text' : '') }}">
                                    <td class="d-none d-md-table-cell">{{ $item->EmployeeID }}</td>
                                    <td>{{ $item->userProfile ? $item->userProfile->NAME : 'N/A' }}</td>
                                    <td>{{ $item->AttDate }}</td>
                                    <td class="d-none d-md-table-cell">{{ $item->shift ? $item->shift->ShiftName : 'Off Shift' }}</td>
                                    <td class="d-none d-md-table-cell">{{ $item->shift ? $item->shift->Begin_Time : '0' }}</td>
                                    <td class="d-none d-md-table-cell">{{ $item->shift ? $item->shift->Out_time : '0' }}</td>
                                    <td>
                                        @if ($item->Time_In && $item->shift && $item->ShiftCode == $item->shift->id)
                                        @php
                                        $timeIn = \Carbon\Carbon::parse($item->Time_In);
                                        $beginTime = \Carbon\Carbon::parse($item->AttDate . ' ' . $item->shift->Begin_Time);
                                        $isLate = $timeIn->greaterThan($beginTime);
                                        @endphp
                                        <span class="{{ $isLate ? 'text-danger' : '' }}">
                                            {{ $timeIn->format('H:i') }}
                                        </span>
                                        @else
                                        {{ $item->Time_In ? \Carbon\Carbon::parse($item->Time_In)->format('H:i') : '0' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($item->Time_Out && $item->shift && $item->ShiftCode == $item->shift->id)
                                        @php
                                        $timeOut = \Carbon\Carbon::parse($item->Time_Out);
                                        $outTime = \Carbon\Carbon::parse($item->AttDate . ' ' . $item->shift->Out_time);
                                        $isEarly = $timeOut->lessThan($outTime);
                                        @endphp
                                        <span class="{{ $isEarly ? 'text-danger' : '' }}">
                                            {{ $timeOut->format('H:i') }}
                                        </span>
                                        @else
                                        {{ $item->Time_Out ? \Carbon\Carbon::parse($item->Time_Out)->format('H:i') : '0' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($item instanceof \App\Models\AttendModel)
                                        @if ($item->Remark === 'Holiday')
                                        OFF : Holiday
                                        @elseif ($item->DayType == 2)
                                        OFF
                                        @elseif (!is_null($item->DutyProcessID))
                                        {{ $item->Remark ?? 'N/A' }} <!-- Display Remark when DutyProcessID is non-null -->
                                        @else
                                        {{ $item->Present == 1 ? 'Present' : 'Not Present' }}
                                        @endif
                                        @else
                                        {{ $item->Remark ?? 'Not Present' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($item->Time_In && $item->shift && $item->ShiftCode == $item->shift->id)
                                        @php
                                        $lateIn = $timeIn->greaterThan($beginTime) ? $timeIn->diffInMinutes($beginTime) : 0;
                                        @endphp
                                        <span class="{{ $lateIn > 0 ? 'text-danger' : '' }}">{{ $lateIn }} </span>
                                        @else
                                        0
                                        @endif
                                    </td>
                                    <td>{{ $item->Time_OutShort ?? '0' }} </td>
                                    <td class="d-none d-md-table-cell">{{ $item->WorkTime ?? '0' }}</td>
                                    <td class="d-none d-md-table-cell">{{ $item->TotalWorkHour }}</td>

                                    <td class="d-none d-md-table-cell">{{ $item->TotalOT ? $item->TotalOT : '0' }}</td>
                                    <td class="d-none d-md-table-cell">{{ $item->display_leave_type }}</td>
                                    <td>
                                        @if($item instanceof \App\Models\AttendModel)
                                        <form class="update-remark-text-form" data-id="{{ $item->id }}"
                                            action="{{ route('attendantSheet.updateRemark') }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="id" value="{{ $item->id }}">

                                            <select name="remark_select" class="form-control form-control-sm remark-select" data-id="{{ $item->id }}"
                                                {{ $item->Remark === 'Holiday' ? 'disabled' : '' }}>
                                                <option value="N/A" {{ $item->Remark == null ? 'selected' : '' }}>N/A</option>
                                                <option value="Custom" {{ $item->DutyProcessID == 1 && !is_null($item->Remark) && strpos($item->Remark, 'On Leave:') === 0 ? 'selected' : '' }}>Custom</option>
                                                <!-- <option value="Holiday" {{ $item->Remark === 'Holiday' ? 'selected' : '' }}>Holiday</option> -->
                                                @foreach($leaveTypes as $leaveType)
                                                <option value="On Leave|{{ $leaveType->Id }}|{{ $leaveType->Name }}"
                                                    {{ $item->DutyProcessID == $leaveType->Id && $item->DutyProcessID != 1 ? 'selected' : '' }}>
                                                    {{ $leaveType->Name }}
                                                </option>
                                                @endforeach
                                            </select>

                                            <input type="text" name="remark"
                                                class="form-control form-control-sm remark-text mt-1 {{ $item->DutyProcessID == 1 && !is_null($item->Remark) && strpos($item->Remark, 'On Leave:') === 0 ? '' : 'd-none' }}"
                                                value="{{ $item->DutyProcessID == 1 && !is_null($item->Remark) && strpos($item->Remark, 'On Leave:') === 0 ? substr($item->Remark, 10) : '' }}"
                                                placeholder="Enter custom remark"
                                                data-id="{{ $item->id }}"
                                                {{ $item->Remark === 'Holiday' ? 'disabled' : '' }}>
                                        </form>
                                        @else
                                        {{ $item->Remark ?? 'N/A' }}
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>


<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- Konfigurasi untuk JS --}}
<script>
    window.attendantSheetConfig = {
        updateRemarkUrl: "{{ route('attendantSheet.updateRemark') }}",
        getEmployeesUrl: "{{ route('attendantSheet.getEmployeesByDepartment') }}"
    };
</script>

{{-- Include JS dari folder baru --}}
<script src="{{ asset('js/attendantSheet/attendantSheet.js') }}?v={{ time() }}"></script>

{{-- jQuery & SweetAlert2 (bisa dipindah ke layout) --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>



@endsection
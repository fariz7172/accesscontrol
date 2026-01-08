@extends('layout_background.app_layouts')

@section('content')
<link rel="stylesheet" href="{{ asset('css/square.css') }}">

<div class="container-fluid py-4">
    <h1 class="h3 mb-4 text-gray-800">Attendance Recap</h1>

    <!-- Session messages -->
    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Debug information -->
    @if (app()->environment('local'))
    <div class="alert alert-info">
        <strong>Debug Info:</strong><br>
        Users count: {{ $users->count() }}<br>
        Days in month: {{ $daysInMonth }}<br>
        Month: {{ $month }}<br>
        Year: {{ $year }}<br>
        Selected Month Name: {{ $selectedMonthName }}<br>
    </div>
    @endif

    <!-- Form to select the month -->
    <form method="GET" action="{{ route('addshiftuser.recap') }}" class="mb-3">
        @csrf
        <label for="month">Select Month:</label>
        <select name="month" id="month" onchange="this.form.submit()">
            @for ($i = 1; $i <= 12; $i++)
                <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                {{ [
                        1 => 'January',
                        2 => 'February',
                        3 => 'March',
                        4 => 'April',
                        5 => 'May',
                        6 => 'June',
                        7 => 'July',
                        8 => 'August',
                        9 => 'September',
                        10 => 'October',
                        11 => 'November',
                        12 => 'December'
                    ][$i] }}
                </option>
                @endfor
        </select>
        @foreach (request('user_ids') as $user_id)
        <input type="hidden" name="user_ids[]" value="{{ $user_id }}">
        @endforeach
        <input type="hidden" name="PatternID" value="{{ request('PatternID') }}">
    </form>

    <div class="row">
        <div class="col d-flex ">
            <label for="">Present : <strong style="color:green;margin-right:20px; background-color:green; width:30px">green</strong> </label>
            <label for="">Off day : <strong style="color:red;margin-right:20px;background-color:red; width:30px">green </strong> </label>
            <label for="">Leave Proccess : <strong style="color:yellow;margin-right:20px;background-color:yellow; width:30px">green </strong> </label>
        </div>
    </div>

    <!-- Display the selected month and year -->
    <h2>Month: {{ $selectedMonthName }} {{ $year }}</h2>

    <!-- Form to update ShiftCode -->
    <form method="POST" action="{{ route('attend.updateShiftCode') }}" id="shiftCodeUpdateForm">
        @csrf
        @method('PATCH')

        <!-- Responsive table container -->
        <div class="table-responsive">
            <table class="table table-bordered dataTable" id="dataTable" width="100%" cellspacing="0" role="grid" aria-describedby="dataTable_info" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="sticky-col">ID</th>
                        <th class="sticky-col" style="width: 200px;">Name</th>
                        @for ($day = 1; $day <= $daysInMonth; $day++)
                            <th style="width: 50px;">{{ $day }}</th>
                            @endfor
                    </tr>
                </thead>
                <tbody>
                    @if ($users->isEmpty())
                    <tr>
                        <td colspan="{{ $daysInMonth + 2 }}">No users found.</td>
                    </tr>
                    @else
                    @foreach ($users as $user)
                    <tr>
                        <td class="sticky-col">{{ $user->ID }}</td>
                        <td class="sticky-col">
                            <div style="width: 200px;">{{ $user->NAME ?? 'N/A' }}</div>
                        </td>
                        @for ($day = 1; $day <= $daysInMonth; $day++)
                            @php
                            $userId=$user->ID;
                            $date = new DateTime("$year-$month-$day");
                            $formattedDate = $date->format('Y-m-d');
                            $attendance = isset($attendances[$userId])
                            ? collect($attendances[$userId])->where('AttDate', $formattedDate)->first()
                            : null;
                            $shiftCode = $attendance ? $attendance->ShiftCode : '';
                            $dayType = $attendance ? $attendance->DayType : null;
                            $dutyProcessID = $attendance ? $attendance->DutyProcessID : null;

                            // Determine font class based on conditions
                            $fontClass = '';
                            if ($dutyProcessID !== null && $dutyProcessID != 0) {
                            $fontClass = 'text-warning'; // Yellow for non-null, non-zero DutyProcessID
                            } else {
                            $fontClass = $dayType == 2 ? 'text-danger' : ($shiftCode ? 'text-success' : '');
                            }

                            $inputName = "shift_codes[$userId][$formattedDate]";
                            $attendanceId = $attendance ? $attendance->id : 0;
                            @endphp
                            <td>
                                <input type="text" name="{{ $inputName }}" value="{{ $shiftCode }}"
                                    class="form-control shift-code-input {{ $fontClass }}"
                                    data-attendance-id="{{ $attendanceId }}"
                                    data-user-id="{{ $userId }}"
                                    data-date="{{ $formattedDate }}"
                                    style="width: 20px; min-width: 20px; border: none; background: transparent; text-align: center; padding: 2px;"
                                    onchange="markForUpdate(this)">
                                <input type="hidden" name="attendance_ids[{{ $userId }}][{{ $formattedDate }}]" value="{{ $attendanceId }}">
                            </td>
                            @endfor
                    </tr>
                    @endforeach
                    @endif
                </tbody>
            </table>
        </div>

        <button type="submit" class="btn btn-primary btn-sm mt-3" id="saveShiftCodesButton" disabled>Save Changes</button>
        <a href="{{ route('addshiftuser.index') }}" class="btn btn-secondary btn-sm mt-3">Cancel</a>
    </form>
</div>

<script>
    function markForUpdate(input) {
        document.getElementById('saveShiftCodesButton').disabled = false;
        input.classList.add('changed');
    }

    document.getElementById('shiftCodeUpdateForm').addEventListener('submit', function(event) {
        document.querySelectorAll('.shift-code-input:not(.changed)').forEach(input => {
            input.removeAttribute('name');
        });
        document.querySelectorAll('.changed').forEach(input => {
            input.classList.remove('changed');
        });
        document.getElementById('saveShiftCodesButton').disabled = true;
    });
</script>

<style>
    .shift-code-input.changed {
        background-color: #e0f7fa !important;
    }

    .sticky-col {
        position: sticky;
        left: 0;
        background: white;
        z-index: 1;
        box-shadow: 2px 0 2px -1px rgba(0, 0, 0, 0.1);
    }

    th {
        min-width: 60px;
        text-align: center;
    }

    .text-warning {
        color: #ffc107 !important;
        /* Yellow color */
    }
</style>

@endsection
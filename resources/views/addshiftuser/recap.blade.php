@extends('layout_background.app_layouts')

@section('content')
<link rel="stylesheet" href="{{ asset('css/square.css') }}">

<div class="container-fluid py-4">
    <h1 class="h3 mb-4 text-gray-800">Attendance Recap</h1>

    <!-- Session messages -->
    @if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: `{!! implode('<br>', $errors->all()) !!}`,
                confirmButtonText: 'OK'
            });
        });
    </script>
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
        <div class="col-9">
            <div class="card shadow mb-4" id="shiftPatternCard">
                <div class=" card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Shift Pattern Details</h6>
                </div>
                <div class="card-body">
                    <h6 id="patternName"></h6>
                    <label for="">Present : <strong style="color:green;margin-right:20px; background-color:green; width:30px">batas</strong> </label>
                    <label for="">Off day : <strong style="color:red;margin-right:20px;background-color:red; width:30px">batas</strong> </label>
                    <label for="">Leave day : <strong style="color:yellow;margin-right:20px;background-color:yellow; width:30px">batas</strong> </label>
                </div>
            </div>
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
    window.recapConfig = {
        getShiftDetailsUrl: "{{ route('get.shift.details', ':code') }}",
        csrfToken: "{{ csrf_token() }}"
    };
</script>
<script src="{{ asset('js/addshiftuser/recap.js') }}?v={{ time() }}"></script>

{{-- SweetAlert2 tetap di sini (bisa juga dipindah ke layout) --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



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
    }

    .shift-card {
        position: absolute;
        display: none;
        background-color: white;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 15px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        z-index: 10000;
        min-width: 200px;
    }

    .shift-card-header {
        font-weight: bold;
        margin-bottom: 10px;
        color: #333;
    }

    .shift-card-item {
        margin: 5px 0;
        font-size: 14px;
    }

    .shift-code-input:hover {
        cursor: pointer;
        background-color: #f0f0f0 !important;
    }
</style>



@endsection
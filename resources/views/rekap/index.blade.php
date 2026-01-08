@extends('layout_background.app_layouts')

@section('content')
<link rel="stylesheet" href="css/square.css">


<h1 class="mt-5" style="font-size: 2rem;">Bagan Member</h1>

<!-- Form to select the month -->
<form method="GET" action="{{ route('rekap.index') }}" class="mb-3">
    <label for="month">Pilih Bulan:</label>
    <select name="month" id="month" onchange="this.form.submit()">
        @for ($i = 1; $i <= 12; $i++)
            <option value="{{ $i }}" {{ $selectedMonth == $i ? 'selected' : '' }}>
            {{ [
                        1 => 'Januari',
                        2 => 'Februari',
                        3 => 'Maret',
                        4 => 'April',
                        5 => 'Mei',
                        6 => 'Juni',
                        7 => 'Juli',
                        8 => 'Agustus',
                        9 => 'September',
                        10 => 'Oktober',
                        11 => 'November',
                        12 => 'Desember'
                    ][$i] }}
            </option>
            @endfor
    </select>
</form>

<!-- Display the selected month -->
<h2>Bulan: {{ $selectedMonthName }}</h2>

<!-- Table for the schedule -->
<table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th>ID</th>
            <th style="width:250px;">Name</th>
            <!-- Columns for each day of the selected month -->
            @for ($day = 1; $day <= $daysInMonth; $day++)
                <th>{{ $day }}</th>
                @endfor
        </tr>
    </thead>
    <tbody>
        @foreach ($user as $item)
        <tr>
            <td>{{ $item->ID }}</td>
            <td>
                <div style="width:250px;">{{ $item->NAME }} saya</div>
            </td>
            <!-- Cells for each day, with styling based on Sunday or presence -->
            @for ($day = 1; $day <= $daysInMonth; $day++)
                <!-- Create a DateTime object for the specific date -->
                @php
                $date = new DateTime("2025-$selectedMonth-$day");
                $isSunday = $date->format('N') == 7; // 'N' returns 1 (Monday) to 7 (Sunday)
                $formattedDate = $date->format('Y-m-d'); // Format for comparison
                // Check if the user has a log entry for this date
                $hasLog = isset($userLogs[$item->ID]) && $userLogs[$item->ID]->contains(function ($log) use ($formattedDate) {
                return date('Y-m-d', strtotime($log->TM_EVENT)) == $formattedDate;
                });
                $cellClass = $isSunday ? 'sunday' : ($hasLog ? 'present' : '');
                @endphp
                <td class="{{ $cellClass }}">
                </td>
                @endfor
        </tr>
        @endforeach
    </tbody>
</table>

@endsection
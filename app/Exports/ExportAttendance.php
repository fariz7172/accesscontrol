<?php

namespace App\Exports;

use App\Models\AttendModel;
use App\Models\HolidayCal;
use App\Models\Shift;
use App\Models\userProfileModel;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportAttendance implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $userIds;
    protected $month;
    protected $year;
    protected $patternId;
    protected $holidays;

    public function __construct(array $userIds, int $month, int $year, int $patternId)
    {
        $this->userIds = $userIds;
        $this->month = $month;
        $this->year = $year;
        $this->patternId = $patternId;

        // Fetch holidays for the selected year and month
        $this->holidays = HolidayCal::where(function ($query) use ($year, $month) {
            $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
            $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth();
            $query->whereBetween('StartDate', [$startOfMonth, $endOfMonth])
                ->orWhereBetween('EndDate', [$startOfMonth, $endOfMonth])
                ->orWhere(function ($q) use ($startOfMonth, $endOfMonth) {
                    $q->where('StartDate', '<=', $startOfMonth)
                        ->where('EndDate', '>=', $endOfMonth);
                });
        })->get();

        Log::debug('Holidays fetched for export', ['count' => $this->holidays->count(), 'holidays' => $this->holidays->toArray()]);
    }

    public function collection()
    {
        // Fetch attendance data
        $attendances = AttendModel::with(['userProfile', 'shift'])
            ->whereIn('EmployeeID', $this->userIds)
            ->where('PatternID', $this->patternId)
            ->whereMonth('AttDate', $this->month)
            ->whereYear('AttDate', $this->year)
            ->orderBy('EmployeeID')
            ->orderBy('AttDate')
            ->get();

        Log::debug('Attendance data fetched', ['count' => $attendances->count()]);

        // Group by EmployeeID and add blank rows between groups
        $grouped = $attendances->groupBy('EmployeeID');
        $result = new Collection();

        foreach ($grouped as $employeeId => $records) {
            // Add all records for the current employee
            foreach ($records as $record) {
                $result->push($record);
            }
            // Add a blank row after each group, except for the last group
            if ($employeeId !== $grouped->keys()->last()) {
                $result->push('blank'); // Marker for a blank row
            }
        }

        Log::debug('Collection with blank rows', ['count' => $result->count()]);
        return $result;
    }

    public function headings(): array
    {
        $monthName = Carbon::create($this->year, $this->month, 1)->format('F');
        Log::debug('ExportAttendance Headings', [
            'month' => $this->month,
            'year' => $this->year,
            'monthName' => $monthName,
        ]);
        return [
            ['Periode', $monthName], // Row 1: "Periode" in A1, month name in B1
            [],                      // Row 2: Blank
            ['Employee ID', 'Name', 'Date', 'Day', 'Shift Name', 'Begin Time', 'Out Time', 'In Status', 'Out Status', 'Total Work Hour', 'Status'], // Row 3: Column headers
        ];
    }

    public function map($attendance): array
    {
        // Check if this is a blank row
        if ($attendance === 'blank') {
            return ['', '', '', '', '', '', '', '', '', '', '']; // 11 empty columns to match headings
        }

        $attDate = Carbon::parse($attendance->AttDate);
        $shift = $attendance->shift ?? null;

        // Check if the current date is a holiday
        $isHoliday = false;
        foreach ($this->holidays as $holiday) {
            $startDate = Carbon::parse($holiday->StartDate);
            $endDate = Carbon::parse($holiday->EndDate);
            if ($attDate->equalTo($startDate) || ($attDate->gte($startDate) && $attDate->lte($endDate))) {
                $isHoliday = true;
                break;
            }
        }

        // Initialize In Status and Out Status from attend table
        $inStatus = $attendance->Time_In ? Carbon::parse($attendance->Time_In)->format('H:i:s') : 'N/A';
        $outStatus = $attendance->Time_Out ? Carbon::parse($attendance->Time_Out)->format('H:i:s') : 'N/A';

        // Check DutyProcessID: if non-null and non-zero, set In Status and Out Status to "Ijin"
        if ($attendance->DutyProcessID !== null && $attendance->DutyProcessID != 0) {
            $inStatus = 'Cuti';
            $outStatus = 'Cuti';
            Log::debug('DutyProcessID detected for export', [
                'date' => $attDate->toDateString(),
                'DutyProcessID' => $attendance->DutyProcessID,
                'inStatus' => $inStatus,
                'outStatus' => $outStatus,
            ]);
        }
        // If not "Ijin", check for holiday and override if applicable
        elseif ($isHoliday) {
            $inStatus = 'Libur Nasional';
            $outStatus = 'Libur Nasional';
            Log::debug('Holiday detected for export', [
                'date' => $attDate->toDateString(),
                'inStatus' => $inStatus,
                'outStatus' => $outStatus,
            ]);
        }

        // Prepare Total Work Hour and Status
        $totalWorkHour = $attendance->TotalWorkHour ?? 0; // Use 0 if null
        $status = $attendance->Remark ?? 'N/A'; // Use 'N/A' if Remark is null

        Log::debug('Mapping attendance data', [
            'EmployeeID' => $attendance->EmployeeID,
            'AttDate' => $attDate->toDateString(),
            'TotalWorkHour' => $totalWorkHour,
            'Status' => $status,
        ]);

        return [
            $attendance->EmployeeID,
            $attendance->userProfile ? $attendance->userProfile->NAME : 'Unknown',
            $attDate->format('Y-m-d'),
            $attDate->englishDayOfWeek,
            $shift ? $shift->ShiftName : 'N/A', // Shift Name from shift.ShiftName via ShiftCode
            $shift ? $shift->Begin_Time : '00:00:00', // Begin Time from shift.Begin_Time
            $shift ? $shift->Out_time : '00:00:00', // Out Time from shift.Out_Time
            $inStatus, // In Status from attend.Time_In
            $outStatus, // Out Status from attend.Time_Out
            $totalWorkHour, // Total Work Hour from attend.TotalWorkHour
            $status, // Status from attend.Remark
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Merge cells for the "Periode" label only, leaving B1 for month name
        $sheet->mergeCells('A1:A1'); // Optional, for styling consistency
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getStyle('B1')->getFont()->setBold(true); // Style month name
        $sheet->getStyle('A3:K3')->getFont()->setBold(true); // Updated to include new columns J and K

        // Get the highest row to apply conditional formatting
        $highestRow = $sheet->getHighestRow();

        // Apply conditional formatting for In Status (column H)
        foreach (range(4, $highestRow) as $row) {
            $inStatus = $sheet->getCell("H{$row}")->getValue();
            $beginTime = $sheet->getCell("F{$row}")->getValue();

            if ($inStatus === 'Libur Nasional' || $inStatus === 'Cuti') {
                $sheet->getStyle("H{$row}")->getFont()->getColor()->setRGB('FF0000');
            } elseif ($inStatus !== 'N/A' && $inStatus !== 'Libur Nasional' && $inStatus !== 'Ijin' && $beginTime !== '00:00:00' && strtotime($inStatus) > strtotime($beginTime)) {
                $sheet->getStyle("H{$row}")->getFont()->getColor()->setRGB('FF0000');
            }
        }

        // Apply conditional formatting for Out Status (column I)
        foreach (range(4, $highestRow) as $row) {
            $outStatus = $sheet->getCell("I{$row}")->getValue();
            $outTime = $sheet->getCell("G{$row}")->getValue();

            if ($outStatus === 'Libur Nasional' || $outStatus === 'Cuti') {
                $sheet->getStyle("I{$row}")->getFont()->getColor()->setRGB('FF0000');
            } elseif ($outStatus !== 'N/A' && $outStatus !== 'Libur Nasional' && $outStatus !== 'Ijin' && $outTime !== '00:00:00' && strtotime($outStatus) < strtotime($outTime)) {
                $sheet->getStyle("I{$row}")->getFont()->getColor()->setRGB('FF0000');
            }
        }

        return [
            // Style the header rows
            1 => ['font' => ['bold' => true]],
            3 => ['font' => ['bold' => true]],
        ];
    }
}
<?php

namespace App\Exports;

use App\Models\AttendModel;
use App\Models\userProfileModel;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceSheet implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $employeeIds;
    protected $startMonth;
    protected $endMonth;

    public function __construct($employeeIds = null, $startMonth = null, $endMonth = null)
    {
        $this->employeeIds = $employeeIds;
        $this->startMonth = $startMonth;
        $this->endMonth = $endMonth;
    }

    /**
     * Format Minute menjadi string Hours dan Minute
     * @param int $minutes
     * @return string
     */
    private function formatHoursMinutes($minutes)
    {
        if ($minutes <= 0) {
            return '0 Minute';
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 0 && $remainingMinutes > 0) {
            return "$hours Hours $remainingMinutes Minute";
        } elseif ($hours > 0) {
            return "$hours Hours";
        } else {
            return "$remainingMinutes Minute";
        }
    }

    public function collection()
    {
        $attend = collect();
        $userProfiles = userProfileModel::select('ID', 'NAME')->orderBy('NAME', 'asc')->get();

        if ($this->startMonth && $this->endMonth && $this->employeeIds && is_array($this->employeeIds)) {
            $startDate = Carbon::parse($this->startMonth)->startOfMonth();
            $endDate = Carbon::parse($this->endMonth)->endOfMonth();
            $dateRange = CarbonPeriod::create($startDate, $endDate);

            // Group dates by month
            $months = [];
            foreach ($dateRange as $date) {
                $monthKey = $date->format('Y-m');
                if (!isset($months[$monthKey])) {
                    $months[$monthKey] = [
                        'start' => $date->copy()->startOfMonth(),
                        'end' => $date->copy()->endOfMonth(),
                        'dates' => [],
                    ];
                }
                $months[$monthKey]['dates'][] = $date->copy();
            }

            foreach ($this->employeeIds as $employeeId) {
                $user = $userProfiles->firstWhere('ID', $employeeId);
                if (!$user) continue;

                foreach ($months as $monthKey => $monthData) {
                    $startDate = $monthData['start'];
                    $endDate = $monthData['end'];
                    $monthDateRange = CarbonPeriod::create($startDate, $endDate);

                    // Fetch records for the current employee and month
                    $employeeRecords = AttendModel::query()
                        ->select('attend.*')
                        ->join('usersprofile', 'attend.EmployeeID', '=', 'usersprofile.ID')
                        ->with(['shift', 'leaveType', 'userProfile'])
                        ->where('attend.EmployeeID', $employeeId)
                        ->whereBetween('AttDate', [$startDate, $endDate])
                        ->orderBy('attend.AttDate', 'asc')
                        ->get()
                        ->keyBy('AttDate');

                    // Calculate sums for the month
                    $totals = AttendModel::query()
                        ->where('EmployeeID', $employeeId)
                        ->whereBetween('AttDate', [$startDate, $endDate])
                        ->selectRaw('
                            SUM(COALESCE(WorkTime, 0)) as total_work_time,
                            SUM(COALESCE(Time_InShort, 0)) as total_time_in_short,
                            SUM(COALESCE(Time_OutShort, 0)) as total_time_out_short,
                            SUM(COALESCE(TotalWorkHour, 0)) as total_work_hour,
                            SUM(COALESCE(TotalOT, 0)) as total_ot,
                            SUM(CASE WHEN Present = 1 THEN 1 ELSE 0 END) as total_present,
                            SUM(CASE WHEN DutyProcessID IS NOT NULL THEN 1 ELSE 0 END) as total_leave
                        ')
                        ->first();

                    $totalPresent = $totals->total_present ?? 0;
                    $totalLeave = $totals->total_leave ?? 0;
                    $totalWorkTime = $totals->total_work_time ?? 0;
                    $totalTimeInShort = $totals->total_time_in_short ?? 0;
                    $totalTimeOutShort = $totals->total_time_out_short ?? 0;
                    $totalWorkHour = $totals->total_work_hour ?? 0;
                    $totalOT = $totals->total_ot ?? 0;

                    // Process each date in the month
                    foreach ($monthDateRange as $date) {
                        $attendRecord = $employeeRecords->get($date->toDateString());
                        if (!$attendRecord) {
                            $attendRecord = new AttendModel([
                                'id' => null,
                                'EmployeeID' => $employeeId,
                                'AttDate' => $date->toDateString(),
                                'ShiftCode' => null,
                                'DayType' => null,
                                'Time_In' => null,
                                'Time_Out' => null,
                                'Present' => 0,
                                'TotalWorkHour' => 0,
                                'Remark' => null,
                                'DutyProcessID' => null,
                            ]);
                            $attendRecord->setRelation('userProfile', $user);
                            $attendRecord->setRelation('shift', null);
                            $attendRecord->setRelation('leaveType', null);
                            $attendRecord->is_late = false;
                            $attendRecord->is_early_out = false;
                        } else {
                            // Check for late Time_In
                            if ($attendRecord->Time_In && $attendRecord->shift && $attendRecord->ShiftCode == $attendRecord->shift->id) {
                                $timeIn = Carbon::parse($attendRecord->Time_In);
                                $beginTime = Carbon::parse($attendRecord->AttDate . ' ' . $attendRecord->shift->Begin_Time);
                                $attendRecord->is_late = $timeIn->greaterThan($beginTime);
                            } else {
                                $attendRecord->is_late = false;
                            }

                            // Check for early Time_Out
                            if ($attendRecord->Time_Out && $attendRecord->shift && $attendRecord->ShiftCode == $attendRecord->shift->id) {
                                $timeOut = Carbon::parse($attendRecord->Time_Out);
                                $outTime = Carbon::parse($attendRecord->AttDate . ' ' . $attendRecord->shift->Out_time);
                                $attendRecord->is_early_out = $timeOut->lessThan($outTime);
                            } else {
                                $attendRecord->is_early_out = false;
                            }
                        }

                        $attend->push($attendRecord);
                    }

                    // Add monthly totals
                    $monthlyPresentRow = new AttendModel([
                        'EmployeeID' => $employeeId,
                        'AttDate' => null,
                        'Present' => $totalPresent,
                    ]);
                    $monthlyPresentRow->setRelation('userProfile', $user);
                    $monthlyPresentRow->is_late = false;
                    $monthlyPresentRow->is_early_out = false;
                    $monthlyPresentRow->is_monthly_total = true;
                    $monthlyPresentRow->total_type = 'present';
                    $monthlyPresentRow->month = $monthKey;
                    $attend->push($monthlyPresentRow);

                    $monthlyLeaveRow = new AttendModel([
                        'EmployeeID' => $employeeId,
                        'AttDate' => null,
                        'DutyProcessID' => $totalLeave,
                    ]);
                    $monthlyLeaveRow->setRelation('userProfile', $user);
                    $monthlyLeaveRow->is_late = false;
                    $monthlyLeaveRow->is_early_out = false;
                    $monthlyLeaveRow->is_monthly_total = true;
                    $monthlyLeaveRow->total_type = 'leave';
                    $monthlyLeaveRow->month = $monthKey;
                    $attend->push($monthlyLeaveRow);

                    // Add new summary rows
                    $monthlyWorkTimeRow = new AttendModel([
                        'EmployeeID' => $employeeId,
                        'AttDate' => null,
                        'WorkTime' => $totalWorkTime,
                    ]);
                    $monthlyWorkTimeRow->setRelation('userProfile', $user);
                    $monthlyWorkTimeRow->is_late = false;
                    $monthlyWorkTimeRow->is_early_out = false;
                    $monthlyWorkTimeRow->is_monthly_total = true;
                    $monthlyWorkTimeRow->total_type = 'scheduled_hours';
                    $monthlyWorkTimeRow->month = $monthKey;
                    $attend->push($monthlyWorkTimeRow);

                    $monthlyLateInRow = new AttendModel([
                        'EmployeeID' => $employeeId,
                        'AttDate' => null,
                        'Time_InShort' => $totalTimeInShort,
                    ]);
                    $monthlyLateInRow->setRelation('userProfile', $user);
                    $monthlyLateInRow->is_late = false;
                    $monthlyLateInRow->is_early_out = false;
                    $monthlyLateInRow->is_monthly_total = true;
                    $monthlyLateInRow->total_type = 'late_in';
                    $monthlyLateInRow->month = $monthKey;
                    $attend->push($monthlyLateInRow);

                    $monthlyEarlyOutRow = new AttendModel([
                        'EmployeeID' => $employeeId,
                        'AttDate' => null,
                        'Time_OutShort' => $totalTimeOutShort,
                    ]);
                    $monthlyEarlyOutRow->setRelation('userProfile', $user);
                    $monthlyEarlyOutRow->is_late = false;
                    $monthlyEarlyOutRow->is_early_out = false;
                    $monthlyEarlyOutRow->is_monthly_total = true;
                    $monthlyEarlyOutRow->total_type = 'early_out';
                    $monthlyEarlyOutRow->month = $monthKey;
                    $attend->push($monthlyEarlyOutRow);

                    $monthlyTotalWorkHourRow = new AttendModel([
                        'EmployeeID' => $employeeId,
                        'AttDate' => null,
                        'TotalWorkHour' => $totalWorkHour,
                    ]);
                    $monthlyTotalWorkHourRow->setRelation('userProfile', $user);
                    $monthlyTotalWorkHourRow->is_late = false;
                    $monthlyTotalWorkHourRow->is_early_out = false;
                    $monthlyTotalWorkHourRow->is_monthly_total = true;
                    $monthlyTotalWorkHourRow->total_type = 'total_work_hour';
                    $monthlyTotalWorkHourRow->month = $monthKey;
                    $attend->push($monthlyTotalWorkHourRow);

                    $monthlyOTRow = new AttendModel([
                        'EmployeeID' => $employeeId,
                        'AttDate' => null,
                        'TotalOT' => $totalOT,
                    ]);
                    $monthlyOTRow->setRelation('userProfile', $user);
                    $monthlyOTRow->is_late = false;
                    $monthlyOTRow->is_early_out = false;
                    $monthlyOTRow->is_monthly_total = true;
                    $monthlyOTRow->total_type = 'ot';
                    $monthlyOTRow->month = $monthKey;
                    $attend->push($monthlyOTRow);

                    // Add blank row after monthly totals
                    $emptyRow = new AttendModel();
                    $emptyRow->is_late = false;
                    $emptyRow->is_early_out = false;
                    $attend->push($emptyRow);
                }
            }
        } else {
            AttendModel::query()
                ->select('attend.*')
                ->join('usersprofile', 'attend.EmployeeID', '=', 'usersprofile.ID')
                ->with(['shift', 'leaveType', 'userProfile'])
                ->orderBy('usersprofile.NAME', 'asc')
                ->orderBy('attend.AttDate', 'asc')
                ->chunk(100, function ($records) use (&$attend) {
                    foreach ($records as $item) {
                        if ($item->Time_In && $item->shift && $item->ShiftCode == $item->shift->id) {
                            $timeIn = Carbon::parse($item->Time_In);
                            $beginTime = Carbon::parse($item->AttDate . ' ' . $item->shift->Begin_Time);
                            $item->is_late = $timeIn->greaterThan($beginTime);
                        } else {
                            $item->is_late = false;
                        }

                        if ($item->Time_Out && $item->shift && $item->ShiftCode == $item->shift->id) {
                            $timeOut = Carbon::parse($item->Time_Out);
                            $outTime = Carbon::parse($item->AttDate . ' ' . $item->shift->Out_time);
                            $item->is_early_out = $timeOut->lessThan($outTime);
                        } else {
                            $item->is_early_out = false;
                        }

                        $attend->push($item);
                    }
                });
        }

        return $attend;
    }

    public function headings(): array
    {
        return [
            'No ID',
            'Employee',
            'Date',
            'Shift Code',
            'Schedule IN',
            'Schedule OUT',
            'Time In',
            'Time Out',
            'Status',
            'Late IN',
            'Early Out',
            'Scheduled Hours',
            'Total Work Hours',
            'OT',
            'Leave Type',
        ];
    }

    public function map($item): array
    {
        if (empty($item->EmployeeID) && empty($item->AttDate)) {
            return ['', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        }

        if (isset($item->is_monthly_total)) {
            $name = $item->userProfile ? $item->userProfile->NAME : 'N/A';
            $monthName = Carbon::parse($item->month)->format('F Y');
            if ($item->total_type === 'present') {
                return [
                    '',
                    "Total Present for $name ($monthName):",
                    $item->Present,
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    ''
                ];
            }
            if ($item->total_type === 'leave') {
                return [
                    '',
                    "Total Leaves for $name ($monthName):",
                    $item->DutyProcessID,
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    ''
                ];
            }
            if ($item->total_type === 'scheduled_hours') {
                return [
                    '',
                    "Total Scheduled Hours for $name ($monthName):",
                    $this->formatHoursMinutes($item->WorkTime),
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    ''
                ];
            }
            if ($item->total_type === 'late_in') {
                return [
                    '',
                    "Total Late IN for $name ($monthName):",
                    $this->formatHoursMinutes($item->Time_InShort),
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    ''
                ];
            }
            if ($item->total_type === 'early_out') {
                return [
                    '',
                    "Total Early Out for $name ($monthName):",
                    $this->formatHoursMinutes($item->Time_OutShort),
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    ''
                ];
            }
            if ($item->total_type === 'total_work_hour') {
                return [
                    '',
                    "Total Work Hours for $name ($monthName):",
                    $this->formatHoursMinutes($item->TotalWorkHour),
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    ''
                ];
            }
            if ($item->total_type === 'ot') {
                return [
                    '',
                    "Total OT for $name ($monthName):",
                    $this->formatHoursMinutes($item->TotalOT),
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    ''
                ];
            }
        }

        // Status logic
        $status = $item->Present == 1 ? 'Present' : 'Not Present';
        if ($item->Remark === 'Holiday') {
            $status = 'OFF : Holiday';
        } elseif ($item->DayType == 2) {
            $status = 'OFF';
        } elseif (!is_null($item->DutyProcessID)) {
            $status = $item->Remark ?? 'N/A';
        }

        // Leave type logic
        $leaveType = 'N/A';
        if ($item->Remark === 'Holiday') {
            $leaveType = 'N/A';
        } elseif ($item->leaveType) {
            $leaveType = $item->leaveType->Name;
        }

        // Calculate Late IN
        $lateIn = '0';
        if ($item->Time_In && $item->shift && $item->ShiftCode == $item->shift->id) {
            $timeIn = Carbon::parse($item->Time_In);
            $beginTime = Carbon::parse($item->AttDate . ' ' . $item->shift->Begin_Time);
            $lateIn = $timeIn->greaterThan($beginTime) ? $this->formatHoursMinutes($timeIn->diffInMinutes($beginTime)) : '0';
        }

        return [
            $item->EmployeeID,
            $item->userProfile ? $item->userProfile->NAME : 'N/A',
            $item->AttDate,
            $item->shift ? $item->shift->ShiftName : '0',
            $item->shift ? $item->shift->Begin_Time : '0',
            $item->shift ? $item->shift->Out_time : '0',
            $item->Time_In ?: '0',
            $item->Time_Out ?: '0',
            $status,
            $lateIn,
            $item->Time_OutShort ? $this->formatHoursMinutes($item->Time_OutShort) : '0',
            $item->WorkTime ? $this->formatHoursMinutes($item->WorkTime) : '0',
            $item->TotalWorkHour ? $this->formatHoursMinutes($item->TotalWorkHour) : '0',
            $item->TotalOT ? $this->formatHoursMinutes($item->TotalOT) : '0',
            $leaveType,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:O1')->applyFromArray(['font' => ['bold' => true]]);
        $rows = $this->collection();

        foreach ($rows as $index => $item) {
            $rowNumber = $index + 2;

            if (empty($item->EmployeeID) && empty($item->AttDate)) {
                continue;
            }

            if ($item->AttDate === null && (
                $item->Present !== null ||
                $item->DutyProcessID !== null ||
                $item->WorkTime !== null ||
                $item->Time_InShort !== null ||
                $item->Time_OutShort !== null ||
                $item->TotalWorkHour !== null ||
                $item->TotalOT !== null
            )) {
                $sheet->getStyle("A{$rowNumber}:O{$rowNumber}")->applyFromArray([
                    'font' => ['bold' => true],
                ]);
                continue;
            }

            if ($item->DayType == 2 || $item->Remark === 'Holiday') {
                $sheet->getStyle("A{$rowNumber}:O{$rowNumber}")->applyFromArray([
                    'font' => ['color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FF0000']],
                ]);
            } elseif ($item->DutyProcessID !== null) {
                $sheet->getStyle("A{$rowNumber}:O{$rowNumber}")->applyFromArray([
                    'font' => ['color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '00FF00']],
                ]);
            }

            // Apply red font to Time_In if late
            if (!empty($item->is_late) && $item->is_late === true) {
                $sheet->getStyle("E{$rowNumber}")->applyFromArray([
                    'font' => ['color' => ['rgb' => 'FF0000']],
                ]);
            }

            // Apply red font to Time_Out if early
            if (!empty($item->is_early_out) && $item->is_early_out === true) {
                $sheet->getStyle("F{$rowNumber}")->applyFromArray([
                    'font' => ['color' => ['rgb' => 'FF0000']],
                ]);
            }
        }

        return [];
    }
}

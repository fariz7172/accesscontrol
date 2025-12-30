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

                    $totalPresent = 0;
                    $totalLeave = 0;

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

                        if ($attendRecord->Present == 1) {
                            $totalPresent++;
                        }
                        if (!empty($attendRecord->DutyProcessID)) {
                            $totalLeave++;
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

                        // Check for early Time_Out
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
            'Time In',
            'Time Out',
            'Status',
            'Total Work Hours (Minute)',
            'Leave Type',
        ];
    }

    public function map($item): array
    {
        if (empty($item->EmployeeID) && empty($item->AttDate)) {
            return ['', '', '', '', '', '', '', '', ''];
        }

        if (isset($item->is_monthly_total) && $item->total_type === 'present') {
            $name = $item->userProfile ? $item->userProfile->NAME : 'N/A';
            $monthName = Carbon::parse($item->month)->format('F Y');
            return [
                '',
                "Total Present for $name ($monthName):",
                $item->Present,
                '',
                '',
                '',
                '',
                '',
                ''
            ];
        }

        if (isset($item->is_monthly_total) && $item->total_type === 'leave') {
            $name = $item->userProfile ? $item->userProfile->NAME : 'N/A';
            $monthName = Carbon::parse($item->month)->format('F Y');
            return [
                '',
                "Total Leaves for $name ($monthName):",
                $item->DutyProcessID,
                '',
                '',
                '',
                '',
                '',
                ''
            ];
        }

        // Tentukan status berdasarkan aturan baru
        $status = $item->Present == 1 ? 'Present' : 'Not Present';
        if ($item->DayType == 2) {
            $status = 'Off';
        } elseif ($item->Remark === 'Holiday') {
            $status = 'Holiday';
        } elseif (!is_null($item->DutyProcessID)) {
            $status = $item->Remark ?? 'N/A';
        }

        return [
            $item->EmployeeID,
            $item->userProfile ? $item->userProfile->NAME : 'N/A',
            $item->AttDate,
            $item->shift ? $item->shift->ShiftName : 'N/A',
            $item->Time_In ?: 'N/A',
            $item->Time_Out ?: 'N/A',
            $status,
            $item->TotalWorkHour,
            $item->DutyProcessID !== null ? ($item->Remark ?? 'N/A') : ($item->Remark === 'Holiday' ? 'Holiday' : 'N/A'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:I1')->applyFromArray(['font' => ['bold' => true]]);
        $rows = $this->collection();

        foreach ($rows as $index => $item) {
            $rowNumber = $index + 2;

            if (empty($item->EmployeeID) && empty($item->AttDate)) {
                continue;
            }

            if ($item->AttDate === null && ($item->Present !== null || $item->DutyProcessID !== null)) {
                $sheet->getStyle("A{$rowNumber}:I{$rowNumber}")->applyFromArray([
                    'font' => ['bold' => true],
                ]);
                continue;
            }

            if ($item->DayType == 2 || $item->Remark === 'Holiday') {
                $sheet->getStyle("A{$rowNumber}:I{$rowNumber}")->applyFromArray([
                    'font' => ['color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FF0000']],
                ]);
            } elseif ($item->DutyProcessID !== null) {
                $sheet->getStyle("A{$rowNumber}:I{$rowNumber}")->applyFromArray([
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

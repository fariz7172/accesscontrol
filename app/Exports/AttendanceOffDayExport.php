<?php

namespace App\Exports;

use App\Models\AttendModel;
use App\Models\userProfileModel;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AttendanceOffDayExport implements FromCollection, WithHeadings, WithMapping
{
    protected $employeeIds;
    protected $startDate;
    protected $endDate;

    public function __construct(array $employeeIds, $startDate, $endDate)
    {
        $this->employeeIds = $employeeIds;
        $this->startDate = Carbon::parse($startDate);
        $this->endDate = Carbon::parse($endDate);
    }

    public function collection()
    {
        // Fetch user profiles for the selected employees
        $userProfiles = userProfileModel::whereIn('ID', $this->employeeIds)
            ->select('ID', 'NAME')
            ->orderBy('NAME', 'asc')
            ->get()
            ->keyBy('ID');

        // Fetch attendance records for the selected employees and date range
        $attendanceRecords = AttendModel::whereIn('EmployeeID', $this->employeeIds)
            ->whereBetween('AttDate', [$this->startDate, $this->endDate])
            ->get()
            ->groupBy('EmployeeID');

        $data = collect();
        foreach ($this->employeeIds as $employeeId) {
            if (!isset($userProfiles[$employeeId])) {
                continue;
            }

            $holidayCount = 0;
            $leaveCount = 0;

            // Count Holiday and Leave records
            if (isset($attendanceRecords[$employeeId])) {
                $holidayCount = $attendanceRecords[$employeeId]->where('Remark', 'Holiday')->count();
                $leaveCount = $attendanceRecords[$employeeId]->where('Remark', '!=', 'Holiday')->whereNotNull('Remark')->count();
            }

            $data->push([
                'employee_id' => $employeeId,
                'employee_name' => $userProfiles[$employeeId]->NAME,
                'holiday_count' => $holidayCount,
                'leave_count' => $leaveCount,
            ]);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Employee ID',
            'Employee Name',
            'Holiday Count',
            'Leave Count',
        ];
    }

    public function map($row): array
    {
        return [
            $row['employee_id'],
            $row['employee_name'],
            $row['holiday_count'],
            $row['leave_count'],
        ];
    }
}

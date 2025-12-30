<?php

namespace App\Exports;

use App\Helpers\TimeHelper;
use App\Models\AttendSumary;
use App\Models\userProfileModel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class ExportAttendanceSummary implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $selectedUsers;
    protected $year;
    protected $month;

    public function __construct(array $selectedUsers, int $year, int $month)
    {
        $this->selectedUsers = $selectedUsers;
        $this->year = $year;
        $this->month = $month;
    }

    public function collection()
    {
        return AttendSumary::whereIn('EmployeeID', $this->selectedUsers)
            ->whereYear('Period', $this->year)
            ->whereMonth('Period', $this->month)
            ->get();
    }

    public function headings(): array
    {
        return [
            'Name',
            'Period',
            'Working Days',
            'Present',
            'Absent',
            'Late In',
            'Early Out',
            'Late In (Hours:Minutes)',
            'Early Out (Hours:Minutes)',
            'Total Work Time (Hours:Minutes)',
            'Total Work Hour (Hours:Minutes)',
            'OT',
            'OT (Hours:Minutes)',
            'Early Work',
            'Early Work (Hours:Minutes)',
            'No Checkin',
            'No Checkout',
            'Leave Taken',
        ];
    }

    public function map($summary): array
    {
        $user = userProfileModel::find($summary->EmployeeID);

        return [
            $user ? $user->NAME : 'Unknown',
            \Carbon\Carbon::parse($summary->Period)->format('F Y'),
            $summary->WorkingDays,
            $summary->Present,
            $summary->Absent,
            $summary->LateIn,
            $summary->EarlyOut,
            TimeHelper::minutesToHoursMinutes($summary->LateInMinute),
            TimeHelper::minutesToHoursMinutes($summary->EarlyOutMinute),
            TimeHelper::minutesToHoursMinutes($summary->TotalWorktime),
            TimeHelper::minutesToHoursMinutes($summary->TotalWorkHour),
            $summary->OT,
            TimeHelper::minutesToHoursMinutes($summary->OTMinute),
            $summary->EarlyWork,
            TimeHelper::minutesToHoursMinutes($summary->EarlyWorkMinute),
            $summary->Ncheckin,
            $summary->Ncheckout,
            $summary->LeaveTaken,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'], // White text
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF808080'], // Grey background
                ],
            ],
        ];
    }
}

<?php

namespace App\Exports;

use App\Models\userLogModel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AttendanceLogsExport implements WithMultipleSheets
{
    protected $attendanceData;
    protected $summaryData;

    public function __construct(array $attendanceData, array $summaryData)
    {
        $this->attendanceData = $attendanceData;
        $this->summaryData = $summaryData;
    }

    public function sheets(): array
    {
        return [
            new AttendanceSheet($this->attendanceData),
            new SummarySheet($this->summaryData),
        ];
    }
}

class AttendanceSheet implements FromCollection, WithTitle, WithHeadings
{
    protected $attendanceData;

    public function __construct(array $attendanceData)
    {
        $this->attendanceData = $attendanceData;
    }

    public function collection()
    {
        return new Collection($this->attendanceData);
    }

    public function headings(): array
    {
        return [
            'No',
            'Name',
            'Member',
            'Attend',
            'Department',
            'Description',
        ];
    }

    public function title(): string
    {
        return 'Attendance Report';
    }
}

class SummarySheet implements FromCollection, WithTitle, WithHeadings
{
    protected $summaryData;

    public function __construct(array $summaryData)
    {
        $this->summaryData = $summaryData;
    }

    public function collection()
    {
        return new Collection($this->summaryData);
    }

    public function headings(): array
    {
        return [
            'Day',
            'Total Present',
            'Total Not Present',
            'Attendance Percentage',
        ];
    }

    public function title(): string
    {
        return 'Recap Attendance';
    }
}

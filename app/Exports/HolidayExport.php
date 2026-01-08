<?php

namespace App\Exports;

use App\Models\HolidayCal;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HolidayExport implements FromCollection, WithHeadings, WithStyles
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return HolidayCal::query()
            ->select('Name', 'StartDate', 'EndDate') // Select only the desired columns
            ->whereBetween('StartDate', [$this->startDate, $this->endDate])
            ->orWhereBetween('EndDate', [$this->startDate, $this->endDate])
            ->orWhere(function ($q) {
                $q->where('StartDate', '<=', $this->startDate)
                    ->where('EndDate', '>=', $this->endDate);
            })
            ->orderBy('StartDate', 'asc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Name',
            'Start Date',
            'End Date',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:C1')->applyFromArray([
            'font' => ['bold' => true],
        ]);

        return [];
    }
}

<?php

namespace App\Exports;

use App\Models\PtSchedule;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PtScheduleExport implements FromCollection, WithHeadings, WithMapping
{
    protected $schedules;

    public function __construct($schedules)
    {
        $this->schedules = $schedules;
    }

    public function collection()
    {
        return $this->schedules;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Personal Trainer',
            'Member',
            'Start Time',
            'End Time',
            'Booked At',
            'Status',
            'Note',
            'Created At',
        ];
    }

    public function map($schedule): array
    {
        return [
            $schedule->ID ?? 'N/A',
            $schedule->personalTrainer->NAME ?? 'N/A',
            $schedule->member->NAME ?? 'N/A',
            $schedule->START_TIME ?? '-',
            $schedule->END_TIME ?? '-',
            $schedule->BOOKED_AT ?? '-',
            $schedule->getStatusTextAttribute(),
            $schedule->NOTE ?? '-',
            $schedule->CREATED_AT ?? '-',
        ];
    }
}

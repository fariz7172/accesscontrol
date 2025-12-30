<?php

namespace App\Exports;

use App\Models\userLogModel;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class UserLogsExportInvalid implements FromQuery, WithHeadings, WithMapping
{
    protected $dateFrom;
    protected $dateTo;
    protected $deviceName; // TAMBAHAN

    public function __construct($dateFrom, $dateTo, $deviceName = null)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->deviceName = $deviceName;
    }

    public function query()
    {
        return userLogModel::query()
            ->with(['userProfile', 'deviceGate'])
            ->where('stat', 1)
            ->when($this->dateFrom && $this->dateTo, function ($q) {
                return $q->whereBetween('TM_EVENT', [
                    Carbon::parse($this->dateFrom)->startOfDay(),
                    Carbon::parse($this->dateTo)->endOfDay()
                ]);
            })
            ->when($this->deviceName, function ($q) {
                return $q->whereHas('deviceGate', function ($subQ) {
                    $subQ->where('name', $this->deviceName);
                });
            })
            ->orderBy('TM_EVENT', 'desc');
    }

    public function headings(): array
    {
        return [
            'No',
            'User Name',
            'Event Time',
            'Status',
            'Device Name',
            'Card',
        ];
    }

    public function map($log): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        return [
            $rowNumber,
            $log->userProfile?->NAME ?? 'User Not Registered',
            $log->TM_EVENT,
            'INVALID',
            $log->deviceGate?->name ?? $log->DEVICESN ?? 'N/A',
            $log->card ?? 'N/A',
        ];
    }
}

<?php

namespace App\Exports;

use App\Models\userLogModel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class UserLogsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $dateFrom;
    protected $dateTo;
    protected $branchId;
    protected $depId;
    protected $deviceName; // ← Tambah ini!

    public function __construct($dateFrom, $dateTo, $branchId, $depId, $deviceName = null)
    {
        $this->dateFrom = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : Carbon::today()->startOfDay();
        $this->dateTo = $dateTo ? Carbon::parse($dateTo)->endOfDay() : Carbon::today()->endOfDay();
        $this->branchId = $branchId;
        $this->depId = $depId;
        $this->deviceName = $deviceName; // ← Simpan device name
    }

    public function collection()
    {
        return userLogModel::with(['userProfile', 'userProfile.branch', 'userProfile.department', 'deviceGate'])
            ->where('USER_ADDR', '!=', 0)
            ->when($this->dateFrom && $this->dateTo, function ($q) {
                return $q->whereBetween('TM_EVENT', [$this->dateFrom, $this->dateTo]);
            })
            ->when($this->branchId, function ($q) {
                return $q->whereHas('userProfile', function ($subQ) {
                    $subQ->where('Branchid', $this->branchId);
                });
            })
            ->when($this->depId, function ($q) {
                return $q->whereHas('userProfile', function ($subQ) {
                    $subQ->where('Depid', $this->depId);
                });
            })
            ->when($this->deviceName, function ($q) {
                return $q->whereHas('deviceGate', function ($subQ) {
                    $subQ->where('name', $this->deviceName);
                });
            })
            ->orderBy('TM_EVENT', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'User Name',
            'Branch',
            'Department',
            'Event Time',
            'Status',
            'Device Name',
            'Card',
        ];
    }

    public function map($log): array
    {
        static $no = 0;
        $no++;

        $status = $log->stat == '1' ? 'CARD EXPIRED' : ($log->stat == '0' ? 'CARD ACTIVE' : 'N/A');

        return [
            $no,
            $log->userProfile?->NAME ?? 'N/A',
            $log->userProfile?->branch?->name ?? 'N/A',
            $log->userProfile?->department?->name ?? 'N/A',
            $log->TM_EVENT,
            $status,
            $log->deviceGate?->name ?? 'N/A', // ← Pakai relasi langsung
            $log->userProfile?->Card ?? 'N/A',
        ];
    }
}
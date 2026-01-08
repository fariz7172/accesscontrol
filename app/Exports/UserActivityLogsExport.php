<?php

namespace App\Exports;

use App\Models\userLogModel;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Log;

class UserActivityLogsExport implements FromQuery, WithHeadings, WithMapping
{
    protected $dateFrom;
    protected $dateTo;
    protected $branchId;
    protected $depId;
    protected $search2;

    public function __construct($dateFrom, $dateTo, $branchId, $depId, $search2)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->branchId = $branchId;
        $this->depId = $depId;
        $this->search2 = $search2;

        // Debugging: Log the parameters received
        Log::info('UserActivityLogsExport initialized with parameters:', [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'branch_id' => $branchId,
            'dep_id' => $depId,
            'search2' => $search2,
        ]);
    }

    public function query()
    {
        $query = userLogModel::query()
            ->with(['userProfile', 'userProfile.branch', 'userProfile.department', 'deviceGate'])
            ->where('USER_ADDR', '!=', 0)
            ->when($this->dateFrom && $this->dateTo, function ($query) {
                return $query->whereBetween('TM_EVENT', [
                    \Carbon\Carbon::parse($this->dateFrom)->startOfDay(),
                    \Carbon\Carbon::parse($this->dateTo)->endOfDay()
                ]);
            })
            ->when($this->branchId, function ($query) {
                return $query->whereHas('userProfile', function ($q) {
                    $q->where('Branchid', $this->branchId);
                });
            })
            ->when($this->depId, function ($query) {
                return $query->whereHas('userProfile', function ($q) {
                    $q->where('Depid', $this->depId);
                });
            })
            ->when($this->search2, function ($query) {
                return $query->whereHas('userProfile', function ($q) {
                    $q->where('NAME', 'like', '%' . $this->search2 . '%');
                });
            })
            ->orderBy('TM_EVENT', 'desc');

        // Debugging: Log the generated SQL query
        Log::info('Export Query: ' . $query->toSql(), $query->getBindings());

        return $query;
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
            'Device Serial Number',
        ];
    }

    public function map($log): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        $status = 'N/A';
        if ($log->userProfile && $log->userProfile->END_DATE) {
            $status = \Carbon\Carbon::parse($log->userProfile->END_DATE)->isPast() ? 'CARD EXPIRED' : 'ACTIVE';
        }

        return [
            $rowNumber,
            $log->userProfile->NAME ?? 'N/A',
            $log->userProfile->branch->name ?? 'N/A',
            $log->userProfile->department->name ?? 'N/A',
            $log->TM_EVENT,
            $status,
            $log->DEVICESN,
        ];
    }
}

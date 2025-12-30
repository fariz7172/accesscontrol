<?php

namespace App\Exports;

use App\Models\LeaveProcess;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Log;

class LeaveProcessExport implements FromCollection, WithHeadings, WithMapping, WithStyles
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
        $query = LeaveProcess::query()
            ->with(['userProfile', 'leaveType', 'approver']) // Load approver relationship
            ->whereBetween('FromDate', [$this->startDate, $this->endDate])
            ->orWhereBetween('ToDate', [$this->startDate, $this->endDate])
            ->orWhere(function ($q) {
                $q->where('FromDate', '<=', $this->startDate)
                    ->where('ToDate', '>=', $this->endDate);
            })
            ->orderBy('FromDate', 'asc');

        $results = $query->get();

        // Log the results for debugging
        Log::info('LeaveProcessExport collection retrieved', [
            'count' => $results->count(),
            'data' => $results->map(function ($item) {
                return [
                    'Id' => $item->Id,
                    'EmplID' => $item->EmplID,
                    'leaveid' => $item->leaveid,
                    'userProfile' => $item->userProfile ? $item->userProfile->toArray() : null,
                    'leaveType' => $item->leaveType ? $item->leaveType->toArray() : null,
                    'approver' => $item->approver ? $item->approver->toArray() : null,
                ];
            })->toArray(),
        ]);

        return $results;
    }

    public function headings(): array
    {
        return [
            'Employee',
            'Leave Type',
            'From Date',
            'To Date',
            'Notes',
            'Approver',
            'Approved At',
            'Created At',
            'Updated At',
            'Status',
        ];
    }

    public function map($leaveProcess): array
    {
        return [
            $leaveProcess->userProfile->NAME ?? 'N/A',
            $leaveProcess->leaveType->Name ?? 'N/A',
            \Carbon\Carbon::parse($leaveProcess->FromDate)->format('Y-m-d'),
            \Carbon\Carbon::parse($leaveProcess->ToDate)->format('Y-m-d'),
            $leaveProcess->Notes ?? '-',
            $leaveProcess->approver->username ?? 'N/A', // Get username from User model
            $leaveProcess->approved_at ? \Carbon\Carbon::parse($leaveProcess->approved_at)->format('Y-m-d H:i:s') : 'N/A',
            \Carbon\Carbon::parse($leaveProcess->created_at)->format('Y-m-d H:i:s'),
            \Carbon\Carbon::parse($leaveProcess->updated_at)->format('Y-m-d H:i:s'),
            $leaveProcess->getStatusTextAttribute(), // Use status text accessor
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => ['bold' => true],
        ]);

        return [];
    }
}

<?php

namespace App\Exports;

use App\Models\userLogModel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserLogStatusEntryExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithEvents
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $req        = $this->request;
        $startDate  = $req->get('start_date') ?? now()->toDateString();
        $endDate    = $req->get('end_date')   ?? now()->toDateString();
        $department = $req->get('department');
        $branch     = $req->get('branch');
        $gate       = $req->get('gate');
        $search     = $req->get('search');

        return userLogModel::query()
            ->with(['userProfile.department', 'userProfile.branch', 'deviceGate']) // ini kuncinya!
            ->when($department, fn($q) => $q->whereHas('userProfile.department', fn($sq) => $sq->where('name', $department)))
            ->when($branch,     fn($q) => $q->whereHas('userProfile.branch',     fn($sq) => $sq->where('name', $branch)))
            ->when($gate,       fn($q) => $q->whereHas('deviceGate',             fn($sq) => $sq->where('name', $gate)))
            ->when($search, function ($q) use ($search) {
                $q->whereHas('userProfile', fn($sq) => $sq->where('NAME', 'like', "%{$search}%")
                    ->orWhere('ID',   'like', "%{$search}%"))
                    ->orWhere('DEVICESN', 'like', "%{$search}%");
            })
            ->whereBetween(DB::raw('DATE(TM_EVENT)'), [$startDate, $endDate])
            ->orderBy('TM_EVENT', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Waktu',
            'Nama Karyawan',
            'ID Karyawan',
            'Gate / Device',
            'Status',
            'Department',
            'Branch'
        ];
    }

    public function map($log): array
    {
        static $no = 0;
        $no++;

        $status = $log->deviceGate && $log->deviceGate->stat == 0 ? 'IN' : 'OUT';

        return [
            $no,
            Carbon::parse($log->TM_EVENT)->format('d-m-Y H:i:s'),
            $log->userProfile?->NAME ?? 'N/A',
            $log->USER_ADDR,
            $log->device_name,              // ini otomatis dari accessor getDeviceNameAttribute()
            $status,
            $log->userProfile?->department?->name ?? '-',
            $log->userProfile?->branch?->name     ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font'  => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'  => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E88E5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                // Judul
                $sheet->insertNewRowBefore(1, 3);
                $sheet->setCellValue('A1', 'LAPORAN LOG STATUS ENTRY GATE');
                $sheet->mergeCells('A1:H1');
                $sheet->getStyle('A1')->getFont()->setSize(18)->setBold(true);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Info filter
                $r = $this->request;
                $info = "Periode: " . Carbon::parse($r->start_date ?? now())->format('d M Y') .
                    " s/d " . Carbon::parse($r->end_date ?? now())->format('d M Y') .
                    " | Dept: " . ($r->department ?: 'Semua') .
                    " | Branch: " . ($r->branch ?: 'Semua') .
                    " | Gate: " . ($r->gate ?: 'Semua');

                $sheet->setCellValue('A2', $info);
                $sheet->mergeCells('A2:H2');
                $sheet->getStyle('A2')->getFont()->setItalic(true);

                // Auto-size + freeze
                foreach (range('A', 'H') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
                $sheet->freezePane('A5');
            }
        ];
    }
}

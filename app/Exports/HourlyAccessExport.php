<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HourlyAccessExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $startDate;
    protected $endDate;
    protected $gate;

    public function __construct($startDate, $endDate, $gate = null)
    {
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
        $this->gate      = $gate;
    }

    public function collection()
    {
        // Query yang sama dengan controller
        $hourlyData = DB::table('tbl_userlog')
            ->join('devicegate', 'tbl_userlog.DEVICESN', '=', 'devicegate.sn')
            ->selectRaw("
                HOUR(tbl_userlog.TM_EVENT) as hour,
                COUNT(*) as total_access,
                SUM(CASE WHEN tbl_userlog.stat = 0 THEN 1 ELSE 0 END) as valid_in,
                SUM(CASE WHEN tbl_userlog.stat = 1 THEN 1 ELSE 0 END) as invalid_out,
                GROUP_CONCAT(DISTINCT devicegate.name ORDER BY devicegate.name SEPARATOR ', ') as devices
            ")
            ->whereBetween(DB::raw('DATE(tbl_userlog.TM_EVENT)'), [$this->startDate, $this->endDate])
            ->when($this->gate, fn($q) => $q->where('devicegate.name', $this->gate))
            ->groupBy(DB::raw('HOUR(tbl_userlog.TM_EVENT)'))
            ->orderBy('hour')
            ->get();

        // Mapping ke 24 jam
        return collect(range(0, 23))->map(function ($hour) use ($hourlyData) {
            $data = $hourlyData->firstWhere('hour', $hour);
            return [
                'hour_display' => sprintf('%02d:00 - %02d:59', $hour, $hour),
                'total_access' => $data->total_access ?? 0,
                'valid_in'     => $data->valid_in ?? 0,
                'invalid_out'  => $data->invalid_out ?? 0,
                'devices'      => $data->devices ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Hour',
            'Total Access',
            'Valid (IN)',
            'Invalid (OUT)',
            'Device',
        ];
    }

    public function map($row): array
    {
        return [
            $row['hour_display'],
            $row['total_access'],
            $row['valid_in'],
            $row['invalid_out'],
            $row['devices'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}

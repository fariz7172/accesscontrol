<?php

namespace App\Exports;

use App\Models\userLogModel;
use App\Models\ShiftPattern;
use App\Models\userProfileModel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class UserAttendanceSheet implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $userId;
    protected $month;
    protected $patternId;
    protected $pattern;
    protected $userName;

    public function __construct(int $userId, int $month, int $patternId)
    {
        $this->userId = $userId;
        $this->month = $month;
        $this->patternId = $patternId;
        $this->pattern = ShiftPattern::findOrFail($patternId);

        // Validate that the shift pattern has at least one shift
        if (
            !$this->pattern->shiftDay1 && !$this->pattern->shiftDay2 && !$this->pattern->shiftDay3 &&
            !$this->pattern->shiftDay4 && !$this->pattern->shiftDay5 && !$this->pattern->shiftDay6 &&
            !$this->pattern->shiftDay7
        ) {
            \Illuminate\Support\Facades\Log::error("No shifts defined for PatternID: {$patternId}");
            throw new \Exception("Invalid shift pattern: No shifts defined.");
        }

        $user = userProfileModel::find($userId);
        $this->userName = $user && !empty($user->NAME) ? $user->NAME : "Employee_{$userId}";
    }

    public function collection()
    {
        $year = Carbon::today()->year; // e.g., 2025
        $daysInMonth = Carbon::create($year, $this->month)->daysInMonth;
        $data = new Collection();

        // Preload logs for the entire month to avoid N+1 queries
        $logs = userLogModel::where('USER_ADDR', $this->userId)
            ->whereYear('TM_EVENT', $year)
            ->whereMonth('TM_EVENT', $this->month)
            ->get()
            ->groupBy(function ($log) {
                return Carbon::parse($log->TM_EVENT)->toDateString();
            });

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($year, $this->month, $day);
            $dayOfWeek = $date->dayOfWeekIso; // 1 = Monday, 7 = Sunday
            $patternDay = (($day - 1) % 7) + 1; // Loop pattern every 7 days

            $data->push([
                'user_id' => $this->userId,
                'date' => $date->toDateString(),
                'day_of_week' => $dayOfWeek,
                'pattern_day' => $patternDay,
                'logs' => $logs[$date->toDateString()] ?? collect([]),
            ]);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Employee ID',
            'Name',
            'Date',
            'Day',
            'Shift Name',
            'Begin Time',
            'Out Time',
            'In Status',
            'Out Status',
        ];
    }

    public function map($row): array
    {
        $date = Carbon::parse($row['date']);
        $patternDay = $row['pattern_day'];
        $logs = $row['logs'];

        // Get shift for the pattern day
        $shift = $this->pattern->getShiftForDay($patternDay);

        // Get shift details
        $shiftName = $shift ? $shift->ShiftName : 'N/A';
        $beginTime = $shift ? $shift->Begin_Time : null;
        $outTime = $shift ? $shift->Out_time : null;

        // Log for debugging
        if (!$shift) {
            \Illuminate\Support\Facades\Log::warning("No shift found for PatternID: {$this->patternId}, PatternDay: {$patternDay}, Date: {$row['date']}, UserID: {$this->userId}");
        } elseif (!$beginTime || !$outTime) {
            \Illuminate\Support\Facades\Log::warning("Begin_Time or Out_time is null for ShiftID: {$shift->id}, PatternID: {$this->patternId}, Date: {$row['date']}, UserID: {$this->userId}, ShiftName: {$shift->ShiftName}, Begin_Time: {$shift->Begin_Time}, Out_time: {$shift->Out_time}");
        }

        $inStatus = 'N/A';
        $outStatus = 'N/A';
        $inStatusTime = null; // To store TM_EVENT for In Status
        $outStatusTime = null; // To store TM_EVENT for Out Status
        $isLate = false; // Track for red font styling

        if ($shift && $logs->isNotEmpty()) {
            if ($logs->count() === 1) {
                // Single log case: Use for In Status, set Out Status to "Belum Absen"
                $logTime = Carbon::parse($logs->first()->TM_EVENT);
                $inStatusTime = $logTime->format('H:i:s');
                if ($beginTime) {
                    $beginTimeParsed = Carbon::parse($beginTime)->format('H:i:s');
                    $inStatus = $inStatusTime > $beginTimeParsed ? 'Telat' : 'On Time';
                    $isLate = $inStatus === 'Telat';
                } else {
                    $inStatus = 'On Time';
                }
                $outStatus = 'Belum Absen';
            } else {
                // Multiple logs: Use min for In Status, max for Out Status
                $minLog = $logs->min('TM_EVENT');
                $maxLog = $logs->max('TM_EVENT');

                // In Status
                if ($minLog && $beginTime) {
                    $inStatusTime = Carbon::parse($minLog)->format('H:i:s');
                    $beginTimeParsed = Carbon::parse($beginTime)->format('H:i:s');
                    $inStatus = $inStatusTime > $beginTimeParsed ? 'Telat' : 'On Time';
                    $isLate = $inStatus === 'Telat';
                }

                // Out Status
                if ($maxLog && $outTime) {
                    $outStatusTime = Carbon::parse($maxLog)->format('H:i:s');
                    $outTimeParsed = Carbon::parse($outTime)->format('H:i:s');
                    $outStatus = $outStatusTime < $outTimeParsed ? 'Belum Absen Keluar' : 'Complete';
                }
            }
        }

        // Apply TM_EVENT values based on conditions
        $displayInStatus = ($inStatus === 'Telat' && $inStatusTime) ? $inStatusTime : $inStatus;
        $displayOutStatus = ($outStatus === 'Complete' && $outStatusTime) ? $outStatusTime : $outStatus;

        // Store isLate for styling
        $row['is_late'] = $isLate;

        return [
            $this->userId,
            $this->userName,
            $date->format('Y-m-d'),
            $date->format('l'), // Day name
            $shiftName,
            $beginTime ?? 'N/A',
            $outTime ?? 'N/A',
            $displayInStatus,
            $displayOutStatus,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Get the highest row number (total rows in the sheet)
        $highestRow = $sheet->getHighestRow();
        // Define range for In Status (column H) and Out Status (column I), starting from row 2 (after header)
        $inStatusRange = 'H2:H' . $highestRow;
        $outStatusRange = 'I2:I' . $highestRow;

        // Collect rows where In Status is Telat (for TM_EVENT > Begin_Time)
        $lateRows = [];
        $data = $this->collection();
        foreach ($data as $index => $row) {
            $mappedRow = $this->map($row);
            if (isset($row['is_late']) && $row['is_late']) {
                $lateRows[] = $index + 2; // Row number in Excel (1-based, +1 for header)
            }
        }

        // Apply red font to In Status cells where TM_EVENT > Begin_Time
        foreach ($lateRows as $rowNum) {
            $sheet->getStyle("H{$rowNum}")->getFont()->setColor(new Color(Color::COLOR_RED));
        }

        // Conditional formatting for Out Status = "Belum Absen" or "Belum Absen Keluar"
        $outStatusCondition1 = new Conditional();
        $outStatusCondition1->setConditionType(Conditional::CONDITION_CONTAINSTEXT)
            ->setOperatorType(Conditional::OPERATOR_CONTAINSTEXT)
            ->setText('Belum Absen')
            ->getStyle()
            ->getFont()
            ->setColor(new Color(Color::COLOR_RED));

        $outStatusCondition2 = new Conditional();
        $outStatusCondition2->setConditionType(Conditional::CONDITION_CONTAINSTEXT)
            ->setOperatorType(Conditional::OPERATOR_CONTAINSTEXT)
            ->setText('Belum Absen Keluar')
            ->getStyle()
            ->getFont()
            ->setColor(new Color(Color::COLOR_RED));

        // Apply conditional formatting to Out Status column
        $sheet->getStyle($outStatusRange)->setConditionalStyles([$outStatusCondition1, $outStatusCondition2]);

        // Bold the header row
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);

        return [];
    }

    public function title(): string
    {
        // Sanitize sheet name to comply with Excel rules
        $safeName = preg_replace('/[\/\\\*\?\:\[\]]/', '', $this->userName);
        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $safeName);
        $safeName = empty($safeName) ? "Employee_{$this->userId}" : $safeName;
        $safeName = substr($safeName, 0, 31);
        static $usedNames = [];
        $baseName = $safeName;
        $counter = 1;
        while (in_array($safeName, $usedNames)) {
            $suffix = '_' . $this->userId . ($counter > 1 ? "_$counter" : '');
            $safeName = substr($baseName, 0, 31 - strlen($suffix)) . $suffix;
            $counter++;
        }
        $usedNames[] = $safeName;
        return $safeName;
    }
}

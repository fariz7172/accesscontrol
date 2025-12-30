<?php

namespace App\Http\Controllers;

use App\Exports\ExportAttendance;
use App\Models\ShiftPattern;
use App\Models\userProfileModel;
use App\Models\AttendModel;
use App\Models\departmentModel;
use App\Models\HolidayCal;
use App\Models\LeaveProcess;
use App\Models\LeaveType;
use App\Models\Shift;
use App\Models\userLogModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class AttendUserController extends Controller
{
    public function index(Request $request)
    {
        $query = userProfileModel::with(['shiftPattern', 'department']);

        if ($request->has('search_name') && $request->search_name) {
            $query->where('NAME', 'like', '%' . $request->search_name . '%');
        }

        if ($request->has('search_department') && $request->search_department) {
            $query->whereHas('department', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search_department . '%');
            });
        }

        $users = $query->get();
        $departments = departmentModel::all();

        return view('addshiftuser.index', compact('users', 'departments'));
    }


    public function addReportNewPattern(Request $request)
    {
        $request->validate([
            'PatternID' => 'required|exists:shiftpattern,Id',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:usersprofile,Id',
            'month' => 'required|integer|between:1,12',
        ]);

        try {
            // Ambil bulan dan tahun saat ini
            $month = (int) $request->month;
            $year = Carbon::today()->year; // Misalnya, 2025
            $daysInMonth = Carbon::create($year, $month)->daysInMonth;

            // Log awal untuk memastikan input yang diterima
            Log::info('Memulai addReportNewPattern', [
                'PatternID' => $request->PatternID,
                'user_ids' => $request->user_ids,
                'month' => $month,
                'year' => $year,
                'daysInMonth' => $daysInMonth,
            ]);

            // Ambil data hari libur untuk tahun dan bulan yang dipilih dari tabel holidaycal
            $holidays = HolidayCal::where('StartDate', '<=', Carbon::create($year, $month, $daysInMonth)->endOfDay())
                ->where('EndDate', '>=', Carbon::create($year, $month, 1)->startOfDay())
                ->get();

            Log::debug('Data holidaycal yang diambil', [
                'holidays' => $holidays->toArray(),
                'count' => $holidays->count(),
            ]);

            // Muat pola shift dengan detail shift
            $pattern = ShiftPattern::with([
                'shiftDay1' => function ($query) {
                    $query->select('id', 'ShiftNo', 'Tipe', 'Begin_Time', 'Out_Time', 'Start_Break', 'Range_Break', 'Break_Time', 'Resume_Time', 'Start_Resume', 'Range_Resume', 'Start_Out', 'Start_In', 'Range_In', 'Range_Out');
                },
                'shiftDay2' => function ($query) {
                    $query->select('id', 'ShiftNo', 'Tipe', 'Begin_Time', 'Out_Time', 'Start_Break', 'Range_Break', 'Break_Time', 'Resume_Time', 'Start_Resume', 'Range_Resume', 'Start_Out', 'Start_In', 'Range_In', 'Range_Out');
                },
                'shiftDay3' => function ($query) {
                    $query->select('id', 'ShiftNo', 'Tipe', 'Begin_Time', 'Out_Time', 'Start_Break', 'Range_Break', 'Break_Time', 'Resume_Time', 'Start_Resume', 'Range_Resume', 'Start_Out', 'Start_In', 'Range_In', 'Range_Out');
                },
                'shiftDay4' => function ($query) {
                    $query->select('id', 'ShiftNo', 'Tipe', 'Begin_Time', 'Out_Time', 'Start_Break', 'Range_Break', 'Break_Time', 'Resume_Time', 'Start_Resume', 'Range_Resume', 'Start_Out', 'Start_In', 'Range_In', 'Range_Out');
                },
                'shiftDay5' => function ($query) {
                    $query->select('id', 'ShiftNo', 'Tipe', 'Begin_Time', 'Out_Time', 'Start_Break', 'Range_Break', 'Break_Time', 'Resume_Time', 'Start_Resume', 'Range_Resume', 'Start_Out', 'Start_In', 'Range_In', 'Range_Out');
                },
                'shiftDay6' => function ($query) {
                    $query->select('id', 'ShiftNo', 'Tipe', 'Begin_Time', 'Out_Time', 'Start_Break', 'Range_Break', 'Break_Time', 'Resume_Time', 'Start_Resume', 'Range_Resume', 'Start_Out', 'Start_In', 'Range_In', 'Range_Out');
                },
                'shiftDay7' => function ($query) {
                    $query->select('id', 'ShiftNo', 'Tipe', 'Begin_Time', 'Out_Time', 'Start_Break', 'Range_Break', 'Break_Time', 'Resume_Time', 'Start_Resume', 'Range_Resume', 'Start_Out', 'Start_In', 'Range_In', 'Range_Out');
                },
            ])->findOrFail($request->PatternID);

            // Log untuk memeriksa data pola shift
            Log::debug('Data ShiftPattern dimuat', [
                'PatternID' => $request->PatternID,
                'shiftDay6' => $pattern->shiftDay6 ? $pattern->shiftDay6->toArray() : null,
                'shiftDay7' => $pattern->shiftDay7 ? $pattern->shiftDay7->toArray() : null,
            ]);

            $updatedCount = 0;
            $createdCount = 0;

            // Tentukan pemetaan pola shift mulai dari pola7 untuk hari Minggu
            $dayToPolaMap = [
                0 => 'shiftDay7', // Minggu -> pola7
                1 => 'shiftDay1', // Senin -> pola1
                2 => 'shiftDay2', // Selasa -> pola2
                3 => 'shiftDay3', // Rabu -> pola3
                4 => 'shiftDay4', // Kamis -> pola4
                5 => 'shiftDay5', // Jumat -> pola5
                6 => 'shiftDay6', // Sabtu -> pola6
            ];

            // Urutkan user_ids untuk memastikan data disimpan dengan urutan EmployeeID
            $sortedUserIds = $request->user_ids;
            sort($sortedUserIds);

            // Mulai transaksi database
            DB::transaction(function () use ($request, $daysInMonth, $year, $month, $dayToPolaMap, $pattern, $holidays, $sortedUserIds, &$createdCount, &$updatedCount) {
                // Iterasi setiap hari dalam bulan
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $attDate = Carbon::create($year, $month, $day);
                    $dayOfWeek = $attDate->dayOfWeek; // 0 = Minggu, 1 = Senin, ..., 6 = Sabtu
                    $shiftField = $dayToPolaMap[$dayOfWeek];
                    $shift = $pattern->$shiftField;

                    // Log untuk memeriksa hari yang sedang diproses
                    Log::debug('Memproses hari', [
                        'AttDate' => $attDate->toDateString(),
                        'dayOfWeek' => $dayOfWeek,
                        'dayName' => $attDate->dayName,
                        'shiftField' => $shiftField,
                        'shift' => $shift ? $shift->toArray() : null,
                    ]);

                    // Ambil ShiftCode dan DayType dari shift
                    $shiftCode = $shift ? $shift->id : null;
                    $dayType = $shift ? $shift->Tipe : 0; // Default ke 0 jika shift tidak ada

                    // Periksa apakah tanggal saat ini adalah hari libur
                    $isHoliday = false;
                    $holidayName = null;
                    foreach ($holidays as $holiday) {
                        $startDate = Carbon::parse($holiday->StartDate)->startOfDay();
                        $endDate = Carbon::parse($holiday->EndDate)->startOfDay();
                        if ($attDate->equalTo($startDate) || ($attDate->gte($startDate) && $attDate->lte($endDate))) {
                            $isHoliday = true;
                            $holidayName = $holiday->Name;
                            Log::debug('Hari libur terdeteksi untuk AttDate', [
                                'AttDate' => $attDate->toDateString(),
                                'NamaHariLibur' => $holidayName,
                                'TanggalMulai' => $startDate->toDateString(),
                                'TanggalSelesai' => $endDate->toDateString(),
                            ]);
                            break;
                        }
                    }

                    // Log untuk memeriksa status hari libur
                    Log::debug('Memeriksa hari libur', [
                        'AttDate' => $attDate->toDateString(),
                        'isHoliday' => $isHoliday,
                        'holidayName' => $holidayName,
                    ]);

                    // Timpa DayType ke 2 jika tanggal adalah hari libur
                    if ($isHoliday) {
                        $dayType = 2;
                        Log::debug('Hari libur terdeteksi, menimpa DayType', [
                            'tanggal' => $attDate->toDateString(),
                            'dayType' => $dayType,
                        ]);
                    }

                    // Proses setiap pengguna dalam urutan yang sudah diurutkan
                    foreach ($sortedUserIds as $user_id) {
                        $timeIn = null;
                        $timeBreak = null;
                        $timeOut = null;
                        $timeResume = null;
                        $timeInShort = 0;
                        $timeOutShort = 0;
                        $workTime = 0;
                        $earlyWork = null;
                        $totalWorkHour = 0;
                        $timeBreakShort = 0;
                        $timeResumeShort = 0;
                        $totalOT = 0;
                        $present = 0;
                        $remark = null;
                        $dutyProcessId = null;

                        // Periksa catatan cuti di tabel leaveprocess
                        $leaveRecord = LeaveProcess::where('EmplID', $user_id)
                            ->where('FromDate', '<=', $attDate)
                            ->where('ToDate', '>=', $attDate)
                            ->first();

                        // Atur Remark dan DutyProcessID
                        if ($isHoliday) {
                            $remark = 'Holiday';
                            Log::debug('Hari libur terdeteksi untuk AttDate', [
                                'user_id' => $user_id,
                                'AttDate' => $attDate->toDateString(),
                                'NamaHariLibur' => $holidayName,
                            ]);
                        } elseif ($leaveRecord) {
                            $remark = 'On Leave';
                            $dutyProcessId = $leaveRecord->leaveid;
                            Log::debug("Catatan cuti terdeteksi untuk pengguna", [
                                'user_id' => $user_id,
                                'AttDate' => $attDate->toDateString(),
                                'Remark' => 'On Leave',
                                'DutyProcessID' => $dutyProcessId,
                            ]);
                        }

                        // Jika hari adalah hari libur, cuti, atau tanpa shift, masukkan data default
                        if ($isHoliday || $leaveRecord || !$shift) {
                            Log::debug('Menyimpan data default untuk hari libur, cuti, atau tanpa shift', [
                                'user_id' => $user_id,
                                'AttDate' => $attDate->toDateString(),
                                'isHoliday' => $isHoliday,
                                'leaveRecord' => $leaveRecord ? $leaveRecord->toArray() : null,
                                'shiftExists' => $shift ? true : false,
                            ]);

                            $attendanceData = [
                                'PatternID' => $request->PatternID,
                                'ShiftCode' => $shiftCode,
                                'DayType' => $dayType,
                                'Time_In' => null,
                                'Time_Break' => null,
                                'Time_Resume' => null,
                                'Time_Out' => null,
                                'Time_InShort' => 0,
                                'Time_BreakShort' => 0,
                                'Time_ResumeShort' => 0,
                                'Time_OutShort' => 0,
                                'WorkTime' => 0,
                                'EarlyWork' => null,
                                'TotalWorkHour' => 0,
                                'TotalOT' => 0,
                                'Present' => 0,
                                'Remark' => $remark,
                                'DutyProcessID' => $dutyProcessId,
                            ];
                        } else {
                            // Validasi dan pengambilan Time_In dan Time_InShort
                            if ($shift && $shift->Start_In && $shift->Range_In && $shift->Begin_Time) {
                                $startIn = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Start_In);
                                $rangeIn = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Range_In);
                                $beginTime = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Begin_Time)->startOfMinute();

                                $logs = DB::table('tbl_userlog')
                                    ->where('USER_ADDR', $user_id)
                                    ->whereDate('TM_EVENT', $attDate)
                                    ->select('TM_EVENT')
                                    ->get()
                                    ->map(function ($log) {
                                        return Carbon::parse($log->TM_EVENT)->startOfMinute();
                                    });

                                Log::debug('Log absensi untuk Time_In', [
                                    'user_id' => $user_id,
                                    'AttDate' => $attDate->toDateString(),
                                    'logs' => $logs->toArray(),
                                ]);

                                $validLogs = $logs->filter(function ($log) use ($startIn, $rangeIn) {
                                    return $log->gte($startIn) && $log->lte($rangeIn);
                                });

                                if ($validLogs->isNotEmpty()) {
                                    $timeIn = $validLogs->sortBy('TM_EVENT')->first()->format('Y-m-d H:i:s');
                                    $lateLogs = $validLogs->filter(function ($log) use ($beginTime) {
                                        return $log->gt($beginTime);
                                    });

                                    if ($lateLogs->isNotEmpty()) {
                                        $closestLateLog = $lateLogs->sortBy('TM_EVENT')->first();
                                        $timeInShort = $closestLateLog->diffInMinutes($beginTime);
                                        Log::debug("Time_InShort diatur", [
                                            'user_id' => $user_id,
                                            'AttDate' => $attDate->toDateString(),
                                            'timeInShort' => $timeInShort,
                                            'TM_EVENT' => $closestLateLog->toDateTimeString(),
                                        ]);
                                    }
                                }

                                // Validasi dan pengambilan EarlyWork
                                $earlyWorkLogs = DB::table('tbl_userlog')
                                    ->where('USER_ADDR', $user_id)
                                    ->whereDate('TM_EVENT', $attDate)
                                    ->where('TM_EVENT', '>=', $startIn)
                                    ->where('TM_EVENT', '<', $beginTime)
                                    ->select(DB::raw('MIN(TM_EVENT) as min_early'))
                                    ->first();

                                if ($earlyWorkLogs && $earlyWorkLogs->min_early) {
                                    $earlyWork = abs(Carbon::parse($earlyWorkLogs->min_early)->startOfMinute()->diffInMinutes($beginTime));
                                    Log::debug("EarlyWork diatur", [
                                        'user_id' => $user_id,
                                        'AttDate' => $attDate->toDateString(),
                                        'earlyWork' => $earlyWork,
                                    ]);
                                }
                            }

                            // Validasi dan pengambilan Time_Out dan Time_OutShort
                            if ($shift && $shift->Out_Time && $shift->Start_Out && $shift->Range_Out) {
                                $outTime = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Out_Time)->startOfMinute();
                                $startOut = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Start_Out)->startOfMinute();
                                $rangeOut = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Range_Out)->startOfMinute();

                                $outLogs = DB::table('tbl_userlog')
                                    ->where('USER_ADDR', $user_id)
                                    ->whereDate('TM_EVENT', $attDate)
                                    ->select('TM_EVENT')
                                    ->get()
                                    ->map(function ($log) {
                                        return Carbon::parse($log->TM_EVENT)->startOfMinute();
                                    });

                                $logsInRange = $outLogs->filter(function ($log) use ($startOut, $rangeOut) {
                                    return $log->gte($startOut) && $log->lte($rangeOut);
                                });

                                if ($logsInRange->isNotEmpty()) {
                                    $closestLog = $logsInRange->sortBy(function ($log) use ($outTime) {
                                        return abs($log->diffInMinutes($outTime));
                                    })->first();
                                    $timeOut = $closestLog->format('Y-m-d H:i:s');
                                    $timeOutShort = abs($closestLog->diffInMinutes($outTime));
                                }
                            }

                            // Logika untuk Time_Break dan Time_BreakShort
                            if ($shift && $shift->Start_Break && $shift->Range_Break && $shift->Break_Time) {
                                $startBreakTime = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->toDateString() . ' ' . $shift->Start_Break)->startOfMinute();
                                $rangeBreakTime = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->toDateString() . ' ' . $shift->Range_Break)->startOfMinute();
                                $shiftBreakTime = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->toDateString() . ' ' . $shift->Break_Time)->startOfMinute();

                                $breakLogs = DB::table('tbl_userlog')
                                    ->where('USER_ADDR', $user_id)
                                    ->whereDate('TM_EVENT', $attDate)
                                    ->select('TM_EVENT')
                                    ->get()
                                    ->map(function ($log) {
                                        return Carbon::parse($log->TM_EVENT)->startOfMinute();
                                    });

                                $breakLogsInRange = $breakLogs->filter(function ($log) use ($startBreakTime, $rangeBreakTime) {
                                    return $log->gte($startBreakTime) && $log->lte($rangeBreakTime);
                                });

                                if ($breakLogsInRange->isNotEmpty()) {
                                    $closestBreakLog = $breakLogsInRange->sortBy(function ($log) use ($shiftBreakTime) {
                                        return abs($log->diffInMinutes($shiftBreakTime));
                                    })->first();
                                    $timeBreak = $closestBreakLog->format('Y-m-d H:i:s');
                                    if ($timeIn && $timeBreak === $timeIn) {
                                        $otherBreakLogs = $breakLogsInRange->filter(function ($log) use ($timeIn) {
                                            return $log->format('Y-m-d H:i:s') !== $timeIn;
                                        });
                                        if ($otherBreakLogs->isNotEmpty()) {
                                            $closestBreakLog = $otherBreakLogs->sortBy(function ($log) use ($shiftBreakTime) {
                                                return abs($log->diffInMinutes($shiftBreakTime));
                                            })->first();
                                            $timeBreak = $closestBreakLog->format('Y-m-d H:i:s');
                                        } else {
                                            $timeBreak = null;
                                        }
                                    }
                                    $timeBreakShort = $timeBreak ? abs(Carbon::parse($timeBreak)->diffInMinutes($shiftBreakTime)) : 0;
                                }
                            }

                            // Logika untuk Time_Resume dan Time_ResumeShort
                            if ($shift && $shift->Start_Resume && $shift->Range_Resume && $shift->Resume_Time) {
                                $startResumeTime = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->toDateString() . ' ' . $shift->Start_Resume)->startOfMinute();
                                $rangeResumeTime = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->toDateString() . ' ' . $shift->Range_Resume)->startOfMinute();
                                $shiftResumeTime = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->toDateString() . ' ' . $shift->Resume_Time)->startOfMinute();

                                $resumeLogs = DB::table('tbl_userlog')
                                    ->where('USER_ADDR', $user_id)
                                    ->whereDate('TM_EVENT', $attDate)
                                    ->select('TM_EVENT')
                                    ->get()
                                    ->map(function ($log) {
                                        return Carbon::parse($log->TM_EVENT)->startOfMinute();
                                    });

                                $resumeLogsInRange = $resumeLogs->filter(function ($log) use ($startResumeTime, $rangeResumeTime) {
                                    return $log->gte($startResumeTime) && $log->lte($rangeResumeTime);
                                });

                                if ($resumeLogsInRange->isNotEmpty()) {
                                    $closestResumeLog = $resumeLogsInRange->sortBy(function ($log) use ($shiftResumeTime) {
                                        return abs($log->diffInMinutes($shiftResumeTime));
                                    })->first();
                                    $timeResume = $closestResumeLog->format('Y-m-d H:i:s');
                                    if ($timeIn && $timeResume === $timeIn || $timeBreak && $timeResume === $timeBreak || $timeOut && $timeResume === $timeOut) {
                                        $timeResume = null;
                                        $timeResumeShort = 0;
                                    } else {
                                        $timeResumeShort = abs($closestResumeLog->diffInMinutes($shiftResumeTime));
                                    }
                                }
                            }

                            // Hitung WorkTime
                            if ($shift && $shift->Begin_Time && $shift->Out_Time && $shift->Break_Time && $shift->Resume_Time) {
                                $beginTime = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Begin_Time)->startOfMinute();
                                $outTime = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Out_Time)->startOfMinute();
                                $shiftBreakTime = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->toDateString() . ' ' . $shift->Break_Time)->startOfMinute();
                                $shiftResumeTime = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->toDateString() . ' ' . $shift->Resume_Time)->startOfMinute();

                                $totalShiftMinutes = $outTime->diffInMinutes($beginTime);
                                $breakDurationMinutes = $shiftResumeTime->diffInMinutes($shiftBreakTime);
                                $workTime = $totalShiftMinutes - $breakDurationMinutes;

                                if ($workTime < 0) {
                                    $workTime = 0;
                                    Log::warning("WorkTime bernilai negatif", [
                                        'user_id' => $user_id,
                                        'AttDate' => $attDate->toDateString(),
                                        'ShiftID' => $shift->id,
                                    ]);
                                }
                            }

                            // Hitung TotalWorkHour
                            if ($timeIn && $timeOut && $shift->Break_Time && $shift->Resume_Time) {
                                $timeInCarbon = Carbon::parse($timeIn)->startOfMinute();
                                $timeOutCarbon = Carbon::parse($timeOut)->startOfMinute();
                                $shiftBreakTimeCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->toDateString() . ' ' . $shift->Break_Time)->startOfMinute();
                                $shiftResumeTimeCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->toDateString() . ' ' . $shift->Resume_Time)->startOfMinute();

                                $totalLogMinutes = $timeOutCarbon->diffInMinutes($timeInCarbon);
                                $breakDurationMinutes = $shiftResumeTimeCarbon->diffInMinutes($shiftBreakTimeCarbon);
                                $totalWorkHour = $totalLogMinutes - $breakDurationMinutes;

                                if ($totalWorkHour < 0) {
                                    $totalWorkHour = 0;
                                    Log::warning("TotalWorkHour bernilai negatif", [
                                        'user_id' => $user_id,
                                        'AttDate' => $attDate->toDateString(),
                                    ]);
                                }
                            }

                            // Hitung TotalOT
                            if ($timeOut && $shift->Out_Time) {
                                $outTimeCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->toDateString() . ' ' . $shift->Out_Time)->startOfMinute();
                                $timeOutCarbon = Carbon::parse($timeOut)->startOfMinute();
                                if ($timeOutCarbon->greaterThan($outTimeCarbon)) {
                                    $totalOT = $timeOutCarbon->diffInMinutes($outTimeCarbon);
                                }
                            }

                            // Atur Present ke 1 jika Time_In, Time_Break, Time_Out, atau Time_Resume tidak null
                            if ($timeIn || $timeBreak || $timeOut || $timeResume) {
                                $present = 1;
                            }

                            // Log untuk memverifikasi pengaturan Present
                            Log::debug('Mengatur nilai Present', [
                                'user_id' => $user_id,
                                'AttDate' => $attDate->toDateString(),
                                'Time_In' => $timeIn,
                                'Time_Break' => $timeBreak,
                                'Time_Out' => $timeOut,
                                'Time_Resume' => $timeResume,
                                'Present' => $present,
                            ]);

                            $attendanceData = [
                                'PatternID' => $request->PatternID,
                                'ShiftCode' => $shiftCode,
                                'DayType' => $dayType,
                                'Time_In' => $timeIn,
                                'Time_Break' => $timeBreak,
                                'Time_Resume' => $timeResume,
                                'Time_Out' => $timeOut,
                                'Time_InShort' => $timeInShort,
                                'Time_BreakShort' => $timeBreakShort,
                                'Time_ResumeShort' => $timeResumeShort,
                                'Time_OutShort' => $timeOutShort,
                                'WorkTime' => $workTime,
                                'EarlyWork' => $earlyWork,
                                'TotalWorkHour' => $totalWorkHour,
                                'TotalOT' => $totalOT,
                                'Present' => $present,
                                'Remark' => $remark,
                                'DutyProcessID' => $dutyProcessId,
                            ];
                        }

                        // Log sebelum menyimpan data absensi
                        Log::debug('Sebelum menyimpan data absensi', [
                            'user_id' => $user_id,
                            'AttDate' => $attDate->toDateString(),
                            'attendanceData' => $attendanceData,
                        ]);

                        // Buat atau perbarui catatan absensi
                        $result = AttendModel::updateOrCreate(
                            [
                                'EmployeeID' => $user_id,
                                'AttDate' => $attDate,
                            ],
                            $attendanceData
                        );

                        // Log setelah menyimpan data
                        Log::debug('Setelah menyimpan data absensi', [
                            'user_id' => $user_id,
                            'AttDate' => $attDate->toDateString(),
                            'wasRecentlyCreated' => $result->wasRecentlyCreated,
                            'attendanceData' => $attendanceData,
                        ]);

                        if ($result->wasRecentlyCreated) {
                            $createdCount++;
                        } else {
                            $updatedCount++;
                        }
                    }
                }
            });

            // Siapkan pesan umpan balik
            $monthName = Carbon::create($year, $month, 1)->format('F');
            $message = "Berhasil membuat $createdCount dan memperbarui $updatedCount catatan absensi untuk $monthName $year.";

            Log::info('Proses addReportNewPattern selesai', [
                'createdCount' => $createdCount,
                'updatedCount' => $updatedCount,
                'message' => $message,
            ]);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return redirect()->route('addshiftuser.index', ['tab' => 'assign-shift'])
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Kesalahan saat membuat catatan absensi', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->ajax()) {
                return response()->json(['success' => false, 'error' => 'Gagal membuat catatan absensi. Silakan coba lagi.'], 500);
            }

            return redirect()->back()->with('error', 'Gagal membuat catatan absensi. Silakan coba lagi.');
        }
    }

    public function recap(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:usersprofile,ID',
            'PatternID' => 'required|exists:shiftpattern,Id',
            'month' => 'required|integer|between:1,12',
        ]);

        try {
            $month = (int) $request->month;
            $year = Carbon::today()->year; // e.g., 2025
            $daysInMonth = Carbon::create($year, $month)->daysInMonth;
            $selectedMonthName = Carbon::create($year, $month, 1)->format('F');

            // Fetch users
            $users = userProfileModel::whereIn('ID', $request->user_ids)->get();
            Log::debug('Recap Users', ['count' => $users->count(), 'ids' => $users->pluck('ID')->toArray()]);

            // Fetch attendances with related data
            $attendances = AttendModel::with(['shift', 'leaveType'])
                ->whereIn('EmployeeID', $request->user_ids)
                ->whereYear('AttDate', $year)
                ->whereMonth('AttDate', $month)
                ->select('id', 'EmployeeID', 'AttDate', 'ShiftCode', 'DayType', 'DutyProcessID')
                ->get()
                ->groupBy('EmployeeID');
            Log::debug('Recap Attendances', ['keys' => array_keys($attendances->toArray()), 'count' => $attendances->map->count()->sum()]);

            return view('addshiftuser.recap', compact('users', 'attendances', 'selectedMonthName', 'month', 'daysInMonth', 'year'));
        } catch (\Exception $e) {
            Log::error('Error fetching recap data: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to fetch recap data. Please try again.');
        }
    }

    public function bulkAssign(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:usersprofile,ID',
            'PatternID' => 'nullable|exists:shiftpattern,Id',
            'month' => 'nullable|integer|between:1,12',
        ]);

        try {
            foreach ($request->user_ids as $user_id) {
                $user = userProfileModel::findOrFail($user_id);
                $user->update(['PatternID' => $request->PatternID]);
            }
            return redirect()->route('shift.index', ['tab' => 'assign-shift'])
                ->with('success', 'Shift patterns assigned successfully to selected users.');
        } catch (\Exception $e) {
            Log::error('Error assigning shift patterns: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to assign shift patterns. Please try again.');
        }
    }

    public function edit($id)
    {
        $user = userProfileModel::findOrFail($id);
        $patterns = ShiftPattern::all();
        return view('addshiftuser.edit', compact('user', 'patterns'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'PatternID' => 'nullable|exists:shiftpattern,Id',
        ]);

        try {
            $user = userProfileModel::findOrFail($id);
            $user->update(['PatternID' => $request->PatternID]);
            return redirect()->route('shift.index', ['tab' => 'assign-shift'])
                ->with('success', 'Shift pattern assigned successfully.');
        } catch (\Exception $e) {
            Log::error('Error assigning shift pattern: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to assign shift pattern. Please try again.');
        }
    }

    public function getShiftPatternDetails($id)
    {
        try {
            $pattern = ShiftPattern::with([
                'shiftDay1' => function ($query) {
                    $query->select('id', 'ShiftName', 'Begin_Time', 'Out_Time', 'Tipe');
                },
                'shiftDay2' => function ($query) {
                    $query->select('id', 'ShiftName', 'Begin_Time', 'Out_Time', 'Tipe');
                },
                'shiftDay3' => function ($query) {
                    $query->select('id', 'ShiftName', 'Begin_Time', 'Out_Time', 'Tipe');
                },
                'shiftDay4' => function ($query) {
                    $query->select('id', 'ShiftName', 'Begin_Time', 'Out_Time', 'Tipe');
                },
                'shiftDay5' => function ($query) {
                    $query->select('id', 'ShiftName', 'Begin_Time', 'Out_Time', 'Tipe');
                },
                'shiftDay6' => function ($query) {
                    $query->select('id', 'ShiftName', 'Begin_Time', 'Out_Time', 'Tipe');
                },
                'shiftDay7' => function ($query) {
                    $query->select('id', 'ShiftName', 'Begin_Time', 'Out_Time', 'Tipe');
                }
            ])->findOrFail($id);

            return response()->json([
                'PatternName' => $pattern->PatternName,
                'PatternType' => $pattern->PatternType,
                'pola1' => $pattern->shiftDay1 ? [
                    'ShiftName' => $pattern->shiftDay1->ShiftName,
                    'Begin_Time' => $pattern->shiftDay1->Begin_Time,
                    'Out_Time' => $pattern->shiftDay1->Out_Time,
                    'Tipe' => $pattern->shiftDay1->Tipe
                ] : null,
                'pola2' => $pattern->shiftDay2 ? [
                    'ShiftName' => $pattern->shiftDay2->ShiftName,
                    'Begin_Time' => $pattern->shiftDay2->Begin_Time,
                    'Out_Time' => $pattern->shiftDay2->Out_Time,
                    'Tipe' => $pattern->shiftDay2->Tipe
                ] : null,
                'pola3' => $pattern->shiftDay3 ? [
                    'ShiftName' => $pattern->shiftDay3->ShiftName,
                    'Begin_Time' => $pattern->shiftDay3->Begin_Time,
                    'Out_Time' => $pattern->shiftDay3->Out_Time,
                    'Tipe' => $pattern->shiftDay3->Tipe
                ] : null,
                'pola4' => $pattern->shiftDay4 ? [
                    'ShiftName' => $pattern->shiftDay4->ShiftName,
                    'Begin_Time' => $pattern->shiftDay4->Begin_Time,
                    'Out_Time' => $pattern->shiftDay4->Out_Time,
                    'Tipe' => $pattern->shiftDay4->Tipe
                ] : null,
                'pola5' => $pattern->shiftDay5 ? [
                    'ShiftName' => $pattern->shiftDay5->ShiftName,
                    'Begin_Time' => $pattern->shiftDay5->Begin_Time,
                    'Out_Time' => $pattern->shiftDay5->Out_Time,
                    'Tipe' => $pattern->shiftDay5->Tipe
                ] : null,
                'pola6' => $pattern->shiftDay6 ? [
                    'ShiftName' => $pattern->shiftDay6->ShiftName,
                    'Begin_Time' => $pattern->shiftDay6->Begin_Time,
                    'Out_Time' => $pattern->shiftDay6->Out_Time,
                    'Tipe' => $pattern->shiftDay6->Tipe
                ] : null,
                'pola7' => $pattern->shiftDay7 ? [
                    'ShiftName' => $pattern->shiftDay7->ShiftName,
                    'Begin_Time' => $pattern->shiftDay7->Begin_Time,
                    'Out_Time' => $pattern->shiftDay7->Out_Time,
                    'Tipe' => $pattern->shiftDay7->Tipe
                ] : null,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching shift pattern details: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch shift pattern details'], 500);
        }
    }

    public function show($id)
    {
        try {
            $user = userProfileModel::with(['shiftPattern', 'department'])->findOrFail($id);
            return view('addshiftuser.show', compact('user'));
        } catch (\Exception $e) {
            Log::error('Error fetching user details: ' . $e->getMessage());
            return redirect()->route('addshiftuser.index')->with('error', 'Failed to fetch user details.');
        }
    }

    public function export(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:usersprofile,ID',
            'PatternID' => 'required|exists:shiftpattern,Id',
            'month' => 'required|integer|between:1,12',
        ]);

        try {
            $month = (int) $request->month;
            $year = Carbon::today()->year;
            $monthName = Carbon::create($year, $month, 1)->format('F');

            // Log input untuk debugging
            Log::debug('Export Request', [
                'user_ids' => $request->user_ids,
                'PatternID' => $request->PatternID,
                'month' => $month,
                'year' => $year,
            ]);

            $attendances = AttendModel::whereIn('EmployeeID', $request->user_ids)
                ->where('PatternID', $request->PatternID)
                ->whereMonth('AttDate', $month)
                ->whereYear('AttDate', $year)
                ->count();

            if ($attendances == 0) {
                Log::warning('No attendance data found for export', [
                    'user_ids' => $request->user_ids,
                    'PatternID' => $request->PatternID,
                    'month' => $month,
                    'year' => $year,
                ]);
                return redirect()->back()->with('error', 'No attendance data found for the selected criteria.');
            }

            return Excel::download(
                new ExportAttendance($request->user_ids, $month, $year, $request->PatternID),
                "Attendance_{$monthName}_{$year}.xlsx"
            );
        } catch (\Exception $e) {
            Log::error('Error exporting attendance: ' . $e->getMessage(), [
                'user_ids' => $request->user_ids,
                'PatternID' => $request->PatternID,
                'month' => $month,
                'year' => $year,
            ]);
            return redirect()->back()->with('error', 'Failed to export attendance data. Please try again.');
        }
    }

    public function getShift($id)
    {
        try {
            $pattern = ShiftPattern::get(['id', 'ID']);
            return $pattern;
        } catch (\Exception $e) {
            Log::error('Error fetching shift pattern id: ' . $e->getMessage());
            return response()->back()->with('error', 'An error occurred while fetching shift pattern id.');
        }
    }

    public function updateShiftCode(Request $request)
    {
        $request->validate([
            'shift_codes' => ['required', 'array'],
            'shift_codes.*.*' => ['nullable', 'integer', function ($attribute, $value, $fail) {
                if ($value !== null && !Shift::where('id', $value)->exists()) {
                    $fail("Data ID {$value} tidak ada, periksa kembali atau buat data baru Shift");
                }
            }],
            'attendance_ids' => ['required', 'array'],
            'attendance_ids.*.*' => ['required', 'integer'],
        ]);

        try {
            DB::beginTransaction();

            // Log request data untuk debugging
            Log::debug('ShiftCode Update Request', [
                'shift_codes' => $request->shift_codes,
                'attendance_ids' => $request->attendance_ids,
            ]);

            // Check if shift table is empty
            if (!Shift::exists()) {
                DB::rollback();
                return redirect()->back()->with('sweet_alert', [
                    'type' => 'error',
                    'title' => 'Error',
                    'text' => 'Tidak Ada Data ID untuk Di update, Silahkan Tambah Shift'
                ]);
            }

            // Check if there are any non-null shift_codes
            $hasValidShiftCode = false;
            foreach ($request->shift_codes as $userId => $dates) {
                if (!is_array($dates)) {
                    continue;
                }
                foreach ($dates as $shiftCode) {
                    if ($shiftCode !== null) {
                        $hasValidShiftCode = true;
                        break 2; // Exit both loops
                    }
                }
            }

            // If no valid shift_codes are provided, return error
            if (!$hasValidShiftCode) {
                DB::rollback();
                return redirect()->back()->with('sweet_alert', [
                    'type' => 'error',
                    'title' => 'Error',
                    'text' => 'Tidak Ada Data ID untuk Di update, Silahkan Tambah Shift'
                ]);
            }

            $updatedCount = 0;

            foreach ($request->shift_codes as $userId => $dates) {
                if (!is_array($dates)) {
                    Log::warning("Invalid dates array for userId: {$userId}");
                    continue;
                }

                foreach ($dates as $date => $shiftCode) {
                    if (!isset($request->attendance_ids[$userId][$date])) {
                        Log::warning("Missing attendance_id for userId: {$userId}, date: {$date}");
                        continue;
                    }

                    $attendanceId = $request->attendance_ids[$userId][$date];

                    // Tentukan DayType berdasarkan ShiftCode
                    $dayType = 1; // Default DayType
                    if ($shiftCode) {
                        $shift = Shift::find($shiftCode);
                        if ($shift) {
                            $dayType = $shift->Tipe;
                            Log::debug("Found shift for ShiftCode (id): {$shiftCode}, setting DayType to {$dayType}");
                        } else {
                            Log::warning("No shift found for ShiftCode (id): {$shiftCode}, using default DayType: {$dayType}");
                        }
                    }

                    if ($attendanceId > 0) {
                        $attendance = AttendModel::find($attendanceId);
                        if ($attendance) {
                            // Hanya update jika ShiftCode atau DayType berubah
                            if ($attendance->ShiftCode != $shiftCode || $attendance->DayType != $dayType) {
                                $attendance->update([
                                    'ShiftCode' => $shiftCode,
                                    'DayType' => $dayType,
                                ]);
                                $updatedCount++;
                                Log::info("Updated attendance for userId: {$userId}, date: {$date}, ShiftCode: {$shiftCode}, DayType: {$dayType}");
                            }
                        } else {
                            Log::warning("Attendance record not found for id: {$attendanceId}");
                        }
                    } else {
                        if ($shiftCode) {
                            AttendModel::create([
                                'EmployeeID' => $userId,
                                'AttDate' => $date,
                                'ShiftCode' => $shiftCode,
                                'DayType' => $dayType,
                                'PatternID' => $request->PatternID ?? null,
                                'DutyProcessID' => 0,
                                'Present' => 0,
                            ]);
                            $updatedCount++;
                            Log::info("Created attendance for userId: {$userId}, date: {$date}, ShiftCode: {$shiftCode}, DayType: {$dayType}");
                        }
                    }
                }
            }

            DB::commit();

            if ($updatedCount === 0) {
                return redirect()->back()->with('sweet_alert', [
                    'type' => 'warning',
                    'title' => 'Warning',
                    'text' => 'No ShiftCode changes were made.'
                ]);
            }

            return redirect()->back()->with('sweet_alert', [
                'type' => 'success',
                'title' => 'Success',
                'text' => "Successfully updated $updatedCount ShiftCode(s)."
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating ShiftCodes: ' . $e->getMessage());
            return redirect()->back()->with('sweet_alert', [
                'type' => 'error',
                'title' => 'Error',
                'text' => 'Failed to update ShiftCodes. Please try again.'
            ]);
        }
    }

    public function getShiftDetails(Request $request, $shiftCode)
    {
        try {
            $userId = $request->query('user_id');
            $attDate = $request->query('date');

            // Validate query parameters
            if (!$userId || !$attDate) {
                return response()->json(['error' => 'Missing user_id or date'], 400);
            }

            // Cari data shift berdasarkan ShiftCode
            $shift = Shift::where('id', $shiftCode)->first();

            if (!$shift) {
                return response()->json(['error' => 'Shift not found'], 404);
            }

            // Cari attendance berdasarkan EmployeeID dan AttDate
            $attendance = AttendModel::where('EmployeeID', $userId)
                ->where('AttDate', $attDate)
                ->where('ShiftCode', $shiftCode)
                ->first();

            // Inisialisasi default Type dari shift
            $type = $shift->Tipe == 1 ? 'Working Days' : 'Off Days';

            // Periksa Remark terlebih dahulu
            if ($attendance && $attendance->Remark === 'Holiday') {
                $type = 'Holiday';
            }
            // Jika tidak ada Remark 'Holiday', periksa DutyProcessID
            elseif ($attendance && $attendance->DutyProcessID && $attendance->DutyProcessID != 0) {
                $leaveType = LeaveType::where('Id', $attendance->DutyProcessID)->first();
                if ($leaveType) {
                    $type = $leaveType->Name;
                }
            }

            return response()->json([
                'id' => $shift->id,
                'ShiftName' => $shift->ShiftName,
                'Begin_Time' => $shift->Begin_Time,
                'Out_time' => $shift->Out_time,
                'Type' => $type
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching shift details: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch shift details'], 500);
        }
    }
}

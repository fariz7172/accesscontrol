<?php

namespace App\Http\Controllers;

use App\Exports\ExportAttendance;
use App\Models\AttendModel;
use App\Models\departmentModel;
use App\Models\HolidayCal;
use App\Models\LeaveProcess;
use App\Models\LeaveType;
use App\Models\Shift;
use App\Models\ShiftPattern;
use App\Models\userProfileModel;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class AbsenController extends Controller
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

        return view('absen.index', compact('users', 'departments'));
    }


    public function tambahLaporan(Request $request)
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
            $year = Carbon::today()->year;
            $daysInMonth = Carbon::create($year, $month)->daysInMonth;

            // Log awal untuk memastikan input yang diterima
            Log::info('Memulai tambahLaporan', [
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

            // Muat pola shift dengan detail shift
            $pattern = ShiftPattern::with([
                'shiftDay1' => function ($query) {
                    $query->select('id', 'ShiftNo', 'Tipe', 'Start_In', 'Begin_Time', 'Range_In', 'Start_Break', 'Range_Break', 'Break_Time', 'Resume_Time', 'Start_Resume', 'Range_Resume', 'Out_time', 'Start_Out', 'Range_Out');
                },
                'shiftDay2' => function ($query) {
                    $query->select('id', 'ShiftNo', 'Tipe', 'Start_In', 'Begin_Time', 'Range_In', 'Start_Break', 'Range_Break', 'Break_Time', 'Resume_Time', 'Start_Resume', 'Range_Resume', 'Out_time', 'Start_Out', 'Range_Out');
                },
                'shiftDay3' => function ($query) {
                    $query->select('id', 'ShiftNo', 'Tipe', 'Start_In', 'Begin_Time', 'Range_In', 'Start_Break', 'Range_Break', 'Break_Time', 'Resume_Time', 'Start_Resume', 'Range_Resume', 'Out_time', 'Start_Out', 'Range_Out');
                },
                'shiftDay4' => function ($query) {
                    $query->select('id', 'ShiftNo', 'Tipe', 'Start_In', 'Begin_Time', 'Range_In', 'Start_Break', 'Range_Break', 'Break_Time', 'Resume_Time', 'Start_Resume', 'Range_Resume', 'Out_time', 'Start_Out', 'Range_Out');
                },
                'shiftDay5' => function ($query) {
                    $query->select('id', 'ShiftNo', 'Tipe', 'Start_In', 'Begin_Time', 'Range_In', 'Start_Break', 'Range_Break', 'Break_Time', 'Resume_Time', 'Start_Resume', 'Range_Resume', 'Out_time', 'Start_Out', 'Range_Out');
                },
                'shiftDay6' => function ($query) {
                    $query->select('id', 'ShiftNo', 'Tipe', 'Start_In', 'Begin_Time', 'Range_In', 'Start_Break', 'Range_Break', 'Break_Time', 'Resume_Time', 'Start_Resume', 'Range_Resume', 'Out_time', 'Start_Out', 'Range_Out');
                },
                'shiftDay7' => function ($query) {
                    $query->select('id', 'ShiftNo', 'Tipe', 'Start_In', 'Begin_Time', 'Range_In', 'Start_Break', 'Range_Break', 'Break_Time', 'Resume_Time', 'Start_Resume', 'Range_Resume', 'Out_time', 'Start_Out', 'Range_Out');
                },
            ])->findOrFail($request->PatternID);

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
                            break;
                        }
                    }

                    // Timpa DayType ke 2 jika tanggal adalah hari libur
                    if ($isHoliday) {
                        $dayType = 2;
                    }

                    // Proses setiap pengguna dalam urutan yang sudah diurutkan
                    foreach ($sortedUserIds as $user_id) {
                        $remark = null;
                        $dutyProcessId = null;
                        $present = 0;

                        // Periksa catatan cuti di tabel leaveprocess
                        $leaveRecord = LeaveProcess::where('EmplID', $user_id)
                            ->where('FromDate', '<=', $attDate)
                            ->where('ToDate', '>=', $attDate)
                            ->first();

                        // Atur Remark dan DutyProcessID
                        if ($isHoliday) {
                            $remark = 'Holiday';
                        } elseif ($leaveRecord) {
                            $remark = 'On Leave: ' . ($leaveRecord->Notes ?? 'N/A');
                            $dutyProcessId = $leaveRecord->leaveid;
                        }

                        // Siapkan data absensi
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
                            'Remark' => $remark,
                            'DutyProcessID' => $dutyProcessId,
                            'Present' => $present,
                        ];

                        // Hitung WorkTime untuk DayType = 1
                        if ($dayType == 1 && $shift && $shift->Begin_Time && $shift->Out_time && $shift->Break_Time && $shift->Resume_Time) {
                            try {
                                $beginTime = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Begin_Time)->startOfMinute();
                                $outTime = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Out_time)->startOfMinute();
                                $breakTime = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Break_Time)->startOfMinute();
                                $resumeTime = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Resume_Time)->startOfMinute();

                                $workTime = $outTime->diffInMinutes($beginTime) - $resumeTime->diffInMinutes($breakTime);
                                $attendanceData['WorkTime'] = $workTime >= 0 ? $workTime : 0;

                                // Log untuk debugging WorkTime
                                Log::info('Proses WorkTime untuk user', [
                                    'EmployeeID' => $user_id,
                                    'AttDate' => $attDate->format('Y-m-d'),
                                    'DayType' => $dayType,
                                    'WorkTime' => $attendanceData['WorkTime'],
                                    'Begin_Time' => $shift->Begin_Time,
                                    'Out_time' => $shift->Out_time,
                                    'Break_Time' => $shift->Break_Time,
                                    'Resume_Time' => $shift->Resume_Time,
                                ]);
                            } catch (\Exception $e) {
                                Log::error('Gagal menghitung WorkTime', [
                                    'EmployeeID' => $user_id,
                                    'AttDate' => $attDate->format('Y-m-d'),
                                    'error' => $e->getMessage(),
                                    'Begin_Time' => $shift->Begin_Time,
                                    'Out_time' => $shift->Out_time,
                                    'Break_Time' => $shift->Break_Time,
                                    'Resume_Time' => $shift->Resume_Time,
                                ]);
                                $attendanceData['WorkTime'] = 0;
                            }
                        } else {
                            $attendanceData['WorkTime'] = 0;
                            Log::info('WorkTime tidak dihitung karena DayType != 1 atau data shift tidak lengkap', [
                                'EmployeeID' => $user_id,
                                'AttDate' => $attDate->format('Y-m-d'),
                                'DayType' => $dayType,
                                'ShiftExists' => !empty($shift),
                                'Begin_Time' => $shift ? $shift->Begin_Time : null,
                                'Out_time' => $shift ? $shift->Out_time : null,
                                'Break_Time' => $shift ? $shift->Break_Time : null,
                                'Resume_Time' => $shift ? $shift->Resume_Time : null,
                            ]);
                        }

                        // Jika bukan hari libur, cuti, atau tanpa shift, proses Time_In, Time_Break, Time_Resume, Time_Out, TotalWorkHour, dan TotalOT
                        if (!$isHoliday && !$leaveRecord && $shift && $shift->Start_In && $shift->Begin_Time && $shift->Range_In) {
                            $startIn = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Start_In)->startOfMinute();
                            $beginTime = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Begin_Time)->startOfMinute();
                            $rangeIn = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Range_In)->startOfMinute();

                            // Ambil semua TM_EVENT untuk user_id dan AttDate
                            $logs = DB::table('tbl_userlog')
                                ->where('USER_ADDR', $user_id)
                                ->whereDate('TM_EVENT', $attDate)
                                ->select('TM_EVENT')
                                ->get()
                                ->map(function ($log) {
                                    return Carbon::parse($log->TM_EVENT)->startOfMinute();
                                });

                            if ($logs->isNotEmpty()) {
                                // Proses Time_In, Time_InShort, dan EarlyWork
                                $logsBetweenStartInAndBeginTime = $logs->filter(function ($log) use ($startIn, $beginTime) {
                                    return $log->gte($startIn) && $log->lte($beginTime);
                                });

                                $logsAfterBeginTime = $logs->filter(function ($log) use ($beginTime, $rangeIn) {
                                    return $log->gt($beginTime) && $log->lte($rangeIn);
                                });

                                if ($logsBetweenStartInAndBeginTime->isNotEmpty()) {
                                    $earliestLog = $logsBetweenStartInAndBeginTime->sortBy(function ($log) {
                                        return $log->timestamp;
                                    })->first();
                                    $attendanceData['Time_In'] = $earliestLog->format('Y-m-d H:i:s');
                                    $attendanceData['Time_InShort'] = 0;
                                    $attendanceData['EarlyWork'] = abs($earliestLog->diffInMinutes($beginTime));
                                    $attendanceData['Present'] = 1;
                                } elseif ($logsAfterBeginTime->isNotEmpty()) {
                                    $earliestLog = $logsAfterBeginTime->sortBy(function ($log) {
                                        return $log->timestamp;
                                    })->first();
                                    $attendanceData['Time_In'] = $earliestLog->format('Y-m-d H:i:s');
                                    $attendanceData['Time_InShort'] = abs($earliestLog->diffInMinutes($beginTime));
                                    $attendanceData['EarlyWork'] = null;
                                    $attendanceData['Present'] = 1;
                                } else {
                                    $attendanceData['Time_In'] = null;
                                    $attendanceData['Time_InShort'] = 0;
                                    $attendanceData['EarlyWork'] = null;
                                }

                                // Log untuk debugging Time_In
                                Log::info('Proses Time_In untuk user', [
                                    'EmployeeID' => $user_id,
                                    'AttDate' => $attDate->format('Y-m-d'),
                                    'Time_In' => $attendanceData['Time_In'],
                                    'Time_InShort' => $attendanceData['Time_InShort'],
                                    'EarlyWork' => $attendanceData['EarlyWork'],
                                    'LogsBetweenStartInAndBeginTime' => $logsBetweenStartInAndBeginTime->map(function ($log) {
                                        return $log->format('Y-m-d H:i:s');
                                    })->toArray(),
                                    'LogsAfterBeginTime' => $logsAfterBeginTime->map(function ($log) {
                                        return $log->format('Y-m-d H:i:s');
                                    })->toArray(),
                                ]);

                                // Proses Time_Break dan Time_BreakShort
                                if ($shift->Start_Break && $shift->Range_Break && $shift->Break_Time && $shift->Resume_Time) {
                                    $startBreak = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Start_Break)->startOfMinute();
                                    $rangeBreak = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Range_Break)->startOfMinute();
                                    $breakTime = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Break_Time)->startOfMinute();
                                    $resumeTime = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Resume_Time)->startOfMinute();

                                    // Hindari penggunaan TM_EVENT yang sama dengan Time_In
                                    $timeInCarbon = $attendanceData['Time_In'] ? Carbon::parse($attendanceData['Time_In'])->startOfMinute() : null;
                                    $filteredLogsForBreak = $logs->filter(function ($log) use ($timeInCarbon) {
                                        return !$timeInCarbon || $log->notEqualTo($timeInCarbon);
                                    });

                                    // Time_Break: Ambil TM_EVENT terkecil (MIN) antara Start_Break dan Range_Break
                                    $logsBetweenStartBreakAndRangeBreak = $filteredLogsForBreak->filter(function ($log) use ($startBreak, $rangeBreak) {
                                        return $log->gte($startBreak) && $log->lte($rangeBreak);
                                    });

                                    if ($logsBetweenStartBreakAndRangeBreak->isNotEmpty()) {
                                        $earliestLog = $logsBetweenStartBreakAndRangeBreak->sortBy(function ($log) {
                                            return $log->timestamp;
                                        })->first();
                                        $attendanceData['Time_Break'] = $earliestLog->format('Y-m-d H:i:s');
                                        $attendanceData['Present'] = 1;

                                        // Cek apakah Time_Break berada di antara Break_Time dan Resume_Time
                                        $timeBreakCarbon = Carbon::parse($attendanceData['Time_Break'])->startOfMinute();
                                        if ($timeBreakCarbon->gte($breakTime) && $timeBreakCarbon->lte($resumeTime)) {
                                            $attendanceData['Time_BreakShort'] = 0;
                                        } else {
                                            $attendanceData['Time_BreakShort'] = abs($timeBreakCarbon->diffInMinutes($breakTime));
                                        }
                                    } else {
                                        $attendanceData['Time_Break'] = null;
                                        $attendanceData['Time_BreakShort'] = 0;
                                    }

                                    // Log untuk debugging Time_Break
                                    Log::info('Proses Time_Break untuk user', [
                                        'EmployeeID' => $user_id,
                                        'AttDate' => $attDate->format('Y-m-d'),
                                        'Time_Break' => $attendanceData['Time_Break'],
                                        'Time_BreakShort' => $attendanceData['Time_BreakShort'],
                                        'FilteredLogsForBreak' => $filteredLogsForBreak->map(function ($log) {
                                            return $log->format('Y-m-d H:i:s');
                                        })->toArray(),
                                        'LogsBetweenStartBreakAndRangeBreak' => $logsBetweenStartBreakAndRangeBreak->map(function ($log) {
                                            return $log->format('Y-m-d H:i:s');
                                        })->toArray(),
                                        'BreakTime' => $breakTime->format('Y-m-d H:i:s'),
                                        'ResumeTime' => $resumeTime->format('Y-m-d H:i:s'),
                                    ]);
                                }

                                // Proses Time_Resume dan Time_ResumeShort
                                if ($shift->Start_Resume && $shift->Range_Resume && $shift->Break_Time && $shift->Resume_Time) {
                                    $startResume = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Start_Resume)->startOfMinute();
                                    $rangeResume = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Range_Resume)->startOfMinute();
                                    $breakTime = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Break_Time)->startOfMinute();
                                    $resumeTime = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Resume_Time)->startOfMinute();

                                    // Hindari penggunaan TM_EVENT yang sama dengan Time_In dan Time_Break
                                    $timeInCarbon = $attendanceData['Time_In'] ? Carbon::parse($attendanceData['Time_In'])->startOfMinute() : null;
                                    $timeBreakCarbon = $attendanceData['Time_Break'] ? Carbon::parse($attendanceData['Time_Break'])->startOfMinute() : null;
                                    $filteredLogsForResume = $logs->filter(function ($log) use ($timeInCarbon, $timeBreakCarbon) {
                                        return (!$timeInCarbon || $log->notEqualTo($timeInCarbon)) && (!$timeBreakCarbon || $log->notEqualTo($timeBreakCarbon));
                                    });

                                    // Time_Resume: Ambil TM_EVENT terbesar (MAX) antara Break_Time dan Resume_Time
                                    $logsBetweenBreakAndResume = $filteredLogsForResume->filter(function ($log) use ($breakTime, $resumeTime) {
                                        return $log->gte($breakTime) && $log->lte($resumeTime);
                                    });

                                    if ($logsBetweenBreakAndResume->isNotEmpty()) {
                                        $latestLog = $logsBetweenBreakAndResume->sortByDesc(function ($log) {
                                            return $log->timestamp;
                                        })->first();
                                        $attendanceData['Time_Resume'] = $latestLog->format('Y-m-d H:i:s');
                                        $attendanceData['Present'] = 1;

                                        // Cek apakah Time_Resume berada di antara Break_Time dan Resume_Time
                                        $timeResumeCarbon = Carbon::parse($attendanceData['Time_Resume'])->startOfMinute();
                                        $attendanceData['Time_ResumeShort'] = $timeResumeCarbon->gte($breakTime) && $timeResumeCarbon->lte($resumeTime) ? 0 : abs($timeResumeCarbon->diffInMinutes($resumeTime));
                                    } else {
                                        // Fallback: Check TM_EVENT antara Start_Resume dan Range_Resume, ambil yang terkecil
                                        $logsBetweenStartResumeAndRangeResume = $filteredLogsForResume->filter(function ($log) use ($startResume, $rangeResume) {
                                            return $log->gte($startResume) && $log->lte($rangeResume);
                                        });

                                        if ($logsBetweenStartResumeAndRangeResume->isNotEmpty()) {
                                            $earliestLog = $logsBetweenStartResumeAndRangeResume->sortBy(function ($log) {
                                                return $log->timestamp;
                                            })->first();
                                            $attendanceData['Time_Resume'] = $earliestLog->format('Y-m-d H:i:s');
                                            $attendanceData['Present'] = 1;

                                            // Cek apakah Time_Resume berada di antara Break_Time dan Resume_Time
                                            $timeResumeCarbon = Carbon::parse($attendanceData['Time_Resume'])->startOfMinute();
                                            $attendanceData['Time_ResumeShort'] = $timeResumeCarbon->gte($breakTime) && $timeResumeCarbon->lte($resumeTime) ? 0 : abs($timeResumeCarbon->diffInMinutes($resumeTime));
                                        } else {
                                            $attendanceData['Time_Resume'] = null;
                                            $attendanceData['Time_ResumeShort'] = 0;
                                        }
                                    }

                                    // Kondisi: Jika Time_Break sama dengan Time_Resume
                                    if ($attendanceData['Time_Break'] && $attendanceData['Time_Resume'] && $attendanceData['Time_Break'] === $attendanceData['Time_Resume']) {
                                        $attendanceData['Time_Resume'] = null;
                                        $attendanceData['Time_ResumeShort'] = 0;
                                    }

                                    // Log untuk debugging Time_Resume
                                    Log::info('Proses Time_Resume untuk user', [
                                        'EmployeeID' => $user_id,
                                        'AttDate' => $attDate->format('Y-m-d'),
                                        'Time_Resume' => $attendanceData['Time_Resume'],
                                        'Time_ResumeShort' => $attendanceData['Time_ResumeShort'],
                                        'FilteredLogsForResume' => $filteredLogsForResume->map(function ($log) {
                                            return $log->format('Y-m-d H:i:s');
                                        })->toArray(),
                                        'BreakTime' => $breakTime->format('Y-m-d H:i:s'),
                                        'ResumeTime' => $resumeTime->format('Y-m-d H:i:s'),
                                    ]);
                                }

                                // Proses Time_Out dan Time_OutShort
                                if ($shift->Out_time && $shift->Start_Out && $shift->Range_Out) {
                                    $outTime = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Out_time)->startOfMinute();
                                    $startOut = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Start_Out)->startOfMinute();
                                    $rangeOut = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Range_Out)->startOfMinute();

                                    // Hindari penggunaan TM_EVENT yang sama dengan Time_In, Time_Break, dan Time_Resume
                                    $timeInCarbon = $attendanceData['Time_In'] ? Carbon::parse($attendanceData['Time_In'])->startOfMinute() : null;
                                    $timeBreakCarbon = $attendanceData['Time_Break'] ? Carbon::parse($attendanceData['Time_Break'])->startOfMinute() : null;
                                    $timeResumeCarbon = $attendanceData['Time_Resume'] ? Carbon::parse($attendanceData['Time_Resume'])->startOfMinute() : null;

                                    $filteredLogsForOut = $logs->filter(function ($log) use ($timeInCarbon, $timeBreakCarbon, $timeResumeCarbon) {
                                        return (!$timeInCarbon || $log->notEqualTo($timeInCarbon)) &&
                                            (!$timeBreakCarbon || $log->notEqualTo($timeBreakCarbon)) &&
                                            (!$timeResumeCarbon || $log->notEqualTo($timeResumeCarbon));
                                    });

                                    // Kondisi 1: TM_EVENT antara Out_time dan Range_Out, ambil yang terbesar
                                    $logsBetweenOutTimeAndRangeOut = $filteredLogsForOut->filter(function ($log) use ($outTime, $rangeOut) {
                                        return $log->gte($outTime) && $log->lte($rangeOut);
                                    });

                                    if ($logsBetweenOutTimeAndRangeOut->isNotEmpty()) {
                                        $latestLog = $logsBetweenOutTimeAndRangeOut->sortByDesc(function ($log) {
                                            return $log->timestamp;
                                        })->first();
                                        $attendanceData['Time_Out'] = $latestLog->format('Y-m-d H:i:s');
                                        $attendanceData['Time_OutShort'] = 0;
                                        $attendanceData['Present'] = 1;
                                    } else {
                                        $logsBetweenStartOutAndOutTime = $filteredLogsForOut->filter(function ($log) use ($startOut, $outTime) {
                                            return $log->gte($startOut) && $log->lt($outTime);
                                        });

                                        if ($logsBetweenStartOutAndOutTime->isNotEmpty()) {
                                            $latestLog = $logsBetweenStartOutAndOutTime->sortByDesc(function ($log) {
                                                return $log->timestamp;
                                            })->first();
                                            $attendanceData['Time_Out'] = $latestLog->format('Y-m-d H:i:s');
                                            $attendanceData['Present'] = 1;

                                            $timeOutCarbon = Carbon::parse($attendanceData['Time_Out'])->startOfMinute();
                                            $attendanceData['Time_OutShort'] = abs($timeOutCarbon->diffInMinutes($outTime));
                                        } else {
                                            $attendanceData['Time_Out'] = null;
                                            $attendanceData['Time_OutShort'] = 0;
                                        }
                                    }

                                    // Log untuk debugging Time_Out
                                    Log::info('Proses Time_Out untuk user', [
                                        'EmployeeID' => $user_id,
                                        'AttDate' => $attDate->format('Y-m-d'),
                                        'Time_Out' => $attendanceData['Time_Out'],
                                        'Time_OutShort' => $attendanceData['Time_OutShort'],
                                        'FilteredLogs' => $filteredLogsForOut->map(function ($log) {
                                            return $log->format('Y-m-d H:i:s');
                                        })->toArray(),
                                    ]);
                                } else {
                                    $attendanceData['Time_Out'] = null;
                                    $attendanceData['Time_OutShort'] = 0;
                                }

                                // Hitung TotalWorkHour: (Time_Out - Time_In) - (Resume_Time - Break_Time dari shift)
                                if ($attendanceData['Time_In'] && $attendanceData['Time_Out'] && $shift->Break_Time && $shift->Resume_Time) {
                                    $timeInCarbon = Carbon::parse($attendanceData['Time_In'])->startOfMinute();
                                    $timeOutCarbon = Carbon::parse($attendanceData['Time_Out'])->startOfMinute();

                                    $breakTime = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Break_Time)->startOfMinute();
                                    $resumeTime = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Resume_Time)->startOfMinute();

                                    $totalWorkHour = $timeOutCarbon->diffInMinutes($timeInCarbon) - $resumeTime->diffInMinutes($breakTime);
                                    $attendanceData['TotalWorkHour'] = $totalWorkHour >= 0 ? $totalWorkHour : 0;
                                } else {
                                    $attendanceData['TotalWorkHour'] = 0;
                                }

                                // Hitung TotalOT: Selisih Time_Out (attend) dengan Out_time (shift)
                                if ($attendanceData['Time_Out'] && $shift->Out_time) {
                                    $timeOutCarbon = Carbon::parse($attendanceData['Time_Out'])->startOfMinute();
                                    $outTime = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Out_time)->startOfMinute();

                                    if ($timeOutCarbon->gt($outTime)) {
                                        $attendanceData['TotalOT'] = $timeOutCarbon->diffInMinutes($outTime);
                                    } else {
                                        $attendanceData['TotalOT'] = 0;
                                    }
                                } else {
                                    $attendanceData['TotalOT'] = 0;
                                }
                            } else {
                                $attendanceData['Time_In'] = null;
                                $attendanceData['Time_InShort'] = 0;
                                $attendanceData['EarlyWork'] = null;
                                $attendanceData['Time_Break'] = null;
                                $attendanceData['Time_BreakShort'] = 0;
                                $attendanceData['Time_Resume'] = null;
                                $attendanceData['Time_ResumeShort'] = 0;
                                $attendanceData['Time_Out'] = null;
                                $attendanceData['Time_OutShort'] = 0;
                                $attendanceData['TotalWorkHour'] = 0;
                                $attendanceData['TotalOT'] = 0;
                            }
                        }

                        // Buat atau perbarui catatan absensi
                        $result = AttendModel::updateOrCreate(
                            [
                                'EmployeeID' => $user_id,
                                'AttDate' => $attDate,
                            ],
                            $attendanceData
                        );

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
            $message = "Berhasil  memperbarui  catatan absensi untuk $monthName $year.";

            Log::info('Proses tambahLaporan selesai', [
                'createdCount' => $createdCount,
                'updatedCount' => $updatedCount,
                'message' => $message,
            ]);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return redirect()->route('absen.index', ['tab' => 'assign-shift'])
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

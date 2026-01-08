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
 $year = Carbon::today()->year;
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
         $attDate=Carbon::create($year, $month, $day);
         $dayOfWeek=$attDate->dayOfWeek; // 0 = Minggu, 1 = Senin, ..., 6 = Sabtu
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
             $remark = 'On Leave';
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
             'EarlyWork' => null,
             'Remark' => $remark,
             'DutyProcessID' => $dutyProcessId,
             'Present' => $present,
             ];

             // Jika bukan hari libur, cuti, atau tanpa shift, proses Time_In, Time_Break, Time_Resume, dan Time_Out
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
             return Carbon::parse($log->TM_EVENT); // Tidak menggunakan startOfMinute()
             });

             if ($logs->isNotEmpty()) {
             // Proses Time_In, Time_InShort, dan EarlyWork
             $allBeforeStartIn = $logs->every(function ($log) use ($startIn) {
             return $log->lt($startIn);
             });

             if ($allBeforeStartIn) {
             $attendanceData['Time_In'] = null;
             $attendanceData['Time_InShort'] = 0;
             $attendanceData['EarlyWork'] = null;
             } else {
             $logsBetweenStartInAndBeginTime = $logs->filter(function ($log) use ($startIn, $beginTime) {
             return $log->gte($startIn) && $log->lte($beginTime);
             });

             if ($logsBetweenStartInAndBeginTime->isNotEmpty()) {
             $latestLog = $logsBetweenStartInAndBeginTime->sortByDesc(function ($log) {
             return $log->timestamp;
             })->first();
             $attendanceData['Time_In'] = $latestLog->format('Y-m-d H:i:s');
             $attendanceData['Time_InShort'] = 0;
             $attendanceData['EarlyWork'] = abs($latestLog->diffInMinutes($beginTime));
             $attendanceData['Present'] = 1;
             } else {
             $logsAfterBeginTime = $logs->filter(function ($log) use ($beginTime, $rangeIn) {
             return $log->gt($beginTime) && $log->lte($rangeIn);
             });

             if ($logsAfterBeginTime->isNotEmpty()) {
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
             }
             }

             // Proses Time_Break dan Time_BreakShort
             if ($shift->Start_Break && $shift->Range_Break && $shift->Break_Time && $shift->Resume_Time) {
             $startBreak = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Start_Break)->startOfMinute();
             $rangeBreak = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Range_Break)->startOfMinute();
             $breakTime = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Break_Time)->startOfMinute();
             $resumeTime = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Resume_Time)->startOfMinute();

             // Hindari penggunaan TM_EVENT yang sama dengan Time_In
             $timeInCarbon = $attendanceData['Time_In'] ? Carbon::parse($attendanceData['Time_In']) : null;
             $filteredLogsForBreak = $logs->filter(function ($log) use ($timeInCarbon) {
             return !$timeInCarbon || $log->notEqualTo($timeInCarbon);
             });

             // Time_Break: Ambil TM_EVENT terbesar antara Break_Time dan Resume_Time
             $logsBetweenBreakAndResume = $filteredLogsForBreak->filter(function ($log) use ($breakTime, $resumeTime) {
             return $log->gte($breakTime) && $log->lte($resumeTime);
             });

             if ($logsBetweenBreakAndResume->isNotEmpty()) {
             $latestLog = $logsBetweenBreakAndResume->sortByDesc(function ($log) {
             return $log->timestamp;
             })->first();
             $attendanceData['Time_Break'] = $latestLog->format('Y-m-d H:i:s');
             $attendanceData['Present'] = 1;
             } else {
             // Fallback: Check TM_EVENT antara Start_Break dan Break_Time, ambil yang terbesar
             $logsBetweenStartBreakAndBreakTime = $filteredLogsForBreak->filter(function ($log) use ($startBreak, $breakTime) {
             return $log->gte($startBreak) && $log->lte($breakTime);
             });

             if ($logsBetweenStartBreakAndBreakTime->isNotEmpty()) {
             $latestLog = $logsBetweenStartBreakAndBreakTime->sortByDesc(function ($log) {
             return $log->timestamp;
             })->first();
             $attendanceData['Time_Break'] = $latestLog->format('Y-m-d H:i:s');
             $attendanceData['Present'] = 1;
             } else {
             $attendanceData['Time_Break'] = null;
             }
             }

             // Time_BreakShort: Jika ada TM_EVENT antara Break_Time dan Resume_Time, set ke 0
             if ($logsBetweenBreakAndResume->isNotEmpty()) {
             $attendanceData['Time_BreakShort'] = 0;
             } else {
             // Jika tidak ada TM_EVENT antara Break_Time dan Resume_Time, hitung Time_BreakShort
             $logsBetweenStartBreakAndRangeBreak = $filteredLogsForBreak->filter(function ($log) use ($startBreak, $rangeBreak) {
             return $log->gte($startBreak) && $log->lte($rangeBreak);
             });

             if ($logsBetweenStartBreakAndRangeBreak->isNotEmpty()) {
             $earliestBreakLog = $logsBetweenStartBreakAndRangeBreak->sortBy(function ($log) {
             return $log->timestamp;
             })->first();
             $attendanceData['Time_BreakShort'] = abs($earliestBreakLog->diffInMinutes($breakTime));

             $logsBeforeBreakTime = $logsBetweenStartBreakAndRangeBreak->filter(function ($log) use ($breakTime) {
             return $log->lt($breakTime);
             });

             if ($logsBeforeBreakTime->isNotEmpty()) {
             $latestLogBeforeBreak = $logsBeforeBreakTime->sortByDesc(function ($log) {
             return $log->timestamp;
             })->first();
             $attendanceData['Time_BreakShort'] = abs($latestLogBeforeBreak->diffInMinutes($breakTime));
             }
             } else {
             $attendanceData['Time_BreakShort'] = 0;
             }
             }
             }

             // Proses Time_Resume dan Time_ResumeShort
             if ($shift->Start_Resume && $shift->Range_Resume && $shift->Break_Time && $shift->Resume_Time) {
             $startResume = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Start_Resume)->startOfMinute();
             $rangeResume = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Range_Resume)->startOfMinute();
             $breakTime = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Break_Time)->startOfMinute();
             $resumeTime = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Resume_Time)->startOfMinute();

             // Hindari penggunaan TM_EVENT yang sama dengan Time_In dan Time_Break
             $timeInCarbon = $attendanceData['Time_In'] ? Carbon::parse($attendanceData['Time_In']) : null;
             $timeBreakCarbon = $attendanceData['Time_Break'] ? Carbon::parse($attendanceData['Time_Break']) : null;
             $filteredLogsForResume = $logs->filter(function ($log) use ($timeInCarbon, $timeBreakCarbon) {
             return (!$timeInCarbon || $log->notEqualTo($timeInCarbon)) && (!$timeBreakCarbon || $log->notEqualTo($timeBreakCarbon));
             });

             // Check if any TM_EVENT is greater than Resume_Time
             $logsAfterResumeTime = $filteredLogsForResume->filter(function ($log) use ($resumeTime) {
             return $log->gt($resumeTime);
             });

             if ($logsAfterResumeTime->isNotEmpty()) {
             // If TM_EVENT > Resume_Time exists, take TM_EVENT between Start_Resume and Range_Resume
             $logsBetweenStartResumeAndRangeResume = $filteredLogsForResume->filter(function ($log) use ($startResume, $rangeResume) {
             return $log->gte($startResume) && $log->lte($rangeResume);
             });

             if ($logsBetweenStartResumeAndRangeResume->isNotEmpty()) {
             $earliestLog = $logsBetweenStartResumeAndRangeResume->sortBy(function ($log) {
             return $log->timestamp;
             })->first();
             $attendanceData['Time_Resume'] = $earliestLog->format('Y-m-d H:i:s');
             $attendanceData['Present'] = 1;

             // Calculate Time_ResumeShort: Check if Time_Resume > Resume_Time
             $timeResumeCarbon = Carbon::parse($attendanceData['Time_Resume']);
             if ($timeResumeCarbon->gt($resumeTime)) {
             $attendanceData['Time_ResumeShort'] = abs($timeResumeCarbon->diffInMinutes($resumeTime));
             } else {
             $attendanceData['Time_ResumeShort'] = 0;
             }
             } else {
             $attendanceData['Time_Resume'] = null;
             $attendanceData['Time_ResumeShort'] = 0;
             }
             } else {
             // Existing logic: Take the earliest TM_EVENT between Break_Time and Resume_Time
             $logsBetweenBreakAndResume = $filteredLogsForResume->filter(function ($log) use ($breakTime, $resumeTime) {
             return $log->gte($breakTime) && $log->lte($resumeTime);
             });

             if ($logsBetweenBreakAndResume->isNotEmpty()) {
             $earliestLog = $logsBetweenBreakAndResume->sortBy(function ($log) {
             return $log->timestamp;
             })->first();
             $attendanceData['Time_Resume'] = $earliestLog->format('Y-m-d H:i:s');
             $attendanceData['Present'] = 1;

             // Calculate Time_ResumeShort: Check if Time_Resume > Resume_Time
             $timeResumeCarbon = Carbon::parse($attendanceData['Time_Resume']);
             if ($timeResumeCarbon->gt($resumeTime)) {
             $attendanceData['Time_ResumeShort'] = abs($timeResumeCarbon->diffInMinutes($resumeTime));
             } else {
             $attendanceData['Time_ResumeShort'] = 0;
             }
             } else {
             $attendanceData['Time_Resume'] = null;
             $attendanceData['Time_ResumeShort'] = 0;
             }
             }

             // Kondisi 5: Jika Time_Break sama dengan Time_Resume
             if ($attendanceData['Time_Break'] && $attendanceData['Time_Resume'] && $attendanceData['Time_Break'] === $attendanceData['Time_Resume']) {
             $attendanceData['Time_Resume'] = null;
             $attendanceData['Time_ResumeShort'] = 0;
             }
             }

             // Proses Time_Out dan Time_OutShort
             if ($shift->Out_time && $shift->Start_Out && $shift->Range_Out) {
             $outTime = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Out_time)->startOfMinute();
             $startOut = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Start_Out)->startOfMinute();
             $rangeOut = Carbon::createFromFormat('Y-m-d H:i:s', $attDate->format('Y-m-d') . ' ' . $shift->Range_Out)->startOfMinute();

             // Hindari penggunaan TM_EVENT yang sama dengan Time_In, Time_Break, dan Time_Resume
             $timeInCarbon = $attendanceData['Time_In'] ? Carbon::parse($attendanceData['Time_In']) : null;
             $timeBreakCarbon = $attendanceData['Time_Break'] ? Carbon::parse($attendanceData['Time_Break']) : null;
             $timeResumeCarbon = $attendanceData['Time_Resume'] ? Carbon::parse($attendanceData['Time_Resume']) : null;

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
             $attendanceData['Time_OutShort'] = 0; // Kondisi 3: TM_EVENT >= Out_time
             $attendanceData['Present'] = 1;
             } else {
             // Kondisi 2: TM_EVENT < Out_time, antara Start_Out dan Out_time, ambil yang terbesar
                 $logsBetweenStartOutAndOutTime=$filteredLogsForOut->filter(function ($log) use ($startOut, $outTime) {
                 return $log->gte($startOut) && $log->lt($outTime);
                 });

                 if ($logsBetweenStartOutAndOutTime->isNotEmpty()) {
                 $latestLog = $logsBetweenStartOutAndOutTime->sortByDesc(function ($log) {
                 return $log->timestamp;
                 })->first();
                 $attendanceData['Time_Out'] = $latestLog->format('Y-m-d H:i:s');
                 $attendanceData['Present'] = 1;

                 // Kondisi 4: Hitung Time_OutShort berdasarkan menit, abaikan detik
                 $timeOutCarbon = Carbon::parse($attendanceData['Time_Out'])->startOfMinute(); // Bulatkan ke menit
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
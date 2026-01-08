<?php

namespace App\Http\Controllers;

use App\Models\AttendSumary;
use App\Models\AttendModel;
use App\Models\userProfileModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendSumaryController extends Controller
{
    public function index(Request $request)
    {
        // Ambil semua pengguna dari tabel usersprofile
        $users = userProfileModel::select('ID', 'NAME')->with('department')->get();

        // Inisialisasi variabel
        $selectedUsers = $request->input('selected_users', []);
        $month = $request->input('month', Carbon::now()->month); // Default ke bulan saat ini
        $year = $request->input('year', Carbon::now()->year); // Default ke tahun saat ini
        $startPeriod = $request->input('StartPeriod', Carbon::create()->month($month)->startOfMonth()->format('Y-m-d'));
        $endPeriod = $request->input('EndPeriod', Carbon::create()->month($month)->endOfMonth()->format('Y-m-d'));
        $summaries = [];

        // Jika ada pengguna yang dipilih, ambil ringkasan kehadiran untuk bulan dan tahun yang dipilih
        if (!empty($selectedUsers)) {
            $summaries = AttendSumary::whereIn('EmployeeID', $selectedUsers)
                ->whereYear('StartPeriod', $year)
                ->whereMonth('StartPeriod', $month)
                ->get()
                ->groupBy(function ($summary) {
                    return Carbon::parse($summary->StartPeriod)->month;
                });
        }

        // Siapkan data bulanan untuk view
        $monthlyData = [];
        $monthlyData[$month] = isset($summaries[$month]) ? $summaries[$month] : collect([]);

        // Kirim variabel ke view
        return view('summary.index', compact('users', 'monthlyData', 'month', 'year', 'selectedUsers', 'startPeriod', 'endPeriod'));
    }

    public function generate(Request $request)
    {
        // Validasi input
        $request->validate([
            'StartPeriod' => 'required|date',
            'EndPeriod' => 'required|date|after_or_equal:StartPeriod',
            'selected_users' => 'required|array|min:1',
            'selected_users.*' => 'exists:usersprofile,ID',
            'month' => 'required|integer|between:1,12', // Validasi bulan
        ]);

        $startPeriod = Carbon::parse($request->input('StartPeriod'));
        $endPeriod = Carbon::parse($request->input('EndPeriod'));
        $selectedUsers = $request->input('selected_users', []);
        $month = $request->input('month');
        $year = Carbon::parse($startPeriod)->year; // Ambil tahun dari StartPeriod
        // Gunakan hari pertama dari bulan yang dipilih untuk kolom Period
        $period = Carbon::create($year, $month, 1)->startOfMonth()->format('Y-m-d'); // Format YYYY-MM-DD

        try {
            DB::beginTransaction();

            foreach ($selectedUsers as $userId) {
                // Ambil data kehadiran untuk pengguna dalam rentang tanggal
                $attendances = AttendModel::where('EmployeeID', $userId)
                    ->whereBetween('AttDate', [$startPeriod, $endPeriod])
                    ->get();

                // Inisialisasi data ringkasan
                $summaryData = [
                    'Period' => $period, // Gunakan format tanggal YYYY-MM-DD
                    'StartPeriod' => $startPeriod->format('Y-m-d'),
                    'EndPeriod' => $endPeriod->format('Y-m-d'),
                    'EmployeeID' => $userId,
                    'WorkingDays' => 0,
                    'Present' => 0,
                    'Absent' => 0,
                    'LateIn' => 0,
                    'EarlyOut' => 0,
                    'LateInMinute' => 0,
                    'EarlyOutMinute' => 0,
                    'TotalWorktime' => 0,
                    'TotalWorkHour' => 0,
                    'OT' => 0,
                    'OTMinute' => 0,
                    'EarlyWork' => 0,
                    'EarlyWorkMinute' => 0,
                    'Ncheckin' => 0,
                    'Ncheckout' => 0,
                    'LeaveTaken' => 0,
                    'D1' => 0,
                    'D2' => 0,
                    'D3' => 0,
                    'D4' => 0,
                    'D5' => 0,
                    'D6' => 0,
                    'D7' => 0,
                    'D8' => 0,
                    'D9' => 0,
                    'D10' => 0,
                    'D11' => 0,
                    'D12' => 0,
                    'D13' => 0,
                    'D14' => 0,
                    'D15' => 0,
                    'D16' => 0,
                    'D17' => 0,
                    'D18' => 0,
                    'D19' => 0,
                    'D20' => 0,
                ];

                foreach ($attendances as $attendance) {
                    // Hitung hari kerja (DayType = 1)
                    if ($attendance->DayType == 1) {
                        $summaryData['WorkingDays']++;
                    }

                    // Hitung kehadiran (Present = 1)
                    if ($attendance->Present == 1) {
                        $summaryData['Present']++;
                    }

                    // Hitung absen (Present = 0, kecuali DayType = 2)
                    if ($attendance->Present == 0 && $attendance->DayType != 2) {
                        $summaryData['Absent']++;
                    }

                    // Hitung terlambat masuk
                    if (!is_null($attendance->Time_InShort)) {
                        $summaryData['LateIn']++;
                        $summaryData['LateInMinute'] += $attendance->Time_InShort;
                    }

                    // Hitung pulang awal
                    if (!is_null($attendance->Time_OutShort)) {
                        $summaryData['EarlyOut']++;
                        $summaryData['EarlyOutMinute'] += $attendance->Time_OutShort;
                    }

                    // Jumlahkan total waktu kerja
                    if (!is_null($attendance->WorkTime)) {
                        $summaryData['TotalWorktime'] += $attendance->WorkTime;
                    }

                    // Jumlahkan total jam kerja
                    if (!is_null($attendance->TotalWorkHour)) {
                        $summaryData['TotalWorkHour'] += $attendance->TotalWorkHour;
                    }

                    // Hitung lembur
                    if (!is_null($attendance->TotalOT)) {
                        $summaryData['OT']++;
                        $summaryData['OTMinute'] += $attendance->TotalOT;
                    }

                    // Hitung kerja awal
                    if (!is_null($attendance->EarlyWork)) {
                        $summaryData['EarlyWork']++;
                        $summaryData['EarlyWorkMinute'] += $attendance->EarlyWork;
                    }

                    // Hitung tidak check-in
                    if ($attendance->Present == 1 && is_null($attendance->Time_In)) {
                        $summaryData['Ncheckin']++;
                    }

                    // Hitung tidak check-out
                    if ($attendance->Present == 1 && is_null($attendance->Time_Out)) {
                        $summaryData['Ncheckout']++;
                    }

                    // Hitung cuti yang diambil
                    if (!is_null($attendance->DutyProcessID)) {
                        $summaryData['LeaveTaken']++;
                        $dutyId = $attendance->DutyProcessID;
                        if ($dutyId >= 1 && $dutyId <= 20) {
                            $summaryData['D' . $dutyId]++;
                        }
                    }
                }

                // Cek apakah ringkasan sudah ada untuk pengguna dan periode ini
                $existingSummary = AttendSumary::where('EmployeeID', $userId)
                    ->where('Period', $period)
                    ->first();

                if ($existingSummary) {
                    // Perbarui record yang ada
                    $existingSummary->update($summaryData);
                } else {
                    // Buat record baru
                    AttendSumary::create($summaryData);
                }
            }

            DB::commit();
            return redirect()->route('attendantSheet.index')
                ->with('success', 'Ringkasan kehadiran berhasil dibuat.')
                ->withInput($request->all());
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('attendantSheet.index')
                ->with('error', 'Gagal membuat ringkasan kehadiran: ' . $e->getMessage())
                ->withInput($request->all());
        }
    }
}

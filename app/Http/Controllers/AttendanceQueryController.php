<?php

namespace App\Http\Controllers;

use App\Models\AttendModel;
use App\Models\userProfileModel;
use App\Models\AttendSumary;
use App\Models\LeaveType;
use App\Models\LeaveProcess;
use App\Models\HolidayCal;
use App\Models\userLogModel;
use App\Services\GrokService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceQueryController extends Controller
{
    protected $grokService;

    public function __construct(GrokService $grokService)
    {
        $this->grokService = $grokService;
    }

    public function index()
    {
        return view('attendance.query');
    }

    public function query(Request $request)
    {
        $prompt = $request->input('query');
        $context = $this->buildContext();

        // Prompt untuk Grok: Berikan saran query Eloquent atau penjelasan
        $fullPrompt = $context . "\n\nAnalisis pertanyaan pengguna berikut dan berikan saran query Eloquent untuk Laravel atau penjelasan umum jika tidak relevan. Jika pertanyaan meminta jumlah data, sarankan query seperti Model::count(). Gunakan istilah 'user', 'karyawan', atau 'member' untuk merujuk ke EmployeeID, EmplID, atau NAME yang terkait dengan usersprofile.ID.\nPertanyaan: " . $prompt;

        // Kirim ke Grok
        $grokResponse = $this->grokService->query($fullPrompt);

        if (isset($grokResponse['error'])) {
            Log::warning('Grok API error, falling back to manual query', ['error' => $grokResponse['error']]);
            // Fallback: Jalankan query manual tanpa Grok
            $dbResult = $this->processPrompt($prompt, '');
            return view('attendance.query', [
                'result' => ['grok_analysis' => 'Grok API error: ' . $grokResponse['error'] . '. Menggunakan pencarian manual.', 'db_data' => $dbResult],
                'query' => $prompt
            ]);
        }

        // Jalankan query database berdasarkan prompt
        $dbResult = $this->processPrompt($prompt, $grokResponse['response']);

        // Log hasil untuk debugging
        Log::info('Prompt: ' . $prompt, [
            'dbResult' => $dbResult,
            'leaveProcessCount' => LeaveProcess::where('STATUS', 1)->count(),
            'uniqueEmplIDs' => LeaveProcess::where('STATUS', 1)->distinct()->pluck('EmplID')->toArray()
        ]);

        // Gabungkan hasil
        $result = [
            'grok_analysis' => $grokResponse['response'],
            'db_data' => $dbResult
        ];

        return view('attendance.query', ['result' => $result, 'query' => $prompt]);
    }

    private function buildContext()
    {
        return <<<EOT
Berikut adalah struktur database untuk aplikasi absensi:
Catatan penting: Istilah 'user', 'karyawan', atau 'member' merujuk ke entitas yang sama, diidentifikasi oleh kolom berikut:
- Tabel `usersprofile`: Kolom `ID` (primary key) dan `NAME` (nama user/karyawan).
- Tabel `attend`: Kolom `EmployeeID` (merujuk ke usersprofile.ID).
- Tabel `attendsummary`: Kolom `EmployeeID` (merujuk ke usersprofile.ID).
- Tabel `leaveprocess`: Kolom `EmplID` (merujuk ke usersprofile.ID).
Semua kolom ini terkait melalui usersprofile.ID.

1. Tabel `attend` (AttendModel):
   - Kolom: EmployeeID, AttDate, PatternID, ShiftCode, DayType, Time_In, Time_Break, Time_Resume, Time_Out, dll.
   - Relasi: belongsTo userProfileModel (EmployeeID ke usersprofile.ID), Shift (ShiftCode), LeaveType (DutyProcessID).

2. Tabel `usersprofile` (userProfileModel):
   - Kolom: ID, NAME, BEGIN_DATE, END_DATE, Depid, Branchid, dll.
   - Relasi: belongsTo ShiftPattern, department, branch; hasMany LeaveProcess (EmplID), AttendSumary (EmployeeID).

3. Tabel `attendsummary` (AttendSumary):
   - Kolom: Period, StartPeriod, EndPeriod, EmployeeID, WorkingDays, Present, Absent, dll.
   - Relasi: belongsTo userProfileModel (EmployeeID ke usersprofile.ID).

4. Tabel `leavetype` (LeaveType):
   - Kolom: Name, Type.
   - Relasi: hasMany LeaveProcess (leaveid).

5. Tabel `leaveprocess` (LeaveProcess):
   - Kolom: leaveid, EmplID, FromDate, ToDate, Notes, STATUS, approver_id, approved_at, created_at, updated_at, priv.
   - Relasi: belongsTo userProfileModel (EmplID ke usersprofile.ID), LeaveType (leaveid), User (approver_id).

6. Tabel `holidaycal` (HolidayCal):
   - Kolom: Name, StartDate, EndDate.

7. Tabel `tbl_userlog` (userLogModel):
   - Kolom: USER_ADDR, TM_EVENT, DEVICESN, IMGPATH, dll.
   - Relasi: hasOne userProfileModel (USER_ADDR ke usersprofile.ID), belongsTo deviceGateModel.

Contoh prompt:
- 'Cari absensi user John Doe di bulan September 2025' → Query: AttendModel::whereHas('userProfile', function(\$query) { \$query->where('NAME', 'like', '%John Doe%'); })->whereMonth('AttDate', 9)->whereYear('AttDate', 2025)->get();
- 'Daftar cuti karyawan di tahun 2025' → Query: LeaveProcess::whereYear('FromDate', 2025)->with('userProfile', 'leaveType')->get();
- 'Jumlah data user di tabel usersprofile' → Query: userProfileModel::count();
- 'Ringkasan absensi user John Doe' → Query: AttendSumary::whereHas('userProfile', function(\$query) { \$query->where('NAME', 'like', '%John Doe%'); })->get();
- 'Ada berapa user yang cuti' → Query: LeaveProcess::where('STATUS', 1)->distinct()->count('EmplID');
- 'Siapa user yang cuti' → Query: LeaveProcess::where('STATUS', 1)->select('EmplID')->distinct()->with('userProfile')->get()->pluck('userProfile.NAME');
- 'Siapa saja karyawan yang cuti' → Query: LeaveProcess::where('STATUS', 1)->select('EmplID')->distinct()->with('userProfile')->get()->pluck('userProfile.NAME');
- 'Siapa yang cuti dan berapa lama' → Query: LeaveProcess::where('STATUS', 1)->join('usersprofile', 'leaveprocess.EmplID', '=', 'usersprofile.ID')->join('leavetype', 'leaveprocess.leaveid', '=', 'leavetype.Id')->select('usersprofile.NAME as name', 'leaveprocess.FromDate as from_date', 'leaveprocess.ToDate as to_date', 'leavetype.Name as leave_type', DB::raw('DATEDIFF(leaveprocess.ToDate, leaveprocess.FromDate) + 1 as duration'))->get();
EOT;
    }

    private function processPrompt($prompt, $grokResponse)
    {
        // Logika untuk parsing prompt dan respons Grok
        // Deteksi prompt yang merujuk ke "user" atau "karyawan" dengan nama spesifik
        if ((stripos($prompt, 'user') !== false || stripos($prompt, 'karyawan') !== false) && preg_match('/(user|karyawan)\s+(\w+)/i', $prompt, $matches)) {
            $userName = $matches[2];

            // Cari absensi user
            if (stripos($prompt, 'absensi') !== false) {
                $result = AttendModel::whereHas('userProfile', function ($query) use ($userName) {
                    $query->where('NAME', 'like', "%$userName%");
                })->with('userProfile', 'shift', 'leaveType')->get();
                return $result->isEmpty() ? ['message' => "Tidak ada data absensi untuk karyawan dengan nama seperti '$userName'."] : $result;
            }

            // Cari ringkasan absensi user
            elseif (stripos($prompt, 'ringkasan absensi') !== false) {
                $result = AttendSumary::whereHas('userProfile', function ($query) use ($userName) {
                    $query->where('NAME', 'like', "%$userName%");
                })->with('userProfile')->get();
                return $result->isEmpty() ? ['message' => "Tidak ada ringkasan absensi untuk karyawan dengan nama seperti '$userName'."] : $result;
            }

            // Cari cuti user
            elseif (stripos($prompt, 'cuti') !== false) {
                $result = LeaveProcess::whereHas('userProfile', function ($query) use ($userName) {
                    $query->where('NAME', 'like', "%$userName%");
                })->with('userProfile', 'leaveType')->get();
                return $result->isEmpty() ? ['message' => "Tidak ada data cuti untuk karyawan dengan nama seperti '$userName'."] : $result;
            }
        }

        // Jumlah user/karyawan yang cuti
        elseif (stripos($prompt, 'berapa user yang cuti') !== false || stripos($prompt, 'berapa karyawan yang cuti') !== false || stripos($prompt, 'jumlah user cuti') !== false || stripos($prompt, 'jumlah karyawan cuti') !== false || stripos($prompt, 'berapa member yang cuti') !== false) {
            $count = LeaveProcess::where('STATUS', 1)->distinct()->count('EmplID');
            return ['message' => "Jumlah karyawan yang cuti (disetujui): $count"];
        }

        // Siapa user yang cuti (termasuk "siapa saja karyawan yang cuti")
        elseif (stripos($prompt, 'siapa user yang cuti') !== false || stripos($prompt, 'siapa karyawan yang cuti') !== false || stripos($prompt, 'siapa yang cuti') !== false || stripos($prompt, 'siapa saja karyawan yang cuti') !== false) {
            $users = LeaveProcess::where('STATUS', 1)
                ->select('EmplID')
                ->distinct()
                ->with(['userProfile' => function ($query) {
                    $query->select('ID', 'NAME'); // Hanya ambil kolom yang diperlukan
                }])
                ->get()
                ->pluck('userProfile.NAME')
                ->filter()
                ->values();
            return ['message' => !empty($users) ? "Karyawan yang cuti: " . implode(', ', $users->toArray()) : "Tidak ada karyawan yang sedang cuti."];
        }

        // Siapa yang cuti dan berapa lama
        elseif (stripos($prompt, 'siapa yang cuti dan berapa lama') !== false || stripos($prompt, 'user yang cuti dan durasi') !== false) {
            $leaves = LeaveProcess::where('STATUS', 1)
                ->join('usersprofile', 'leaveprocess.EmplID', '=', 'usersprofile.ID')
                ->join('leavetype', 'leaveprocess.leaveid', '=', 'leavetype.Id')
                ->select(
                    'usersprofile.NAME as name',
                    'leaveprocess.FromDate as from_date',
                    'leaveprocess.ToDate as to_date',
                    'leavetype.Name as leave_type',
                    DB::raw('DATEDIFF(leaveprocess.ToDate, leaveprocess.FromDate) + 1 as duration')
                )
                ->get();
            return $leaves->isEmpty() ? ['message' => 'Tidak ada data cuti yang disetujui.'] : $leaves;
        }

        // Jumlah data di tabel usersprofile
        elseif (stripos($prompt, 'jumlah data') !== false && stripos($prompt, 'usersprofile') !== false) {
            $count = userProfileModel::count();
            return ['message' => "Jumlah data user di tabel usersprofile: $count"];
        }

        // Hari libur
        elseif (stripos($prompt, 'libur') !== false) {
            $result = HolidayCal::all();
            return $result->isEmpty() ? ['message' => 'Tidak ada data hari libur.'] : $result;
        }

        return ['message' => 'Pertanyaan tidak dikenali. Silakan coba lagi dengan format yang lebih jelas, misalnya "absensi user John", "siapa saja karyawan yang cuti", atau "siapa yang cuti dan berapa lama".'];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\AttendModel;
use App\Models\LeaveProcess;
use App\Models\LeaveType;
use App\Models\userProfileModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeaveProcessController extends Controller
{
    public function index()
    {
        $leaveProcesses = LeaveProcess::with(['userProfile', 'leaveType'])->paginate(10);
        $users = userProfileModel::all();
        $leaveTypes = LeaveType::all();

        return view('leaveprocess.index', compact('leaveProcesses', 'users', 'leaveTypes'));
    }

    public function create()
    {
        $users = userProfileModel::all();
        $leaveTypes = LeaveType::all();
        return view('leaveprocess.create', compact('users', 'leaveTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'leaveid' => 'required|exists:leavetype,Id',
            'EmplID' => 'required|exists:usersprofile,ID',
            'FromDate' => 'required|date',
            'ToDate' => 'required|date|after_or_equal:FromDate',
            'Notes' => 'nullable|string|max:100',
        ]);

        try {
            DB::beginTransaction(); // Mulai transaksi

            // Hitung rentang tanggal untuk cuti
            $fromDate = new \DateTime($request->FromDate);
            $toDate = new \DateTime($request->ToDate);

            // Buat catatan proses cuti
            $leaveProcess = LeaveProcess::create($request->only(['leaveid', 'EmplID', 'FromDate', 'ToDate', 'Notes']));

            // Hitung rentang tanggal untuk cuti
            $interval = \DateInterval::createFromDateString('1 day');
            $period = new \DatePeriod($fromDate, $interval, $toDate->modify('+1 day')); // +1 hari untuk menyertakan ToDate

            // Sisipkan atau perbarui catatan di tabel kehadiran untuk setiap hari dalam rentang
            foreach ($period as $date) {
                // Periksa apakah ada catatan yang sudah ada dengan nilai non-null di kolom tertentu
                $hasTimeFields = AttendModel::where('EmployeeID', $request->EmplID)
                    ->where('AttDate', $date->format('Y-m-d'))
                    ->where(function ($query) {
                        $query->whereNotNull('Time_In')
                            ->orWhereNotNull('Time_Break')
                            ->orWhereNotNull('Time_Resume')
                            ->orWhereNotNull('Time_Out')
                            ->orWhereNotNull('Time_InShort');
                    })
                    ->exists();

                // Tetapkan Present berdasarkan apakah ada kolom waktu
                $presentValue = $hasTimeFields ? 0 : 1;

                AttendModel::updateOrCreate(
                    [
                        'EmployeeID' => $request->EmplID,
                        'AttDate' => $date->format('Y-m-d'),
                    ],
                    [
                        'DutyProcessID' => $leaveProcess->leaveid, // Petakan leaveid ke DutyProcessID
                        'Present' => $presentValue, // Tetapkan Present berdasarkan kondisi
                        'Remark' => 'On Leave: ' . ($request->Notes ?? 'On Leave'), // Opsional: tambahkan catatan
                    ]
                );
            }

            DB::commit(); // Konfirmasi transaksi

            return redirect()->route('leaveprocess.index')
                ->with('success', 'Permintaan cuti berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan transaksi jika terjadi kesalahan
            Log::error('Kesalahan saat membuat permintaan cuti: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membuat permintaan cuti. Silakan coba lagi.');
        }
    }

    public function edit($leaveprocess)
    {
        $leaveProcess = LeaveProcess::findOrFail($leaveprocess);
        $users = userProfileModel::all();
        $leaveTypes = LeaveType::all();

        return view('leaveprocess.edit', compact('leaveProcess', 'users', 'leaveTypes'));
    }

    public function update(Request $request, $leaveprocess)
    {
        // Validasi input
        $validated = $request->validate([
            'leaveid' => 'required|exists:leavetype,Id',
            'EmplID' => 'required|exists:usersprofile,ID',
            'FromDate' => 'required|date',
            'ToDate' => 'required|date|after_or_equal:FromDate',
            'Notes' => 'nullable|string|max:100',
        ]);

        try {
            DB::beginTransaction(); // Mulai transaksi

            // Temukan catatan proses cuti
            $leaveProcess = LeaveProcess::findOrFail($leaveprocess);
            Log::info('Memperbarui proses cuti ID: ' . $leaveprocess, $validated);

            // Hitung rentang tanggal baru
            $fromDate = new \DateTime($request->FromDate);
            $toDate = new \DateTime($request->ToDate);

            // Perbarui catatan kehadiran dengan Present = 1 dalam rentang tanggal baru
            $updatedPresentRecords = AttendModel::where('EmployeeID', $request->EmplID)
                ->whereBetween('AttDate', [
                    $fromDate->format('Y-m-d'),
                    $toDate->format('Y-m-d')
                ])
                ->where('Present', 1)
                ->update([
                    'Present' => 0,
                    'Remark' => 'On Leave: ' . ($request->Notes ?? 'On Leave'),
                    'DutyProcessID' => $request->leaveid,
                ]);
            Log::info('Memperbarui ' . $updatedPresentRecords . ' catatan kehadiran dengan Present = 1 menjadi Present = 0, Remark = On Leave: ' . ($request->Notes ?? 'On Leave') . ' untuk EmployeeID: ' . $request->EmplID . ' dalam rentang tanggal: ' . $fromDate->format('Y-m-d') . ' hingga ' . $toDate->format('Y-m-d'));

            // Simpan data asli untuk debugging
            $originalData = $leaveProcess->getOriginal();
            Log::info('Data asli proses cuti: ', $originalData);

            // Hitung rentang tanggal asli
            $originalFromDate = new \DateTime($originalData['FromDate']);
            $originalToDate = new \DateTime($originalData['ToDate']);
            $originalInterval = \DateInterval::createFromDateString('1 day');
            $originalPeriod = new \DatePeriod($originalFromDate, $originalInterval, $originalToDate->modify('+1 day'));

            // Perbarui catatan kehadiran dalam rentang tanggal asli
            // Kasus 1: Catatan dengan kolom waktu non-null (Time_In, Time_Break, Time_Resume, Time_Out)
            $updatedRowsWithTime = AttendModel::where('EmployeeID', $leaveProcess->EmplID)
                ->where('DutyProcessID', $leaveProcess->leaveid)
                ->whereBetween('AttDate', [
                    $originalFromDate->format('Y-m-d'),
                    $originalToDate->format('Y-m-d')
                ])
                ->where(function ($query) {
                    $query->whereNotNull('Time_In')
                        ->orWhereNotNull('Time_Break')
                        ->orWhereNotNull('Time_Resume')
                        ->orWhereNotNull('Time_Out');
                })
                ->update([
                    'DutyProcessID' => null,
                    'Remark' => null,
                    'Present' => 1
                ]);
            Log::info('Memperbarui ' . $updatedRowsWithTime . ' catatan kehadiran dengan kolom waktu non-null untuk mengatur DutyProcessID dan Remark menjadi NULL untuk EmployeeID: ' . $leaveProcess->EmplID . ' dan DutyProcessID: ' . $leaveProcess->leaveid . ' dalam rentang tanggal: ' . $originalFromDate->format('Y-m-d') . ' hingga ' . $originalToDate->format('Y-m-d'));

            // Kasus 2: Catatan di mana semua kolom waktu NULL
            $updatedRowsWithoutTime = AttendModel::where('EmployeeID', $leaveProcess->EmplID)
                ->where('DutyProcessID', $leaveProcess->leaveid)
                ->whereBetween('AttDate', [
                    $originalFromDate->format('Y-m-d'),
                    $originalToDate->format('Y-m-d')
                ])
                ->whereNull('Time_In')
                ->whereNull('Time_Break')
                ->whereNull('Time_Resume')
                ->whereNull('Time_Out')
                ->update([
                    'DutyProcessID' => null,
                    'Remark' => null,
                    'Present' => 0
                ]);
            Log::info('Memperbarui ' . $updatedRowsWithoutTime . ' catatan kehadiran dengan semua kolom waktu null untuk mengatur DutyProcessID, Remark, dan Present menjadi 0 untuk EmployeeID: ' . $leaveProcess->EmplID . ' dan DutyProcessID: ' . $leaveProcess->leaveid . ' dalam rentang tanggal: ' . $originalFromDate->format('Y-m-d') . ' hingga ' . $originalToDate->format('Y-m-d'));

            // Perbarui catatan proses cuti
            $updateSuccess = $leaveProcess->update($validated);
            if (!$updateSuccess) {
                throw new \Exception('Gagal memperbarui catatan proses cuti.');
            }
            Log::info('Memperbarui proses cuti ID: ' . $leaveprocess, $leaveProcess->toArray());

            // Hitung rentang tanggal baru
            $interval = \DateInterval::createFromDateString('1 day');
            $period = new \DatePeriod($fromDate, $interval, $toDate->modify('+1 day')); // +1 hari untuk menyertakan ToDate

            // Buat atau perbarui catatan kehadiran untuk rentang tanggal baru
            $createdRecords = 0;
            foreach ($period as $date) {
                $attendData = [
                    'EmployeeID' => $request->EmplID,
                    'AttDate' => $date->format('Y-m-d'),
                    'DutyProcessID' => $request->leaveid,
                    'Present' => 0,
                    'Remark' => 'On Leave: ' . ($request->Notes ?? 'On Leave'),
                ];

                $attendRecord = AttendModel::updateOrCreate(
                    [
                        'EmployeeID' => $request->EmplID,
                        'AttDate' => $date->format('Y-m-d'),
                    ],
                    $attendData
                );

                if ($attendRecord->wasRecentlyCreated) {
                    $createdRecords++;
                }
                Log::info('Membuat/Memperbarui catatan kehadiran untuk tanggal: ' . $date->format('Y-m-d'), $attendData);
            }

            Log::info('Membuat/Memperbarui ' . $createdRecords . ' catatan kehadiran untuk proses cuti ID: ' . $leaveprocess);

            DB::commit(); // Konfirmasi transaksi

            return redirect()->route('leaveprocess.index')
                ->with('success', 'Permintaan cuti berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan transaksi jika terjadi kesalahan
            Log::error('Kesalahan saat memperbarui permintaan cuti ID: ' . $leaveprocess . ' - ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'exception_trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Gagal memperbarui permintaan cuti: ' . $e->getMessage());
        }
    }

    public function destroy($leaveprocess)
    {
        try {
            DB::beginTransaction();

            $leaveProcess = LeaveProcess::findOrFail($leaveprocess);
            Log::info('Menghapus proses cuti ID: ' . $leaveprocess, $leaveProcess->toArray());

            // Ambil rentang tanggal dari LeaveProcess
            $fromDate = new \DateTime($leaveProcess->FromDate);
            $toDate = new \DateTime($leaveProcess->ToDate);

            // Perbarui hanya catatan kehadiran dalam rentang FromDate dan ToDate
            $updatedRows = AttendModel::where('EmployeeID', $leaveProcess->EmplID)
                ->where('DutyProcessID', $leaveProcess->leaveid)
                ->whereBetween('AttDate', [
                    $fromDate->format('Y-m-d'),
                    $toDate->format('Y-m-d')
                ])
                ->update([
                    'DutyProcessID' => null,
                    'Remark' => null,
                    'Present' => 1
                ]);
            Log::info('Memperbarui ' . $updatedRows . ' catatan kehadiran untuk mengatur DutyProcessID, Remark, dan Present menjadi NULL untuk EmployeeID: ' . $leaveProcess->EmplID . ' dan DutyProcessID: ' . $leaveProcess->leaveid . ' dalam rentang tanggal: ' . $fromDate->format('Y-m-d') . ' hingga ' . $toDate->format('Y-m-d'));

            $leaveProcess->delete();
            Log::info('Menghapus proses cuti ID: ' . $leaveprocess);

            DB::commit();

            return redirect()->route('leaveprocess.index')
                ->with('success', 'Permintaan cuti berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Kesalahan saat menghapus permintaan cuti ID: ' . $leaveprocess . ' - ' . $e->getMessage(), [
                'exception_trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Gagal menghapus permintaan cuti: ' . $e->getMessage());
        }
    }
}

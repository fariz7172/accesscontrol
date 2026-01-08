<?php

namespace App\Http\Controllers;

use App\Models\LeaveProcess;
use App\Models\LeaveType;
use App\Models\userProfileModel;
use App\Models\AttendModel;
use App\Models\PtAvailability;
use App\Models\PtSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class sessionController extends Controller
{
    public function login(Request $request)
    {
        $request->validate(
            [
                'username' => 'required',
                'password' => 'required',
            ],
            [
                'username.required' => 'Username Wajib Di Isi',
                'password.required' => 'Password Wajib Di Isi',
            ]
        );

        try {
            $user = \App\Models\User::where('username', $request->username)->first();

            if (!$user) {
                return redirect('sesi')->withErrors('Username tidak ditemukan');
            }

            if (!Hash::check($request->password, $user->password)) {
                return redirect('sesi')->withErrors('Password yang Anda masukkan salah');
            }

            Auth::login($user);
            return redirect('/dashboard')->with(['success' => 'Berhasil Login', 'id' => $user->id]);
        } catch (\Illuminate\Database\QueryException $e) {
            return view('errors.custom_db');
        } catch (\Exception $e) {
             if (str_contains($e->getMessage(), 'SQLSTATE')) {
                 return view('errors.custom_db');
            }
            throw $e;
        }
    }

    public function memberLogin(Request $request)
    {
        $request->validate(
            [
                'name' => 'required',
                'password' => 'required',
            ],
            [
                'name.required' => 'Nama Wajib Di Isi',
                'password.required' => 'Password Wajib Di Isi',
            ]
        );

        $user = \App\Models\userProfileModel::where('NAME', $request->name)->first();

        if (!$user) {
            return redirect('sesi')->withErrors('Nama tidak ditemukan');
        }

        if ($request->password !== $user->PASSWORD) {
            return redirect('sesi')->withErrors('Password yang Anda masukkan salah');
        }

        if (!in_array($user->user_type, ['1', '2'])) {
            return redirect('sesi')->withErrors('Hanya Member atau Personal Trainer yang dapat login sebagai pengguna.');
        }

        Auth::guard('member')->login($user);

        if ($user->user_type == '1') {
            return redirect('/member')->with(['success' => 'Berhasil Login sebagai Member', 'id' => $user->ID]);
        } elseif ($user->user_type == '2') {
            return redirect('/trainer')->with(['success' => 'Berhasil Login sebagai Personal Trainer', 'id' => $user->ID]);
        }
    }


    public function trainer(Request $request)
    {
        $trainer = Auth::guard('member')->user();
        if (!$trainer || $trainer->user_type != '2') {
            return redirect('sesi')->withErrors('Anda harus login sebagai trainer untuk mengakses halaman ini.');
        }

        // Ambil jadwal trainer dari PtSchedule
        $schedules = PtSchedule::with(['member'])
            ->where('PT_ID', $trainer->ID)
            ->whereIn('STATUS', [0, 1]) // Hanya ambil status Booked atau Selesai
            ->orderBy('START_TIME', 'asc')
            ->paginate(10);

        // Ambil ketersediaan trainer dari PtAvailability
        $availabilities = PtAvailability::where('PT_ID', $trainer->ID)
            ->where('IS_ACTIVE', 1)
            ->get();

        return view('trainer.index', compact('schedules', 'availabilities', 'trainer'));
    }


    public function trainerLogin(Request $request)
    {
        $request->validate(
            [
                'name' => 'required|min:3|max:50',
                'password' => 'required|min:6',
            ],
            [
                'name.required' => 'Nama Wajib Di Isi',
                'name.min' => 'Nama minimal 3 karakter',
                'name.max' => 'Nama maksimal 50 karakter',
                'password.required' => 'Password Wajib Di Isi',
                'password.min' => 'Password minimal 6 karakter',
            ]
        );

        try {
            $trainer = \App\Models\userProfileModel::where('NAME', $request->name)
                ->where('user_type', '2') // Hanya trainer
                ->first();

            if (!$trainer) {
                return redirect('sesi')->withErrors('Nama trainer tidak ditemukan');
            }

            // Gunakan Hash::check() jika kata sandi di-hash
            if (!Hash::check($request->password, $trainer->PASSWORD)) {
                return redirect('sesi')->withErrors('Password yang Anda masukkan salah');
            }

            Auth::guard('member')->login($trainer);
            Log::info('Trainer login successful', ['id' => $trainer->ID, 'name' => $trainer->NAME]);
            return redirect('/trainer')->with(['success' => 'Berhasil Login sebagai Personal Trainer', 'id' => $trainer->ID]);
        } catch (\Exception $e) {
            Log::error('Kesalahan saat login trainer: ' . $e->getMessage());
            return redirect('sesi')->withErrors('Terjadi kesalahan saat login. Silakan coba lagi.');
        }
    }


    public function member(Request $request)
    {
        $user = Auth::guard('member')->user();
        if (!$user) {
            return redirect('sesi')->withErrors('Anda harus login sebagai member untuk mengakses halaman ini.');
        }

        $query = LeaveProcess::with(['userProfile', 'leaveType'])
            ->where('EmplID', $user->ID);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate && $endDate) {
            $query->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('FromDate', [$startDate, $endDate])
                    ->orWhereBetween('ToDate', [$startDate, $endDate])
                    ->orWhere(function ($subQ) use ($startDate, $endDate) {
                        $subQ->where('FromDate', '<=', $startDate)
                            ->where('ToDate', '>=', $endDate);
                    });
            });
        }

        $leaveProcesses = $query->paginate(10);
        $leaveTypes = LeaveType::select('Id', 'Name')->get();

        $leaveProcesses->appends([
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        Log::info('Member filter applied', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'user_id' => $user->ID,
        ]);

        return view('member.index', compact('leaveProcesses', 'leaveTypes', 'startDate', 'endDate'));
    }



    public function memberCreate()
    {
        $user = Auth::guard('member')->user();
        if (!$user) {
            return redirect('sesi')->withErrors('Anda harus login sebagai member untuk mengakses halaman ini.');
        }

        $leaveTypes = LeaveType::select('Id', 'Name')->get();
        return view('member.create', compact('leaveTypes', 'user'));
    }

    public function memberStore(Request $request)
    {
        $user = Auth::guard('member')->user();
        if (!$user) {
            return redirect('sesi')->withErrors('Anda harus login sebagai member untuk membuat permintaan cuti.');
        }

        $request->validate([
            'leaveid' => 'required|exists:leavetype,Id',
            'FromDate' => 'required|date',
            'ToDate' => 'required|date|after_or_equal:FromDate',
            'Notes' => 'nullable|string|max:100',
        ]);

        try {
            DB::beginTransaction();

            $leaveProcessCount = LeaveProcess::count();
            if ($leaveProcessCount >= 20) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Data cuti tidak boleh melebihi 20 catatan.');
            }

            $fromDate = new \DateTime($request->FromDate);
            $toDate = new \DateTime($request->ToDate);
            $existingAttendance = AttendModel::where('EmployeeID', $user->ID)
                ->whereBetween('AttDate', [
                    $fromDate->format('Y-m-d'),
                    $toDate->format('Y-m-d')
                ])
                ->where('Present', 1)
                ->exists();

            if ($existingAttendance) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Terdapat catatan kehadiran pada tanggal yang dipilih. Silakan pilih tanggal lain.');
            }

            $currentDate = now()->format('Y-m-d');
            $leaveProcess = LeaveProcess::create([
                'leaveid' => $request->leaveid,
                'EmplID' => $user->ID,
                'FromDate' => $request->FromDate,
                'ToDate' => $request->ToDate,
                'Notes' => $request->Notes,
                'approver_id' => 0,
                'approved_at' => null,
                'created_at' => $currentDate,
                'updated_at' => $currentDate,
                'STATUS' => 0, // Pending by default
                'priv' => 0,
            ]);

            $interval = \DateInterval::createFromDateString('1 day');
            $period = new \DatePeriod($fromDate, $interval, $toDate->modify('+1 day'));

            foreach ($period as $date) {
                $hasTimeFields = AttendModel::where('EmployeeID', $user->ID)
                    ->where('AttDate', $date->format('Y-m-d'))
                    ->where(function ($query) {
                        $query->whereNotNull('Time_In')
                            ->orWhereNotNull('Time_Break')
                            ->orWhereNotNull('Time_Resume')
                            ->orWhereNotNull('Time_Out')
                            ->orWhereNotNull('Time_InShort');
                    })
                    ->exists();

                $presentValue = $hasTimeFields ? 0 : 1;

                AttendModel::updateOrCreate(
                    [
                        'EmployeeID' => $user->ID,
                        'AttDate' => $date->format('Y-m-d'),
                    ],
                    [
                        'DutyProcessID' => $leaveProcess->leaveid,
                        'Present' => $presentValue,
                        'Remark' => 'On Leave: ' . ($request->Notes ?? 'On Leave'),
                    ]
                );
            }

            DB::commit();
            return redirect()->route('member.index')->with('success', 'Permintaan cuti berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Kesalahan saat membuat permintaan cuti untuk member: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membuat permintaan cuti. Silakan coba lagi.');
        }
    }

    public function memberEdit($id)
    {
        $user = Auth::guard('member')->user();
        if (!$user) {
            return redirect('sesi')->withErrors('Anda harus login sebagai member untuk mengedit permintaan cuti.');
        }

        $leaveProcess = LeaveProcess::where('Id', $id)
            ->where('EmplID', $user->ID)
            ->firstOrFail();

        $leaveTypes = LeaveType::select('Id', 'Name')->get();
        return view('member.edit', compact('leaveProcess', 'leaveTypes', 'user'));
    }

    public function memberUpdate(Request $request, $id)
    {
        $user = Auth::guard('member')->user();
        if (!$user) {
            return redirect('sesi')->withErrors('Anda harus login sebagai member untuk memperbarui permintaan cuti.');
        }

        $request->validate([
            'leaveid' => 'required|exists:leavetype,Id',
            'FromDate' => 'required|date',
            'ToDate' => 'required|date|after_or_equal:FromDate',
            'Notes' => 'nullable|string|max:100',
        ]);

        try {
            DB::beginTransaction();

            $leaveProcess = LeaveProcess::where('Id', $id)
                ->where('EmplID', $user->ID)
                ->firstOrFail();

            if ($leaveProcess->STATUS != 0) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Hanya permintaan cuti dengan status Pending yang dapat diedit.');
            }

            $fromDate = new \DateTime($request->FromDate);
            $toDate = new \DateTime($request->ToDate);

            $updatedPresentRecords = AttendModel::where('EmployeeID', $user->ID)
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

            $originalFromDate = new \DateTime($leaveProcess->FromDate);
            $originalToDate = new \DateTime($leaveProcess->ToDate);
            $originalInterval = \DateInterval::createFromDateString('1 day');
            $originalPeriod = new \DatePeriod($originalFromDate, $originalInterval, $originalToDate->modify('+1 day'));

            $updatedRowsWithTime = AttendModel::where('EmployeeID', $user->ID)
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

            $updatedRowsWithoutTime = AttendModel::where('EmployeeID', $user->ID)
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

            $leaveProcess->update([
                'leaveid' => $request->leaveid,
                'FromDate' => $request->FromDate,
                'ToDate' => $request->ToDate,
                'Notes' => $request->Notes,
                'updated_at' => now()->format('Y-m-d'),
            ]);

            $interval = \DateInterval::createFromDateString('1 day');
            $period = new \DatePeriod($fromDate, $interval, $toDate->modify('+1 day'));

            foreach ($period as $date) {
                $attendData = [
                    'EmployeeID' => $user->ID,
                    'AttDate' => $date->format('Y-m-d'),
                    'DutyProcessID' => $request->leaveid,
                    'Present' => 0,
                    'Remark' => 'On Leave: ' . ($request->Notes ?? 'On Leave'),
                ];

                AttendModel::updateOrCreate(
                    [
                        'EmployeeID' => $user->ID,
                        'AttDate' => $date->format('Y-m-d'),
                    ],
                    $attendData
                );
            }

            DB::commit();
            return redirect()->route('member.index')->with('success', 'Permintaan cuti berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Kesalahan saat memperbarui permintaan cuti untuk member: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui permintaan cuti. Silakan coba lagi.');
        }
    }

    public function memberExportPDF(Request $request)
    {
        $user = Auth::guard('member')->user();
        if (!$user) {
            return redirect('sesi')->withErrors('Anda harus login sebagai member untuk mengakses ekspor PDF.');
        }

        $query = LeaveProcess::with(['userProfile.department', 'leaveType', 'approver'])
            ->where('EmplID', $user->ID);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate && $endDate) {
            $query->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('FromDate', [$startDate, $endDate])
                    ->orWhereBetween('ToDate', [$startDate, $endDate])
                    ->orWhere(function ($subQ) use ($startDate, $endDate) {
                        $subQ->where('FromDate', '<=', $startDate)
                            ->where('ToDate', '>=', $endDate);
                    });
            });
        }

        $leaveProcesses = $query->get();

        Log::info('PDF export query executed', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'user_id' => $user->ID,
            'record_count' => $leaveProcesses->count(),
            'empl_ids' => $leaveProcesses->pluck('EmplID')->unique()->toArray(),
        ]);

        $pdf = Pdf::loadView('member.pdf', compact('leaveProcesses', 'user'))
            ->setPaper('A4', 'landscape')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
                'dpi' => 96,
            ]);

        return $pdf->download('leave_requests_' . str_replace(' ', '_', $user->NAME) . '_' . now()->format('Ymd') . '.pdf');
    }

    public function memberExportPDFById($id)
    {
        $user = Auth::guard('member')->user();
        if (!$user) {
            return redirect('sesi')->withErrors('Anda harus login sebagai member untuk mengakses ekspor PDF.');
        }

        $leaveProcess = LeaveProcess::with(['userProfile.department', 'leaveType', 'approver'])
            ->where('Id', $id)
            ->where('EmplID', $user->ID)
            ->firstOrFail();

        $pdf = Pdf::loadView('member.pdf', [
            'leaveProcesses' => collect([$leaveProcess]), // Wrap single record in collection
            'user' => $user
        ])
            ->setPaper('A4', 'landscape')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
                'dpi' => 96,
            ]);

        return $pdf->download('leave_request_' . $leaveProcess->Id . '_' . str_replace(' ', '_', $user->NAME) . '_' . now()->format('Ymd') . '.pdf');
    }

    public function index()
    {
        return $this->showEnv();
    }

    public function showEnv()
    {
        $path = base_path('.env');
        $envContent = file_get_contents($path);

        preg_match('/^DB_DATABASE=(.*)$/m', $envContent, $databaseMatches);
        preg_match('/^DB_CONNECTION=(.*)$/m', $envContent, $connectionMatches);
        preg_match('/^DB_HOST=(.*)$/m', $envContent, $hostMatches);
        preg_match('/^DB_PORT=(.*)$/m', $envContent, $portMatches);
        preg_match('/^DB_USERNAME=(.*)$/m', $envContent, $usernameMatches);
        preg_match('/^DB_PASSWORD=(.*)$/m', $envContent, $passwordMatches);
        preg_match('/^REGISTER_API=(.*)$/m', $envContent, $registerAPIMatches);
        preg_match('/^DELETE_API=(.*)$/m', $envContent, $deleteAPIMatches);

        $dbDatabase = isset($databaseMatches[1]) ? $databaseMatches[1] : '';
        $dbConnection = isset($connectionMatches[1]) ? $connectionMatches[1] : '';
        $dbHost = isset($hostMatches[1]) ? $hostMatches[1] : '';
        $dbPort = isset($portMatches[1]) ? $portMatches[1] : '';
        $dbUsername = isset($usernameMatches[1]) ? $usernameMatches[1] : '';
        $dbPassword = isset($passwordMatches[1]) ? $passwordMatches[1] : '';
        $dbRegisterAPI = isset($registerAPIMatches[1]) ? $registerAPIMatches[1] : '';
        $dbDeleteAPI = isset($deleteAPIMatches[1]) ? $deleteAPIMatches[1] : '';

        return view('welcome', compact('dbDatabase', 'dbConnection', 'dbHost', 'dbPort', 'dbUsername', 'dbPassword', 'dbRegisterAPI', 'dbDeleteAPI'));
    }

    public function updateEnvSetting(Request $request)
    {
        $newDatabase = $request->input('DB_DATABASE');
        $newConnection = $request->input('DB_CONNECTION');
        $newHost = $request->input('DB_HOST');
        $newPort = $request->input('DB_PORT');
        $newUsername = $request->input('DB_USERNAME');
        $newPassword = $request->input('DB_PASSWORD');
        $newRegisterAPI = $request->input('REGISTER_API');
        $newDeleteAPIID = $request->input('DELETE_API');

        $path = base_path('.env');
        $envContent = file_get_contents($path);

        $envContent = preg_replace('/^DB_CONNECTION=.*$/m', 'DB_CONNECTION=' . $newConnection, $envContent);
        $envContent = preg_replace('/^DB_HOST=.*$/m', 'DB_HOST=' . $newHost, $envContent);
        $envContent = preg_replace('/^DB_PORT=.*$/m', 'DB_PORT=' . $newPort, $envContent);
        $envContent = preg_replace('/^DB_DATABASE=.*$/m', 'DB_DATABASE=' . $newDatabase, $envContent);
        $envContent = preg_replace('/^DB_USERNAME=.*$/m', 'DB_USERNAME=' . $newUsername, $envContent);
        $envContent = preg_replace('/^DB_PASSWORD=.*$/m', 'DB_PASSWORD=' . $newPassword, $envContent);
        $envContent = preg_replace('/^REGISTER_API=.*$/m', 'REGISTER_API=' . $newRegisterAPI, $envContent);
        $envContent = preg_replace('/^DELETE_API=.*$/m', 'DELETE_API=' . $newDeleteAPIID, $envContent);

        file_put_contents($path, $envContent);

        try {
            Artisan::call('config:cache');
            Artisan::call('route:clear');
            Artisan::call('optimize');
            return response()->json(['success' => true, 'message' => 'Environment variables updated and cache cleared successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error updating environment variables: ' . $e->getMessage()]);
        }
    }

    public function logout()
    {
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }
        if (Auth::guard('member')->check()) {
            Auth::guard('member')->logout();
        }

        return redirect('sesi')->with('success', 'Berhasil Logout');
    }

    public function destroy($id)
    {
        $user = Auth::guard('member')->user();
        if (!$user) {
            return redirect('sesi')->withErrors('Anda harus login sebagai member untuk menghapus permintaan cuti.');
        }

        $leaveProcess = LeaveProcess::where('Id', $id)
            ->where('EmplID', $user->ID)
            ->firstOrFail();

        if ($leaveProcess->STATUS != 0) {
            return redirect()->back()->with('error', 'Hanya permintaan cuti dengan status Pending yang dapat dihapus.');
        }

        try {
            DB::beginTransaction();

            $fromDate = new \DateTime($leaveProcess->FromDate);
            $toDate = new \DateTime($leaveProcess->ToDate);
            $interval = \DateInterval::createFromDateString('1 day');
            $period = new \DatePeriod($fromDate, $interval, $toDate->modify('+1 day'));

            foreach ($period as $date) {
                AttendModel::where('EmployeeID', $user->ID)
                    ->where('AttDate', $date->format('Y-m-d'))
                    ->where('DutyProcessID', $leaveProcess->leaveid)
                    ->update([
                        'DutyProcessID' => null,
                        'Remark' => null,
                        'Present' => 0
                    ]);
            }

            $leaveProcess->delete();

            DB::commit();
            return redirect()->route('member.index')->with('success', 'Permintaan cuti berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Kesalahan saat menghapus permintaan cuti: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus permintaan cuti. Silakan coba lagi.');
        }
    }
}

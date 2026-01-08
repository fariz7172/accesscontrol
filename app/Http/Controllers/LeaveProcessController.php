<?php

namespace App\Http\Controllers;

use App\Exports\LeaveProcessExport;
use App\Models\AttendModel;
use App\Models\LeaveProcess;
use App\Models\LeaveType;
use App\Models\userProfileModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;

class LeaveProcessController extends Controller
{
    public function index(Request $request)
    {
        $query = LeaveProcess::with(['userProfile', 'leaveType']);

        // Apply date range filter if provided
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate && $endDate) {
            $query->whereBetween('FromDate', [$startDate, $endDate])
                ->orWhereBetween('ToDate', [$startDate, $endDate])
                ->orWhere(function ($q) use ($startDate, $endDate) {
                    $q->where('FromDate', '<=', $startDate)
                        ->where('ToDate', '>=', $endDate);
                });
        }

        $leaveProcesses = $query->paginate(10);
        $users = userProfileModel::all();
        $leaveTypes = LeaveType::all();

        // Preserve filter parameters in pagination links
        $leaveProcesses->appends(['start_date' => $startDate, 'end_date' => $endDate]);

        return view('leaveprocess.index', compact('leaveProcesses', 'users', 'leaveTypes', 'startDate', 'endDate'));
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
            'priv' => 'nullable|in:0,1', // Validate priv if provided
        ]);

        try {
            DB::beginTransaction();

            // Check the number of records in leaveprocess
            $leaveProcessCount = LeaveProcess::count();
            if ($leaveProcessCount >= 20) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Data cannot exceed 20 records');
            }

            // Check for existing attendance records with Present = 1 in the date range
            $fromDate = new \DateTime($request->FromDate);
            $toDate = new \DateTime($request->ToDate);
            $existingAttendance = AttendModel::where('EmployeeID', $request->EmplID)
                ->whereBetween('AttDate', [
                    $fromDate->format('Y-m-d'),
                    $toDate->format('Y-m-d')
                ])
                ->where('Present', 1)
                ->exists();

            if ($existingAttendance) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Terdapat Record Hari Yang Dipilih Sudah Terdata Masuk, Silahkan Pilih Dihari Yang Lain');
            }

            // Get current date and authenticated user
            $currentDate = now()->format('Y-m-d');
            $approverId = Auth::id();

            // Create leave process record with new fields, including priv defaulting to 1
            $leaveProcess = LeaveProcess::create([
                'leaveid' => $request->leaveid,
                'EmplID' => $request->EmplID,
                'FromDate' => $request->FromDate,
                'ToDate' => $request->ToDate,
                'Notes' => $request->Notes,
                'approver_id' => $approverId,
                'approved_at' => $currentDate,
                'created_at' => $currentDate,
                'updated_at' => $currentDate,
                'STATUS' => 1,
                'priv' => 1, // Default to 1 if not provided
            ]);

            // Calculate date range for leave
            $interval = \DateInterval::createFromDateString('1 day');
            $period = new \DatePeriod($fromDate, $interval, $toDate->modify('+1 day'));

            // Insert or update attendance records for each day in the range
            foreach ($period as $date) {
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

                $presentValue = $hasTimeFields ? 0 : 1;

                AttendModel::updateOrCreate(
                    [
                        'EmployeeID' => $request->EmplID,
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

            return redirect()->route('leaveprocess.index')
                ->with('success', 'Permintaan cuti berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
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
        $validated = $request->validate([
            'leaveid' => 'required|exists:leavetype,Id',
            'EmplID' => 'required|exists:usersprofile,ID',
            'FromDate' => 'required|date',
            'ToDate' => 'required|date|after_or_equal:FromDate',
            'Notes' => 'nullable|string|max:100',
            'priv' => 'nullable|in:0,1', // Validate priv if provided
        ]);

        try {
            DB::beginTransaction();

            $leaveProcess = LeaveProcess::findOrFail($leaveprocess);
            Log::info('Memperbarui proses cuti ID: ' . $leaveprocess, $validated);

            $fromDate = new \DateTime($request->FromDate);
            $toDate = new \DateTime($request->ToDate);

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

            $originalData = $leaveProcess->getOriginal();
            Log::info('Data asli proses cuti: ', $originalData);

            $originalFromDate = new \DateTime($originalData['FromDate']);
            $originalToDate = new \DateTime($originalData['ToDate']);
            $originalInterval = \DateInterval::createFromDateString('1 day');
            $originalPeriod = new \DatePeriod($originalFromDate, $originalInterval, $originalToDate->modify('+1 day'));

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

            // Include priv in the update, default to existing value if not provided
            $validated['priv'] = $request->input('priv', $leaveProcess->priv);

            $updateSuccess = $leaveProcess->update($validated);
            if (!$updateSuccess) {
                throw new \Exception('Gagal memperbarui catatan proses cuti.');
            }
            Log::info('Memperbarui proses cuti ID: ' . $leaveprocess, $leaveProcess->toArray());

            $interval = \DateInterval::createFromDateString('1 day');
            $period = new \DatePeriod($fromDate, $interval, $toDate->modify('+1 day'));

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

            DB::commit();

            return redirect()->route('leaveprocess.index')
                ->with('success', 'Permintaan cuti berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
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

            // Check if priv is 1
            if ($leaveProcess->priv != 1) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Hanya User Pemohon cuti  yang dapat Menghapus Data.');
            }

            Log::info('Menghapus proses cuti ID: ' . $leaveprocess, $leaveProcess->toArray());

            $fromDate = new \DateTime($leaveProcess->FromDate);
            $toDate = new \DateTime($leaveProcess->ToDate);

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

    public function export(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if (!$startDate || !$endDate) {
            return redirect()->back()->with('error', 'Please provide both start and end dates for export.');
        }

        $export = new LeaveProcessExport($startDate, $endDate);
        if ($export->collection()->isEmpty()) {
            return redirect()->back()->with('error', 'No leave process data available to export for the selected date range.');
        }

        return Excel::download($export, 'leave_processes_' . $startDate . '_to_' . $endDate . '.xlsx');
    }

    public function updateStatus(Request $request, $leaveprocess)
    {
        $request->validate([
            'status' => 'required|in:0,1,2,3',
        ]);

        try {
            DB::beginTransaction();

            $leaveProcess = LeaveProcess::findOrFail($leaveprocess);
            $status = $request->status;
            $approverId = Auth::id();
            $currentDateTime = now()->format('Y-m-d H:i:s');

            if (!$approverId) {
                throw new \Exception('No authenticated user found to set as approver.');
            }

            // Validasi untuk status Cancelled (3)


            Log::info('Updating status for LeaveProcess ID: ' . $leaveprocess, [
                'new_status' => $status,
                'EmplID' => $leaveProcess->EmplID,
                'FromDate' => $leaveProcess->FromDate,
                'ToDate' => $leaveProcess->ToDate,
                'leaveid' => $leaveProcess->leaveid,
                'approver_id' => $approverId,
                'approved_at' => $status == 1 ? $currentDateTime : null,
            ]);

            // Update the STATUS, approver_id, and approved_at fields in leaveprocess
            $leaveProcess->update([
                'STATUS' => $status,
                'approver_id' => $approverId,
                'approved_at' => $status == 1 ? $currentDateTime : null,
                'updated_at' => $currentDateTime,
            ]);

            $fromDate = new \DateTime($leaveProcess->FromDate);
            $toDate = new \DateTime($leaveProcess->ToDate);
            $interval = \DateInterval::createFromDateString('1 day');
            $period = new \DatePeriod($fromDate, $interval, $toDate->modify('+1 day'));

            // If status is Pending (0), Rejected (2), or Cancelled (3), update attend table
            if (in_array($status, [0, 2, 3])) {
                $updatedRecords = 0;
                foreach ($period as $date) {
                    $attDate = $date->format('Y-m-d');
                    $hasTimeFields = AttendModel::where('EmployeeID', $leaveProcess->EmplID)
                        ->where('AttDate', $attDate)
                        ->where(function ($query) {
                            $query->whereNotNull('Time_In')
                                ->orWhereNotNull('Time_Break')
                                ->orWhereNotNull('Time_Resume')
                                ->orWhereNotNull('Time_Out');
                        })
                        ->exists();

                    $presentValue = $hasTimeFields ? 1 : 0;

                    $attendData = [
                        'DutyProcessID' => null,
                        'Remark' => null,
                        'Present' => $presentValue,
                    ];

                    Log::info('Updating AttendModel for EmployeeID: ' . $leaveProcess->EmplID . ', AttDate: ' . $attDate, [
                        'hasTimeFields' => $hasTimeFields,
                        'presentValue' => $presentValue,
                        'attendData' => $attendData,
                    ]);

                    $updated = AttendModel::where('EmployeeID', $leaveProcess->EmplID)
                        ->where('AttDate', $attDate)
                        ->update($attendData);

                    if ($updated) {
                        $updatedRecords++;
                        Log::info('Updated AttendModel record for AttDate: ' . $attDate);
                    } else {
                        Log::warning('No AttendModel record found or updated for AttDate: ' . $attDate);
                    }
                }

                Log::info('Total AttendModel records updated for status ' . $status . ': ' . $updatedRecords);
            } elseif ($status == 1) {
                // If Approved, update attend table with leave data
                $updatedRecords = 0;
                foreach ($period as $date) {
                    $attDate = $date->format('Y-m-d');
                    $hasTimeFields = AttendModel::where('EmployeeID', $leaveProcess->EmplID)
                        ->where('AttDate', $attDate)
                        ->where(function ($query) {
                            $query->whereNotNull('Time_In')
                                ->orWhereNotNull('Time_Break')
                                ->orWhereNotNull('Time_Resume')
                                ->orWhereNotNull('Time_Out');
                        })
                        ->exists();

                    $presentValue = $hasTimeFields ? 0 : 1;

                    $attendData = [
                        'DutyProcessID' => $leaveProcess->leaveid,
                        'Present' => $presentValue,
                        'Remark' => 'On Leave: ' . ($leaveProcess->Notes ?? 'On Leave'),
                    ];

                    Log::info('Updating AttendModel for EmployeeID: ' . $leaveProcess->EmplID . ', AttDate: ' . $attDate, [
                        'hasTimeFields' => $hasTimeFields,
                        'presentValue' => $presentValue,
                        'attendData' => $attendData,
                    ]);

                    $updated = AttendModel::where('EmployeeID', $leaveProcess->EmplID)
                        ->where('AttDate', $attDate)
                        ->update($attendData);

                    if ($updated) {
                        $updatedRecords++;
                        Log::info('Updated AttendModel record for AttDate: ' . $attDate);
                    } else {
                        $attendRecord = AttendModel::create([
                            'EmployeeID' => $leaveProcess->EmplID,
                            'AttDate' => $attDate,
                            'DutyProcessID' => $leaveProcess->leaveid,
                            'Present' => $presentValue,
                            'Remark' => 'On Leave: ' . ($leaveProcess->Notes ?? 'On Leave'),
                        ]);
                        $updatedRecords++;
                        Log::info('Created new AttendModel record for AttDate: ' . $attDate, [
                            'attendRecord' => $attendRecord->toArray(),
                        ]);
                    }
                }

                Log::info('Total AttendModel records updated/created for Approved status: ' . $updatedRecords);
            }

            DB::commit();

            // Determine if edit and delete actions should be available
            $actionsAvailable = $status == 0;

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.',
                'status_text' => $leaveProcess->status_text,
                'actions_available' => $actionsAvailable,
                'leave_process_id' => $leaveProcess->Id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating leave status for ID: ' . $leaveprocess . ' - ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'exception_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage(),
            ], 500);
        }
    }
}

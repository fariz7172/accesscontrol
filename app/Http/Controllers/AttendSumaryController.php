<?php

namespace App\Http\Controllers;

use App\Exports\ExportAttendanceSummary;
use App\Models\AttendSumary;
use App\Models\AttendModel;
use App\Models\userProfileModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class AttendSumaryController extends Controller
{
    public function index(Request $request)
    {
        // Fetch all users with their department
        $users = userProfileModel::select('ID', 'NAME', 'Depid')
            ->with('department')
            ->orderBy('ID', 'asc')
            ->get();
        Log::info('Users with Department: ', $users->toArray());

        // Prioritize old input or session data, then fallback to defaults
        $selectedUsers = $request->old('selected_users', $request->input('selected_users', []));
        $startPeriod = $request->old('StartPeriod', $request->input('StartPeriod', Carbon::now()->startOfMonth()->format('Y-m-d')));
        $endPeriod = $request->old('EndPeriod', $request->input('EndPeriod', Carbon::now()->endOfMonth()->format('Y-m-d')));
        $selectedMonth = $request->old('selected_month', $request->input('selected_month', Carbon::now()->month));
        $selectedYear = $request->old('selected_year', $request->input('selected_year', Carbon::now()->year));

        // Validation for POST requests (excluding export)
        if ($request->isMethod('post') && !$request->has('export')) {
            $request->validate([
                'StartPeriod' => 'required|date',
                'EndPeriod' => 'required|date|after_or_equal:StartPeriod',
                'selected_users' => 'required|array|min:1',
                'selected_users.*' => 'exists:usersprofile,ID',
                'selected_month' => 'nullable|integer|between:1,12',
                'selected_year' => 'nullable|integer|min:1900|max:9999',
            ]);
        }

        // Use selected_month and selected_year for filtering
        $year = $selectedYear ?: Carbon::parse($startPeriod)->year;
        $month = $selectedMonth ?: Carbon::parse($startPeriod)->month;

        // Initialize summaries
        $summaries = collect([]);

        // Fetch attendance summaries if users are selected
        if (!empty($selectedUsers)) {
            $summaries = AttendSumary::whereIn('EmployeeID', $selectedUsers)
                ->whereYear('Period', $year)
                ->whereMonth('Period', $month)
                ->get();
        }

        // Prepare data for view
        $monthlyData = $summaries;

        // Debugging logs
        Log::info('Selected Users: ', $selectedUsers);
        Log::info('Summaries: ', $summaries->toArray());
        Log::info('StartPeriod: ', [$startPeriod]);
        Log::info('Year: ' . $year . ', Month: ' . $month);

        // Pass variables to view
        return view('summary.index', compact('users', 'monthlyData', 'month', 'year', 'selectedUsers', 'startPeriod', 'endPeriod', 'selectedMonth', 'selectedYear'));
    }

    public function generate(Request $request)
    {
        // Validation
        $request->validate([
            'StartPeriod' => 'required|date',
            'EndPeriod' => [
                'required',
                'date',
                'after_or_equal:StartPeriod',
                function ($attribute, $value, $fail) use ($request) {
                    $startPeriod = Carbon::parse($request->input('StartPeriod'));
                    if (Carbon::parse($value)->month != $startPeriod->month) {
                        $fail('Tanggal Akhir harus berada dalam bulan yang sama dengan Tanggal Mulai.');
                    }
                },
            ],
            'selected_users' => 'required|array|min:1',
            'selected_users.*' => 'exists:usersprofile,ID',
        ]);

        $startPeriod = Carbon::parse($request->input('StartPeriod'));
        $endPeriod = Carbon::parse($request->input('EndPeriod'));
        $selectedUsers = $request->input('selected_users', []);
        $period = $startPeriod->startOfMonth()->format('Y-m-d');

        try {
            DB::beginTransaction();

            foreach ($selectedUsers as $userId) {
                $attendances = AttendModel::where('EmployeeID', $userId)
                    ->whereBetween('AttDate', [$startPeriod, $endPeriod])
                    ->get();

                $summaryData = [
                    'Period' => $period,
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
                    if ($attendance->DayType == 1) {
                        $summaryData['WorkingDays']++;
                    }
                    if ($attendance->Present == 1) {
                        $summaryData['Present']++;
                    }
                    if ($attendance->Present == 0 && $attendance->DayType != 2) {
                        $summaryData['Absent']++;
                    }
                    if (!is_null($attendance->Time_InShort) && $attendance->Time_InShort > 0) {
                        $summaryData['LateIn']++;
                        $summaryData['LateInMinute'] += $attendance->Time_InShort;
                    }
                    if (!is_null($attendance->Time_OutShort) && $attendance->Time_OutShort > 0) {
                        $summaryData['EarlyOut']++;
                        $summaryData['EarlyOutMinute'] += $attendance->Time_OutShort;
                    }
                    if (!is_null($attendance->WorkTime)) {
                        $summaryData['TotalWorktime'] += $attendance->WorkTime;
                    }
                    if (!is_null($attendance->TotalWorkHour)) {
                        $summaryData['TotalWorkHour'] += $attendance->TotalWorkHour;
                    }
                    if (!is_null($attendance->TotalOT) && $attendance->TotalOT > 0) {
                        $summaryData['OT']++;
                        $summaryData['OTMinute'] += $attendance->TotalOT;
                    }
                    if (!is_null($attendance->EarlyWork)) {
                        $summaryData['EarlyWork']++;
                        $summaryData['EarlyWorkMinute'] += $attendance->EarlyWork;
                    }
                    if ($attendance->DayType == 1 && is_null($attendance->Time_In)) {
                        $summaryData['Ncheckin']++;
                    }
                    if ($attendance->DayType == 1 && is_null($attendance->Time_Out)) {
                        $summaryData['Ncheckout']++;
                    }
                    if (!is_null($attendance->DutyProcessID)) {
                        $summaryData['LeaveTaken']++;
                        $dutyId = $attendance->DutyProcessID;
                        if ($dutyId >= 1 && $dutyId <= 20) {
                            $summaryData['D' . $dutyId]++;
                        }
                    }
                }

                $existingSummary = AttendSumary::where('EmployeeID', $userId)
                    ->where('Period', $period)
                    ->first();

                if ($existingSummary) {
                    $existingSummary->update($summaryData);
                } else {
                    AttendSumary::create($summaryData);
                }
            }

            DB::commit();
            return redirect()->route('summary.index')
                ->with('success', 'Ringkasan kehadiran berhasil dibuat.')
                ->withInput($request->all());
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('summary.index')
                ->with('error', 'Gagal membuat ringkasan kehadiran: ' . $e->getMessage())
                ->withInput($request->all());
        }
    }

    public function getEmployeesByDepartment(Request $request)
    {
        $departmentId = $request->input('department_id');
        $selectedEmployeeIds = $request->input('employee_ids', []);

        // Fetch employees based on department ID
        $query = userProfileModel::select('ID', 'NAME', 'Depid')->orderBy('NAME', 'asc');
        if ($departmentId !== null && $departmentId !== '') {
            $query->where('Depid', $departmentId);
        }
        $employees = $query->get();

        // Log for debugging
        Log::info('getEmployeesByDepartment - Department ID: ' . ($departmentId ?? 'All'));
        Log::info('Filtered Employees: ', $employees->toArray());
        Log::info('Selected Employee IDs: ', $selectedEmployeeIds);

        return response()->json([
            'employees' => $employees,
            'selected_employee_ids' => $selectedEmployeeIds
        ]);
    }

    public function export(Request $request)
    {
        $request->validate([
            'StartPeriod' => 'required|date',
            'EndPeriod' => 'required|date|after_or_equal:StartPeriod',
            'selected_users' => 'required|array|min:1',
            'selected_users.*' => 'exists:usersprofile,ID',
        ]);

        $startPeriod = Carbon::parse($request->input('StartPeriod'));
        $year = $startPeriod->year;
        $month = $startPeriod->month;
        $selectedUsers = $request->input('selected_users', []);

        $filename = 'Attendance_Summary_' . $startPeriod->format('Y_m') . '.xlsx';

        return Excel::download(new ExportAttendanceSummary($selectedUsers, $year, $month), $filename);
    }
}

<?php

namespace App\Http\Controllers;

use App\Exports\AttendanceSheet;
use App\Exports\AttendanceOffDayExport;
use App\Models\AttendModel;
use App\Models\userProfileModel;
use App\Models\departmentModel;
use App\Models\LeaveType;
use App\Exports\AttendanceBreakSheet;
use App\Models\Shift;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceSheetController extends Controller
{

    public function index(Request $request)
    {
        $userProfiles = userProfileModel::select('ID', 'NAME')->orderBy('ID', 'asc')->get();
        $leaveTypes = LeaveType::select('Id', 'Name')->orderBy('Name', 'asc')->get();
        $attendByDate = collect();
        $totalPresent = 0;
        $totalAbsent = 0;
        $totalLeave = 0;

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $employeeIds = $request->input('employee_ids', []);

        if ($request->has('generate') && $startDate && $endDate && $employeeIds) {
            // Validate that all selected employees have at least one record in the attend table
            $existingEmployeeIds = AttendModel::whereIn('EmployeeID', $employeeIds)
                ->whereBetween('AttDate', [Carbon::parse($startDate), Carbon::parse($endDate)])
                ->distinct()
                ->pluck('EmployeeID')
                ->toArray();

            // Find employees that do not have attendance records
            $missingEmployeeIds = array_diff($employeeIds, $existingEmployeeIds);
            if (!empty($missingEmployeeIds)) {
                $missingEmployeeNames = userProfileModel::whereIn('ID', $missingEmployeeIds)
                    ->pluck('NAME')
                    ->implode(', ');
                return redirect()->back()->with('error', "Data belum di-generate untuk karyawan: {$missingEmployeeNames}");
            }

            $startDate = Carbon::parse($startDate);
            $endDate = Carbon::parse($endDate);
            $dateRange = CarbonPeriod::create($startDate, $endDate);

            // Fetch all attendance records for the selected employees and date range
            $attendanceRecords = AttendModel::query()
                ->select('attend.*')
                ->join('usersprofile', 'attend.EmployeeID', '=', 'usersprofile.ID')
                ->with(['shift', 'leaveType', 'userProfile'])
                ->whereIn('attend.EmployeeID', $employeeIds)
                ->whereBetween('attend.AttDate', [$startDate, $endDate])
                ->orderBy('usersprofile.NAME', 'asc')
                ->orderBy('attend.AttDate', 'asc')
                ->get();

            $totalPresent = $attendanceRecords->where('Present', 1)->count();
            $totalAbsent = $attendanceRecords->where('Present', 0)
                ->where('DayType', '!=', 2)
                ->whereNull('DutyProcessID')
                ->count();
            $totalLeave = $attendanceRecords->whereNotNull('DutyProcessID')->count();

            // Group records by date and employee
            $attendByDate = collect();
            foreach ($dateRange as $date) {
                $dateStr = $date->toDateString();
                $dailyRecords = collect();
                foreach ($employeeIds as $employeeId) {
                    $user = $userProfiles->firstWhere('ID', $employeeId);
                    if (!$user) continue;

                    $attendRecord = $attendanceRecords->firstWhere(function ($record) use ($dateStr, $employeeId) {
                        return $record->AttDate === $dateStr && $record->EmployeeID == $employeeId;
                    });

                    // In the generate condition, update the placeholder record
                    if ($attendRecord) {
                        $dailyRecords->push($attendRecord);
                    } else {
                        // Fetch the shift for the employee if available
                        $shift = Shift::where('id', $user->ShiftCode)->first();
                        $dailyRecords->push((object)[
                            'EmployeeID' => $employeeId,
                            'AttDate' => $dateStr,
                            'ShiftCode' => null,
                            'Time_In' => null,
                            'Time_Out' => null,
                            'Present' => 0,
                            'TotalWorkHour' => 0,
                            'WorkTime' => 0,
                            'Time_OutShort' => 0,
                            'TotalOT' => 0,
                            'Remark' => null,
                            'DutyProcessID' => null,
                            'DayType' => null,
                            'userProfile' => $user,
                            'shift' => $shift,
                            'leaveType' => null,
                            'display_leave_type' => 'N/A',
                        ]);
                    }
                }
                $attendByDate->put($dateStr, $dailyRecords);
            }
        } elseif ($request->has('export') && $startDate && $endDate && $employeeIds) {
            // Validate for export
            $existingEmployeeIds = AttendModel::whereIn('EmployeeID', $employeeIds)
                ->whereBetween('AttDate', [Carbon::parse($startDate), Carbon::parse($endDate)])
                ->distinct()
                ->pluck('EmployeeID')
                ->toArray();

            $missingEmployeeIds = array_diff($employeeIds, $existingEmployeeIds);
            if (!empty($missingEmployeeIds)) {
                $missingEmployeeNames = userProfileModel::whereIn('ID', $missingEmployeeIds)
                    ->pluck('NAME')
                    ->implode(', ');
                return redirect()->back()->with('error', "Data belum di-generate untuk karyawan: {$missingEmployeeNames}");
            }

            $export = new AttendanceSheet($employeeIds, $startDate, $endDate);
            if ($export->collection()->isEmpty()) {
                return redirect()->back()->with('error', 'No data available to export for the selected criteria.');
            }
            return Excel::download($export, 'attendance_' . $startDate . '_to_' . $endDate . '.xlsx');
        } elseif ($request->has('exportOffDay') && $startDate && $endDate && $employeeIds) {
            // Validate for exportOffDay
            $existingEmployeeIds = AttendModel::whereIn('EmployeeID', $employeeIds)
                ->whereBetween('AttDate', [Carbon::parse($startDate), Carbon::parse($endDate)])
                ->distinct()
                ->pluck('EmployeeID')
                ->toArray();

            $missingEmployeeIds = array_diff($employeeIds, $existingEmployeeIds);
            if (!empty($missingEmployeeIds)) {
                $missingEmployeeNames = userProfileModel::whereIn('ID', $missingEmployeeIds)
                    ->pluck('NAME')
                    ->implode(', ');
                return redirect()->back()->with('error', "Data belum di-generate untuk karyawan: {$missingEmployeeNames}");
            }

            $export = new AttendanceOffDayExport($employeeIds, $startDate, $endDate);
            if ($export->collection()->isEmpty()) {
                return redirect()->back()->with('error', 'No data available to export for the selected criteria.');
            }
            return Excel::download($export, 'offday_attendance_' . $startDate . '_to_' . $endDate . '.xlsx');
        } elseif ($request->has('exportBreak') && $startDate && $endDate && $employeeIds) {
            // Validate for exportBreak
            $existingEmployeeIds = AttendModel::whereIn('EmployeeID', $employeeIds)
                ->whereBetween('AttDate', [Carbon::parse($startDate), Carbon::parse($endDate)])
                ->distinct()
                ->pluck('EmployeeID')
                ->toArray();

            $missingEmployeeIds = array_diff($employeeIds, $existingEmployeeIds);
            if (!empty($missingEmployeeIds)) {
                $missingEmployeeNames = userProfileModel::whereIn('ID', $missingEmployeeIds)
                    ->pluck('NAME')
                    ->implode(', ');
                return redirect()->back()->with('error', "Data belum di-generate untuk karyawan: {$missingEmployeeNames}");
            }

            $export = new AttendanceBreakSheet($employeeIds, $startDate, $endDate);
            if ($export->collection()->isEmpty()) {
                return redirect()->back()->with('error', 'No data available to export for the selected criteria.');
            }
            return Excel::download($export, 'attendance_break_' . $startDate . '_to_' . $endDate . '.xlsx');
        } else {
            // Default behavior: Show attendance for today for users who are present
            $today = Carbon::today()->toDateString();
            $attendanceRecords = AttendModel::query()
                ->select('attend.*')
                ->join('usersprofile', 'attend.EmployeeID', '=', 'usersprofile.ID')
                ->with(['shift', 'leaveType', 'userProfile'])
                ->where('attend.AttDate', $today)
                ->where('attend.Present', 1)
                ->orderBy('usersprofile.NAME', 'asc')
                ->orderBy('attend.AttDate', 'asc')
                ->get();

            $totalPresent = $attendanceRecords->where('Present', 1)->count();
            $totalAbsent = $attendanceRecords->where('Present', 0)
                ->where('DayType', '!=', 2)
                ->whereNull('DutyProcessID')
                ->count();
            $totalLeave = $attendanceRecords->whereNotNull('DutyProcessID')->count();

            $attendByDate->put($today, $attendanceRecords);
        }

        return view('attendantSheet.index', compact('attendByDate', 'userProfiles', 'totalPresent', 'totalAbsent', 'totalLeave', 'leaveTypes'));
    }

    public function updateRemark(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['success' => false, 'message' => 'Only AJAX allowed'], 400);
        }

        $validated = $request->validate([
            'id' => 'required|exists:attend,id',
            'remark_data' => 'required|string',
        ]);

        $attend = AttendModel::find($validated['id']);
        if (!$attend) {
            return response()->json(['success' => false, 'message' => 'Record not found'], 404);
        }

        $remarkData = $validated['remark_data'];

        if ($remarkData === 'N/A') {
            $attend->Remark = null;
            $attend->DutyProcessID = null;
             $attend->Present = 1;
        } elseif ($remarkData === 'Holiday') {
            $attend->Remark = 'Holiday';
            $attend->DutyProcessID = null;
        } elseif (strpos($remarkData, 'On Leave|') === 0) {
            // Extract leaveTypeId from remark_data
            $parts = explode('|', $remarkData);
            $leaveTypeId = $parts[1] ?? null;

            // Fetch the leave type to get the Name
            $leaveType = LeaveType::find($leaveTypeId);
            if ($leaveType) {
                $attend->Remark = "On Leave: {$leaveType->Name}";
                $attend->DutyProcessID = $leaveTypeId;
                $attend->Present = 0;
            } else {
                return response()->json(['success' => false, 'message' => 'Invalid leave type selected'], 400);
            }
        } else {
            // Handle custom remark
            $attend->Remark = "On Leave: {$remarkData}";
            $attend->DutyProcessID = 1; // Set DutyProcessID to 1 for custom remarks
            $attend->Present = 0;
        }

        $attend->save();

        return response()->json(['success' => true, 'message' => 'Remark updated', 'redirect' => url()->previous()]);
    }

    public function updateDutyProcess(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'This endpoint only accepts AJAX requests',
                'redirect' => url()->previous()
            ], 400);
        }

        try {
            $validated = $request->validate([
                'id' => 'required|exists:attend,id',
                'remark_data' => 'required|string',
            ]);

            $attend = AttendModel::find($validated['id']);
            if (!$attend) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance record not found',
                    'redirect' => url()->previous()
                ], 404);
            }

            $remarkData = $validated['remark_data'];
            if ($remarkData === 'N/A') {
                $attend->Remark = null;
                $attend->DutyProcessID = null;
            } elseif ($remarkData === 'Holiday') {
                $attend->Remark = 'Holiday';
                $attend->DutyProcessID = null;
            } elseif (strpos($remarkData, 'On Leave|') === 0) {
                $leaveTypeId = explode('|', $remarkData)[1];
                $leaveType = LeaveType::find($leaveTypeId);
                if ($leaveType) {
                    $attend->Remark = "On Leave: {$leaveType->Name}";
                    $attend->DutyProcessID = $leaveTypeId;
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid leave type selected',
                        'redirect' => url()->previous()
                    ], 400);
                }
            } else {
                // Handle custom remark
                $attend->Remark = "On Leave: {$remarkData}";
                $attend->DutyProcessID = 1; // Set DutyProcessID to 1 for custom remarks
            }

            $attend->save();

            return response()->json([
                'success' => true,
                'message' => 'Remark updated successfully',
                'redirect' => url()->previous()
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', array_merge(...array_values($e->errors()))),
                'redirect' => url()->previous()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update remark: ' . $e->getMessage(),
                'redirect' => url()->previous()
            ], 500);
        }
    }

    public function getEmployeesByDepartment(Request $request)
    {
        $departmentId = $request->input('department_id');
        $selectedEmployeeIds = $request->input('employee_ids', []);

        // Fetch employees based on department ID
        $query = userProfileModel::select('ID', 'NAME', 'Depid')->orderBy('NAME', 'asc');
        if ($departmentId) {
            $query->where('Depid', $departmentId);
        }
        $employees = $query->get();

        return response()->json([
            'employees' => $employees,
            'selected_employee_ids' => $selectedEmployeeIds
        ]);
    }
}

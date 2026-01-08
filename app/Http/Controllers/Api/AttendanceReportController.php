<?php

namespace App\Http\Controllers\Api;

use App\Models\AttendModel;
use App\Models\userProfileModel;
use App\Models\departmentModel;
use App\Models\LeaveType;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AttendanceReportController extends Controller
{
    /**
     * Create a new AttendanceReportController instance.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get user attendance report with date range filter
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|array',
            'user_id.*' => 'integer',
            'user_name' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'include_details' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $includeDetails = $request->input('include_details', false);

            // Build user query
            $userQuery = userProfileModel::query()
                ->select('ID', 'NAME', 'Depid')
                ->with('department:id,name');

            // Filter by user_id or user_name
            if ($request->has('user_id') && !empty($request->user_id)) {
                // Filter only valid user IDs that exist in database
                $validUserIds = userProfileModel::whereIn('ID', $request->user_id)->pluck('ID')->toArray();
                
                if (empty($validUserIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No valid users found with the provided IDs'
                    ], 404);
                }
                
                $userQuery->whereIn('ID', $validUserIds);
            } elseif ($request->has('user_name')) {
                $userQuery->where('NAME', 'like', '%' . $request->user_name . '%');
            }

            $users = $userQuery->get();

            if ($users->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No users found'
                ], 404);
            }

            $userIds = $users->pluck('ID')->toArray();

            // Get attendance records
            $attendanceRecords = AttendModel::query()
                ->with(['shift', 'leaveType', 'userProfile'])
                ->whereIn('EmployeeID', $userIds)
                ->whereBetween('AttDate', [$startDate, $endDate])
                ->orderBy('AttDate', 'asc')
                ->get();

            // Calculate statistics
            $totalDays = $startDate->diffInDays($endDate) + 1;
            $overallStats = [
                'hadir' => 0,
                'alpha' => 0,
                'telat' => 0,
                'ijin' => 0,
                'cuti' => 0,
                'libur' => 0,
            ];

            $usersData = [];

            foreach ($users as $user) {
                $userAttendance = $attendanceRecords->where('EmployeeID', $user->ID);
                
                $stats = $this->calculateStatistics($userAttendance);
                $overallStats['hadir'] += $stats['hadir'];
                $overallStats['alpha'] += $stats['alpha'];
                $overallStats['telat'] += $stats['telat'];
                $overallStats['ijin'] += $stats['ijin'];
                $overallStats['cuti'] += $stats['cuti'];
                $overallStats['libur'] += $stats['libur'];

                $userData = [
                    'user_id' => $user->ID,
                    'user_name' => $user->NAME,
                    'department' => $user->department ? $user->department->name : null,
                    'statistics' => $stats,
                ];

                if ($includeDetails) {
                    $userData['details'] = $this->getAttendanceDetails($userAttendance);
                }

                $usersData[] = $userData;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'period' => [
                        'start_date' => $startDate->toDateString(),
                        'end_date' => $endDate->toDateString(),
                        'total_days' => $totalDays,
                    ],
                    'summary' => $overallStats,
                    'users' => $usersData,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get attendance report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of users for dropdown/autocomplete
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserList(Request $request)
    {
        try {
            $query = userProfileModel::query()
                ->select('ID', 'NAME', 'Depid')
                ->with('department:id,name')
                ->orderBy('NAME', 'asc');

            if ($request->has('search')) {
                $query->where('NAME', 'like', '%' . $request->search . '%');
            }

            if ($request->has('department_id')) {
                $query->where('Depid', $request->department_id);
            }

            $users = $query->get()->map(function ($user) {
                return [
                    'id' => $user->ID,
                    'name' => $user->NAME,
                    'department' => $user->department ? $user->department->name : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $users
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get user list',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get overall attendance statistics
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAttendanceStatistics(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'department_id' => 'nullable|integer|exists:departemen,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            // Get users
            $userQuery = userProfileModel::query();
            if ($request->has('department_id')) {
                $userQuery->where('Depid', $request->department_id);
            }
            $totalEmployees = $userQuery->count();
            $userIds = $userQuery->pluck('ID')->toArray();

            // Get attendance records
            $attendanceRecords = AttendModel::query()
                ->with(['shift', 'leaveType'])
                ->whereIn('EmployeeID', $userIds)
                ->whereBetween('AttDate', [$startDate, $endDate])
                ->get();

            $stats = $this->calculateStatistics($attendanceRecords);
            $totalDays = $startDate->diffInDays($endDate) + 1;
            $totalRecords = array_sum($stats);

            // Calculate percentages
            $percentages = [];
            foreach ($stats as $key => $value) {
                $percentages[$key] = $totalRecords > 0 ? round(($value / $totalRecords) * 100, 2) : 0;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'period' => [
                        'start_date' => $startDate->toDateString(),
                        'end_date' => $endDate->toDateString(),
                        'total_days' => $totalDays,
                    ],
                    'total_employees' => $totalEmployees,
                    'statistics' => $stats,
                    'percentage' => $percentages,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate attendance statistics
     * 
     * @param \Illuminate\Support\Collection $attendanceRecords
     * @return array
     */
    private function calculateStatistics($attendanceRecords)
    {
        $stats = [
            'hadir' => 0,
            'alpha' => 0,
            'telat' => 0,
            'ijin' => 0,
            'cuti' => 0,
            'libur' => 0,
        ];

        foreach ($attendanceRecords as $record) {
            // Libur (Holiday)
            if ($record->DayType == 2 || $record->Remark == 'Holiday') {
                $stats['libur']++;
                continue;
            }

            // Ijin/Cuti (Leave)
            if ($record->DutyProcessID != null) {
                if ($record->leaveType && stripos($record->leaveType->Name, 'cuti') !== false) {
                    $stats['cuti']++;
                } else {
                    $stats['ijin']++;
                }
                continue;
            }

            // Hadir (Present)
            if ($record->Present == 1) {
                $stats['hadir']++;
                
                // Check if late
                if ($this->isLate($record)) {
                    $stats['telat']++;
                }
                continue;
            }

            // Alpha (Absent)
            if ($record->Present == 0 && $record->DutyProcessID == null && $record->DayType != 2) {
                $stats['alpha']++;
            }
        }

        return $stats;
    }

    /**
     * Check if user is late
     * 
     * @param AttendModel $record
     * @return bool
     */
    private function isLate($record)
    {
        if (!$record->Time_In || !$record->shift || !$record->shift->Start_In) {
            return false;
        }

        try {
            $shiftStartTime = Carbon::parse($record->shift->Start_In);
            $actualTimeIn = Carbon::parse($record->Time_In);
            
            return $actualTimeIn->gt($shiftStartTime);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get attendance details per day
     * 
     * @param \Illuminate\Support\Collection $attendanceRecords
     * @return array
     */
    private function getAttendanceDetails($attendanceRecords)
    {
        $details = [];

        foreach ($attendanceRecords as $record) {
            $status = 'unknown';
            $isLate = false;
            $lateMinutes = 0;

            // Determine status
            if ($record->DayType == 2 || $record->Remark == 'Holiday') {
                $status = 'libur';
            } elseif ($record->DutyProcessID != null) {
                $status = ($record->leaveType && stripos($record->leaveType->Name, 'cuti') !== false) ? 'cuti' : 'ijin';
            } elseif ($record->Present == 1) {
                $status = 'hadir';
                
                // Check late
                if ($this->isLate($record)) {
                    $isLate = true;
                    try {
                        $shiftStartTime = Carbon::parse($record->shift->Start_In);
                        $actualTimeIn = Carbon::parse($record->Time_In);
                        $lateMinutes = $actualTimeIn->diffInMinutes($shiftStartTime);
                    } catch (\Exception $e) {
                        $lateMinutes = 0;
                    }
                }
            } elseif ($record->Present == 0) {
                $status = 'alpha';
            }

            $details[] = [
                'date' => $record->AttDate,
                'status' => $status,
                'time_in' => $record->Time_In,
                'time_out' => $record->Time_Out,
                'shift' => $record->shift ? $record->shift->ShiftName : null,
                'is_late' => $isLate,
                'late_minutes' => $lateMinutes,
                'work_hours' => $record->TotalWorkHour ?? 0,
                'remark' => $record->Remark,
                'leave_type' => $record->leaveType ? $record->leaveType->Name : null,
            ];
        }

        return $details;
    }
}

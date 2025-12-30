<?php

namespace App\Http\Controllers;

use App\Models\userLogModel;
use App\Models\Branch;
use App\Models\departmentModel;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UserLogsExport;

class logDataController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from')
            ? Carbon::parse($request->input('date_from'))->startOfDay()
            : Carbon::today()->startOfDay();
        $dateTo = $request->input('date_to')
            ? Carbon::parse($request->input('date_to'))->endOfDay()
            : Carbon::today()->endOfDay();
        $branchId = $request->input('branch_id');
        $depId = $request->input('dep_id');

        $branches = Branch::all();
        $departments = departmentModel::all();

        $logData = userLogModel::with(['userProfile', 'userProfile.branch', 'userProfile.department'])
            ->where('USER_ADDR', '!=', 0)
            ->when($dateFrom && $dateTo, function ($query) use ($dateFrom, $dateTo) {
                return $query->whereBetween('TM_EVENT', [$dateFrom, $dateTo]);
            })
            ->when($branchId, function ($query) use ($branchId) {
                return $query->whereHas('userProfile', function ($q) use ($branchId) {
                    $q->where('Branchid', $branchId);
                });
            })
            ->when($depId, function ($query) use ($depId) {
                return $query->whereHas('userProfile', function ($q) use ($depId) {
                    $q->where('Depid', $depId);
                });
            })
            ->orderBy('TM_EVENT', 'desc')
            ->paginate(50);

        // Calculate Employee count (Depid = 1) for today
        $employeeCount = userLogModel::where('USER_ADDR', '!=', 0)
            ->whereBetween('TM_EVENT', [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()])
            ->whereHas('userProfile', function ($q) {
                $q->where('Depid', 1);
            })
            ->distinct('USER_ADDR')
            ->count('USER_ADDR');

        // Calculate Member count (Depid = 2) for today
        $memberCount = userLogModel::where('USER_ADDR', '!=', 0)
            ->whereBetween('TM_EVENT', [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()])
            ->whereHas('userProfile', function ($q) {
                $q->where('Depid', 2);
            })
            ->distinct('USER_ADDR')
            ->count('USER_ADDR');

        return view('logData.index', compact('logData', 'dateFrom', 'dateTo', 'branches', 'branchId', 'departments', 'depId', 'employeeCount', 'memberCount'));
    }

    public function export(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $branchId = $request->input('branch_id');
        $depId = $request->input('dep_id');

        $fileName = 'User_Logs_' . ($dateFrom ?? 'today') . '_to_' . ($dateTo ?? 'today');
        if ($branchId) {
            $branch = Branch::find($branchId);
            $fileName .= '_Branch_' . ($branch ? str_replace(' ', '_', $branch->name) : 'Unknown');
        }
        if ($depId) {
            $department = departmentModel::find($depId);
            $fileName .= '_Dept_' . ($department ? str_replace(' ', '_', $department->name) : 'Unknown');
        }
        $fileName .= '.xlsx';

        return Excel::download(new UserLogsExport($dateFrom, $dateTo, $branchId, $depId), $fileName);
    }

    public function getLatestLogs(Request $request)
    {
        $lastTimestamp = $request->input('last_timestamp');
        $dateFrom = $request->input('date_from')
            ? Carbon::parse($request->input('date_from'))->startOfDay()
            : Carbon::today()->startOfDay();
        $dateTo = $request->input('date_to')
            ? Carbon::parse($request->input('date_to'))->endOfDay()
            : Carbon::today()->endOfDay();
        $branchId = $request->input('branch_id');
        $depId = $request->input('dep_id');

        $newLogs = userLogModel::with([
            'userProfile',
            'userProfile.branch',
            'userProfile.department',
            'deviceGate'
        ])
            ->where('USER_ADDR', '!=', 0)
            ->when($lastTimestamp, function ($query) use ($lastTimestamp) {
                return $query->where('TM_EVENT', '>', $lastTimestamp);
            })
            ->when($dateFrom && $dateTo, function ($query) use ($dateFrom, $dateTo) {
                return $query->whereBetween('TM_EVENT', [$dateFrom, $dateTo]);
            })
            ->when($branchId, function ($query) use ($branchId) {
                return $query->whereHas('userProfile', function ($q) use ($branchId) {
                    $q->where('Branchid', $branchId);
                });
            })
            ->when($depId, function ($query) use ($depId) {
                return $query->whereHas('userProfile', function ($q) use ($depId) {
                    $q->where('Depid', $depId);
                });
            })
            ->orderBy('TM_EVENT', 'desc')
            ->limit(50)
            ->get();

        $transformedLogs = $newLogs->map(function ($log) {
            if ($log->userProfile && $log->userProfile->photo) {
                // Remove the data:image/...;base64, prefix if present
                $log->userProfile->photo = preg_replace('/^data:image\/(jpeg|png|gif);base64,/', '', $log->userProfile->photo);
            }
            return $log;
        });

        return response()->json($transformedLogs);
    }

    public function getCounts()
    {
        $employeeCount = userLogModel::where('USER_ADDR', '!=', 0)
            ->whereBetween('TM_EVENT', [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()])
            ->whereHas('userProfile', function ($q) {
                $q->where('Depid', 1);
            })
            ->distinct('USER_ADDR')
            ->count('USER_ADDR');

        $memberCount = userLogModel::where('USER_ADDR', '!=', 0)
            ->whereBetween('TM_EVENT', [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()])
            ->whereHas('userProfile', function ($q) {
                $q->where('Depid', 2);
            })
            ->distinct('USER_ADDR')
            ->count('USER_ADDR');

        return response()->json([
            'employeeCount' => $employeeCount,
            'memberCount' => $memberCount,
        ]);
    }
}

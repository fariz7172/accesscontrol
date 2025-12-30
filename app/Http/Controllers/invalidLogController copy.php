<?php

namespace App\Http\Controllers;

use App\Exports\UserLogsExport;
use App\Exports\UserLogsExportInvalid;
use App\Models\Branch;
use App\Models\departmentModel;
use App\Models\userLogModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class invalidLogController extends Controller
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

        // Ambil semua data branch dan departemen untuk dropdown
        $branches = Branch::all();
        $departments = departmentModel::all();

        $logData = userLogModel::with(['userProfile', 'userProfile.branch', 'userProfile.department', 'deviceGate'])
            ->where('USER_ADDR', 0) // Filter USER_ADDR = 0
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

        return view('logInvalidData.index', compact('logData', 'dateFrom', 'dateTo', 'branches', 'branchId', 'departments', 'depId'));
    }

    public function export(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $branchId = $request->input('branch_id');
        $depId = $request->input('dep_id');

        $fileName = 'Invalid_User_Logs_' . ($dateFrom ?? 'today') . '_to_' . ($dateTo ?? 'today');
        if ($branchId) {
            $branch = Branch::find($branchId);
            $fileName .= '_Branch_' . ($branch ? str_replace(' ', '_', $branch->name) : 'Unknown');
        }
        if ($depId) {
            $department = departmentModel::find($depId);
            $fileName .= '_Dept_' . ($department ? str_replace(' ', '_', $department->name) : 'Unknown');
        }
        $fileName .= '.xlsx';

        return Excel::download(new UserLogsExportInvalid($dateFrom, $dateTo, $branchId, $depId), $fileName);
    }

    public function getLatestLogsInvalid(Request $request)
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

        $newLogs = userLogModel::with(['userProfile', 'userProfile.branch', 'userProfile.department', 'deviceGate'])
            ->where('USER_ADDR', 0) // Filter USER_ADDR = 0
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
                $log->userProfile->photo = base64_encode($log->userProfile->photo);
            }
            return $log;
        });

        return response()->json($transformedLogs);
    }
}

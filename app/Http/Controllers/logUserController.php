<?php

namespace App\Http\Controllers;

use App\Exports\UserActivityLogsExport;
use App\Models\Branch;
use App\Models\departmentModel;
use App\Models\userLogModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class logUserController extends Controller
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
        $search2 = $request->input('search2');

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
            ->when($search2, function ($query) use ($search2) {
                return $query->whereHas('userProfile', function ($q) use ($search2) {
                    $q->where('NAME', 'like', '%' . $search2 . '%');
                });
            })
            ->orderBy('TM_EVENT', 'desc')
            ->paginate(50);

        return view('logDataFrontend.index', compact('logData', 'dateFrom', 'dateTo', 'branches', 'branchId', 'departments', 'depId', 'search2'));
    }

    public function exportUserLogs(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $branchId = $request->input('branch_id');
        $depId = $request->input('dep_id');
        $search2 = $request->input('search2');

        // Debugging: Log parameter yang diterima
        Log::info('Export parameters in exportUserLogs:', [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'branch_id' => $branchId,
            'dep_id' => $depId,
            'search2' => $search2
        ]);

        $fileName = 'User_Activity_Logs_' . ($dateFrom ?? 'today') . '_to_' . ($dateTo ?? 'today');
        if ($branchId) {
            $branch = Branch::find($branchId);
            $fileName .= '_Branch_' . ($branch ? str_replace(' ', '_', $branch->name) : 'Unknown');
        }
        if ($depId) {
            $department = departmentModel::find($depId);
            $fileName .= '_Dept_' . ($department ? str_replace(' ', '_', $department->name) : 'Unknown');
        }
        if ($search2) {
            $fileName .= '_Search_' . str_replace(' ', '_', $search2);
        }
        $fileName .= '.xlsx';

        return Excel::download(new UserActivityLogsExport($dateFrom, $dateTo, $branchId, $depId, $search2), $fileName);
    }

    public function getLatestLogs1(Request $request)
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
        $search2 = $request->input('search2');

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
            ->when($search2, function ($query) use ($search2) {
                return $query->whereHas('userProfile', function ($q) use ($search2) {
                    $q->where('NAME', 'like', '%' . $search2 . '%');
                });
            })
            ->orderBy('TM_EVENT', 'desc')
            ->limit(50)
            ->get();

        // Transform the photo field to remove the base64 prefix
        $transformedLogs = $newLogs->map(function ($log) {
            if ($log->userProfile && $log->userProfile->photo) {
                if (str_starts_with($log->userProfile->photo, 'data:image')) {
                    preg_match('/^data:image\/(jpeg|png|gif);base64,/', $log->userProfile->photo, $matches);
                    $log->userProfile->photo = preg_replace('/^data:image\/(jpeg|png|gif);base64,/', '', $log->userProfile->photo);
                    $log->userProfile->photo_mime = $matches[1] ?? 'jpeg'; // Default to JPEG if no match
                } else {
                    $log->userProfile->photo = asset($log->userProfile->photo);
                    $log->userProfile->photo_mime = null;
                }
            }
            return $log;
        });

        return response()->json($transformedLogs);
    }
}

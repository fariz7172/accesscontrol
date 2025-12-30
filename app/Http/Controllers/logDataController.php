<?php

namespace App\Http\Controllers;

use App\Models\userLogModel;
use App\Models\Branch;
use App\Models\departmentModel;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UserLogsExport;
use App\Models\deviceGateModel;
use App\Models\userProfileModel;

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
        $deviceName = $request->input('device_name'); // Tambah ini

        $branches = Branch::all();
        $departments = departmentModel::all();

        // Tambah: Ambil semua device name yang unik & urut
        $devices = deviceGateModel::whereNotNull('name')
            ->where('name', '!=', '')
            ->orderBy('name')
            ->pluck('name', 'name')
            ->unique();

        $logData = userLogModel::with(['userProfile', 'userProfile.branch', 'userProfile.department', 'deviceGate'])
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
            ->when($deviceName, function ($query) use ($deviceName) {
                return $query->whereHas('deviceGate', function ($q) use ($deviceName) {
                    $q->where('name', $deviceName);
                });
            })
            ->orderBy('TM_EVENT', 'desc')
            ->paginate(50);

        // Hitung Employee & Member (hari ini) tetap sama
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

        return view('logData.index', compact(
            'logData',
            'dateFrom',
            'dateTo',
            'branches',
            'branchId',
            'departments',
            'depId',
            'employeeCount',
            'memberCount',
            'devices',
            'deviceName' // Tambah ini
        ));
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
        $deviceName = $request->input('device_name'); // Tambah

        $newLogs = userLogModel::with([
            'userProfile',
            'userProfile.branch',
            'userProfile.department',
            'deviceGate'
        ])
            ->where('USER_ADDR', '!=', 0)
            ->when($lastTimestamp, fn($q) => $q->where('TM_EVENT', '>', $lastTimestamp))
            ->when($dateFrom && $dateTo, fn($q) => $q->whereBetween('TM_EVENT', [$dateFrom, $dateTo]))
            ->when($branchId, fn($q) => $q->whereHas('userProfile', fn($qq) => $qq->where('Branchid', $branchId)))
            ->when($depId, fn($q) => $q->whereHas('userProfile', fn($qq) => $qq->where('Depid', $depId)))
            ->when($deviceName, fn($q) => $q->whereHas('deviceGate', fn($qq) => $qq->where('name', $deviceName)))
            ->orderBy('TM_EVENT', 'desc')
            ->limit(50)
            ->get();

        // Transform (status kartu) tetap sama
        $transformedLogs = $newLogs->map(function ($log) {
            if ($log->userProfile && $log->userProfile->photo) {
                $log->userProfile->photo = preg_replace('/^data:image\/(jpeg|png|gif);base64,/', '', $log->userProfile->photo);
            }

            $status = 'N/A';
            if ($log->userProfile && $log->TM_EVENT && $log->userProfile->BEGIN_DATE && $log->userProfile->END_DATE) {
                try {
                    $eventDate = Carbon::parse($log->TM_EVENT);
                    $beginDate = Carbon::parse($log->userProfile->BEGIN_DATE);
                    $endDate   = Carbon::parse($log->userProfile->END_DATE)->endOfDay();

                    $status = ($eventDate->lt($beginDate) || $eventDate->gt($endDate))
                        ? 'CARD EXPIRED' : 'CARD ACTIVE';
                } catch (\Exception $e) {
                    $status = 'N/A';
                }
            }
            $log->card_status = $status;
            return $log;
        });

        return response()->json($transformedLogs);
    }

    public function export(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $branchId = $request->input('branch_id');
        $depId = $request->input('dep_id');
        $deviceName = $request->input('device_name'); // Tambah

        $fileName = 'User_Logs_' . ($dateFrom ?? 'today') . '_to_' . ($dateTo ?? 'today');
        if ($branchId) {
            $branch = Branch::find($branchId);
            $fileName .= '_Branch_' . ($branch ? str_replace(' ', '_', $branch->name) : 'Unknown');
        }
        if ($depId) {
            $department = departmentModel::find($depId);
            $fileName .= '_Dept_' . ($department ? str_replace(' ', '_', $department->name) : 'Unknown');
        }
        if ($deviceName) {
            $fileName .= '_Device_' . str_replace(' ', '_', $deviceName);
        }
        $fileName .= '.xlsx';

        return Excel::download(new UserLogsExport($dateFrom, $dateTo, $branchId, $depId, $deviceName), $fileName);
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

    public function checkStatus(Request $request)
    {
        $logs = $request->input('logs', []); // Array of [USER_ADDR, TM_EVENT]
        $results = [];

        foreach ($logs as $log) {
            $userAddr = $log['USER_ADDR'] ?? null;
            $tmEvent = $log['TM_EVENT'] ?? null;

            if (!$userAddr || !$tmEvent) {
                $results[] = ['USER_ADDR' => $userAddr, 'TM_EVENT' => $tmEvent, 'status' => 'N/A'];
                continue;
            }

            $userProfile = userProfileModel::where('ID', $userAddr)->first();

            if (!$userProfile || $userProfile->ID != $userAddr) {
                $results[] = ['USER_ADDR' => $userAddr, 'TM_EVENT' => $tmEvent, 'status' => 'N/A'];
                continue;
            }

            if ($tmEvent && $userProfile->BEGIN_DATE && $userProfile->END_DATE) {
                $tmEventDate = Carbon::parse($tmEvent);
                $beginDate = Carbon::parse($userProfile->BEGIN_DATE);
                $endDate = Carbon::parse($userProfile->END_DATE);

                if ($tmEventDate->lt($beginDate) || $tmEventDate->gt($endDate)) {
                    $status = 'CARD EXPIRED';
                } else {
                    $status = 'CARD ACTIVE';
                }
            } else {
                $status = 'N/A';
            }

            $results[] = [
                'USER_ADDR' => $userAddr,
                'TM_EVENT' => $tmEvent,
                'status' => $status
            ];
        }

        return response()->json($results);
    }
}
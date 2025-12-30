<?php

namespace App\Http\Controllers;

use App\Exports\UserLogStatusEntryExport;
use App\Models\userLogModel;
use App\Models\deviceGateModel;
use App\Models\departmentModel;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class UserStatusController extends Controller
{
    public function index(Request $request)
    {
        $startDate  = $request->get('start_date', Carbon::today()->toDateString());
        $endDate    = $request->get('end_date', Carbon::today()->toDateString());
        $search     = $request->get('search');
        $department = $request->get('department');
        $branch     = $request->get('branch');
        $gate       = $request->get('gate'); // TAMBAHAN

        if (Carbon::parse($endDate)->lessThan(Carbon::parse($startDate))) {
            return redirect()->back()->with('error', 'End date must be greater than or equal to start date.');
        }

        // Dropdown data
        $departments = departmentModel::select('name')->distinct()->orderBy('name')->get();
        $branches    = Branch::select('name')->distinct()->orderBy('name')->get();
        $gates       = deviceGateModel::select('name')->distinct()->orderBy('name')->get(); // TAMBAHAN

        // === TOTAL ENTRY IN & OUT ===
        $baseCountQuery = DB::table('tbl_userlog')
            ->join('devicegate', 'tbl_userlog.DEVICESN', '=', 'devicegate.sn')
            ->join('usersprofile', 'tbl_userlog.USER_ADDR', '=', 'usersprofile.ID')
            ->leftJoin('departemen', 'usersprofile.Depid', '=', 'departemen.id')
            ->leftJoin('branch', 'usersprofile.Branchid', '=', 'branch.id')
            ->whereBetween(DB::raw('DATE(tbl_userlog.TM_EVENT)'), [$startDate, $endDate])
            ->when($department, fn($q) => $q->where('departemen.name', $department))
            ->when($branch,     fn($q) => $q->where('branch.name', $branch))
            ->when($gate,       fn($q) => $q->where('devicegate.name', $gate)); // TAMBAHAN

        $totalEntryIn  = (clone $baseCountQuery)->where('devicegate.stat', 0)->count();
        $totalEntryOut = (clone $baseCountQuery)->where('devicegate.stat', 1)->count();

        // === Latest Status per User ===
        $latestSubquery = userLogModel::selectRaw('USER_ADDR, MAX(TM_EVENT) as max_time')
            ->whereBetween(DB::raw('DATE(TM_EVENT)'), [$startDate, $endDate])
            ->groupBy('USER_ADDR');

        $userStatusesQuery = userLogModel::selectRaw('
            tbl_userlog.USER_ADDR,
            usersprofile.NAME as user_name,
            tbl_userlog.TM_EVENT as last_event,
            tbl_userlog.DEVICESN,
            devicegate.stat,
            devicegate.name as device_name,
            departemen.name as dept_name
        ')
            ->join('devicegate', 'tbl_userlog.DEVICESN', '=', 'devicegate.sn')
            ->join('usersprofile', 'tbl_userlog.USER_ADDR', '=', 'usersprofile.ID')
            ->leftJoin('departemen', 'usersprofile.Depid', '=', 'departemen.id')
            ->leftJoin('branch', 'usersprofile.Branchid', '=', 'branch.id')
            ->joinSub($latestSubquery, 'latest', function ($join) {
                $join->on('tbl_userlog.USER_ADDR', '=', 'latest.USER_ADDR')
                    ->on('tbl_userlog.TM_EVENT', '=', 'latest.max_time');
            })
            ->whereBetween(DB::raw('DATE(tbl_userlog.TM_EVENT)'), [$startDate, $endDate])
            ->when($department, fn($q) => $q->where('departemen.name', $department))
            ->when($branch,     fn($q) => $q->where('branch.name', $branch))
            ->when($gate,       fn($q) => $q->where('devicegate.name', $gate)); // TAMBAHAN

        if ($search) {
            $userStatusesQuery->where(function ($q) use ($search) {
                $q->where('usersprofile.NAME', 'like', "%{$search}%")
                    ->orWhere('usersprofile.ID', 'like', "%{$search}%")
                    ->orWhere('departemen.name', 'like', "%{$search}%")
                    ->orWhere('devicegate.name', 'like', "%{$search}%")
                    ->orWhere('tbl_userlog.DEVICESN', 'like', "%{$search}%");
                if (is_numeric($search)) {
                    $q->orWhere('usersprofile.Depid', '=', intval($search));
                }
            });
        }

        $userStatuses = $userStatusesQuery->orderBy('last_event', 'desc')->get();

        foreach ($userStatuses as $status) {
            $status->status_display = ($status->stat == 0) ? 'IN' : 'OUT';
            $status->last_event_formatted = Carbon::parse($status->last_event)->format('Y-m-d H:i:s');
        }

        // === All User Logs (paginated) ===
        $allUserLogsQuery = userLogModel::selectRaw('
            tbl_userlog.USER_ADDR,
            tbl_userlog.TM_EVENT,
            tbl_userlog.DEVICESN,
            tbl_userlog.card,
            usersprofile.NAME as user_name,
            devicegate.name as device_name,
            devicegate.stat,
            departemen.name as dept_name
        ')
            ->join('usersprofile', 'tbl_userlog.USER_ADDR', '=', 'usersprofile.ID')
            ->join('devicegate', 'tbl_userlog.DEVICESN', '=', 'devicegate.sn')
            ->leftJoin('departemen', 'usersprofile.Depid', '=', 'departemen.id')
            ->leftJoin('branch', 'usersprofile.Branchid', '=', 'branch.id')
            ->whereBetween(DB::raw('DATE(tbl_userlog.TM_EVENT)'), [$startDate, $endDate])
            ->when($department, fn($q) => $q->where('departemen.name', $department))
            ->when($branch,     fn($q) => $q->where('branch.name', $branch))
            ->when($gate,       fn($q) => $q->where('devicegate.name', $gate)); // TAMBAHAN

        if ($search) {
            $allUserLogsQuery->where(function ($q) use ($search) {
                $q->where('usersprofile.NAME', 'like', "%{$search}%")
                    ->orWhere('usersprofile.ID', 'like', "%{$search}%")
                    ->orWhere('departemen.name', 'like', "%{$search}%")
                    ->orWhere('devicegate.name', 'like', "%{$search}%")
                    ->orWhere('tbl_userlog.DEVICESN', 'like', "%{$search}%");
                if (is_numeric($search)) {
                    $q->orWhere('usersprofile.Depid', '=', intval($search));
                }
            });
        }

        $allUserLogs = $allUserLogsQuery->orderBy('tbl_userlog.TM_EVENT', 'desc')
            ->paginate(100)
            ->appends($request->query());

        foreach ($allUserLogs as $log) {
            $log->tm_event_formatted = Carbon::parse($log->TM_EVENT)->format('Y-m-d H:i:s');
            $log->status_display = ($log->stat == 0) ? 'IN' : 'OUT';
        }

        return view('userstatus.index', compact(
            'userStatuses',
            'startDate',
            'endDate',
            'totalEntryIn',
            'totalEntryOut',
            'allUserLogs',
            'search',
            'departments',
            'branches',
            'gates',        // TAMBAHAN
            'department',
            'branch',
            'gate'          // TAMBAHAN
        ));
    }

    public function getLatestStatuses(Request $request)
    {
        $startDate  = $request->get('start_date', Carbon::today()->toDateString());
        $endDate    = $request->get('end_date', Carbon::today()->toDateString());
        $search     = $request->get('search');
        $department = $request->get('department');
        $branch     = $request->get('branch');
        $gate       = $request->get('gate'); // TAMBAHAN

        if (Carbon::parse($endDate)->lessThan(Carbon::parse($startDate))) {
            return response()->json(['success' => false, 'message' => 'End date must be greater than or equal to start date.'], 400);
        }

        // Dropdown data untuk AJAX (opsional, kalau butuh refresh dropdown)
        $gates = deviceGateModel::select('name')->distinct()->orderBy('name')->get();

        // === TOTAL ENTRY IN & OUT ===
        $baseCountQuery = DB::table('tbl_userlog')
            ->join('devicegate', 'tbl_userlog.DEVICESN', '=', 'devicegate.sn')
            ->join('usersprofile', 'tbl_userlog.USER_ADDR', '=', 'usersprofile.ID')
            ->leftJoin('departemen', 'usersprofile.Depid', '=', 'departemen.id')
            ->leftJoin('branch', 'usersprofile.Branchid', '=', 'branch.id')
            ->whereBetween(DB::raw('DATE(tbl_userlog.TM_EVENT)'), [$startDate, $endDate])
            ->when($department, fn($q) => $q->where('departemen.name', $department))
            ->when($branch,     fn($q) => $q->where('branch.name', $branch))
            ->when($gate,       fn($q) => $q->where('devicegate.name', $gate));

        $totalEntryIn  = (clone $baseCountQuery)->where('devicegate.stat', 0)->count();
        $totalEntryOut = (clone $baseCountQuery)->where('devicegate.stat', 1)->count();

        // === Latest Status per User ===
        $latestSubquery = userLogModel::selectRaw('USER_ADDR, MAX(TM_EVENT) as max_time')
            ->whereBetween(DB::raw('DATE(TM_EVENT)'), [$startDate, $endDate])
            ->groupBy('USER_ADDR');

        $userStatusesQuery = userLogModel::selectRaw('
            tbl_userlog.USER_ADDR,
            usersprofile.NAME as user_name,
            tbl_userlog.TM_EVENT as last_event,
            devicegate.stat,
            devicegate.name as device_name,
            departemen.name as dept_name,
            usersprofile.END_DATE
        ')
            ->join('devicegate', 'tbl_userlog.DEVICESN', '=', 'devicegate.sn')
            ->join('usersprofile', 'tbl_userlog.USER_ADDR', '=', 'usersprofile.ID')
            ->leftJoin('departemen', 'usersprofile.Depid', '=', 'departemen.id')
            ->leftJoin('branch', 'usersprofile.Branchid', '=', 'branch.id')
            ->joinSub($latestSubquery, 'latest', function ($join) {
                $join->on('tbl_userlog.USER_ADDR', '=', 'latest.USER_ADDR')
                    ->on('tbl_userlog.TM_EVENT', '=', 'latest.max_time');
            })
            ->whereBetween(DB::raw('DATE(tbl_userlog.TM_EVENT)'), [$startDate, $endDate])
            ->when($department, fn($q) => $q->where('departemen.name', $department))
            ->when($branch,     fn($q) => $q->where('branch.name', $branch))
            ->when($gate,       fn($q) => $q->where('devicegate.name', $gate))
            ->where(function ($q) {
                $q->whereNull('usersprofile.END_DATE')
                    ->orWhereRaw('tbl_userlog.TM_EVENT <= usersprofile.END_DATE');
            });

        if ($search) {
            $userStatusesQuery->where(function ($q) use ($search) {
                $q->where('usersprofile.NAME', 'like', "%{$search}%")
                    ->orWhere('usersprofile.ID', 'like', "%{$search}%")
                    ->orWhere('departemen.name', 'like', "%{$search}%")
                    ->orWhere('devicegate.name', 'like', "%{$search}%")
                    ->orWhere('tbl_userlog.DEVICESN', 'like', "%{$search}%");
                if (is_numeric($search)) $q->orWhere('usersprofile.Depid', '=', intval($search));
            });
        }

        $userStatuses = $userStatusesQuery->orderBy('last_event', 'desc')->get();

        foreach ($userStatuses as $status) {
            $status->status_display = ($status->stat == 0) ? 'IN' : 'OUT';
            $status->last_event_formatted = Carbon::parse($status->last_event)->format('Y-m-d H:i:s');
        }

        $totalIn  = $userStatuses->where('status_display', 'IN')->count();
        $totalOut = $userStatuses->where('status_display', 'OUT')->count();

        // === All Logs ===
        $allUserLogsQuery = userLogModel::selectRaw('
            tbl_userlog.USER_ADDR,
            tbl_userlog.TM_EVENT,
            tbl_userlog.DEVICESN,
            tbl_userlog.card,
            usersprofile.NAME as user_name,
            devicegate.name as device_name,
            devicegate.stat,
            departemen.name as dept_name,
            usersprofile.END_DATE
        ')
            ->join('usersprofile', 'tbl_userlog.USER_ADDR', '=', 'usersprofile.ID')
            ->join('devicegate', 'tbl_userlog.DEVICESN', '=', 'devicegate.sn')
            ->leftJoin('departemen', 'usersprofile.Depid', '=', 'departemen.id')
            ->leftJoin('branch', 'usersprofile.Branchid', '=', 'branch.id')
            ->whereBetween(DB::raw('DATE(tbl_userlog.TM_EVENT)'), [$startDate, $endDate])
            ->when($department, fn($q) => $q->where('departemen.name', $department))
            ->when($branch,     fn($q) => $q->where('branch.name', $branch))
            ->when($gate,       fn($q) => $q->where('devicegate.name', $gate));

        if ($search) {
            $allUserLogsQuery->where(function ($q) use ($search) {
                $q->where('usersprofile.NAME', 'like', "%{$search}%")
                    ->orWhere('usersprofile.ID', 'like', "%{$search}%")
                    ->orWhere('departemen.name', 'like', "%{$search}%")
                    ->orWhere('devicegate.name', 'like', "%{$search}%")
                    ->orWhere('tbl_userlog.DEVICESN', 'like', "%{$search}%");
                if (is_numeric($search)) $q->orWhere('usersprofile.Depid', '=', intval($search));
            });
        }

        $allUserLogs = $allUserLogsQuery->orderBy('tbl_userlog.TM_EVENT', 'desc')
            ->paginate(100)
            ->appends($request->query());

        foreach ($allUserLogs as $log) {
            $log->tm_event_formatted = Carbon::parse($log->TM_EVENT)->format('Y-m-d H:i:s');

            $eventTime = Carbon::parse($log->TM_EVENT);
            $endDate   = $log->END_DATE ? Carbon::parse($log->END_DATE) : null;

            if ($endDate && $eventTime->gt($endDate)) {
                $log->status_display = 'EXPIRED';
            } else {
                $log->status_display = ($log->stat == 0) ? 'IN' : 'OUT';
            }
        }

        return response()->json([
            'userStatuses'     => $userStatuses,
            'total_in'         => $totalIn,
            'total_out'        => $totalOut,
            'total_entry_in'   => $totalEntryIn,
            'total_entry_out'  => $totalEntryOut,
            'allUserLogs'      => $allUserLogs->items(),
            'pagination'       => [
                'current_page' => $allUserLogs->currentPage(),
                'last_page'    => $allUserLogs->lastPage(),
                'per_page'     => $allUserLogs->perPage(),
                'total'        => $allUserLogs->total(),
                'links'        => $allUserLogs->links()->toHtml(),
            ],
            'gates'            => $gates->pluck('name'),
            'gate'             => $gate,
            'start_date'       => $startDate,
            'end_date'         => $endDate,
            'search'           => $search,
            'department'       => $department,
            'branch'           => $branch
        ]);
    }

    // method updateStatus tetap sama
    public function updateStatus(Request $request)
    {
        try {
            $request->validate([
                'devicesn'   => 'required|string',
                'user_addr'  => 'required|integer',
            ]);

            $device = deviceGateModel::where('sn', $request->devicesn)->first();
            if (!$device) {
                return response()->json(['success' => false, 'message' => 'Device tidak ditemukan'], 404);
            }

            $stat   = $device->stat;
            $status = ($stat == 0) ? 'IN' : 'OUT';

            return response()->json([
                'success' => true,
                'message' => "Status updated: {$status}",
                'stat'    => $stat,
                'status'  => $status
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }


    public function hourly(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->toDateString());
        $endDate   = $request->get('end_date', Carbon::today()->toDateString());
        $gate      = $request->get('gate');

        if (Carbon::parse($endDate)->lessThan(Carbon::parse($startDate))) {
            return redirect()->back()->with('error', 'End date harus ≥ start date.');
        }

        $gates = deviceGateModel::select('name')->distinct()->orderBy('name')->get();

        // PERBAIKAN DI SINI: Gunakan tbl_userlog.stat, bukan devicegate.stat
        $hourlyData = DB::table('tbl_userlog')
            ->join('devicegate', 'tbl_userlog.DEVICESN', '=', 'devicegate.sn')
            ->selectRaw("
            HOUR(tbl_userlog.TM_EVENT) as hour,
            COUNT(*) as total_access,
            SUM(CASE WHEN tbl_userlog.stat = 0 THEN 1 ELSE 0 END) as valid_in,
            SUM(CASE WHEN tbl_userlog.stat = 1 THEN 1 ELSE 0 END) as invalid_out,
            GROUP_CONCAT(DISTINCT devicegate.name ORDER BY devicegate.name SEPARATOR ', ') as devices
        ")
            ->whereBetween(DB::raw('DATE(tbl_userlog.TM_EVENT)'), [$startDate, $endDate])
            ->when($gate, fn($q) => $q->where('devicegate.name', $gate))
            ->groupBy(DB::raw('HOUR(tbl_userlog.TM_EVENT)'))
            ->orderBy('hour')
            ->get();

        // Buat 24 jam lengkap
        $fullHours = collect(range(0, 23))->map(function ($hour) use ($hourlyData) {
            $data = $hourlyData->firstWhere('hour', $hour);
            return [
                'hour'         => $hour,
                'hour_display' => sprintf('%02d:00 - %02d:59', $hour, $hour),
                'total_access' => $data->total_access ?? 0,
                'valid_in'     => $data->valid_in ?? 0,
                'invalid_out'  => $data->invalid_out ?? 0,
                'devices'      => $data->devices ?? '-',
            ];
        });

        return view('userstatus.hourly', compact(
            'fullHours',
            'startDate',
            'endDate',
            'gate',
            'gates'
        ));
    }

    public function exportExcel(Request $request)
    {
        $filename = 'Log_status_Entry' . Carbon::now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new UserLogStatusEntryExport($request), $filename);
    }
}
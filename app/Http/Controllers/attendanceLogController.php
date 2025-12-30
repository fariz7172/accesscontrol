<?php

namespace App\Http\Controllers;

use App\Exports\AttendanceLogsExport;
use App\Models\Branch;
use App\Models\departmentModel;
use App\Models\deviceGateModel;
use App\Models\userLogModel;
use App\Models\userProfileModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class attendanceLogController extends Controller
{

    public function index(Request $request)
    {
        $today      = Carbon::today()->format('Y-m-d');

        $dateFrom   = $request->input('date_from', $today);
        $dateTo     = $request->input('date_to', $today);
        $branchId   = $request->input('branch_id');
        $depId      = $request->input('dep_id');
        $deviceName = $request->input('device_name');
        $status     = $request->input('status');
        $perPage    = $request->input('per_page', 50);

        $startDate  = Carbon::parse($dateFrom)->startOfDay();
        $endDate    = Carbon::parse($dateTo)->endOfDay();

        // Daftar tanggal dalam rentang
        $dateRange = [];
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $dateRange[] = $current->format('Y-m-d');
            $current->addDay();
        }

        // Ambil semua device name untuk filter
        $devices = deviceGateModel::whereNotNull('name')
            ->where('name', '!=', '')
            ->orderBy('name')
            ->pluck('name', 'name');

        // Query profil user
        $usersQuery = userProfileModel::query()
            ->select('usersprofile.ID', 'usersprofile.NAME', 'usersprofile.Card', 'departemen.name as department_name')
            ->leftJoin('departemen', 'usersprofile.Depid', '=', 'departemen.id');

        if ($branchId) $usersQuery->where('usersprofile.Branchid', $branchId);
        if ($depId)    $usersQuery->where('usersprofile.Depid', $depId);

        $users      = $usersQuery->get();
        $totalUsers = $users->count();

        // Query log absensi — HANYA stat = 0 yang dianggap HADIR (Present)
        $logsQuery = userLogModel::query()
            ->select('tbl_userlog.USER_ADDR', 'tbl_userlog.TM_EVENT', 'tbl_userlog.DEVICESN')
            ->whereBetween('tbl_userlog.TM_EVENT', [$startDate, $endDate])
            ->where('tbl_userlog.stat', 0); // <-- INI KUNCI: stat = 0 = Masuk = Present

        // Join + filter jika diperlukan
        if ($branchId || $depId || $deviceName) {
            $logsQuery->join('usersprofile', 'tbl_userlog.USER_ADDR', '=', 'usersprofile.ID');

            if ($branchId)   $logsQuery->where('usersprofile.Branchid', $branchId);
            if ($depId)      $logsQuery->where('usersprofile.Depid', $depId);
            if ($deviceName) $logsQuery->whereHas('deviceGate', fn($q) => $q->where('name', $deviceName));
        }

        $attendanceLogs = $logsQuery->get()
            ->groupBy('USER_ADDR')
            ->map(function ($userLogs) {
                return $userLogs->groupBy(fn($log) => Carbon::parse($log->TM_EVENT)->format('Y-m-d'))
                    ->map(fn($dayLogs) => $dayLogs->sortBy('TM_EVENT')->first());
            });

        // Build detail attendance
        $attendanceData = [];
        $no = ($request->input('page', 1) - 1) * $perPage + 1;

        foreach ($dateRange as $date) {
            foreach ($users as $user) {
                $log  = $attendanceLogs->get($user->ID, collect())->get($date);
                $desc = $log ? 'Present' : 'Not Present';

                if ($status && $desc !== $status) continue;

                $attendanceData[] = [
                    'no'         => $no++,
                    'name'       => $user->NAME,
                    'member'     => $user->Card ?? '-',
                    'attend'     => $log ? Carbon::parse($log->TM_EVENT)->format('d/m/Y H:i:s') : '-',
                    'department' => $user->department_name ?? 'N/A',
                    'desc'       => $desc,
                ];
            }
        }

        // Pagination manual
        $currentPage = $request->input('page', 1);
        $pagedData   = array_slice($attendanceData, ($currentPage - 1) * $perPage, $perPage);
        $attendanceData = new \Illuminate\Pagination\LengthAwarePaginator(
            $pagedData,
            count($attendanceData),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Summary harian
        $dayNames = [
            'Sunday'    => 'Sunday',
            'Monday'    => 'Monday',
            'Tuesday'   => 'Tuesday',
            'Wednesday' => 'Wednesday',
            'Thursday'  => 'Thursday',
            'Friday'    => 'Friday',
            'Saturday'  => 'Saturday'
        ];

        $summaryData = [];
        foreach ($dateRange as $date) {
            $carbon   = Carbon::parse($date);
            $present  = $attendanceLogs->filter(fn($logs) => $logs->has($date))->count();
            $absent   = $totalUsers - $present;
            $percent  = $totalUsers > 0 ? round(($present / $totalUsers) * 100, 2) : 0;

            $summaryData[] = [
                'day'                  => $dayNames[$carbon->englishDayOfWeek] . ' (' . $carbon->format('d/m/Y') . ')',
                'total_present'        => $present,
                'total_not_present'    => $absent,
                'attendance_percentage' => $percent . '%',
            ];
        }

        $branches    = Branch::all();
        $departments = departmentModel::all();

        return view('AttendanceLog.index', compact(
            'attendanceData',
            'summaryData',
            'branches',
            'departments',
            'devices',
            'dateFrom',
            'dateTo',
            'branchId',
            'depId',
            'deviceName',
            'status',
            'perPage'
        ));
    }

    public function export(Request $request)
    {
        // Logika sama persis dengan index(), tapi tanpa pagination
        // (Copy dari atas sampai $summaryData)

        $dateFrom   = $request->input('date_from', now()->format('Y-m-d'));
        $dateTo     = $request->input('date_to', now()->format('Y-m-d'));
        $branchId   = $request->input('branch_id');
        $depId      = $request->input('dep_id');
        $deviceName = $request->input('device_name');
        $status     = $request->input('status');

        $startDate = Carbon::parse($dateFrom)->startOfDay();
        $endDate   = Carbon::parse($dateTo)->endOfDay();

        $dateRange = [];
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $dateRange[] = $current->format('Y-m-d');
            $current->addDay();
        }

        $usersQuery = userProfileModel::query()
            ->select('usersprofile.ID', 'usersprofile.NAME', 'usersprofile.Card', 'departemen.name as department_name')
            ->leftJoin('departemen', 'usersprofile.Depid', '=', 'departemen.id');
        if ($branchId) $usersQuery->where('usersprofile.Branchid', $branchId);
        if ($depId)    $usersQuery->where('usersprofile.Depid', $depId);
        $users      = $usersQuery->get();
        $totalUsers = $users->count();

        $logsQuery = userLogModel::query()
            ->whereBetween('TM_EVENT', [$startDate, $endDate])
            ->where('stat', 0);

        if ($branchId || $depId || $deviceName) {
            $logsQuery->join('usersprofile', 'tbl_userlog.USER_ADDR', '=', 'usersprofile.ID');
            if ($branchId)   $logsQuery->where('usersprofile.Branchid', $branchId);
            if ($depId)      $logsQuery->where('usersprofile.Depid', $depId);
            if ($deviceName) $logsQuery->whereHas('deviceGate', fn($q) => $q->where('name', $deviceName));
        }

        $attendanceLogs = $logsQuery->get()
            ->groupBy('USER_ADDR')
            ->map(fn($logs) => $logs->groupBy(fn($l) => Carbon::parse($l->TM_EVENT)->format('Y-m-d'))
                ->map(fn($day) => $day->sortBy('TM_EVENT')->first()));

        $attendanceData = [];
        $no = 1;
        foreach ($dateRange as $date) {
            foreach ($users as $user) {
                $log  = $attendanceLogs->get($user->ID, collect())->get($date);
                $desc = $log ? 'Present' : 'Not Present';
                if ($status && $desc !== $status) continue;

                $attendanceData[] = [
                    'no'         => $no++,
                    'name'       => $user->NAME,
                    'member'     => $user->Card ?? '-',
                    'attend'     => $log ? Carbon::parse($log->TM_EVENT)->format('d/m/Y H:i:s') : '-',
                    'department' => $user->department_name ?? 'N/A',
                    'desc'       => $desc,
                ];
            }
        }

        // Summary untuk export
        $summaryData = [];
        $dayNames = ['Sunday' => 'Sunday', 'Monday' => 'Monday', 'Tuesday' => 'Tuesday', 'Wednesday' => 'Wednesday', 'Thursday' => 'Thursday', 'Friday' => 'Friday', 'Saturday' => 'Saturday'];
        foreach ($dateRange as $date) {
            $c = Carbon::parse($date);
            $present = $attendanceLogs->filter(fn($l) => $l->has($date))->count();
            $percent = $totalUsers > 0 ? round(($present / $totalUsers) * 100, 2) : 0;

            $summaryData[] = [
                'day'                  => $dayNames[$c->englishDayOfWeek] . ' (' . $c->format('d/m/Y') . ')',
                'total_present'        => $present,
                'total_not_present'    => $totalUsers - $present,
                'attendance_percentage' => $percent . '%',
            ];
        }

        return Excel::download(
            new AttendanceLogsExport($attendanceData, $summaryData),
            'Attendance_Report_' . $dateFrom . '_to_' . $dateTo . '.xlsx'
        );
    }
}
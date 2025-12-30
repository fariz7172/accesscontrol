<?php

namespace App\Http\Controllers;

use App\Models\cardModel;
use App\Models\deviceGateModel;
use App\Models\guestLog2Model;
use App\Models\GuestModel;
use App\Models\User;
use App\Models\userProfileModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stats = $this->calculateDailyStats();

        // Map for view compatibility if needed, though view now uses new variable names
        $data = array_merge($stats, [
            'adminCount' => $stats['totalEmployee'],
            'memberCount' => $stats['presentCount'],
            'deviceCount' => $stats['lateCount'],
        ]);

        return view('dashboard', $data);
    }

    public function getMetrics()
    {
        $stats = $this->calculateDailyStats();
        return response()->json($stats);
    }

    private function calculateDailyStats()
    {
        $today = now()->format('Y-m-d');

        // 1. Total Employee
        $totalEmployee = userProfileModel::count();

        // 2. Present Today
        $presentUserIds = \DB::table('tbl_userlog')
            ->join('attend', function ($join) use ($today) {
                $join->on('tbl_userlog.USER_ADDR', '=', 'attend.EmployeeID')
                    ->where('attend.AttDate', '=', $today);
            })
            ->whereDate('tbl_userlog.TM_EVENT', '=', $today)
            ->distinct()
            ->pluck('tbl_userlog.USER_ADDR');

        $presentCount = $presentUserIds->count();

        // 3. Late Today
        $lateCount = 0;
        $attendances = \App\Models\AttendModel::with('shift')
            ->whereIn('EmployeeID', $presentUserIds)
            ->where('AttDate', $today)
            ->get()
            ->keyBy('EmployeeID');

        foreach ($presentUserIds as $userId) {
            $attendance = $attendances->get($userId);
            
            if (!$attendance || !$attendance->shift) {
                continue;
            }

            $earliestLog = \App\Models\userLogModel::where('USER_ADDR', $userId)
                ->whereDate('TM_EVENT', $today)
                ->orderBy('TM_EVENT', 'asc')
                ->first();

            if ($earliestLog) {
                $logTime = \Carbon\Carbon::parse($earliestLog->TM_EVENT)->format('H:i:s');
                $beginTime = $attendance->shift->Begin_Time;

                if ($logTime > $beginTime) {
                    $lateCount++;
                }
            }
        }

        // 4. Absent
        $absentCount = $totalEmployee - $presentCount;
        if ($absentCount < 0) $absentCount = 0;

        return [
            'totalEmployee' => $totalEmployee,
            'presentCount' => $presentCount,
            'lateCount' => $lateCount,
            'absentCount' => $absentCount,
        ];
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

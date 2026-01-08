<?php

namespace App\Http\Controllers;

use App\Models\userProfileModel;
use App\Models\userLogModel;
use Illuminate\Http\Request;

class rekapController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = userProfileModel::all(); // Fetch all users

        // Get the selected month from the request (default to January if not set)
        $selectedMonth = $request->input('month', 1); // 1 = January, 2 = February, etc.
        $year = now()->year; // Use the current year (2025 as per the current date)

        // Calculate the number of days in the selected month
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $year);

        // Array of month names in Indonesian
        $monthNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];
        $selectedMonthName = $monthNames[$selectedMonth];

        // Fetch one log per USER_ADDR for the selected month and year
        $userLogs = userLogModel::whereYear('TM_EVENT', $year)
            ->whereMonth('TM_EVENT', $selectedMonth)
            ->select('USER_ADDR', 'TM_EVENT')
            ->groupBy('USER_ADDR', 'TM_EVENT') // Group to ensure one record per USER_ADDR per date
            ->get()
            ->groupBy('USER_ADDR'); // Group by USER_ADDR for easier access in the view

        // Pass the data to the view
        return view('rekap.index', compact('user', 'selectedMonth', 'daysInMonth', 'selectedMonthName', 'userLogs'));
    }
}

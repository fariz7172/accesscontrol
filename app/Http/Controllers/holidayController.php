<?php

namespace App\Http\Controllers;

use App\Exports\HolidayExport;
use App\Models\AttendModel;
use App\Models\HolidayCal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class holidayController extends Controller
{
    public function index(Request $request)
    {
        $query = HolidayCal::query();

        // Apply date range filter if provided
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate && $endDate) {
            $query->whereBetween('StartDate', [$startDate, $endDate])
                ->orWhereBetween('EndDate', [$startDate, $endDate])
                ->orWhere(function ($q) use ($startDate, $endDate) {
                    $q->where('StartDate', '<=', $startDate)
                        ->where('EndDate', '>=', $endDate);
                });
        }

        $holiday = $query->paginate(10);

        // Preserve filter parameters in pagination links
        $holiday->appends(['start_date' => $startDate, 'end_date' => $endDate]);

        return view('holiday.index', compact('holiday', 'startDate', 'endDate'));
    }

    public function create()
    {
        return view('holiday.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'Name' => 'required|string|max:150',
            'StartDate' => 'required|date',
            'EndDate' => 'required|date|after_or_equal:StartDate',
        ]);

        try {
            HolidayCal::create($request->only(['Name', 'StartDate', 'EndDate']));
            return redirect()->route('holiday.index')
                ->with('success', 'Holiday created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating holiday: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create holiday. Please try again.');
        }
    }

    public function show($holiday)
    {
        $holiday = HolidayCal::findOrFail($holiday);
        return view('holiday.show', compact('holiday'));
    }

    public function edit($holiday)
    {
        $holiday = HolidayCal::findOrFail($holiday);
        return view('holiday.edit', compact('holiday'));
    }

    public function update(Request $request, $holiday)
    {
        $request->validate([
            'Name' => 'required|string|max:150',
            'StartDate' => 'required|date',
            'EndDate' => 'required|date|after_or_equal:StartDate',
        ]);

        try {
            // Find the holiday record
            $holiday = HolidayCal::findOrFail($holiday);

            // Get original dates before update
            $originalStartDate = $holiday->StartDate;
            $originalEndDate = $holiday->EndDate;

            // Update holiday record
            $holiday->update($request->only(['Name', 'StartDate', 'EndDate']));

            // Update attend records for original date range (set DayType to 1 and Remark to null)
            AttendModel::whereBetween('AttDate', [$originalStartDate, $originalEndDate])
                ->where('DayType', 2)
                ->update([
                    'DayType' => 1,
                    'Remark' => null
                ]);

            // Update attend records for new date range (set DayType to 2)
            AttendModel::whereBetween('AttDate', [$request->StartDate, $request->EndDate])
                ->update([
                    'DayType' => 2,
                    'Remark' => 'Holiday'
                ]);

            return redirect()->route('holiday.index')
                ->with('success', 'Holiday updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating holiday: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update holiday. Please try again.');
        }
    }

    public function destroy($holiday)
    {
        try {
            $holiday = HolidayCal::findOrFail($holiday);

            // Get the date range before deletion
            $startDate = $holiday->StartDate;
            $endDate = $holiday->EndDate;

            // Delete the holiday record
            $holiday->delete();

            // Update attend records for the holiday date range
            AttendModel::whereBetween('AttDate', [$startDate, $endDate])
                ->where('DayType', 2)
                ->update([
                    'DayType' => 1,
                    'Remark' => null
                ]);

            return redirect()->route('holiday.index')
                ->with('success', 'Holiday deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting holiday: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete holiday. Please try again.');
        }
    }

    public function export(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if (!$startDate || !$endDate) {
            return redirect()->back()->with('error', 'Please provide both start and end dates for export.');
        }

        $export = new HolidayExport($startDate, $endDate);
        if ($export->collection()->isEmpty()) {
            return redirect()->back()->with('error', 'No holiday data available to export for the selected date range.');
        }

        return Excel::download($export, 'holidays_' . $startDate . '_to_' . $endDate . '.xlsx');
    }
}

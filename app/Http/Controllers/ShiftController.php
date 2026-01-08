<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\ShiftPattern;
use App\Models\userProfileModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::all();
        $patterns = ShiftPattern::all(); // For shiftpattern.index
        $users = userProfileModel::with('shiftPattern')->get(); // For addshiftuser.index

        return view('shift.index', compact('shifts', 'patterns', 'users'));
    }

    // Other methods (create, store, edit, update, destroy) remain unchanged
    public function create()
    {
        return view('shift.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'ShiftNo' => 'required|string|max:50|unique:shift,ShiftNo',
            'ShiftName' => 'required|string|max:255',
            'Begin_Time' => 'nullable|date_format:H:i',
            'Break_Time' => 'nullable|date_format:H:i',
            'Resume_Time' => 'nullable|date_format:H:i',
            'Out_time' => 'nullable|date_format:H:i',
            'Start_In' => 'nullable|date_format:H:i',
            'Start_Break' => 'nullable|date_format:H:i',
            'Start_Resume' => 'nullable|date_format:H:i',
            'Start_Out' => 'nullable|date_format:H:i',
            'Range_In' => 'nullable|date_format:H:i',
            'Range_Break' => 'nullable|date_format:H:i',
            'Range_Resume' => 'nullable|date_format:H:i',
            'Range_Out' => 'nullable|date_format:H:i',
            'Tipe' => 'required|in:1,2',
            'DDay' => 'nullable|string|max:50',
        ]);

        try {
            $data = $request->all();

            // Format time fields to HH:mm:ss
            $timeFields = [
                'Begin_Time',
                'Break_Time',
                'Resume_Time',
                'Out_time',
                'Start_In',
                'Start_Break',
                'Start_Resume',
                'Start_Out',
                'Range_In',
                'Range_Break',
                'Range_Resume',
                'Range_Out'
            ];

            if ($data['Tipe'] == 2) {
                // Set all time fields to 00:00:00 for Off Days
                foreach ($timeFields as $field) {
                    $data[$field] = '00:00:00';
                }
            } else {
                // Append :00 to time fields for Working Days
                foreach ($timeFields as $field) {
                    if (!empty($data[$field])) {
                        $data[$field] = $data[$field] . ':00';
                    } else {
                        $data[$field] = null; // Or '00:00:00' if null is not allowed
                    }
                }
            }

            Shift::create($data);
            return redirect()->route('shift.index')
                ->with('success', 'Shift created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating shift: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to create shift. Please try again.');
        }
    }


    public function edit($id)
    {
        $shift = Shift::findOrFail($id);
        return view('shift.edit', compact('shift'));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'ShiftNo' => 'required|string|max:50|unique:shift,ShiftNo,' . $id . ',id',
            'ShiftName' => 'required|string|max:255',
            'Begin_Time' => 'nullable|date_format:H:i',
            'Break_Time' => 'nullable|date_format:H:i',
            'Resume_Time' => 'nullable|date_format:H:i',
            'Out_time' => 'nullable|date_format:H:i',
            'Start_In' => 'nullable|date_format:H:i',
            'Start_Break' => 'nullable|date_format:H:i',
            'Start_Resume' => 'nullable|date_format:H:i',
            'Start_Out' => 'nullable|date_format:H:i',
            'Range_In' => 'nullable|date_format:H:i',
            'Range_Break' => 'nullable|date_format:H:i',
            'Range_Resume' => 'nullable|date_format:H:i',
            'Range_Out' => 'nullable|date_format:H:i',
            'Tipe' => 'required|in:1,2',
            'DDay' => 'required|in:1,2',
        ]);

        try {
            $shift = Shift::findOrFail($id);
            $data = $request->all();

            $timeFields = [
                'Begin_Time',
                'Break_Time',
                'Resume_Time',
                'Out_time',
                'Start_In',
                'Start_Break',
                'Start_Resume',
                'Start_Out',
                'Range_In',
                'Range_Break',
                'Range_Resume',
                'Range_Out'
            ];

            if ($data['Tipe'] == 2) {
                foreach ($timeFields as $field) {
                    $data[$field] = '00:00:00';
                }
            } else {
                foreach ($timeFields as $field) {
                    if (!empty($data[$field]) && strlen($data[$field]) == 5) {
                        $data[$field] = $data[$field] . ':00';
                    } else {
                        $data[$field] = null;
                    }
                }
            }

            $shift->update($data);
            return redirect()->route('shift.index')
                ->with('success', 'Shift updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating shift: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to update shift. Please try again.');
        }
    }


    public function destroy($id)
    {
        try {
            $shift = Shift::findOrFail($id);
            $shift->delete();
            return redirect()->route('shift.index')
                ->with('success', 'Shift deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting shift: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete shift. Please try again.');
        }
    }
}
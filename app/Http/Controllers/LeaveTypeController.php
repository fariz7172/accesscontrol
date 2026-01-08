<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeaveTypeController extends Controller
{
    public function index()
    {
        $leaveTypes = LeaveType::all();
        Log::info('Leave types index loaded', ['leaveTypes_count' => $leaveTypes->count()]);
        return view('leavetype.index', compact('leaveTypes'));
    }

    public function create()
    {
        return view('leavetype.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'Name' => 'required|string|max:255',
            'Type' => 'required|integer|min:0',
        ]);

        try {
            Log::debug('Attempting to create leave type', ['input' => $request->only(['Name', 'Type'])]);

            $leaveType = LeaveType::create($request->only(['Name', 'Type']));
            Log::info('Leave type created successfully', ['id' => $leaveType->Id, 'name' => $leaveType->Name, 'type' => $leaveType->Type]);

            return redirect()->route('leaveprocess.index')
                ->with('success', 'Leave type created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating leave type', [
                'error' => $e->getMessage(),
                'input' => $request->only(['Name', 'Type']),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Failed to create leave type: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $leaveType = LeaveType::findOrFail($id);
        return view('leavetype.edit', compact('leaveType'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'Name' => 'required|string|max:255',
            'Type' => 'required|integer',
        ]);

        try {
            $leaveType = LeaveType::findOrFail($id);
            $leaveType->update($request->only(['Name', 'Type']));
            return redirect()->route('leaveprocess.index')
                ->with('success', 'Leave type updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating leave type: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update leave type. Please try again.');
        }
    }

    public function destroy($id)
    {
        try {
            $leaveType = LeaveType::findOrFail($id);
            if ($leaveType->leaveProcesses()->count() > 0) {
                return redirect()->back()->with('error', 'Cannot delete leave type because it is associated with leave requests.');
            }
            $leaveType->delete();
            return redirect()->route('leaveprocess.index')
                ->with('success', 'Leave type deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting leave type: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete leave type. Please try again.');
        }
    }
}

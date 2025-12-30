<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\ShiftPattern;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShiftPatternController extends Controller

{
    public function index()
    {
        $patterns = ShiftPattern::all();
        return view('shiftpattern.index', compact('patterns'));
    }

    public function create()
    {
        $shifts = Shift::all();
        return view('shiftpattern.create', compact('shifts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'PatternName' => 'required|string|max:255',
            'PatternType' => 'nullable|string|max:50',
            'pola1' => 'nullable|exists:shift,id',
            'pola2' => 'nullable|exists:shift,id',
            'pola3' => 'nullable|exists:shift,id',
            'pola4' => 'nullable|exists:shift,id',
            'pola5' => 'nullable|exists:shift,id',
            'pola6' => 'nullable|exists:shift,id',
            'pola7' => 'nullable|exists:shift,id',
        ]);

        try {
            ShiftPattern::create($request->all());
            return redirect()->route('shift.index')
                ->with('success', 'Shift pattern created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating shift pattern: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create shift pattern. Please try again.');
        }
    }

    public function edit($id)
    {
        $pattern = ShiftPattern::findOrFail($id);
        $shifts = Shift::all();
        return view('shiftpattern.edit', compact('pattern', 'shifts'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'PatternName' => 'required|string|max:255',
            'PatternType' => 'nullable|string|max:50',
            'pola1' => 'nullable|exists:shift,id',
            'pola2' => 'nullable|exists:shift,id',
            'pola3' => 'nullable|exists:shift,id',
            'pola4' => 'nullable|exists:shift,id',
            'pola5' => 'nullable|exists:shift,id',
            'pola6' => 'nullable|exists:shift,id',
            'pola7' => 'nullable|exists:shift,id',
        ]);

        try {
            $pattern = ShiftPattern::findOrFail($id);
            $pattern->update($request->all());
            return redirect()->route('shift.index')
                ->with('success', 'Shift pattern updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating shift pattern: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update shift pattern. Please try again.');
        }
    }

    public function destroy($id)
    {
        try {
            $pattern = ShiftPattern::findOrFail($id);
            $pattern->delete();
            return redirect()->route('shift.index')
                ->with('success', 'Shift pattern deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting shift pattern: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete shift pattern. Please try again.');
        }
    }
}

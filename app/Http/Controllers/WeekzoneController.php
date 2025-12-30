<?php

namespace App\Http\Controllers;

use App\Models\Weekzone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WeekzoneController extends Controller
{
    public function index()
    {
        $weekzones = Weekzone::paginate(10);
        return view('weekzone.index', compact('weekzones'));
    }

    public function create()
    {
        return view('weekzone.create');
    }

    public function store(Request $request)
    {
        if (Weekzone::count() >= 8) {
            return redirect()->back()->withErrors(['error' => 'Cannot create more than 8 weekzone records.'])->withInput();
        }

        $validator = Validator::make($request->all(), [
            'ID' => 'required|integer|unique:weekzone,ID',
            'Name' => 'required|string|max:15',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Weekzone::create($request->all());
        return redirect()->route('weekzone.index')->with('success', 'Weekzone created successfully.');
    }

    public function edit($id)
    {
        $weekzone = Weekzone::findOrFail($id);
        return view('weekzone.edit', compact('weekzone'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'ID' => 'required|integer|unique:weekzone,ID,' . $id,
            'Name' => 'required|string|max:15',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $weekzone = Weekzone::findOrFail($id);
        $weekzone->update($request->all());
        return redirect()->route('weekzone.index')->with('success', 'Weekzone updated successfully.');
    }

    public function destroy($id)
    {
        $weekzone = Weekzone::findOrFail($id);
        $weekzone->delete();
        return redirect()->route('weekzone.index')->with('success', 'Weekzone deleted successfully.');
    }
}

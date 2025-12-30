<?php

namespace App\Http\Controllers;

use App\Models\Dayzone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DayzoneController extends Controller
{
    public function index()
    {
        $dayzones = Dayzone::paginate(10);
        return view('dayzone.index', compact('dayzones'));
    }

    public function create()
    {
        return view('dayzone.create');
    }

    public function store(Request $request)
    {
        // Check if the number of records is already 8
        if (Dayzone::count() >= 8) {
            return redirect()->back()->withErrors(['error' => 'Cannot create more than 8 dayzone records.'])->withInput();
        }

        $validator = Validator::make($request->all(), [
            'ID' => 'required|integer|unique:dayzone,ID',
            'Name' => 'required|string|max:15',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Dayzone::create($request->all());
        return redirect()->route('dayzone.index')->with('success', 'Dayzone created successfully.');
    }

    public function edit($id)
    {
        $dayzone = Dayzone::findOrFail($id);
        return view('dayzone.edit', compact('dayzone'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'ID' => 'required|integer|unique:dayzone,ID,' . $id,
            'Name' => 'required|string|max:15',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $dayzone = Dayzone::findOrFail($id);
        $dayzone->update($request->all());
        return redirect()->route('dayzone.index')->with('success', 'Dayzone updated successfully.');
    }

    public function destroy($id)
    {
        $dayzone = Dayzone::findOrFail($id);
        $dayzone->delete();
        return redirect()->route('dayzone.index')->with('success', 'Dayzone deleted successfully.');
    }
}

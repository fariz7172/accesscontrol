<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\departmentModel;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $departments = departmentModel::all();
        $branches = Branch::paginate(10);
        return view('branches.index', compact('branches', 'departments'));
    }

    public function create()
    {
        return view('branches.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'number' => 'required|numeric|unique:branch,number',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
        ], [
            'number.unique' => 'Data number already exists',
        ]);

        Branch::create($request->all());

        return redirect()->route('branches.index')
            ->with('success', 'Branch created successfully.');
    }

    public function edit($id)
    {
        $departments = departmentModel::all();
        $branch = Branch::findOrFail($id);
        return view('branches.edit', compact('branch', 'departments'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'number' => 'required|numeric',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $branch = Branch::findOrFail($id);
        $branch->update($request->all());

        return redirect()->route('branches.index')
            ->with('success', 'Branch updated successfully');
    }

    public function destroy($id)
    {
        $branch = Branch::findOrFail($id);
        $branch->delete();

        return redirect()->route('branches.index')
            ->with('success', 'Branch deleted successfully');
    }
}

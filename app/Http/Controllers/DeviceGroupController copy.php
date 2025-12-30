<?php

namespace App\Http\Controllers;

use App\Models\DeviceGroupModel;
use App\Models\deviceGateModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeviceGroupController extends Controller
{
    public function index()
    {
        $deviceGroup = DeviceGroupModel::with('deviceGates')->paginate(10);
        $deviceGates = deviceGateModel::all();
        return view('deviceGroup.index', compact('deviceGroup', 'deviceGates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|numeric|unique:devicegroup,number',
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'devicegroup_id' => 'nullable|array',
            'devicegroup_id.*' => 'exists:devicegate,id',
        ]);

        try {
            DB::beginTransaction();

            $deviceGroup = DeviceGroupModel::create([
                'number' => $request->number,
                'name' => $request->name,
                'description' => $request->description,
            ]);

            if (!empty($request->devicegroup_id)) {
                $deviceGroup->deviceGates()->attach($request->devicegroup_id);
            }

            DB::commit();
            return redirect()->route('deviceGroup')->with('success', 'Device Group created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('deviceGroup')->with('error', 'Failed to create Device Group: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $deviceGroup = DeviceGroupModel::findOrFail($id);
        $deviceGates = deviceGateModel::all();
        $selectedGates = $deviceGroup->deviceGates->pluck('id')->toArray();

        return view('DeviceGroup.edit', compact('deviceGroup', 'deviceGates', 'selectedGates'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'number' => 'required|numeric|unique:devicegroup,number,' . $id,
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'devicegroup_id' => 'nullable|array',
            'devicegroup_id.*' => 'exists:devicegate,id',
        ]);

        try {
            DB::beginTransaction();

            $deviceGroup = DeviceGroupModel::findOrFail($id);

            $deviceGroup->update([
                'number' => $request->number,
                'name' => $request->name,
                'description' => $request->description,
            ]);

            $deviceGroup->deviceGates()->sync($request->devicegroup_id ?? []);

            DB::commit();
            return redirect()->route('deviceGroup')->with('success', 'Device Group updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('deviceGroup')->with('error', 'Failed to update Device Group: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $deviceGroup = DeviceGroupModel::findOrFail($id);
            $deviceGroup->deviceGates()->detach();
            $deviceGroup->delete();

            return redirect()->route('deviceGroup')->with('success', 'Device Group deleted successfully');
        } catch (\Exception $e) {
            return redirect()->route('deviceGroup')->with('error', 'Failed to delete Device Group: ' . $e->getMessage());
        }
    }
}

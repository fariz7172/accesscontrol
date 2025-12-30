<?php

namespace App\Http\Controllers;

use App\Models\DeviceGroupModel;
use App\Models\deviceGateModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeviceGroupController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 10;
        $groups = DeviceGroupModel::with('deviceGates')
            ->paginate($perPage);

        return view('deviceGroup.index', compact('groups'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'number' => 'required|unique:devicegroup,number',
            'name' => 'required',
            'description' => 'nullable',
            'device_gates' => 'array',
            'device_gates.*' => 'exists:devicegate,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $group = DeviceGroupModel::create([
                'number' => $request->number,
                'name' => $request->name,
                'description' => $request->description
            ]);

            if ($request->has('device_gates')) {
                deviceGateModel::whereIn('id', $request->device_gates)
                    ->update(['groupid' => $group->id]);
            }

            return response()->json(['message' => 'Device Group created successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create Device Group'], 500);
        }
    }


    public function edit($id)
    {
        $deviceGroup = DeviceGroupModel::findOrFail($id);
        $deviceGates = deviceGateModel::all();
        $selectedGates = $deviceGroup->deviceGates->pluck('id')->toArray();

        return view('DeviceGroup.edit', compact('deviceGroup', 'deviceGates', 'selectedGates'));
    }


    public function show($id)
    {
        $group = DeviceGroupModel::with('deviceGates')->findOrFail($id);
        $availableDeviceGates = deviceGateModel::where(function ($query) {
            $query->whereNull('groupid')
                ->orWhere('groupid', 0);
        })->get();

        return response()->json([
            'id' => $group->id,
            'number' => $group->number,
            'name' => $group->name,
            'description' => $group->description,
            'device_gates' => $group->deviceGates,
            'available_device_gates' => $availableDeviceGates
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'number' => 'required|unique:devicegroup,number,' . $id,
            'name' => 'required',
            'description' => 'nullable',
            'device_gates' => 'array',
            'device_gates.*' => 'exists:devicegate,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $group = DeviceGroupModel::findOrFail($id);
            $group->update([
                'number' => $request->number,
                'name' => $request->name,
                'description' => $request->description
            ]);

            // Reset groupid for all device gates not in the request
            deviceGateModel::where('groupid', $id)
                ->whereNotIn('id', $request->device_gates ?? [])
                ->update(['groupid' => null]);

            // Update groupid for selected device gates
            if ($request->has('device_gates')) {
                deviceGateModel::whereIn('id', $request->device_gates)
                    ->update(['groupid' => $group->id]);
            }

            return response()->json(['message' => 'Device Group updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update Device Group'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $group = DeviceGroupModel::findOrFail($id);
            // Reset groupid for associated device gates
            $group->deviceGates()->update(['groupid' => null]);
            $group->delete();
            return response()->json(['message' => 'Device Group deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete Device Group'], 500);
        }
    }
}

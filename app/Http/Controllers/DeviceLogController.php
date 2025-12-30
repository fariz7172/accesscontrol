<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeviceLog;
use App\Models\devicelogModel;

class DeviceLogController extends Controller
{
    public function saveToDeviceLog(Request $request)
    {
        $data = $request->validate([
            'log_date' => 'required|string',
            'modul' => 'required|string',
            'desc' => 'required|string',
            'status' => 'required|string'
        ]);

        devicelogModel::create($data);

        return response()->json(['success' => true]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Exports\UserLogsExportInvalid;
use App\Models\userLogModel;
use App\Models\deviceGateModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class invalidLogController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from')
            ? Carbon::parse($request->input('date_from'))->startOfDay()
            : Carbon::today()->startOfDay();
        $dateTo = $request->input('date_to')
            ? Carbon::parse($request->input('date_to'))->endOfDay()
            : Carbon::today()->endOfDay();
        $deviceName = $request->input('device_name');

        $devices = deviceGateModel::whereNotNull('name')
            ->where('name', '!=', '')
            ->orderBy('name')
            ->pluck('name')
            ->unique();

        $logData = userLogModel::with(['userProfile', 'deviceGate'])
            ->where('stat', 1)
            ->when($dateFrom && $dateTo, fn($q) => $q->whereBetween('TM_EVENT', [$dateFrom, $dateTo]))
            ->when($deviceName, fn($q) => $q->whereHas('deviceGate', fn($qq) => $qq->where('name', $deviceName)))
            ->orderBy('TM_EVENT', 'desc')
            ->paginate(50);

        return view('logInvalidData.index', compact(
            'logData',
            'dateFrom',
            'dateTo',
            'devices',
            'deviceName'
        ));
    }

    public function export(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $deviceName = $request->input('device_name');

        $fileName = 'Invalid_User_Logs_' . ($dateFrom ?? 'today') . '_to_' . ($dateTo ?? 'today');
        if ($deviceName) {
            $fileName .= '_Device_' . str_replace(' ', '_', $deviceName);
        }
        $fileName .= '.xlsx';

        return Excel::download(new UserLogsExportInvalid($dateFrom, $dateTo, $deviceName), $fileName);
    }

    public function getLatestLogsInvalid(Request $request)
    {
        try {
            $lastTimestamp = $request->input('last_timestamp');
            $dateFrom = $request->input('date_from')
                ? Carbon::parse($request->input('date_from'))->startOfDay()
                : Carbon::today()->startOfDay();
            $dateTo = $request->input('date_to')
                ? Carbon::parse($request->input('date_to'))->endOfDay()
                : Carbon::today()->endOfDay();
            $deviceName = $request->input('device_name');

            $logs = userLogModel::with(['userProfile', 'deviceGate'])
                ->where('stat', 1)
                ->when($lastTimestamp, fn($q) => $q->where('TM_EVENT', '>', $lastTimestamp))
                ->when($dateFrom && $dateTo, fn($q) => $q->whereBetween('TM_EVENT', [$dateFrom, $dateTo]))
                ->when($deviceName, fn($q) => $q->whereHas('deviceGate', fn($qq) => $qq->where('name', $deviceName)))
                ->orderBy('TM_EVENT', 'desc')
                ->limit(50)
                ->get();

            $formatted = $logs->map(function ($log) {
                $eventTime = $log->TM_EVENT ? Carbon::parse($log->TM_EVENT) : now();

                $photoDataUri = $log->userProfile?->photo;

                // Ekstrak hanya bagian base64 dari data:image/jpeg;base64,....
                $photoBase64 = $photoDataUri
                    ? preg_replace('#^data:image/\w+;base64,#i', '', $photoDataUri)
                    : null;

                return [
                    'TM_EVENT'       => $eventTime->format('d/m/Y H:i:s'),
                    'TM_EVENT_RAW'   => $eventTime->toDateTimeString(),
                    'user_name'      => $log->userProfile?->NAME ?? 'NOT REGISTERED',
                    'is_registered'  => !is_null($log->userProfile?->NAME),
                    'device_name'    => $log->deviceGate?->name ?? $log->DEVICESN ?? 'Unknown Device',
                    'card'           => $log->card ?? '-',
                    'photo_base64'   => $photoBase64, // hanya base64 murni!
                ];
            });

            return response()->json($formatted);
        } catch (\Exception $e) {
            Log::error('getLatestLogsInvalid Error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\userProfileModel;
use App\Models\WeekzoneUser;
use App\Models\deviceGateModel;
use App\Models\usersDevice;
use App\Models\ApiModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SetUserTimeoneController extends Controller
{
    public function index()
    {
        $devices = deviceGateModel::select('id', 'name', 'sn', 'type')->get();
        return view('SetUserTimeone.index', compact('devices'));
    }

    /**
     * Ambil user berdasarkan device yang dipilih
     */
    public function getUsersByDevices(Request $request)
    {
        $deviceIds = $request->input('device_ids', []);
        if (empty($deviceIds)) {
            return response()->json([]);
        }

        $users = usersDevice::whereIn('gateId', $deviceIds)
            ->with(['user' => function ($q) {
                $q->with('department')
                    ->select('ID', 'NAME', 'Depid', 'photo', 'Card', 'BEGIN_DATE', 'END_DATE')
                    ->orderBy('ID', 'asc');  // <-- TAMBAHKAN INI
            }])
            ->get()
            ->pluck('user')
            ->filter()
            ->values();

        return response()->json($users);
    }
    /**
     * Ambil semua user (jika "Tampilkan Semua User" dicentang)
     */
    public function getAllUsers()
    {
        $users = userProfileModel::with(['department'])
            ->select('ID', 'NAME', 'Depid', 'photo', 'Card', 'BEGIN_DATE', 'END_DATE')
            ->orderBy('ID', 'asc')  // <-- TAMBAHKAN INI
            ->get();

        return response()->json($users);
    }
    /**
     * Ambil weekzone yang sudah di-assign ke user (via weekzoneuser + deviceid)
     */
    public function getUserWeekzones($userId)
    {
        try {
            $user = userProfileModel::findOrFail($userId);

            // Ambil device yang terkait dengan user
            $assignedGateIds = usersDevice::where('userId', $user->ID)
                ->pluck('gateId')
                ->toArray();

            $deviceNames = deviceGateModel::whereIn('id', $assignedGateIds)
                ->pluck('name', 'id')
                ->toArray();

            $deviceList = !empty($deviceNames)
                ? implode(', ', array_values($deviceNames))
                : 'No Device';

            // Ambil weekzone dari weekzoneuser (termasuk deviceid)
            $weekzoneUsers = WeekzoneUser::where('userid', $user->ID)
                ->with([
                    'weekzone' => function ($q) {
                        $q->with(['weekzoneDetails' => function ($qd) {
                            $qd->with(['day1', 'day2', 'day3', 'day4', 'day5', 'day6', 'day7']);
                        }]);
                    },
                    'device' => function ($q) {
                        $q->select('id', 'name');
                    }
                ])
                ->get();

            return response()->json([
                'user' => $user->only(['NAME', 'Card', 'BEGIN_DATE', 'END_DATE']),
                'device' => $deviceList,
                'via_user' => $weekzoneUsers, // Sudah include device
            ]);
        } catch (\Exception $e) {
            Log::error('getUserWeekzones error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    /**
     * Assign weekzone ke user + device (simpan ke weekzoneuser)
     */
    /**
     * Assign weekzone ke user + device (simpan ke weekzoneuser)
     */
    /**
     * Assign weekzone ke user + device (simpan ke weekzoneuser + push ke API)
     */
    public function assignWeekzone(Request $request)
    {
        $request->validate([
            'user_ids'      => 'required|string|min:1',
            'weekzone_ids'  => 'required|array|min:1',
            'weekzone_ids.*' => 'numeric',
            'device_ids'    => 'required|array|min:1',
            'device_ids.*'  => 'numeric',
        ]);

        $userIds     = array_filter(array_map('trim', explode(',', $request->input('user_ids'))));
        $weekzoneIds = array_map('intval', $request->input('weekzone_ids'));
        $deviceIds   = array_map('intval', $request->input('device_ids', []));

        if (empty($userIds) || empty($deviceIds) || empty($weekzoneIds)) {
            return redirect()->back()->withErrors(['error' => 'Incomplete data.']);
        }

        try {
            $errors = [];
            $successCount = 0;
            $apiUrl = ApiModel::find(16)?->name;

            $devices = deviceGateModel::whereIn('id', $deviceIds)->pluck('sn', 'id')->toArray();

            $batchSize = 20;
            $payloadBatch = [];

            foreach ($userIds as $userId) {
                $user = userProfileModel::find($userId);
                if (!$user) {
                    $errors[] = "User ID {$userId} Not Found.";
                    continue;
                }

                foreach ($deviceIds as $deviceId) {
                    if (!isset($devices[$deviceId])) {
                        $errors[] = "Device ID {$deviceId} Not valid.";
                        continue;
                    }

                    $sn = $devices[$deviceId];

                    WeekzoneUser::where('userid', $userId)->where('deviceid', $deviceId)->delete();

                    foreach ($weekzoneIds as $wzId) {
                        WeekzoneUser::create([
                            'wzid'       => $wzId,
                            'userid'     => $userId,
                            'deviceid'   => $deviceId,
                            'created_at' => now(),
                        ]);

                        $successCount++;

                        $payloadBatch[] = [
                            "sn"       => $sn,
                            "userid"   => (string) $user->ID,
                            "weekzone" => (int) $wzId,
                        ];

                        if (count($payloadBatch) >= $batchSize && $apiUrl) {
                            $this->postToApi($apiUrl, $payloadBatch);
                            $payloadBatch = [];
                        }
                    }
                }
            }

            if (!empty($payloadBatch) && $apiUrl) {
                $this->postToApi($apiUrl, $payloadBatch);
            }

            $message = "Success assign weekzone to {$successCount}  user-device.";

            // Jika request dari AJAX (JavaScript), kembalikan JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'errors'  => $errors
                ]);
            }

            // Jika normal request, redirect seperti biasa
            return redirect()->back()->with(
                empty($errors) ? ['success' => $message] : ['success' => $message, 'details' => $errors]
            );
        } catch (\Exception $e) {
            Log::error('assignWeekzone error: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->withErrors(['Error: ' . $e->getMessage()]);
        }
    }


    /**
     * Kirim data ke API eksternal
     */
    private function postToApi($url, $data, $useJson = true)
    {
        if (!$url) {
            Log::warning("API URL Not Found.");
            return false;
        }

        try {
            $response = $useJson
                ? Http::withHeaders(['Accept' => 'application/json'])->post($url, $data)
                : Http::asForm()->post($url, $data);

            Log::info("POST API Success", [
                'url' => $url,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Failed POST to API: " . $e->getMessage());
            return false;
        }
    }
}

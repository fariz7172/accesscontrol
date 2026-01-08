<?php

namespace App\Http\Controllers;

use App\Models\ApiModel;
use App\Models\deviceGateModel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class openDoorController extends Controller
{

    public function index()
    {
        $devices = deviceGateModel::with(['userProfiles'])->paginate(10);
        $sztimmyGetOpenDoorApiUrl = ApiModel::where('id', 9)->value('name'); // Ambil URL dari ApiModel
        return view('door.index', compact('devices', 'sztimmyGetOpenDoorApiUrl'));
    }

    public function openDevice($id)
    {
        // Ambil device berdasarkan ID
        $device = deviceGateModel::find($id);

        // Cek apakah device ada
        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found.'
            ], 404);
        }

        // Ambil URL API dari tabel ApiModel
        $sztimmyGetOpenDoorApiUrl = ApiModel::where('id', 9)->value('name');

        if (!$sztimmyGetOpenDoorApiUrl) {
            return response()->json([
                'success' => false,
                'message' => 'API URL not configured (API ID: 9)'
            ], 500);
        }

        try {
            // Kirim data SN ke API dengan timeout
            $response = Http::timeout(10)->post($sztimmyGetOpenDoorApiUrl, [
                'sn' => $device->sn,
            ]);

            // Log response untuk debugging
            \Log::info('Open Door API Response', [
                'device_id' => $id,
                'device_sn' => $device->sn,
                'api_url' => $sztimmyGetOpenDoorApiUrl,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            // Check response status
            if ($response->successful()) {
                // Status 200-299
                return response()->json([
                    'success' => true,
                    'message' => 'Pintu ' . $device->name . ' berhasil dibuka'
                ]);
            } elseif ($response->status() === 400) {
                // Bad Request
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuka pintu: Bad Request - Data tidak valid atau device tidak terhubung'
                ], 400);
            } elseif ($response->status() === 404) {
                // Not Found
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuka pintu: Device tidak ditemukan di server'
                ], 404);
            } elseif ($response->status() >= 500) {
                // Server Error
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuka pintu: Server error (' . $response->status() . ')'
                ], 500);
            } else {
                // Other errors
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuka pintu: HTTP ' . $response->status()
                ], $response->status());
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Connection timeout or failed
            return response()->json([
                'success' => false,
                'message' => 'DATA TIDAK TERHUBUNG DENGAN SERVER, CHECK KONEKSI'
            ], 500);
        } catch (\Exception $e) {
            // Other exceptions
            \Log::error('Open Door Error', [
                'device_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function openGate(Request $request)
    {
        $nodeId = intval($request->node_id);
        $ipAddress = $request->ip_address;

        // Buat array byte perintah
        $bytes = [];
        $bytes[0] = 0x7E; // Header
        $bytes[1] = 0x06; // Panjang data
        $bytes[2] = $nodeId; // ID perangkat
        $bytes[3] = 0x21; // Perintah Open Gate
        $bytes[4] = 0x84; // Kode aksi
        $bytes[5] = 0x00; // Data tambahan

        // Hitung LRC (Longitudinal Redundancy Check)
        $LRC = 255;
        for ($i = 2; $i <= 5; $i++) {
            $LRC ^= $bytes[$i];
        }
        $bytes[6] = $LRC;

        // Hitung checksum tambahan
        $sum = array_sum(array_slice($bytes, 2, 5));
        $bytes[7] = $sum % 256;

        // Kirim perintah ke perangkat Soyal 725e melalui TCP
        $socket = @stream_socket_client("tcp://$ipAddress:1621", $errno, $errstr, 5);

        if (!$socket) {
            return response()->json(['success' => false, 'message' => "Gagal terhubung ke perangkat: $errstr"], 500);
        }

        fwrite($socket, pack('C*', ...$bytes));
        stream_set_timeout($socket, 15);

        $response = fread($socket, 8);
        fclose($socket);

        if ($response) {
            // Konversi respons ke Hex String
            $responseHex = '';
            foreach (str_split($response) as $char) {
                $responseHex .= ' ' . $this->asciiToHex($char);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pintu berhasil dibuka!',
                'response' => trim($responseHex) // ASCII to HEX
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Tidak ada respons dari perangkat.'], 500);
    }

    // Fungsi Konversi ASCII ke HEX
    private function asciiToHex($char)
    {
        return strtoupper(dechex(ord($char)));
    }

    /**
     * API endpoint untuk membuka gate/pintu
     * Support untuk Soyal (type=1) dan Tasoft (type=0/50)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function openGateApi(Request $request)
    {
        // Validation - basic type checking only
        $validator = \Validator::make($request->all(), [
            'device_id' => 'required_without:device_sn|integer',
            'device_sn' => 'required_without:device_id|string',
        ], [
            'device_id.required_without' => 'Device ID or Serial Number is required',
            'device_sn.required_without' => 'Device ID or Serial Number is required',
            'device_id.integer' => 'Device ID must be an integer',
            'device_sn.string' => 'Device Serial Number must be a string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Debug logging
            \Log::info('Open Gate API - Looking for device', [
                'request_device_id' => $request->device_id ?? null,
                'request_device_sn' => $request->device_sn ?? null,
                'has_device_id' => $request->has('device_id'),
                'has_device_sn' => $request->has('device_sn'),
            ]);

            // Find device by ID or SN
            if ($request->has('device_id')) {
                $device = deviceGateModel::find($request->device_id);
                
                \Log::info('Device lookup by ID', [
                    'device_id' => $request->device_id,
                    'found' => $device ? 'yes' : 'no',
                    'device_data' => $device ? $device->toArray() : null
                ]);
            } else {
                $device = deviceGateModel::where('sn', $request->device_sn)->first();
                
                \Log::info('Device lookup by SN', [
                    'device_sn' => $request->device_sn,
                    'found' => $device ? 'yes' : 'no',
                    'device_data' => $device ? $device->toArray() : null
                ]);
            }

            // Check if device exists
            if (!$device) {
                // Additional debug - list all devices
                $allDevices = deviceGateModel::select('id', 'name', 'type', 'sn')->get();
                \Log::warning('Device not found - Available devices', [
                    'requested_id' => $request->device_id ?? null,
                    'requested_sn' => $request->device_sn ?? null,
                    'available_devices' => $allDevices->toArray()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Device not found',
                    'error_code' => 'DEVICE_NOT_FOUND'
                ], 404);
            }

            // Log attempt
            \Log::info('Open Gate API Request', [
                'device_id' => $device->id,
                'device_sn' => $device->sn,
                'device_type' => $device->type,
                'device_name' => $device->name,
                'user_id' => auth('api')->id(),
            ]);

            // Handle based on device type
            if ($device->type == 1) {
                // Soyal - Use TCP connection
                return $this->openSoyalGate($device);
            } else {
                // Tasoft (type 0 or 50) - Use HTTP API
                return $this->openTasoftGate($device);
            }

        } catch (\Exception $e) {
            \Log::error('Open Gate API Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
                'error_code' => 'INTERNAL_ERROR'
            ], 500);
        }
    }

    /**
     * Open Soyal gate via TCP connection
     * 
     * @param deviceGateModel $device
     * @return \Illuminate\Http\JsonResponse
     */
    private function openSoyalGate($device)
    {
        $nodeId = intval($device->nodeid);
        $ipAddress = $device->ip;

        // Build command bytes
        $bytes = [];
        $bytes[0] = 0x7E; // Header
        $bytes[1] = 0x06; // Data length
        $bytes[2] = $nodeId; // Device ID
        $bytes[3] = 0x21; // Open Gate command
        $bytes[4] = 0x84; // Action code
        $bytes[5] = 0x00; // Additional data

        // Calculate LRC (Longitudinal Redundancy Check)
        $LRC = 255;
        for ($i = 2; $i <= 5; $i++) {
            $LRC ^= $bytes[$i];
        }
        $bytes[6] = $LRC;

        // Calculate checksum
        $sum = array_sum(array_slice($bytes, 2, 5));
        $bytes[7] = $sum % 256;

        // Send command via TCP
        $socket = @stream_socket_client("tcp://$ipAddress:1621", $errno, $errstr, 5);

        if (!$socket) {
            \Log::error('Soyal TCP Connection Failed', [
                'device_id' => $device->id,
                'ip' => $ipAddress,
                'error' => $errstr,
                'errno' => $errno
            ]);

            return response()->json([
                'success' => false,
                'message' => "Gagal terhubung ke perangkat: $errstr",
                'error_code' => 'CONNECTION_ERROR'
            ], 500);
        }

        fwrite($socket, pack('C*', ...$bytes));
        stream_set_timeout($socket, 15);

        $response = fread($socket, 8);
        fclose($socket);

        if ($response) {
            // Convert response to Hex
            $responseHex = '';
            foreach (str_split($response) as $char) {
                $responseHex .= ' ' . $this->asciiToHex($char);
            }

            \Log::info('Soyal Gate Opened Successfully', [
                'device_id' => $device->id,
                'device_name' => $device->name,
                'response' => trim($responseHex)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pintu ' . $device->name . ' berhasil dibuka',
                'data' => [
                    'device_id' => $device->id,
                    'device_name' => $device->name,
                    'device_type' => $device->type,
                    'device_sn' => $device->sn,
                    'ip_address' => $device->ip,
                    'opened_at' => now()->format('Y-m-d H:i:s'),
                    'response_hex' => trim($responseHex)
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Tidak ada respons dari perangkat',
            'error_code' => 'NO_RESPONSE'
        ], 500);
    }

    /**
     * Open Tasoft gate via HTTP API
     * 
     * @param deviceGateModel $device
     * @return \Illuminate\Http\JsonResponse
     */
    private function openTasoftGate($device)
    {
        // Get API URL from ApiModel (id=9)
        $apiUrl = ApiModel::where('id', 9)->value('name');

        if (!$apiUrl) {
            return response()->json([
                'success' => false,
                'message' => 'API URL not configured (API ID: 9)',
                'error_code' => 'API_URL_NOT_CONFIGURED'
            ], 500);
        }

        try {
            // Send HTTP POST to external API
            $response = \Http::timeout(10)->post($apiUrl, [
                'sn' => $device->sn,
            ]);

            \Log::info('Tasoft API Response', [
                'device_id' => $device->id,
                'device_sn' => $device->sn,
                'api_url' => $apiUrl,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            // Check response status
            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pintu ' . $device->name . ' berhasil dibuka',
                    'data' => [
                        'device_id' => $device->id,
                        'device_name' => $device->name,
                        'device_type' => $device->type,
                        'device_sn' => $device->sn,
                        'ip_address' => $device->ip,
                        'opened_at' => now()->format('Y-m-d H:i:s')
                    ]
                ]);
            } elseif ($response->status() === 400) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuka pintu: Bad Request - Data tidak valid atau device tidak terhubung',
                    'error_code' => 'API_ERROR'
                ], 400);
            } elseif ($response->status() === 404) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuka pintu: Device tidak ditemukan di server',
                    'error_code' => 'API_ERROR'
                ], 404);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuka pintu: HTTP ' . $response->status(),
                    'error_code' => 'API_ERROR'
                ], $response->status());
            }

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal terhubung ke API server',
                'error_code' => 'CONNECTION_ERROR'
            ], 500);
        }
    }
}

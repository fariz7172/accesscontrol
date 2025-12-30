<?php
// app/Http/Controllers/deviceController.php

namespace App\Http\Controllers;

use App\Models\ApiModel;
use App\Models\deviceEvent;
use App\Models\deviceGateModel;
use App\Models\devicelogModel;
use App\Models\pictureModel;
use App\Models\tagModel;
use App\Models\userLogModel;
use App\Models\userProfileModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

class deviceController extends Controller
{

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 50);
        $perPage = $perPage == 'all' ? deviceGateModel::count() : (int) $perPage;

        $devices = deviceGateModel::paginate($perPage);

        foreach ($devices as $device) {
            $latestEvent = deviceEvent::where('DEVICESN', $device->sn)
                ->orderBy('TM_EVENT', 'desc')
                ->first();

            if ($latestEvent) {
                $lastEventTime = Carbon::parse($latestEvent->TM_EVENT);
                $now = Carbon::now();
                $diffInMinutes = $lastEventTime->diffInMinutes($now);

                Log::info("Device SN: {$device->sn}, TM_EVENT: {$lastEventTime}, Now: {$now}, Diff: {$diffInMinutes} minutes");

                $device->connection_status = $diffInMinutes <= 5 ? 'Connected' : 'Not Connected';
            } else {
                Log::info("No event found for Device SN: {$device->sn}");
                $device->connection_status = 'Not Connected';
            }
        }

        return view('device.index', compact('devices'));
    }

    public function checkConnection(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'sn' => 'required|string',
            ]);

            // Ambil URL API dari ApiModel
            $apiUrl = ApiModel::where('id', 11)->value('name');
            if (!$apiUrl) {
                Log::error('API URL not found for id 11');
                return response()->json([
                    'success' => false,
                    'message' => 'API configuration not found'
                ], 500);
            }

            // Kirim request ke API
            $response = Http::timeout(10)->post($apiUrl, [
                'sn' => $request->sn,
            ]);

            // Periksa status respons
            if ($response->status() === 200) {
                Log::info('Connection check successful', ['sn' => $request->sn, 'api_url' => $apiUrl]);
                return response()->json([
                    'success' => true,
                    'message' => 'Mechine Connected'
                ], 200);
            } elseif ($response->status() === 400) {
                Log::warning('Connection check failed: Bad Request', ['sn' => $request->sn, 'api_url' => $apiUrl]);
                return response()->json([
                    'success' => false,
                    'message' => 'Mechine Not Connected'
                ], 400);
            } else {
                Log::error('Unexpected API response status', [
                    'sn' => $request->sn,
                    'api_url' => $apiUrl,
                    'status' => $response->status(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Unexpected response from API'
                ], $response->status());
            }
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Failed to connect to API', [
                'sn' => $request->sn,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Mechine Not Connected: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error in checkConnection', [
                'sn' => $request->sn,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function ping(Request $request)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'ip' => 'required|ip'
            ]);

            $ip = $validated['ip'];
            // Perform ping (1 attempt, 2-second timeout)
            $output = [];
            $exitCode = 0;

            // Use appropriate ping command based on OS
            $command = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
                ? "ping -n 1 -w 2000 $ip"
                : "ping -c 1 -W 2 $ip";

            exec($command, $output, $exitCode);

            // Check if ping was successful (exit code 0 indicates success)
            if ($exitCode === 0) {
                Log::info('Ping successful', ['ip' => $ip, 'output' => $output]);
                return response()->json([
                    'success' => true,
                    'message' => 'Device is connected.'
                ], 200);
            } else {
                Log::error('Ping failed', ['ip' => $ip, 'exit_code' => $exitCode, 'output' => $output]);
                return response()->json([
                    'success' => false,
                    'message' => 'Device is not connected.'
                ], 200);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in ping', [
                'ip' => $request->ip,
                'errors' => $e->errors()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . implode(', ', $e->errors()['ip'] ?? ['Invalid IP address'])
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in ping', [
                'ip' => $request->ip,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function openTCP()
    {
        $port = 1621;

        $socket = @stream_socket_client("tcp://192.168.100.25:$port", $errno, $errstr, 10);

        if (!$socket) {
            echo "Gagal terhubung: $errstr ($errno)\n";
        } else {
            echo "Berhasil terhubung ke 192.168.100.25:$port\n";
            fclose($socket);
        }
    }

    private function sendToSoyal($ip, $port, $bytes, $retries = 3)
    {
        for ($attempt = 1; $attempt <= $retries; $attempt++) {
            $socket = @stream_socket_client("tcp://$ip:$port", $errno, $errstr, 25);
            if ($socket) {
                fwrite($socket, pack('C*', ...$bytes));
                stream_set_timeout($socket, 30);
                $response = fread($socket, 1024);
                fclose($socket);

                if ($response) {
                    Log::info("Soyal response (attempt $attempt)", ['ip' => $ip, 'port' => $port, 'response' => bin2hex($response)]);
                    return response()->json(['message' => 'Success', 'response' => bin2hex($response)], 200);
                }
            }
            Log::warning("Attempt $attempt failed", ['ip' => $ip, 'port' => $port, 'error' => $errstr]);
            sleep(1); // Tunggu 1 detik sebelum retry
        }
        Log::error("Failed to connect to Soyal after $retries attempts", ['ip' => $ip, 'port' => $port]);
        return response()->json(['message' => 'Failed! Check your Soyal connection!'], 400);
    }

    public function checkCardStatus(Request $request)
    {
        $cardNo = $request->input('card_no');
        try {
            $user = userProfileModel::where('Card', $cardNo)->first();
            if ($user) {
                return response()->json([
                    'BinusianID' => $user->ID,
                    'IsKaryawan' => $user->Depid ? true : false, // Sesuaikan logika
                ], 200);
            }
            return response()->json(['message' => 'Card not found'], 404);
        } catch (\Exception $e) {
            Log::error('Error checking card status: ' . $e->getMessage());
            return response()->json(['message' => 'Error checking card status'], 500);
        }
    }

    public function showLogs(Request $request)
    {
        $perPage = $request->get('per_page', 50);
        $perPage = $perPage == 'all' ? userLogModel::count() : (int) $perPage;

        $logs = userLogModel::with(['userProfile', 'deviceGate'])
            ->orderBy('TM_EVENT', 'desc')
            ->paginate($perPage);

        return view('device.logs', compact('logs'));
    }

    public function store(Request $request)
    {
        Log::info('Request data:', $request->all());

        $request->validate([
            'name' => 'required|string|max:255',
            'number' => 'required|string|max:255',
            'flagstatus' => 'required|boolean',
            'type' => 'required|string|max:100',
            'sn' => 'required|string|max:100|unique:devicegate,sn',
            'ip' => 'required|ip|unique:devicegate,ip',
            'nodeid' => 'required|integer|unique:devicegate,nodeid',
            'description' => 'required|string|max:255',
            'stat' => 'required|string',
        ], [
            'sn.unique' => 'Serial Number (SN) sudah digunakan. Silakan gunakan SN lain.',
            'ip.unique' => 'Alamat IP sudah digunakan. Silakan gunakan IP lain.',
            'nodeid.unique' => 'Node ID sudah digunakan. Silakan gunakan Node ID lain.',
            'name.required' => 'Nama perangkat wajib diisi.',
            'number.required' => 'Nomor perangkat wajib diisi.',
            'flagstatus.required' => 'Status bendera wajib diisi.',
            'type.required' => 'Tipe perangkat wajib diisi.',
            'sn.required' => 'Serial Number (SN) wajib diisi.',
            'ip.required' => 'Alamat IP wajib diisi.',
            'nodeid.required' => 'Node ID wajib diisi.',
            'description.required' => 'Deskripsi wajib diisi.',
            'ip.ip' => 'Alamat IP tidak valid.',
        ]);

        try {
            deviceGateModel::create($request->all());
            return redirect()->back()->with('success', 'Device berhasil ditambahkan!');
        } catch (\Exception $e) {
            Log::error('Error saving device: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menambahkan device: ' . $e->getMessage());
        }
    }

    public function devicelog(Request $request)
    {
        try {
            $validated = $request->validate([
                'log_date' => 'required|date',
                'modul' => 'required',
                'desc' => 'required|string',
                'status' => 'required|string',
            ]);

            // Convert log_date from UTC to Asia/Jakarta
            $logDate = Carbon::parse($validated['log_date'])->setTimezone('Asia/Jakarta');


            devicelogModel::create([
                'log_date' => $logDate,
                'modul' => $validated['modul'],
                'desc' => $validated['desc'],
                'status' => $validated['status'],
            ]);

            return response()->json(['message' => 'Log saved successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Error saving device log: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save log', 'details' => $e->getMessage()], 500);
        }
    }

    public function edit($encryptedId)
    {
        // Mendekripsi ID yang diterima
        $id = decryptId($encryptedId);

        // Jika dekripsi gagal, tampilkan halaman error
        if ($id === null) {
            return view('errorHandler', ['error' => 'The payload is invalid.']);
        }

        $device = deviceGateModel::findOrFail($id);
        return view('device.edit', compact('device'));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'number' => 'required|string|max:255',
            'flagstatus' => 'required|boolean',
            'type' => 'required|string|max:100',
            'sn' => 'required|string|max:100',
            'ip' => 'required|ip',
            'nodeid' => 'required|integer',
            'description' => 'required|string|max:255',
        ]);

        $device = deviceGateModel::findOrFail($id);
        $device->update($request->all());
        return redirect('device')->with('success', 'Device berhasil ditambahkan!');
    }

    public function destroy($id)
    {
        $device = deviceGateModel::findOrFail($id);
        $device->delete();
        return redirect()->back()->with('success', 'Device berhasil dihapus!');
    }
    public function serialCard(Request $request)
    {
        $port = $request->input('comPort', 'com9'); // Default COM port
        $baudRate = 9600;
        $timeout = 5000; // Timeout dalam milidetik

        try {
            // Menjalankan perintah CMD untuk mengatur COM Port
            $command = "mode $port: BAUD=$baudRate PARITY=n DATA=8 STOP=1";
            $output = shell_exec($command);
            if ($output === null) {
                throw new \Exception("Gagal menjalankan perintah terminal untuk COM port: $port");
            }

            // Membuka port serial
            $serial = fopen($port, "r+");
            if (!$serial) {
                throw new \Exception("COM PORT TIDAK TERHUBUNG: $port");
            }

            // Mengatur timeout dan mode non-blocking
            stream_set_blocking($serial, false);
            stream_set_timeout($serial, $timeout / 1000);

            // Kirim perintah untuk membaca kartu
            $dataToSend = chr(0x7E) . chr(0x04) . chr(0x01) . chr(0x13) . chr(0xED) . chr(0x01);
            fwrite($serial, $dataToSend);

            // Tunggu respons (disesuaikan untuk stabilitas)
            usleep(1000000); // 1 detik dalam mikrodetik

            // Membaca respons
            $response = '';
            $maxReadAttempts = 5;
            $attempt = 0;

            while ($attempt < $maxReadAttempts) {
                $data = fread($serial, 255);
                if ($data !== false && $data !== '') {
                    $response .= $data;
                    break;
                }
                usleep(200000); // Tunggu 200ms sebelum coba lagi
                $attempt++;
            }

            fclose($serial);

            if ($response === '') {
                throw new \Exception("Tidak ada respons dari perangkat setelah $maxReadAttempts percobaan.");
            }

            // Mengonversi respons menjadi format heksadesimal untuk debugging
            $hexResponse = strtoupper(bin2hex($response));

            // Log respons untuk debugging
            Log::info("Serial Response: hex=$hexResponse, length=" . strlen($response));

            // Pastikan panjang respons cukup untuk membaca data kartu
            if (strlen($response) >= 9) {
                $byte5 = ord($response[5]);
                $byte6 = ord($response[6]);
                $byte7 = ord($response[7]);
                $byte8 = ord($response[8]);

                // Log byte untuk verifikasi
                Log::info("Bytes: byte5=" . sprintf("0x%02X", $byte5) .
                    ", byte6=" . sprintf("0x%02X", $byte6) .
                    ", byte7=" . sprintf("0x%02X", $byte7) .
                    ", byte8=" . sprintf("0x%02X", $byte8));

                // Menghitung nomor kartu
                $cardNo = ($byte6 << 16) | ($byte7 << 8) | $byte8;

                // Menghitung nomor kartu dalam format word1:word2
                $word1 = ($byte5 << 8) | $byte6;
                $word2 = ($byte7 << 8) | $byte8;
                $cardNoFormatted = "$word1:$word2";

                // Log hasil perhitungan
                Log::info("Result: cardNo=$cardNo, cardNoFormatted=$cardNoFormatted");

                return view('device.serial', [
                    'cardNo' => $cardNo,
                    'cardNoLittleEndian' => $cardNoFormatted,
                    'hexResponse' => $hexResponse,
                    'port' => $port
                ]);
            } else {
                throw new \Exception("Respons tidak valid: $hexResponse (panjang: " . strlen($response) . ")");
            }
        } catch (\Exception $e) {
            Log::error("Error: " . $e->getMessage());
            return view('device.serial', [
                'error' => $e->getMessage(),
                'port' => $port
            ]);
        }
    }

    public function processCardNumber(Request $request)
    {
        $cardNoInput = $request->input('cardNoInput');

        try {
            // Validasi input
            if (!is_numeric($cardNoInput) || $cardNoInput < 0) {
                throw new \Exception("Nomor kartu tidak valid.");
            }

            $cardNo = (int)$cardNoInput;

            // Konversi cardNo ke byte (mirip logika serial)
            // cardNo = (byte6 << 16) | (byte7 << 8) | byte8
            $byte6 = ($cardNo >> 16) & 0xFF;
            $byte7 = ($cardNo >> 8) & 0xFF;
            $byte8 = $cardNo & 0xFF;

            // Asumsi byte5 = 0x5A untuk menghasilkan word1 = 23172 (sesuai diskusi)
            $byte5 = 0x5A; // Bisa diganti dengan 0x00 atau input lain jika diperlukan

            // Hitung word1 dan word2
            $word1 = ($byte5 << 8) | $byte6;
            $word2 = ($byte7 << 8) | $byte8;
            $cardNoFormatted = "$word1:$word2";

            // Buat hexResponse untuk konsistensi (opsional)
            $hexResponse = strtoupper(dechex($cardNo));

            Log::info("Manual Input: cardNo=$cardNo, byte5=" . sprintf("0x%02X", $byte5) .
                ", byte6=" . sprintf("0x%02X", $byte6) .
                ", byte7=" . sprintf("0x%02X", $byte7) .
                ", byte8=" . sprintf("0x%02X", $byte8) .
                ", formatted=$cardNoFormatted");

            return view('device.serial', [
                'cardNo' => $cardNo,
                'cardNoLittleEndian' => $cardNoFormatted,
                'hexResponse' => $hexResponse,
                'port' => null, // Tidak ada port untuk input manual
                'manualCardNo' => $cardNo,
                'manualCardNoFormatted' => $cardNoFormatted,
                'manualHexResponse' => $hexResponse
            ]);
        } catch (\Exception $e) {
            Log::error("Error: " . $e->getMessage());
            return view('device.serial', [
                'error' => $e->getMessage(),
                'port' => null
            ]);
        }
    }

    public function setTime(Request $request)
    {
        Log::info('setTime request data', $request->all()); // Add this to log raw input

        try {
            // Validasi input
            $validated = $request->validate([
                'sn' => 'required|string',
                'time' => 'required|date_format:Y-m-d H:i:s'
            ]);

            // Ambil URL API dari ApiModel berdasarkan id
            $apiUrl = ApiModel::where('id', 12)->value('name');
            if (!$apiUrl) {
                Log::error('API URL not found for id 12');
                return response()->json([
                    'success' => false,
                    'message' => 'API configuration not found'
                ], 500);
            }

            // Kirim request ke API
            $response = Http::timeout(12)->post($apiUrl, [
                'sn' => $validated['sn'],
                'Time' => $validated['time']
            ]);

            // Periksa status respons
            if ($response->successful()) {
                Log::info('Time set successfully', ['sn' => $validated['sn'], 'time' => $validated['time'], 'api_url' => $apiUrl]);
                return response()->json([
                    'success' => true,
                    'message' => 'Device time set successfully'
                ], 200);
            } else {
                Log::error('Failed to set device time', [
                    'sn' => $validated['sn'],
                    'time' => $validated['time'],
                    'api_url' => $apiUrl,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to set device time'
                ], $response->status());
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in setTime', [
                'sn' => $request->sn,
                'time' => $request->time,
                'errors' => $e->errors()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . implode(', ', $e->errors()['time'] ?? ['Invalid time format'])
            ], 422);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Failed to connect to API', [
                'sn' => $request->sn,
                'time' => $request->time,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to connect to API: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error in setTime', [
                'sn' => $request->sn,
                'time' => $request->time,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reboot(Request $request)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'sn' => 'required|string'
            ]);

            // Ambil URL API dari ApiModel berdasarkan id
            $apiUrl = ApiModel::where('id', 13)->value('name');
            if (!$apiUrl) {
                Log::error('API URL not found for id 13');
                return response()->json([
                    'success' => false,
                    'message' => 'API configuration not found'
                ], 500);
            }

            // Kirim request ke API
            $response = Http::timeout(12)->post($apiUrl, [
                'sn' => $validated['sn']
            ]);

            // Periksa status respons
            if ($response->successful()) {
                Log::info('Device rebooted successfully', ['sn' => $validated['sn'], 'api_url' => $apiUrl]);
                return response()->json([
                    'success' => true,
                    'message' => 'Device rebooted successfully'
                ], 200);
            } else {
                Log::error('Failed to reboot device', [
                    'sn' => $validated['sn'],
                    'api_url' => $apiUrl,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to reboot device'
                ], $response->status());
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in reboot', [
                'sn' => $request->sn,
                'errors' => $e->errors()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . implode(', ', $e->errors()['sn'] ?? ['Invalid SN'])
            ], 422);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Failed to connect to API', [
                'sn' => $request->sn,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to connect to API: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error in reboot', [
                'sn' => $request->sn,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}
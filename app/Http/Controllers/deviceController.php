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
use App\Models\usersDevice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class deviceController extends Controller
{

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 50);
        $perPage = $perPage == 'all' ? deviceGateModel::count() : (int) $perPage;
        $user = userProfileModel::all();
        $devices = deviceGateModel::paginate($perPage);
        $lastId = userProfileModel::max('ID');
        $nextUserId = $lastId ? $lastId + 1 : 1;

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

        return view('device.index', compact('devices', 'user', 'nextUserId'));
    }

    // Simpan data ke table usersxdevice
    public function saveUserToDevice(Request $request)
    {
        $data = $request->validate([
            'user_id'   => 'required|integer|exists:usersprofile,ID',
            'device_id' => 'required|integer|exists:devicegate,id',
            'Lift1'     => 'nullable|integer|between:0,255',
            'Lift2'     => 'nullable|integer|between:0,255',
            'Lift3'     => 'nullable|integer|between:0,255',
            'Lift4'     => 'nullable|integer|between:0,255',
        ]);

        usersDevice::updateOrCreate(
            [
                'userId' => $data['user_id'],
                'gateId' => $data['device_id'],
            ],
            [
                'Lift1' => $data['Lift1'] ?? 0,
                'Lift2' => $data['Lift2'] ?? 0,
                'Lift3' => $data['Lift3'] ?? 0,
                'Lift4' => $data['Lift4'] ?? 0,
            ]
        );

        return response()->json(['status' => 'success']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'number' => 'required',
            'type' => 'required',
            'sn' => 'required|unique:devicegate,sn',
            'ip' => 'required|ip|unique:devicegate,ip',
            'nodeid' => 'required|unique:devicegate,nodeid',
            'description' => 'required',
            'stat' => 'required',
            // Hanya validasi jika type == 1
            'Lift1' => 'nullable|integer|between:0,255',
            'Lift2' => 'nullable|integer|between:0,255',
            'Lift3' => 'nullable|integer|between:0,255',
            'Lift4' => 'nullable|integer|between:0,255',
        ]);

        $data = $request->all();

        // Jika bukan Soyal, set ke 0
        if ($request->type != 1) {
            $data['Lift1'] = $data['Lift2'] = $data['Lift3'] = $data['Lift4'] = 0;
        } else {
            // Pastikan nilai ada (default 0)
            $data['Lift1'] = (int)($request->Lift1 ?? 0);
            $data['Lift2'] = (int)($request->Lift2 ?? 0);
            $data['Lift3'] = (int)($request->Lift3 ?? 0);
            $data['Lift4'] = (int)($request->Lift4 ?? 0);
        }

        // Debug: Lihat apa yang dikirim
        Log::info('Data yang akan disimpan:', $data);

        deviceGateModel::create($data);

        return redirect()->back()->with('success', 'Device berhasil ditambahkan dengan akses lift!');
    }

    public function storeDevicelog(Request $request)
    {
        // Validasi sedikit biar aman
        $data = $request->validate([
            'log_date' => 'required|date',
            'modul'    => 'required|string',
            'desc'     => 'required|string',
            'status'   => 'required|in:Success,Failed,Warning',
        ]);

        devicelogModel::create($data);

        return response()->json(['status' => 'logged']);
    }
    // Save ke Table UsersProfile
    public function saveUserToProfile(Request $request)
    {
        $data = $request->validate([
            'user_id'     => 'required|integer',
            'user_name'   => 'required|string|max:255',
            'card_number' => 'required|string',
            'begin_date'  => 'required',
            'begin_time'  => 'required',
            'expire_date' => 'required',
            'expire_time' => 'required',
            'device_id'   => 'required|integer',
        ]);

        $deviceId   = $data['device_id'];
        $userId     = $data['user_id'];
        $userName   = $data['user_name'];
        $cardInput  = trim($data['card_number']);

        $device = deviceGateModel::find($deviceId);
        if (!$device) {
            return response()->json(['status' => 'error', 'message' => 'Device tidak ditemukan!'], 400);
        }

        $success = false;
        $message = 'Gagal proses user';
        $cardForDb = null;

        try {
            // === KONVERSI KARTU ===
            if (preg_match('/^\d{7,10}$/', $cardInput)) {
                $cardForDb = ltrim($cardInput, '0') ?: '0';
            } elseif (preg_match('/^(\d{1,5}):(\d{1,5})$/', $cardInput, $m)) {
                $high = intval(str_pad($m[1], 5, '0', STR_PAD_LEFT));
                $low  = intval(str_pad($m[2], 5, '0', STR_PAD_LEFT));
                $cardForDb = (string)(($high << 16) | $low);
            } else {
                throw new \Exception('Format kartu tidak valid!');
            }

            // Format tanggal
            $begin = Carbon::createFromFormat('m-d-Y H:i', $data['begin_date'] . ' ' . $data['begin_time']);
            $end   = Carbon::createFromFormat('m-d-Y H:i', $data['expire_date'] . ' ' . $data['expire_time']);

            // === CEK APAKAH USER SUDAH ADA DI DATABASE ===
            $userExists = userProfileModel::where('ID', $userId)->exists();

            if ($userExists) {
                // USER SUDAH ADA → HANYA UPDATE
                userProfileModel::where('ID', $userId)->update([
                    'NAME'       => $userName,
                    'Card'       => $cardForDb,
                    'BEGIN_DATE' => $begin,
                    'END_DATE'   => $end,
                ]);
                $message = "Success Update User $userName (ID: $userId)";
            } else {
                // USER BARU → INSERT
                $chars = '123456789ABCDEF';
                $password = '';
                for ($i = 0; $i < 6; $i++) {
                    $password .= $chars[random_int(0, 15)];
                }

                userProfileModel::create([
                    'ID'         => $userId,
                    'NAME'       => $userName,
                    'Card'       => $cardForDb,
                    'BEGIN_DATE' => $begin,
                    'END_DATE'   => $end,
                    'user_type'  => 0,
                    'PASSWORD'   => $password,
                ]);
                $message = "Success Insert User $userName (ID: $userId)";
            }

            $success = true;

            // Simpan relasi ke usersxdevice (jika belum ada)
            usersDevice::firstOrCreate([
                'userId' => $userId,
                'gateId' => $deviceId,
            ]);
        } catch (\Exception $e) {
            $message = 'Error: ' . $e->getMessage();
            Log::error('Proses user gagal: ' . $e->getMessage());
        }

        // === LOG SELALU MASUK (sukses/gagal) ===
        devicelogModel::create([
            'log_date' => Carbon::now(),
            'modul'    => json_encode([
                'user_id' => $userId,
                'sn'      => $device->sn ?? 'unknown',
                'ip'      => $device->ip ?? 'unknown',
                'card'    => $cardInput,
                'action'  => $userExists ? 'UPDATE' : 'INSERT'
            ], JSON_UNESCAPED_SLASHES),
            'desc'     => $message,
            'status'   => $success ? 'Success' : 'Failed',
        ]);

        return response()->json([
            'status'  => $success ? 'success' : 'error',
            'message' => $message,
            'card_db' => $cardForDb ?? null,
        ], $success ? 200 : 400);
    }


    // PAGINATION MODAL ADD USER LIFT
    public function searchUsersForLift(Request $request)
    {
        // BACA per_page DARI REQUEST (ini yang kamu lupa!)
        $perPage = $request->get('per_page', 10); // default 10 kalau tidak ada
        $perPage = in_array($perPage, [5, 10, 25, 50, 100]) ? (int)$perPage : 10;

        $query = userProfileModel::query()
            ->select('ID', 'NAME', 'Card', 'BEGIN_DATE', 'END_DATE')
            ->whereNotNull('Card')
            ->where('Card', '!=', '');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ID', 'like', "%{$search}%")
                    ->orWhere('NAME', 'like', "%{$search}%")
                    ->orWhere('Card', 'like', "%{$search}%");
            });
        }

        try {
            $users = $query->with(['usersDevices.device'])
                ->orderBy('ID', 'asc')
                ->paginate($perPage); // ← SEKARANG PAKAI $perPage DARI REQUEST!

            // Pastikan relasi aman
            foreach ($users as $user) {
                $user->setRelation('usersDevices', $user->usersDevices ?? collect());
            }

            return view('device.modal.partials.user_lift_table', compact('users'))->render();
        } catch (\Exception $e) {
            Log::error('Error searchUsersForLift: ' . $e->getMessage());
            return '<tr><td colspan="6" class="text-danger text-center py-4">Error server</td>';
        }
    }

    // Soyal 871821:891211 Ke Decimal 1277191210
    function soyalToNumber($soyalCard)
    {
        if (empty($soyalCard)) return null;

        // Jika sudah angka 7-10 digit → langsung kembalikan (tanpa konversi)
        if (preg_match('/^\d{7,10}$/', $soyalCard)) {
            return ltrim($soyalCard, '0') ?: '0'; // hilangkan leading zero
        }

        // Jika format XXXXX:YYYYY
        if (preg_match('/^(\d{1,5}):(\d{1,5})$/', $soyalCard, $m)) {
            $word1 = str_pad($m[1], 5, '0', STR_PAD_LEFT);
            $word2 = str_pad($m[2], 5, '0', STR_PAD_LEFT);

            $high = intval($word1);           // 00093 → 93
            $low  = intval($word2);           // 21611 → 21611

            $num = ($high << 16) | $low;      // gabungkan jadi 32-bit number
            return (string) $num;             // hasil: 6116459
        }

        return null;
    }

    public function edit($encryptedId)
    {
        $id = decryptId($encryptedId);
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
            'number' => 'required',
            'flagstatus' => 'required|boolean',
            'type' => 'required',
            'sn' => 'required|string|max:100',
            'ip' => 'required|ip',
            'nodeid' => 'required|integer',
            'description' => 'required|string|max:255',
            'stat' => 'required',
            // Hanya validasi lift jika type == 1
            'Lift1' => 'nullable|integer|between:0,255',
            'Lift2' => 'nullable|integer|between:0,255',
            'Lift3' => 'nullable|integer|between:0,255',
            'Lift4' => 'nullable|integer|between:0,255',
        ]);
        $device = deviceGateModel::findOrFail($id);
        $data = $request->only([
            'name',
            'number',
            'flagstatus',
            'type',
            'sn',
            'ip',
            'nodeid',
            'description',
            'stat',
            'Lift1',
            'Lift2',
            'Lift3',
            'Lift4'
        ]);
        // Jika bukan Soyal, set lift ke 0
        if ($request->type != 1) {
            $data['Lift1'] = $data['Lift2'] = $data['Lift3'] = $data['Lift4'] = 0;
        }
        Log::info('Update Device ID ' . $id, $data);
        $device->update($data);
        return redirect()->route('device')->with('success', 'Device berhasil diperbarui!');
    }

    // deviceController.php
    public function getUsersForModal(Request $request)
    {
        try {
            $query = userProfileModel::query();

            // Pencarian
            if ($search = $request->query('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('NAME', 'LIKE', "%{$search}%")
                        ->orWhere('ID', 'LIKE', "%{$search}%")
                        ->orWhere('Card', 'LIKE', "%{$search}%");
                });
            }

            $users = $query->orderBy('NAME', 'ASC')->paginate(25);

            // Pastikan path view benar
            $html = view('device.modal.partials.user_table_modal', compact('users'))->render();

            return response()->json([
                'html' => $html
            ]);
        } catch (\Exception $e) {
            Log::error('Error getUsersForModal: ' . $e->getMessage());
            return response()->json([
                'html' => '<div class="text-center py-5 text-danger"><strong>Gagal memuat data user</strong><br>' . $e->getMessage() . '</div>'
            ], 500);
        }
    }


    public function destroy($id)
    {
        $device = deviceGateModel::findOrFail($id);
        $device->delete();
        return redirect()->back()->with('success', 'Device berhasil dihapus!');
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


    public function adduser(Request $request)
    {
        $validated = $request->validate([
            'user_id'      => 'required|string',
            'user_name'    => 'required|string',
            'card_number'  => 'required|string',
            'ip_address'   => 'required|ip',
            'node_id'      => 'required|integer',
            'pin'          => 'sometimes|string',
            'type'         => 'sometimes|string|in:kartu,finger,face',
            'begin_date'   => 'required|string',
            'expire_date'  => 'required|string',
            'begin_time'   => 'required|string',
            'expire_time'  => 'required|string',
            'lift1'        => 'required|integer|between:0,255',
            'lift2'        => 'required|integer|between:0,255',
            'lift3'        => 'required|integer|between:0,255',
            'lift4'        => 'required|integer|between:0,255',
        ]);

        // Cari device berdasarkan IP atau nodeid
        $device = deviceGateModel::where('ip', $request->ip_address)
            ->orWhere('nodeid', $request->node_id)
            ->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device tidak ditemukan di database!'
            ], 404);
        }

        $soyalApiUrl = ApiModel::where('id', 1)->value('name');

        try {
            $response = Http::timeout(20)->post($soyalApiUrl, $validated);

            if ($response->successful()) {
                $responseData = $response->json();

                // === 1. SIMPAN KE usersxdevice (firstOrCreate) ===
                usersDevice::updateOrCreate(
                    ['userId' => $request->user_id, 'gateId' => $device->id],
                    ['userId' => $request->user_id, 'gateId' => $device->id]
                );

                // === 2. SIMPAN LOG KE devicelog ===
                devicelogModel::create([
                    'log_date' => Carbon::now(),
                    'modul'    => json_encode([
                        'user_id'     => $request->user_id,
                        'sn'          => $device->sn,
                        'username'    => $request->user_name,
                        'card_number' => $request->card_number,
                        'ip_address'  => $request->ip_address,
                    ], JSON_UNESCAPED_SLASHES),
                    'desc'     => "Success Insert User {$request->user_name}",
                    'status'   => 'Successfully',
                ]);

                Log::info('Soyal Add User Success + DB Saved', [
                    'user_id' => $request->user_id,
                    'device_id' => $device->id,
                    'ip' => $request->ip_address,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'User berhasil ditambahkan ke mesin Soyal dan disimpan ke database!'
                ], 200);
            } else {
                // Gagal kirim ke mesin → tetap log error
                devicelogModel::create([
                    'log_date' => Carbon::now(),
                    'modul'    => json_encode([
                        'user_id'     => $request->user_id,
                        'username'    => $request->user_name,
                        'ip_address'  => $request->ip_address,
                    ]),
                    'desc'     => "Gagal kirim user {$request->user_name} ke mesin Soyal",
                    'status'   => 'Failed',
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Gagal kirim ke mesin Soyal',
                    'detail'  => $response->body()
                ], $response->status());
            }
        } catch (\Exception $e) {
            devicelogModel::create([
                'log_date' => Carbon::now(),
                'modul'    => json_encode([
                    'user_id'    => $request->user_id,
                    'username'   => $request->user_name,
                    'ip_address' => $request->ip_address,
                ]),
                'desc'    => "Error: " . $e->getMessage(),
                'status'  => 'Failed',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa connect ke mesin Soyal',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function saveUserDeviceRelation(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'device_ip' => 'required|ip',
            'user_name' => 'required',
            'card_number' => 'required'
        ]);

        $device = deviceGateModel::where('ip', $request->device_ip)->firstOrFail();

        usersDevice::updateOrCreate(
            ['userId' => $request->user_id, 'gateId' => $device->id],
            ['userId' => $request->user_id, 'gateId' => $device->id]
        );

        devicelogModel::create([
            'log_date' => now(),
            'modul'    => json_encode([
                'user_id'     => $request->user_id,
                'sn'          => $device->sn,
                'username'    => $request->user_name,
                'card_number' => $request->card_number,
                'ip_address'  => $device->ip,
            ]),
            'desc'     => "Success Insert User {$request->user_name}",
            'status'   => 'Successfully',
        ]);

        return response()->json(['status' => 'saved']);
    }



    public function setTime(Request $request)
    {
        Log::info('setTime raw request', $request->all());

        try {
            $validated = $request->validate([
                'sn'   => 'required|string',
                'time' => 'required|date', // terima semua format tanggal yang bisa diparse Carbon
            ]);

            // Konversi ke format yang API butuhkan: Y-m-d H:i:s
            $formattedTime = Carbon::parse($validated['time'])->format('Y-m-d H:i:s');

            $apiUrl = ApiModel::where('id', 12)->value('name');
            if (!$apiUrl) {
                return response()->json(['success' => false, 'message' => 'API URL not found'], 500);
            }

            $response = Http::timeout(15)->post($apiUrl, [
                'sn'        => $validated['sn'],
                'Time'      => $formattedTime,   // tetap pakai key "Time" sesuai API
                // kalau kamu butuh userid, starttime, endtime → tambahkan di sini
            ]);

            if ($response->successful()) {
                return response()->json(['success' => true, 'message' => 'Time set successfully']);
            } else {
                Log::error('API gagal', ['response' => $response->body(), 'status' => $response->status()]);
                return response()->json(['success' => false, 'message' => 'Failed to set time'], $response->status());
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Format waktu tidak valid',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('setTime error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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

            $isConnected = $response->status() === 200;

            // Update database flagstatus
            $device = deviceGateModel::where('sn', $request->sn)->first();
            if ($device) {
                $device->flagstatus = $isConnected ? 1 : 0;
                $device->save();
            }

            // Periksa status respons
            if ($isConnected) {
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
            // Update database flagstatus on exception
            $device = deviceGateModel::where('sn', $request->sn)->first();
            if ($device) {
                $device->flagstatus = 0;
                $device->save();
            }

            Log::error('Failed to connect to API', [
                'sn' => $request->sn,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Mechine Not Connected: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
             // Update database flagstatus on exception
             $device = deviceGateModel::where('sn', $request->sn)->first();
             if ($device) {
                 $device->flagstatus = 0;
                 $device->save();
             }

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

            $isConnected = ($exitCode === 0);

            // Update database flagstatus
            $device = deviceGateModel::where('ip', $ip)->first();
            if ($device) {
                $device->flagstatus = $isConnected ? 1 : 0;
                $device->save();
            }

            // Check if ping was successful (exit code 0 indicates success)
            if ($isConnected) {
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
             // Update database flagstatus on exception (assume disconnect)
             $device = deviceGateModel::where('ip', $request->ip)->first();
             if ($device) {
                 $device->flagstatus = 0;
                 $device->save();
             }

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

    }

    public function getDeviceStatuses()
    {
        // Read directly from DB (updated by Console Command)
        $devices = deviceGateModel::select('id', 'sn', 'flagstatus')->get();
        
        $results = $devices->map(function ($device) {
            return [
                'id' => $device->id,
                'sn' => $device->sn,
                'connected' => $device->flagstatus == 1,
                'status_text' => $device->flagstatus == 1 ? 'Connected' : 'Disconnect'
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $results,
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);
    }
    public function getDeviceList(Request $request)
    {
        try {
            $devices = deviceGateModel::select('id', 'name', 'number', 'flagstatus', 'type', 'sn', 'ip', 'nodeid', 'description', 'stat')
                ->get()
                ->map(function ($device) {
                    // Transform flagstatus
                    $flagStatusLabel = $device->flagstatus == 1 ? 'Connect' : 'Disconnect';

                    // Transform type
                    $typeLabel = match ((int)$device->type) {
                        1 => 'Soyal',
                        0 => 'Fingerprint',
                        50 => 'Face',
                        default => 'Unknown'
                    };

                    // Transform stat
                    $statLabel = $device->stat == 0 ? 'IN' : 'OUT';

                    return [
                        'id' => $device->id,
                        'name' => $device->name,
                        'number' => $device->number,
                        'flagstatus' => $flagStatusLabel,
                        'type' => $typeLabel,
                        'sn' => $device->sn,
                        'ip' => $device->ip,
                        'nodeid' => $device->nodeid,
                        'description' => $device->description,
                        'stat' => $statLabel,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $devices
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch device list',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
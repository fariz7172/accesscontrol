<?php

namespace App\Http\Controllers;

use App\Models\ApiModel;
use App\Models\Branch;
use App\Models\departmentModel;
use App\Models\deviceGateModel;
use App\Models\DeviceGroupModel;
use App\Models\DeviceJoinGate;
use App\Models\devicelogModel;
use App\Models\pictureModel;
use App\Models\tagModel;
use App\Models\userDataModel;
use App\Models\userProfileModel;
use App\Models\usersDevice;
use App\Models\Weekzone;
use App\Models\WeekzoneUser;
use Carbon\Carbon;
use DateTime;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class userProfileController extends Controller
{
    public function index(Request $request)
    {
        $devices = deviceGateModel::all();
        $deviceGroup = DeviceGroupModel::all();
        $weekzones = Weekzone::all(); // Tambahkan query untuk mengambil data weekzone
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $query = userProfileModel::with('department', 'userData.deviceGate', 'deviceGroup');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('NAME', 'LIKE', "%{$search}%")
                    ->orWhere('ID', 'LIKE', "%{$search}%")
                    ->orWhereHas('department', function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        $userprofiles = $query->paginate($perPage);
        $userprofiles->appends(['search' => $search, 'per_page' => $perPage]);

        $soyalApiUrl = ApiModel::where('id', 5)->value('name');
        $sztimmyApiUrl = ApiModel::where('id', 6)->value('name');
        $soyalAddUserApiUrl = ApiModel::where('id', 1)->value('name');
        $sztimmyRegisterFaceApiUrl = ApiModel::where('id', 2)->value('name');
        $sztimmyUserAccessApiUrl = ApiModel::where('id', 3)->value('name');
        $sztimmyGetUserListApiUrl = ApiModel::where('id', 4)->value('name');
        $syncMemberDreampost = ApiModel::where('id', 7)->value('name');
        $syncMemberViewDreampos = ApiModel::where('id', 8)->value('name');
        $sztimmyGetFingerprintApiUrl = ApiModel::where('id', 9)->value('name');

        $sztimmyUserWeekzoneApiUrl = ApiModel::where('id', 16)->value('name');

        foreach ($userprofiles as $userprofile) {
            $deviceNames = usersDevice::where('userId', $userprofile->ID)
                ->distinct('gateId')
                ->join('devicegate', 'usersxdevice.gateId', '=', 'devicegate.id')
                ->pluck('devicegate.name')
                ->implode(',');
            $userprofile->deviceNames = $deviceNames;
        }

        return view('userProfile.index', compact(
            'userprofiles',
            'devices',
            'deviceGroup',
            'weekzones', // Tambahkan weekzones ke view
            'soyalApiUrl',
            'sztimmyApiUrl',
            'soyalAddUserApiUrl',
            'sztimmyRegisterFaceApiUrl',
            'sztimmyUserAccessApiUrl',
            'sztimmyGetUserListApiUrl',
            'syncMemberDreampost',
            'syncMemberViewDreampos',
            'sztimmyGetFingerprintApiUrl',
            'sztimmyUserWeekzoneApiUrl'
        ));
    }

    public function getWeekzoneByUserAndSn($userId, $sn)
    {
        $device = deviceGateModel::where('sn', $sn)->first();
        if (!$device) {
            return response()->json(['weekzone' => 0]);
        }

        $weekzone = DB::table('weekzoneuser')
            ->where('userId', $userId)
            ->where('deviceId', $device->id)
            ->value('wzid');

        return response()->json(['weekzone' => $weekzone ?? 0]);
    }

    // simpan data ke table weekzone
    public function insertWeekzoneToMachine(Request $request)
    {
        try {
            $request->validate([
                'userId' => 'required|exists:usersprofile,ID',
                'deviceIds' => 'required|array',
                'deviceIds.*' => 'exists:devicegate,id'
            ]);

            $userId = $request->userId;
            $deviceIds = $request->deviceIds;

            // Ambil API URL
            $sztimmyUserWeekzoneApiUrl = ApiModel::where('id', 16)->value('name');
            if (!$sztimmyUserWeekzoneApiUrl) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'API URL untuk userWeekzone tidak ditemukan (id=16)'
                ], 500);
            }

            // Filter hanya device type 0 atau 50
            $devices = deviceGateModel::whereIn('id', $deviceIds)
                ->whereIn('type', [0, 50])
                ->get();

            if ($devices->isEmpty()) {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'Tidak ada perangkat dengan tipe 0 atau 50 yang dipilih.'
                ], 200);
            }

            $payload = [];
            $client = new Client();

            foreach ($devices as $device) {
                // Cek apakah ada data di weekzoneuser
                $weekzone = WeekzoneUser::where('userid', $userId)
                    ->where('deviceid', $device->id)
                    ->orderBy('id', 'desc')
                    ->first();

                if (!$weekzone) {
                    Log::info("No weekzone data for user {$userId} on device {$device->id}");
                    continue;
                }

                $payload[] = [
                    'sn' => $device->sn,
                    'userid' => $userId,
                    'weekzone' => (int)$weekzone->wzid
                ];
            }

            if (empty($payload)) {
                return response()->json([
                    'status' => 'info',
                    'message' => 'Tidak ada data weekzone yang valid untuk dikirim.'
                ], 200);
            }

            // Kirim ke API
            $response = $client->post($sztimmyUserWeekzoneApiUrl, [
                'json' => $payload,
                'timeout' => 60
            ]);

            $responseBody = $response->getBody()->getContents();
            Log::info('Weekzone sync success', [
                'userId' => $userId,
                'payload' => $payload,
                'response' => $responseBody
            ]);

            // Log ke devicelog
            foreach ($payload as $item) {
                devicelogModel::create([
                    'log_date' => now()->format('Y-m-d H:i:s'),
                    'modul' => json_encode($item),
                    'desc' => "Weekzone synced: User {$userId} → Device {$item['sn']} (WZ: {$item['weekzone']})",
                    'status' => 'Success'
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Data weekzone berhasil dikirim ke mesin.',
                'sent_count' => count($payload)
            ], 200);
        } catch (RequestException $e) {
            $error = $e->getMessage();
            if ($e->hasResponse()) {
                $error = $e->getResponse()->getBody()->getContents();
            }

            Log::error('Weekzone sync failed', [
                'userId' => $request->userId,
                'error' => $error
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengirim data ke mesin: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            Log::error('Unexpected error in insertWeekzoneToMachine', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteFromWeekzoneUser(Request $request)
    {
        $request->validate([
            'userId' => 'required|exists:usersprofile,ID',
            'deviceId' => 'required|exists:devicegate,id',
        ]);

        $deleted = DB::table('weekzoneuser')
            ->where('userid', $request->userId)
            ->where('deviceid', $request->deviceId)
            ->delete();

        return response()->json([
            'status' => 'success',
            'message' => $deleted ? 'Weekzone data deleted' : 'No weekzone data found',
            'deleted' => $deleted
        ]);
    }


    public function create()
    {
        $departments = departmentModel::all();
        $branch = Branch::all();
        return view('userProfile.create', compact('departments', 'branch'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'NAME' => 'required|string|max:255',
            'BEGIN_DATE' => 'required|date',
            'END_DATE' => 'required|date|after_or_equal:BEGIN_DATE',
            'Depid' => 'required|numeric|exists:departemen,id',
            'Branchid' => 'required|numeric|exists:branch,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:3048',
            'Card' => 'nullable|unique:usersprofile,Card',
            'NoIdentitas' => 'required|numeric',
            'BIRTHDAY' => 'required|date',
            'PASSWORD' => 'required|string|min:6',
            'user_type' => 'required|in:0,1,2', // Validasi untuk user_type
        ]);

        $lastAddr = userProfileModel::max('ID');
        $newAddr = $lastAddr ? $lastAddr + 1 : 1;

        $data = [
            'ID' => $newAddr,
            'NAME' => $request->NAME,
            'BEGIN_DATE' => $request->BEGIN_DATE,
            'END_DATE' => $request->END_DATE,
            'Depid' => $request->Depid,
            'Branchid' => $request->Branchid,
            'Card' => $request->Card,
            'NoIdentitas' => $request->NoIdentitas,
            'BIRTHDAY' => $request->BIRTHDAY,
            'PASSWORD' => $request->PASSWORD,
            'user_type' => $request->user_type, // Tambahkan user_type
        ];

        if ($request->hasFile('photo')) {
            $image = Image::make($request->file('photo'));

            $image->resize(450, 450, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $compressedImage = $image->encode('jpg', 75);
            while (strlen($compressedImage) > 75000) {
                $compressedImage = $image->encode('jpg', 50);
            }

            $data['photo'] = 'data:image/jpeg;base64,' . base64_encode($compressedImage);
        }

        $userProfile = userProfileModel::create($data);

        return redirect()->route('userProfiles.index')->with('success', 'User profile added successfully.');
    }

    public function edit($id)
    {
        $user = userProfileModel::with('department', 'userData')->findOrFail(decryptId($id));
        $departments = departmentModel::all();
        $branches = Branch::all();
        return view('userProfile.edit', compact('user', 'departments', 'branches'));
    }

    public function update(Request $request, $id)
    {
        $user = userProfileModel::findOrFail(decryptId($id));

        $data = $request->validate([
            'NAME' => 'required|string|max:255',
            'NoIdentitas' => 'required|numeric',
            'BIRTHDAY' => 'required|date',
            'Branchid' => 'required|exists:branch,id',
            'Depid' => 'required|exists:departemen,id',
            'BEGIN_DATE' => 'required|date',
            'END_DATE' => 'required|date|after_or_equal:BEGIN_DATE',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:3048',
            'Card' => 'nullable|string|unique:usersprofile,Card,' . $user->ID . ',ID',
            'PASSWORD' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) use ($user) {
                    if ($value) {
                        if ($value === $user->PASSWORD) {
                            return;
                        }
                        $existingPassword = userProfileModel::where('PASSWORD', $value)
                            ->where('ID', '!=', $user->ID)
                            ->exists();
                        if ($existingPassword) {
                            $fail('The password has already been taken by another user.');
                        }
                    }
                },
            ],
            'user_type' => 'required|in:0,1,2', // Validasi untuk user_type
        ]);

        if (!$request->filled('PASSWORD')) {
            unset($data['PASSWORD']);
        }

        if ($request->hasFile('photo')) {
            $image = Image::make($request->file('photo'));

            $image->resize(450, 450, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $compressedImage = $image->encode('jpg', 75);
            while (strlen($compressedImage) > 75000) {
                $compressedImage = $image->encode('jpg', 50);
            }

            $data['photo'] = 'data:image/jpeg;base64,' . base64_encode($compressedImage);
        }

        $data['user_type'] = $request->user_type; // Tambahkan user_type ke data update

        $user->update($data);

        return redirect()->route('userProfiles.index')->with('success', 'User profile updated successfully.');
    }


    public function generatePasswords(Request $request)
    {
        try {
            $users = userProfileModel::where(function ($query) {
                $query->where('PASSWORD', 0)
                    ->orWhereNull('PASSWORD');
            })->get();

            if ($users->isEmpty()) {
                return redirect()->route('userProfiles.index')
                    ->with('success', 'No users found with empty or zero passwords to update.');
            }

            $characters = 'ABCDE0123456789';
            $charLength = strlen($characters);
            $updatedCount = 0;

            foreach ($users as $user) {
                $password = '';
                for ($i = 0; $i < 10; $i++) {
                    $password .= $characters[rand(0, $charLength - 1)];
                }

                $hashedPassword = $password;

                $user->update([
                    'PASSWORD' => $hashedPassword,
                ]);

                $updatedCount++;
            }

            return redirect()->route('userProfiles.index')
                ->with('success', "Passwords updated successfully for {$updatedCount} users with empty or zero passwords.");
        } catch (\Exception $e) {
            Log::error('Error generating passwords: ' . $e->getMessage());
            return redirect()->route('userProfiles.index')
                ->with('error', 'Failed to generate passwords: ' . $e->getMessage());
        }
    }

    public function syncMembersFromApi(Request $request)
    {
        try {
            Log::info('syncMembersFromApi called', ['input' => $request->all()]);
            $request->validate([
                'selected_member_numbers' => 'required|array',
                'selected_member_numbers.*' => 'string|min:1',
            ]);

            $selectedMemberNumbers = $request->input('selected_member_numbers', []);

            if (empty($selectedMemberNumbers)) {
                Log::warning('No members selected for syncing');
                return response()->json(['status' => 'error', 'message' => 'No members selected for syncing'], 400);
            }

            $devices = deviceGateModel::where('type', '0')->get();
            $reachableDevices = $devices->filter(function ($device) {
                return $this->isDeviceReachable($device->ip, 787);
            });

            if ($reachableDevices->isEmpty()) {
                Log::error('No reachable devices found');
                return response()->json(['status' => 'error', 'message' => 'Tidak ada perangkat yang dapat dijangkau di jaringan'], 503);
            }

            $syncMemberDreampost = ApiModel::where('id', 7)->value('name') ?? env('SYNC_MEMBER_DREAMPOST_URL', 'http://localhost:8080/api/members');
            $sztimmyRegisterFaceApiUrl = ApiModel::where('id', 2)->value('name') ?? env('SZYMMY_REGISTER_FACE_API_URL', 'http://localhost:787/sztimmy/registerface');
            $sztimmyUserAccessApiUrl = ApiModel::where('id', 3)->value('name') ?? env('SZYMMY_USER_ACCESS_API_URL', 'http://localhost:787/sztimmy/useraccess');

            $missingUrls = [];
            if (!$syncMemberDreampost) $missingUrls[] = 'syncMemberDreampost';
            if (!$sztimmyRegisterFaceApiUrl) $missingUrls[] = 'sztimmy/registerface';
            if (!$sztimmyUserAccessApiUrl) $missingUrls[] = 'sztimmy/useraccess';
            if (!empty($missingUrls)) {
                Log::error('Missing API URLs', ['missing' => $missingUrls]);
                return response()->json(['status' => 'error', 'message' => 'URL API tidak ditemukan: ' . implode(', ', $missingUrls)], 500);
            }

            Log::info('Using API URLs', [
                'syncMemberDreampost' => $syncMemberDreampost,
                'sztimmyRegisterFace' => $sztimmyRegisterFaceApiUrl,
                'sztimmyUserAccess' => $sztimmyUserAccessApiUrl,
            ]);

            $response = Http::timeout(60)->post($syncMemberDreampost, [
                'Number' => $selectedMemberNumbers,
            ]);

            if (!$response->successful()) {
                Log::error('Failed to fetch data from API', ['status' => $response->status(), 'response' => $response->body()]);
                return response()->json(['status' => 'error', 'message' => 'Gagal mengambil data dari API: ' . $response->status()], $response->status());
            }

            $apiData = $response->json();
            $apiData = is_array($apiData) ? $apiData : (isset($apiData['Number']) ? [$apiData] : []);

            if (empty($apiData)) {
                Log::warning('API returned empty data', ['numbers' => $selectedMemberNumbers]);
                return response()->json(['status' => 'error', 'message' => 'Tidak ada data ditemukan untuk nomor yang dipilih'], 404);
            }

            $totalSynced = 0;
            $syncedUserIds = [];

            DB::transaction(function () use ($apiData, $selectedMemberNumbers, &$totalSynced, &$syncedUserIds) {
                foreach ($apiData as $memberData) {
                    if (!is_array($memberData) || !isset($memberData['Number']) || !in_array($memberData['Number'], $selectedMemberNumbers)) {
                        Log::warning('Invalid or unselected member data', ['data' => $memberData]);
                        continue;
                    }

                    $photoData = null;
                    if (isset($memberData['Image']) && !empty($memberData['Image']) && $memberData['Image'] !== '???') {
                        if (preg_match('/^data:image\/[a-z]+;base64,/', $memberData['Image'])) {
                            $photoData = $memberData['Image'];
                        } else if (filter_var($memberData['Image'], FILTER_VALIDATE_URL)) {
                            $photoData = $memberData['Image'];
                        } else {
                            Log::warning('Invalid Image format for member', ['ID' => $memberData['ID'], 'Image' => $memberData['Image']]);
                        }
                    }

                    $memberFields = [
                        'ID' => $memberData['ID'],
                        'MemberNo' => $memberData['ID'],
                        'Card' => $memberData['Number'],
                        'NAME' => $memberData['Name'] ?? null,
                        'BEGIN_DATE' => isset($memberData['JoinDate']) ? date('Y-m-d', strtotime($memberData['JoinDate'])) : null,
                        'END_DATE' => isset($memberData['ExpireDate']) ? date('Y-m-d', strtotime($memberData['ExpireDate'])) : null,
                        'photo' => $photoData,
                        'user_type' => '1', // Default ke 'Member' untuk data dari API
                    ];

                    try {
                        $existingMember = userProfileModel::where('ID', $memberData['ID'])->lockForUpdate()->first();

                        if ($existingMember) {
                            $existingMember->update($memberFields);
                            Log::info('Updated member', ['ID' => $memberData['ID'], 'photo' => $photoData]);
                        } else {
                            userProfileModel::create($memberFields);
                            Log::info('Inserted new member', ['ID' => $memberData['ID'], 'photo' => $photoData]);
                        }
                        $totalSynced++;
                        $syncedUserIds[] = $memberData['ID'];
                    } catch (\Illuminate\Database\QueryException $e) {
                        Log::error('Database error for member ID ' . $memberData['ID'], ['error' => $e->getMessage()]);
                        if ($e->getCode() == 23000) {
                            userProfileModel::where('ID', $memberData['ID'])->update($memberFields);
                            $totalSynced++;
                            $syncedUserIds[] = $memberData['ID'];
                            Log::info('Updated member due to duplicate key', ['ID' => $memberData['ID'], 'photo' => $photoData]);
                        }
                    }
                }
            });

            if ($totalSynced === 0) {
                return response()->json(['status' => 'error', 'message' => 'Tidak ada member yang berhasil disinkronkan'], 404);
            }

            $client = new Client();

            Log::info('Reachable devices to sync', ['devices' => $reachableDevices->pluck('sn')->toArray()]);

            foreach ($syncedUserIds as $userId) {
                $userProfile = userProfileModel::where('ID', $userId)->first();
                if (!$userProfile) {
                    Log::warning('User profile not found for ID', ['userId' => $userId]);
                    continue;
                }

                foreach ($reachableDevices as $device) {
                    if (empty($device->sn)) {
                        Log::warning('Invalid device serial number', ['sn' => $device->sn]);
                        continue;
                    }

                    $registerFacePayload = [
                        'sn' => $device->sn,
                        'userid' => $userProfile->ID,
                        'username' => $userProfile->NAME ?? '',
                        'type' => '0',
                        'admin' => '0',
                        'cardnumber' => $userProfile->Card ?? '',
                        'starttime' => $userProfile->BEGIN_DATE ? Carbon::parse($userProfile->BEGIN_DATE)->format('Y-m-d H:i:s') : '',
                        'endtime' => $userProfile->END_DATE ? Carbon::parse($userProfile->END_DATE)->format('Y-m-d H:i:s') : '',
                        'psw' => '',
                        'fdata' => '',
                    ];

                    $userAccessPayload = [
                        'sn' => $device->sn,
                        'userid' => $userProfile->ID,
                        'starttime' => $userProfile->BEGIN_DATE ? Carbon::parse($userProfile->BEGIN_DATE)->format('Y-m-d H:i:s') : '2025-03-01 00:00:00',
                        'endtime' => $userProfile->END_DATE ? Carbon::parse($userProfile->END_DATE)->format('Y-m-d H:i:s') : '2025-06-06 23:59:00',
                    ];

                    $maxRetries = 3;
                    $retryCount = 0;
                    $registerFaceSuccess = false;
                    $userAccessSuccess = false;

                    while ($retryCount < $maxRetries) {
                        try {
                            $response = $client->post($sztimmyRegisterFaceApiUrl, [
                                'json' => $registerFacePayload,
                                'timeout' => 120,
                            ]);
                            $responseBody = $response->getBody()->getContents();
                            Log::info('Successfully sent data to sztimmy/registerface', [
                                'userId' => $userId,
                                'sn' => $device->sn,
                                'response' => $responseBody,
                            ]);

                            devicelogModel::create([
                                'log_date' => now()->format('Y-m-d H:i:s'),
                                'modul' => json_encode($registerFacePayload),
                                'desc' => "Successfully synced user {$userProfile->NAME} to device {$device->sn} for registerface",
                                'status' => 'Successfully'
                            ]);

                            usersDevice::updateOrCreate(
                                [
                                    'userId' => $userProfile->ID,
                                    'gateId' => $device->id,
                                ],
                                [
                                    'userId' => $userProfile->ID,
                                    'gateId' => $device->id,
                                ]
                            );

                            $registerFaceSuccess = true;
                            break;
                        } catch (RequestException $e) {
                            $retryCount++;
                            Log::warning('Retry attempt ' . $retryCount . ' for userId ' . $userId . ' and sn ' . $device->sn . ' (registerface)', [
                                'error' => $e->getMessage(),
                            ]);
                            if ($retryCount === $maxRetries) {
                                Log::error('Failed to send data to sztimmy/registerface after ' . $maxRetries . ' attempts', [
                                    'userId' => $userId,
                                    'sn' => $device->sn,
                                    'error' => $e->getMessage(),
                                ]);

                                devicelogModel::create([
                                    'log_date' => now()->format('Y-m-d H:i:s'),
                                    'modul' => json_encode($registerFacePayload),
                                    'desc' => "Failed to sync user {$userProfile->NAME} to device {$device->sn} for registerface: {$e->getMessage()}",
                                    'status' => 'Failed'
                                ]);
                            }
                            sleep(2);
                        }
                    }

                    if ($registerFaceSuccess) {
                        $retryCount = 0;
                        while ($retryCount < $maxRetries) {
                            try {
                                $response = $client->post($sztimmyUserAccessApiUrl, [
                                    'json' => $userAccessPayload,
                                    'timeout' => 120,
                                ]);
                                $responseBody = $response->getBody()->getContents();
                                Log::info('Successfully sent data to sztimmy/useraccess', [
                                    'userId' => $userId,
                                    'sn' => $device->sn,
                                    'response' => $responseBody,
                                ]);

                                devicelogModel::create([
                                    'log_date' => now()->format('Y-m-d H:i:s'),
                                    'modul' => json_encode($userAccessPayload),
                                    'desc' => "Successfully synced user {$userProfile->NAME} to device {$device->sn} for useraccess",
                                    'status' => 'Successfully'
                                ]);

                                $userAccessSuccess = true;
                                break;
                            } catch (RequestException $e) {
                                $retryCount++;
                                Log::warning('Retry attempt ' . $retryCount . ' for userId ' . $userId . ' and sn ' . $device->sn . ' (useraccess)', [
                                    'error' => $e->getMessage(),
                                ]);
                                if ($retryCount === $maxRetries) {
                                    Log::error('Failed to send data to sztimmy/useraccess after ' . $maxRetries . ' attempts', [
                                        'userId' => $userId,
                                        'sn' => $device->sn,
                                        'error' => $e->getMessage(),
                                    ]);

                                    devicelogModel::create([
                                        'log_date' => now()->format('Y-m-d H:i:s'),
                                        'modul' => json_encode($userAccessPayload),
                                        'desc' => "Failed to sync user {$userProfile->NAME} to device {$device->sn} for useraccess: {$e->getMessage()}",
                                        'status' => 'Failed'
                                    ]);
                                }
                                sleep(2);
                            }
                        }
                    }

                    if ($registerFaceSuccess && $userAccessSuccess) {
                        Log::info('Successfully recorded to devicelog and usersxdevice', [
                            'userId' => $userProfile->ID,
                            'gateId' => $device->id,
                        ]);
                    }
                }
            }

            if ($totalSynced === 0) {
                return response()->json(['status' => 'error', 'message' => 'Tidak ada member yang berhasil disinkronkan'], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Data dari API berhasil disinkronkan dan dikirim ke mesin',
                'total_synced' => $totalSynced
            ], 200);
        } catch (\Exception $e) {
            Log::error('Sync error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function getProfilePhoto($userId)
    {
        $userProfile = userProfileModel::where('ID', $userId)->first();

        if (!$userProfile || !$userProfile->photo) {
            return response()->file(public_path('images/placeholder.jpg'));
        }

        if (preg_match('/^data:image\/[a-z]+;base64,/', $userProfile->photo)) {
            $base64String = preg_replace('/^data:image\/[a-z]+;base64,/', '', $userProfile->photo);
            $imageData = base64_decode($base64String);

            if ($imageData === false) {
                Log::error('Failed to decode base64 image for user', ['userId' => $userId]);
                return response()->file(public_path('images/placeholder.jpg'));
            }

            return response($imageData)
                ->header('Content-Type', 'image/jpeg')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
        }

        return response()->file(public_path($userProfile->photo));
    }

    public function getDevicesByGroup($deviceGroupId)
    {
        if ($deviceGroupId === 'all') {
            $devices = deviceGateModel::select('id', 'name', 'sn', 'type', 'nodeid', 'ip')->get();
        } else {
            $devices = DeviceGroupModel::find($deviceGroupId)
                ->deviceGates()
                ->get(['devicegate.id', 'devicegate.name', 'devicegate.sn', 'devicegate.type', 'devicegate.nodeid', 'devicegate.ip']);
        }

        return response()->json($devices);
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
            sleep(1);
        }
        Log::error("Failed to connect to Soyal after $retries attempts", ['ip' => $ip, 'port' => $port]);
        return response()->json(['message' => 'Failed! Check your Soyal connection!'], 400);
    }

    private function isDeviceReachable($ip, $port = null)
    {
        $command = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
            ? "ping -n 1 -w 1000 $ip"
            : "ping -c 1 -W 1 $ip";

        exec($command, $output, $result);
        $pingSuccess = $result === 0;

        Log::info('Ping check result', [
            'ip' => $ip,
            'command' => $command,
            'output' => $output,
            'result_code' => $result,
            'ping_success' => $pingSuccess,
        ]);

        if (!$pingSuccess) {
            Log::warning('Device not reachable via ping', ['ip' => $ip]);
            return false;
        }

        if ($port !== null) {
            $timeout = 2;
            $connection = @fsockopen($ip, $port, $errno, $errstr, $timeout);
            if ($connection === false) {
                Log::warning('TCP connection failed', [
                    'ip' => $ip,
                    'port' => $port,
                    'error_no' => $errno,
                    'error_msg' => $errstr,
                ]);
                return false;
            }
            fclose($connection);
            Log::info('TCP connection successful', ['ip' => $ip, 'port' => $port]);
        }

        return true;
    }

    private function isDeviceOnline($ip, $port = 787, $timeout = 3)
    {
        $socket = @fsockopen($ip, $port, $errno, $errstr, $timeout);
        if ($socket) {
            fclose($socket);
            return true;
        }
        return false;
    }

    public function checkDeviceStatus(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip'
        ]);

        $ip = $request->ip;

        // PAKAI PING (Windows/Linux)
        $command = PHP_OS_FAMILY === 'Windows'
            ? "ping -n 1 -w 1000 $ip"
            : "ping -c 1 -W 1 $ip";

        exec($command, $output, $result);

        $online = $result === 0;

        return response()->json(['online' => $online]);
    }

    public function registerUser(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|string',
            'card_number' => 'required|string',
            'ip_address' => 'required|ip',
            'node_id' => 'required|integer',
            'timezone' => 'required|integer',
            'begin_date' => 'required|date_format:m-d-Y',
            'expire_date' => 'required|date_format:m-d-Y',
            'begin_time' => 'required|date_format:H:i',
            'expire_time' => 'required|date_format:H:i',
            'pin' => 'required|string',
            'user_name' => 'required|string',
            'type' => 'required|string',
        ]);

        $cardParts = explode(":", $data['card_number']);
        $card1 = (int)($cardParts[0] ?? 0);
        $card2 = (int)($cardParts[1] ?? 0);

        $beginTime = explode(":", $data['begin_time']);
        $expireTime = explode(":", $data['expire_time']);

        $bytes = array_fill(0, 40, 0);
        $bytes[0] = 0x7E;
        $bytes[1] = 0x26;
        $bytes[2] = (int)$data['node_id'];
        $bytes[3] = 0x8B;
        $bytes[4] = 0x57;

        $userId = (int)$data['user_id'];
        $bytes[5] = 0x00;
        $bytes[6] = ($userId >> 8) & 0xFF;
        $bytes[7] = $userId & 0xFF;

        $bytes[8] = 0x00;
        $bytes[9] = 0x00;
        $bytes[10] = 0x00;
        $bytes[11] = 0x00;

        $bytes[12] = ($card1 >> 8) & 0xFF;
        $bytes[13] = $card1 & 0xFF;
        $bytes[14] = ($card2 >> 8) & 0xFF;
        $bytes[15] = $card2 & 0xFF;

        $bytes[16] = 0x00;
        $bytes[17] = 0x00;

        $pin = (int)$data['pin'];
        $bytes[18] = ($pin >> 8) & 0xFF;
        $bytes[19] = $pin & 0xFF;

        $bytes[20] = 0x86;
        $bytes[21] = (int)$data['timezone'];

        $bytes[22] = 255;
        $bytes[23] = 255;

        $beginDateObj = DateTime::createFromFormat('m-d-Y', $data['expire_date']);
        $bytes[27] = (int)$beginTime[0];
        $bytes[28] = (int)$beginTime[1];
        $bytes[29] = (int)$beginDateObj->format('y');
        $bytes[30] = (int)$beginDateObj->format('m');
        $bytes[31] = (int)$beginDateObj->format('d');

        $expireDateObj = DateTime::createFromFormat('m-d-Y', $data['begin_date']);
        $bytes[32] = (int)$expireTime[0];
        $bytes[33] = (int)$expireTime[1];
        $bytes[34] = (int)$expireDateObj->format('y');
        $bytes[35] = (int)$expireDateObj->format('m');
        $bytes[36] = (int)$expireDateObj->format('d');

        $LRC = 255;
        for ($i = 2; $i <= 37; $i++) {
            $LRC ^= $bytes[$i];
        }
        $bytes[38] = $LRC;

        $sum = array_sum(array_slice($bytes, 2, 37)) % 256;
        $bytes[39] = $sum;

        $response = $this->sendToSoyal($data['ip_address'], 1621, $bytes);

        if ($response->getStatusCode() == 200) {
            return response()->json([
                'message' => 'Success',
                'user_id' => $data['user_id'],
                'card_number' => $data['card_number'],
            ], 200);
        } else {
            return response()->json([
                'message' => 'Failed to register user',
            ], 400);
        }
    }

    public function deleteUser(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|string',
            'card_number' => 'required|string',
            'ip_address' => 'required|ip',
            'node_id' => 'required|integer',
        ]);

        $cardParts = explode(":", $data['card_number']);
        $card1 = (int)$cardParts[0];
        $card2 = (int)$cardParts[1];

        $bytes = array_fill(0, 40, 0);
        $bytes[0] = 0x7E;
        $bytes[1] = 0x26;
        $bytes[2] = (int)$data['node_id'];
        $bytes[3] = 0x8B;
        $bytes[4] = 0x57;

        $userId = (int)$data['user_id'];
        $bytes[5] = 0x00;
        $bytes[6] = ($userId >> 8) & 0xFF;
        $bytes[7] = $userId & 0xFF;

        $bytes[8] = 0x00;
        $bytes[9] = 0x00;
        $bytes[10] = 0x00;
        $bytes[11] = 0x00;

        $bytes[12] = ($card1 >> 8) & 0xFF;
        $bytes[13] = $card1 & 0xFF;
        $bytes[14] = ($card2 >> 8) & 0xFF;
        $bytes[15] = $card2 & 0xFF;

        $bytes[16] = 0x00;
        $bytes[17] = 0x00;
        $bytes[18] = 0x00;
        $bytes[19] = 0x00;

        $bytes[20] = 0x00;
        $bytes[21] = 0x00;

        $bytes[22] = 255;
        $bytes[23] = 255;

        $bytes[24] = 0x00;
        $bytes[25] = 0x00;
        $bytes[26] = 0x00;
        $bytes[27] = 0x00;
        $bytes[28] = 0x00;
        $bytes[29] = 0x00;
        $bytes[30] = 0x00;
        $bytes[31] = 0x00;
        $bytes[32] = 0x00;
        $bytes[33] = 0x00;

        $bytes[34] = 0x00;
        $bytes[35] = 0x00;
        $bytes[36] = 0x00;
        $bytes[37] = 0x00;

        $LRC = 255;
        for ($i = 2; $i <= 37; $i++) {
            $LRC ^= $bytes[$i];
        }
        $bytes[38] = $LRC;

        $sum = array_sum(array_slice($bytes, 2, 37)) % 256;
        $bytes[39] = $sum;

        $response = $this->sendToSoyal($data['ip_address'], 1621, $bytes);

        if ($response->getStatusCode() != 200) {
            return response()->json(['message' => 'Failed! Check your Soyal connection!'], 400);
        }

        $refreshBytes = [0x7E, 0x04, (int)$data['node_id'], 0x19, 0xDB, 0x01];
        $this->sendToSoyal($data['ip_address'], 1621, $refreshBytes);
        $response = $this->sendToSoyal($data['ip_address'], 1621, $bytes);

        if ($response->getStatusCode() == 200) {
            return response()->json([
                'message' => 'Success Delete Data',
                'user_id' => $data['user_id'],
            ], 200);
        } else {
            return response()->json([
                'message' => 'Failed to register user',
            ], 400);
        }
    }

    public function showPhoto($id)
    {
        $userProfile = userProfileModel::findOrFail($id);

        if (!$userProfile->photo) {
            return response()->file(public_path('images/placeholder.jpg'));
        }

        return response($userProfile->photo, 200)
            ->header('Content-Type', 'image/jpeg');
    }

    public function getPhoto($id)
    {
        $userProfile = userProfileModel::findOrFail($id);

        if ($userProfile->photo) {
            $photo = $userProfile->photo;
            return response($photo)->header('Content-Type', 'image/jpeg');
        } else {
            return response()->file(public_path('images/placeholder.jpg'));
        }
    }

    public function getUserDates($userId)
    {
        $user = userProfileModel::where('ID', $userId)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $beginDate = Carbon::parse($user->BEGIN_DATE)->startOfMonth()->toDateTimeString();
        $endDate = $user->END_DATE;

        return response()->json([
            'begin_date' => $beginDate,
            'end_date' => $endDate
        ]);
    }

    public function getUserProfileDates($userId)
    {
        try {
            $user = userProfileModel::findOrFail($userId);
            return response()->json([
                'begin_date' => $user->BEGIN_DATE,
                'end_date' => $user->END_DATE
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'User not found or error fetching dates',
                'details' => $e->getMessage()
            ], 404);
        }
    }


    public function saveToUserData(Request $request)
    {
        $data = $request->validate([
            'fid' => 'required|integer',
            'type' => 'required|string',
            'sn' => 'required|string',
            'fp' => 'required|string',
            'picture' => 'required|string',
            'desc' => 'nullable|string'
        ]);

        try {
            DB::table('tbl_usersdata')->updateOrInsert(
                [
                    'fid' => $data['fid'],
                    'Type' => $data['type'],
                    'FaceNo' => $data['sn'] // atau 'sn' tergantung kolom
                ],
                [
                    'Fp' => $data['fp'],
                    'Picture' => $data['picture'],

                ]
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getUserAndDeviceData($userId, $deviceId)
    {
        $user = userProfileModel::select('ID', 'NAME', 'Card', 'BEGIN_DATE', 'END_DATE', 'timezone', 'PIN')
            ->where('ID', $userId)
            ->first();

        $device = deviceGateModel::select('id', 'sn', 'ip', 'nodeid', 'type')
            ->where('id', $deviceId)
            ->first();

        return response()->json([
            'user' => $user,
            'device' => $device
        ]);
    }

    public function saveToUsersDevice(Request $request)
    {
        try {
            $data = $request->validate([
                'userId' => 'required|exists:usersprofile,ID',
                'gateId' => 'required|exists:devicegate,id',
            ]);

            $userDevice = usersDevice::updateOrCreate(
                [
                    'userId' => $data['userId'],
                    'gateId' => $data['gateId'],
                ],
                [
                    'userId' => $data['userId'],
                    'gateId' => $data['gateId'],
                ]
            );

            $message = $userDevice->wasRecentlyCreated
                ? 'Data inserted into usersxdevice successfully'
                : 'Data updated in usersxdevice successfully';

            return response()->json(['message' => $message], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error processing usersxdevice: ' . $e->getMessage()], 500);
        }
    }

    public function bulkDelete(Request $request)
    {
        Log::info('Users: ' . $request->input('users', '[]'));
        Log::info('Machines: ' . $request->input('machines', '[]'));

        $users = json_decode($request->input('users', '[]'), true);
        $machines = json_decode($request->input('machines', '[]'), true);

        $apiUrl = env('DELETE_API_URL', 'http://localhost:787/sztimmy/deleteface');

        if (is_array($users) && is_array($machines)) {
            $deletedUsers = [];
            $machineIPs = [];

            $client = new Client();

            foreach ($users as $user) {
                if (!isset($user['userid']) || empty($user['userid'])) {
                    continue;
                }

                $userDetails = userProfileModel::where('ID', $user['userid'])->first();
                if ($userDetails) {
                    $deletedUsers[] = $userDetails->NAME;

                    foreach ($machines as $machineSn) {
                        $machineDetails = deviceGateModel::where('sn', $machineSn)->first();
                        if ($machineDetails) {
                            $machineIPs[] = $machineDetails->ipAddress;

                            try {
                                $response = $client->post($apiUrl, [
                                    'form_params' => [
                                        'sn' => $machineSn,
                                        'userid' => $user['userid']
                                    ],
                                    'timeout' => 60,
                                ]);

                                userDataModel::where('fid', $user['userid'])
                                    ->where('FaceNo', $machineSn)
                                    ->delete();
                            } catch (\Exception $e) {
                                Log::error('API Error for User: ' . $user['userid'] . ', Machine: ' . $machineSn . ': ' . $e->getMessage());
                                continue;
                            }
                        }
                    }
                }
            }

            $userNames = implode(', ', $deletedUsers);
            $uniqueMachineIPs = implode(', ', array_unique($machineIPs));

            return redirect()->route('userProfile')
                ->with('success', "Data successfully deleted from machines: $uniqueMachineIPs for users: $userNames.");
        } else {
            return redirect()->route('userProfile')
                ->with('error', 'Invalid data submitted.');
        }
    }

    public function deleteUserPicture(Request $request)
    {
        try {
            $request->validate([
                'userid' => 'required|string',
                'faceNo' => 'required|string'
            ]);

            $userId = $request->userid;
            $faceNo = $request->faceNo;
            Log::info('Attempting to delete picture data for user', ['userid' => $userId, 'faceNo' => $faceNo]);

            $deleted = userDataModel::where('fid', $userId)
                ->where('FaceNo', $faceNo)
                ->delete();

            if ($deleted) {
                Log::info('Picture data deleted successfully', ['userid' => $userId, 'faceNo' => $faceNo]);
                return response()->json(['message' => 'Picture data deleted successfully'], 200);
            } else {
                Log::warning('No picture data found for user with specified FaceNo', ['userid' => $userId, 'faceNo' => $faceNo]);
                return response()->json(['message' => 'No picture data found for the specified user and FaceNo'], 200);
            }
        } catch (\Exception $e) {
            Log::error('Error deleting picture data', ['userid' => $request->userid, 'faceNo' => $request->faceNo, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to delete picture data', 'details' => $e->getMessage()], 500);
        }
    }

    public function deleteUsers(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array'
        ]);

        $userIds = $request->input('user_ids');
        try {
            $deletedCount = userProfileModel::whereIn('ID', $userIds)->delete();
            userDataModel::whereIn('fid', $userIds)->delete();

            return response()->json(['success' => "Deleted $deletedCount users successfully."]);
        } catch (\Exception $e) {
            Log::error('Error deleting users: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while deleting users.'], 500);
        }
    }

    public function deleteFromUsersDevice(Request $request)
    {
        try {
            $data = $request->validate([
                'userId' => 'required|exists:usersprofile,ID',
                'gateId' => 'required|exists:devicegate,id',
            ]);

            $deleted = usersDevice::where('userId', $data['userId'])
                ->where('gateId', $data['gateId'])
                ->delete();

            if ($deleted) {
                return response()->json(['message' => 'Data deleted from usersxdevice successfully'], 200);
            } else {
                return response()->json(['message' => 'No matching record found in usersxdevice'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error deleting from usersxdevice: ' . $e->getMessage()], 500);
        }
    }

    public function deleteFromDeviceGroupDeviceGate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userprofile_id' => 'required|exists:usersprofile,ID',
            'devicegate_id' => 'required|exists:devicegate,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $deleted = DeviceJoinGate::where('userprofile_id', $request->userprofile_id)
                ->where('devicegate_id', $request->devicegate_id)
                ->delete();

            if ($deleted) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data successfully deleted from devicegroup_devicegate',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No matching data found in devicegroup_devicegate',
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete data from devicegroup_devicegate: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getFingerprint($userId)
    {
        $userData = userDataModel::where('fid', $userId)->first();

        if ($userData && $userData->Fp) {
            return response()->json(['Fp' => $userData->Fp], 200);
        }

        return response()->json(['Fp' => '0'], 200);
    }

    public function getFpData($userId)
    {
        $userData = userDataModel::where('fid', $userId)->first();

        $Fp = $userData && $userData->Fp && $userData->Fp !== '0' ? $userData->Fp : null;

        return response()->json([
            'Fp' => $Fp
        ]);
    }

    public function getFpDataBySn($userId, $sn)
    {
        Log::info("getFpDataBySn DIPANGGIL", [
            'userId' => $userId,
            'sn' => $sn,
            'time' => now()
        ]);

        $fp = userDataModel::where('fid', $userId)
            ->where('FaceNo', $sn)
            ->where('Type', '0')
            ->value('Fp');

        Log::info("Hasil Fp", [
            'Fp_length' => strlen($fp ?? ''),
            'Fp_preview' => substr($fp, 0, 50) . '...'
        ]);

        return response()->json(['Fp' => $fp ?? '']);
    }
}

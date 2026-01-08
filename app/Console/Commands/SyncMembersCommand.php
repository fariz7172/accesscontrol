<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\userProfileModel;
use App\Models\ApiModel;
use App\Models\deviceGateModel;
use App\Models\devicelogModel;
use App\Models\usersDevice;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SyncMembersCommand extends Command
{
    protected $signature = 'sync:members';
    protected $description = 'Sync members from Dreampost API every minute';

    protected $delayBetweenRequests = 0.5; // Delay in seconds between API requests

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Check if device is reachable via ping
     */
    private function isDeviceReachable($ip)
    {
        // For Windows and Unix-based systems
        $command = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
            ? "ping -n 1 -w 1000 $ip"
            : "ping -c 1 -W 1 $ip";

        exec($command, $output, $result);

        // Check if ping was successful (result code 0 means success)
        $isReachable = $result === 0;

        if (!$isReachable) {
            Log::warning('Device not reachable', ['ip' => $ip]);
        }

        return $isReachable;
    }

    public function handle()
    {
        try {
            Log::info('SyncMembersCommand started');

            // Fetch API URLs
            $syncMemberViewDreampos = ApiModel::where('id', 8)->value('name');
            $syncMemberDreampost = ApiModel::where('id', 7)->value('name') ?? env('SYNC_MEMBER_DREAMPOST_URL', 'http://localhost:8080/api/members');
            $sztimmyRegisterFaceApiUrl = ApiModel::where('id', 2)->value('name') ?? env('SZYMMY_REGISTER_FACE_API_URL', 'http://localhost:787/sztimmy/registerface');
            $sztimmyUserAccessApiUrl = ApiModel::where('id', 3)->value('name') ?? env('SZYMMY_USER_ACCESS_API_URL', 'http://localhost:787/sztimmy/useraccess');

            // Validate API URLs
            $missingUrls = [];
            if (!$syncMemberViewDreampos) $missingUrls[] = 'syncMemberViewDreampos';
            if (!$syncMemberDreampost) $missingUrls[] = 'syncMemberDreampost';
            if (!$sztimmyRegisterFaceApiUrl) $missingUrls[] = 'sztimmy/registerface';
            if (!$sztimmyUserAccessApiUrl) $missingUrls[] = 'sztimmy/useraccess';
            if (!empty($missingUrls)) {
                Log::error('Missing API URLs', ['missing' => $missingUrls]);
                $this->error('Missing API URLs: ' . implode(', ', $missingUrls));
                return;
            }

            Log::info('API URLs', [
                'syncMemberViewDreampos' => $syncMemberViewDreampos,
                'syncMemberDreampost' => $syncMemberDreampost,
                'sztimmyRegisterFace' => $sztimmyRegisterFaceApiUrl,
                'sztimmyUserAccess' => $sztimmyUserAccessApiUrl,
            ]);

            // Fetch devices with type = '0'
            $devices = deviceGateModel::where('type', '0')->get();
            if ($devices->isEmpty()) {
                Log::error('No devices found with type = 0');
                $this->error('No devices found with type = 0');
                return;
            }

            // Validate device network connectivity
            $reachableDevices = $devices->filter(function ($device) {
                return $this->isDeviceReachable($device->ip);
            });

            if ($reachableDevices->isEmpty()) {
                Log::error('No reachable devices found');
                $this->error('No devices are reachable in the network');
                return;
            }

            Log::info('Reachable devices to sync', ['devices' => $reachableDevices->pluck('sn')->toArray()]);

            // Fetch data from syncMemberViewDreampos
            $response = Http::timeout(60)->get($syncMemberViewDreampos);
            Log::info('syncMemberViewDreampos response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'parsed' => $response->json(),
            ]);

            if (!$response->successful()) {
                Log::error('Failed to fetch data from syncMemberViewDreampos', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                $this->error('Failed to fetch data from API: ' . $response->status());
                return;
            }

            $apiData = $response->json();
            $apiData = is_array($apiData) ? $apiData : (isset($apiData['Number']) ? [$apiData] : []);

            if (empty($apiData)) {
                Log::warning('API returned empty data');
                $this->error('No data found from API');
                return;
            }

            $totalSynced = 0;
            $syncedUserIds = [];
            $failedRequests = 0;

            // Process API data within a transaction
            DB::transaction(function () use ($apiData, &$totalSynced, &$syncedUserIds, &$failedRequests, $syncMemberDreampost) {
                foreach ($apiData as $index => $memberData) {
                    Log::info('Processing member', ['index' => $index, 'data' => $memberData]);

                    // Validate member data
                    if (!is_array($memberData) || !isset($memberData['Number']) || !isset($memberData['ID'])) {
                        Log::warning('Invalid member data', ['index' => $index, 'data' => $memberData]);
                        $failedRequests++;
                        continue;
                    }

                    // Post to syncMemberDreampost
                    $postResponse = Http::timeout(60)->post($syncMemberDreampost, [
                        'Number' => [$memberData['Number']],
                    ]);

                    if (!$postResponse->successful()) {
                        Log::error('Failed to sync data to syncMemberDreampost', [
                            'number' => $memberData['Number'],
                            'status' => $postResponse->status(),
                            'response' => $postResponse->body(),
                        ]);
                        $failedRequests++;
                        continue;
                    }

                    $postData = $postResponse->json();
                    $postData = is_array($postData) ? $postData : (isset($postData['Number']) ? [$postData] : []);

                    if (empty($postData)) {
                        Log::warning('syncMemberDreampost returned empty data', ['number' => $memberData['Number']]);
                        $failedRequests++;
                        continue;
                    }

                    foreach ($postData as $data) {
                        // Validate response data
                        if (!is_array($data) || !isset($data['Number']) || $data['Number'] !== $memberData['Number'] || !isset($data['ID'])) {
                            Log::warning('Invalid or unselected member data from syncMemberDreampost', [
                                'data' => $data,
                                'number' => $memberData['Number'],
                            ]);
                            $failedRequests++;
                            continue;
                        }

                        // Validasi dan proses data Image
                        $photoData = null;
                        if (isset($data['Image']) && !empty($data['Image']) && $data['Image'] !== '???') {
                            if (preg_match('/^data:image\/[a-z]+;base64,/', $data['Image'])) {
                                $photoData = $data['Image'];
                            } else if (filter_var($data['Image'], FILTER_VALIDATE_URL)) {
                                $photoData = $data['Image'];
                            } else {
                                Log::warning('Invalid Image format for member', ['ID' => $data['ID'], 'Image' => $data['Image']]);
                            }
                        }

                        $memberFields = [
                            'ID' => $data['ID'],
                            'MemberNo' => $data['ID'],
                            'Card' => $data['Number'],
                            'NAME' => $data['Name'] ?? null,
                            'BEGIN_DATE' => isset($data['JoinDate']) ? date('Y-m-d', strtotime($data['JoinDate'])) : null,
                            'END_DATE' => isset($data['ExpireDate']) ? date('Y-m-d', strtotime($data['ExpireDate'])) : null,
                            'photo' => $photoData,
                            'last_synced_at' => now(),
                        ];

                        // Validate required fields for database
                        if (empty($memberFields['ID']) || empty($memberFields['Card'])) {
                            Log::warning('Missing required fields for member', ['data' => $memberFields]);
                            $failedRequests++;
                            continue;
                        }

                        try {
                            $existingMember = userProfileModel::where('ID', $data['ID'])->lockForUpdate()->first();

                            if ($existingMember) {
                                $existingMember->update($memberFields);
                                Log::info('Updated member', ['ID' => $data['ID'], 'photo' => $photoData]);
                            } else {
                                userProfileModel::create($memberFields);
                                Log::info('Inserted new member', ['ID' => $data['ID'], 'photo' => $photoData]);
                            }
                            $totalSynced++;
                            $syncedUserIds[] = $data['ID'];
                        } catch (\Illuminate\Database\QueryException $e) {
                            Log::error('Database error for member ID ' . $data['ID'], [
                                'error' => $e->getMessage(),
                                'code' => $e->getCode(),
                            ]);
                            if ($e->getCode() == 23000) {
                                userProfileModel::where('ID', $data['ID'])->update($memberFields);
                                $totalSynced++;
                                $syncedUserIds[] = $data['ID'];
                                Log::info('Updated member due to duplicate key', ['ID' => $data['ID'], 'photo' => $photoData]);
                            } else {
                                $failedRequests++;
                            }
                        }
                    }
                }
            });

            // Sync to sztimmy/registerface and sztimmy/useraccess APIs
            if ($totalSynced > 0) {
                $client = new Client();

                foreach ($syncedUserIds as $userId) {
                    $userProfile = userProfileModel::where('ID', $userId)->first();
                    if (!$userProfile) {
                        Log::warning('User profile not found for ID', ['userId' => $userId]);
                        $failedRequests++;
                        continue;
                    }

                    // Validate required fields for sztimmy/registerface and sztimmy/useraccess
                    if (empty($userProfile->ID) || empty($userProfile->Card)) {
                        Log::warning('Missing required fields for APIs', [
                            'userId' => $userId,
                            'data' => $userProfile->toArray(),
                        ]);
                        $failedRequests++;
                        continue;
                    }

                    foreach ($reachableDevices as $device) {
                        // Validate serial number
                        if (empty($device->sn)) {
                            Log::warning('Invalid device serial number', ['sn' => $device->sn]);
                            $failedRequests++;
                            continue;
                        }

                        // Payload for sztimmy/registerface
                        $registerFacePayload = [
                            'sn' => $device->sn,
                            'userid' => $userProfile->ID,
                            'username' => $userProfile->NAME ?? '',
                            'type' => '0',
                            'admin' => '0',
                            'cardnumber' => $userProfile->Card,
                            'starttime' => $userProfile->BEGIN_DATE ? Carbon::parse($userProfile->BEGIN_DATE)->format('Y-m-d H:i:s') : '',
                            'endtime' => $userProfile->END_DATE ? Carbon::parse($userProfile->END_DATE)->format('Y-m-d H:i:s') : '',
                            'psw' => '',
                            'fdata' => '',
                        ];

                        // Payload for sztimmy/useraccess
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

                        // Sync to sztimmy/registerface
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

                        // Sync to sztimmy/useraccess
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

                        usleep($this->delayBetweenRequests * 1000000);
                    }
                }
            }

            $this->info("Successfully synced $totalSynced members with $failedRequests failures");
        } catch (\Exception $e) {
            Log::error('Sync error in command', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Error occurred: ' . $e->getMessage());
        }
    }
}
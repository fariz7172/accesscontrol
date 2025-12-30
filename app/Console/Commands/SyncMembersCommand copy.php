<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\userProfileModel;
use App\Models\ApiModel;
use App\Models\deviceGateModel;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SyncMembersCommand extends Command
{
    protected $signature = 'sync:members';
    protected $description = 'Sync members from Dreampost API every minute';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            Log::info('SyncMembersCommand started');

            $syncMemberViewDreampos = ApiModel::where('id', 8)->value('name');
            $syncMemberDreampost = ApiModel::where('id', 7)->value('name');

            $response = Http::timeout(60)->get($syncMemberViewDreampos);
            Log::info('syncMemberViewDreampos response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'parsed' => $response->json(),
            ]);

            if (!$response->successful()) {
                Log::error('Failed to fetch data from syncMemberViewDreampos', [
                    'status' => $response->status(),
                    'response' => $response->body()
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

            DB::transaction(function () use ($apiData, &$totalSynced, &$syncedUserIds, &$failedRequests, $syncMemberDreampost) {
                foreach ($apiData as $index => $memberData) {
                    Log::info('Processing member', ['index' => $index, 'data' => $memberData]);

                    if (!is_array($memberData) || !isset($memberData['Number'])) {
                        Log::warning('Invalid member data', ['index' => $index, 'data' => $memberData]);
                        $failedRequests++;
                        continue;
                    }

                    $postResponse = Http::timeout(60)->post($syncMemberDreampost, [
                        'Number' => [$memberData['Number']],
                    ]);

                    if (!$postResponse->successful()) {
                        Log::error('Failed to sync data to syncMemberDreampost', [
                            'number' => $memberData['Number'],
                            'status' => $postResponse->status(),
                            'response' => $postResponse->body()
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
                        if (!is_array($data) || !isset($data['Number']) || $data['Number'] !== $memberData['Number']) {
                            Log::warning('Invalid or unselected member data from syncMemberDreampost', ['data' => $data, 'number' => $memberData['Number']]);
                            $failedRequests++;
                            continue;
                        }

                        $memberFields = [
                            'ID' => $data['ID'],
                            'MemberNo' => $data['ID'],
                            'Card' => $data['Number'],
                            'NAME' => $data['Name'] ?? null,
                            'BEGIN_DATE' => isset($data['JoinDate']) ? date('Y-m-d', strtotime($data['JoinDate'])) : null,
                            'END_DATE' => isset($data['ExpireDate']) ? date('Y-m-d', strtotime($data['ExpireDate'])) : null,
                            'last_synced_at' => now(),
                        ];

                        $existingMember = userProfileModel::where('ID', $data['ID'])->lockForUpdate()->first();

                        try {
                            if ($existingMember) {
                                $existingMember->update($memberFields);
                                Log::info('Updated member', ['ID' => $data['ID']]);
                            } else {
                                userProfileModel::create($memberFields);
                                Log::info('Inserted new member', ['ID' => $data['ID']]);
                            }
                            $totalSynced++;
                            $syncedUserIds[] = $data['ID'];
                        } catch (\Illuminate\Database\QueryException $e) {
                            Log::error('Database error for member ID ' . $data['ID'], [
                                'error' => $e->getMessage(),
                                'code' => $e->getCode()
                            ]);
                            if ($e->getCode() == 23000) {
                                userProfileModel::where('ID', $data['ID'])->update($memberFields);
                                $totalSynced++;
                                $syncedUserIds[] = $data['ID'];
                                Log::info('Updated member due to duplicate key', ['ID' => $data['ID']]);
                            } else {
                                $failedRequests++;
                            }
                        }
                    }
                }
            });

            if ($totalSynced > 0) {
                $client = new Client();
                // Gunakan nilai dari ApiModel dengan id = 2
                $apiUrl = ApiModel::where('id', 2)->value('name');
                if (!$apiUrl) {
                    Log::error('API URL for sztimmy/registerface not found in ApiModel with id = 2');
                    $this->error('API URL for sztimmy/registerface not found');
                    return;
                }
                $devices = deviceGateModel::where('type', '0')->pluck('sn')->toArray(); // Ubah ke type = '50' untuk konsistensi

                foreach ($syncedUserIds as $userId) {
                    $userProfile = userProfileModel::where('ID', $userId)->first();
                    if (!$userProfile) {
                        Log::warning('User profile not found for ID', ['userId' => $userId]);
                        continue;
                    }

                    foreach ($devices as $sn) {
                        $payload = [
                            'sn' => $sn,
                            'userid' => $userProfile->ID,
                            'username' => $userProfile->NAME ?? '',
                            'type' => '0', // Ubah ke '50' untuk konsistensi
                            'admin' => '0',
                            'cardnumber' => $userProfile->Card ?? '',
                            'starttime' => $userProfile->BEGIN_DATE ? Carbon::parse($userProfile->BEGIN_DATE)->format('Y-m-d H:i:s') : '',
                            'endtime' => $userProfile->END_DATE ? Carbon::parse($userProfile->END_DATE)->format('Y-m-d H:i:s') : '',
                            'psw' => '',
                            'fdata' => '',
                        ];

                        try {
                            $response = $client->post($apiUrl, [
                                'json' => $payload,
                                'timeout' => 60,
                            ]);
                            Log::info('Successfully sent data to sztimmy/registerface', [
                                'userId' => $userId,
                                'sn' => $sn,
                                'response' => $response->getBody()->getContents(),
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Failed to send data to sztimmy/registerface', [
                                'userId' => $userId,
                                'sn' => $sn,
                                'error' => $e->getMessage(),
                            ]);
                            $failedRequests++;
                        }
                    }
                }
            }

            $this->info("Successfully synced $totalSynced members with $failedRequests failures");
        } catch (\Exception $e) {
            Log::error('Sync error in command', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $this->error('Error occurred: ' . $e->getMessage());
        }
    }
}

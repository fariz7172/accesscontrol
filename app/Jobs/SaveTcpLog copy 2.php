<?php

namespace App\Jobs;

use App\Models\userLogModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SaveTcpLog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3; // Coba ulang 3 kali jika gagal

    protected $tappingTime;
    protected $clientIp;
    protected $cardNumber;
    protected $cardNumber2;
    protected $nodeId;
    protected $userid;
    protected $deviceType;
    protected $card;

    public function __construct($tappingTime, $clientIp, $cardNumber, $cardNumber2, $nodeId, $userid, $deviceType, $card)
    {
        $this->tappingTime = $tappingTime;
        $this->clientIp = $clientIp;
        $this->cardNumber = $cardNumber;
        $this->cardNumber2 = $cardNumber2;
        $this->nodeId = $nodeId;
        $this->userid = $userid;
        $this->deviceType = $deviceType;
        $this->card = $card;
    }

    public function handle()
    {
        try {
            // Log data yang akan disimpan untuk debugging
            Log::info('Processing SaveTcpLog', [
                'tappingTime' => $this->tappingTime,
                'clientIp' => $this->clientIp,
                'cardNumber' => $this->cardNumber,
                'cardNumber2' => $this->cardNumber2,
                'nodeId' => $this->nodeId,
                'userid' => $this->userid,
                'deviceType' => $this->deviceType,
                'card' => $this->card,
            ]);

            // Simpan ke database
            userLogModel::create([
                'USER_ADDR' => $this->userid ?? 0,
                'TM_EVENT' => $this->tappingTime,
                'DEVICESN' => $this->nodeId,
                'IMGPATH' => '',
                'devicetype' => $this->deviceType,
                'card' => $this->cardNumber,
            ]);

            Log::info('Data successfully saved to database');
        } catch (\Exception $e) {
            // Log error
            Log::error('Failed to save TCP log to database: ' . $e->getMessage(), [
                'tappingTime' => $this->tappingTime,
                'clientIp' => $this->clientIp,
                'cardNumber' => $this->cardNumber,
                'cardNumber2' => $this->cardNumber2,
                'nodeId' => $this->nodeId,
                'userid' => $this->userid,
                'deviceType' => $this->deviceType,
                'card' => $this->card,
            ]);

            // Simpan data yang gagal ke penyimpanan sementara (file)
            $failedData = [
                'tappingTime' => $this->tappingTime,
                'clientIp' => $this->clientIp,
                'cardNumber' => $this->cardNumber,
                'cardNumber2' => $this->cardNumber2,
                'nodeId' => $this->nodeId,
                'userid' => $this->userid,
                'deviceType' => $this->deviceType,
                'card' => $this->card,
                'failed_at' => now()->toDateTimeString(),
                'error' => $e->getMessage(),
            ];
            Storage::append('failed_tcp_logs.txt', json_encode($failedData));

            // Lempar ulang exception untuk memicu retry (jika masih ada percobaan)
            throw $e;
        }
    }

    // Tangani job yang gagal setelah semua percobaan
    public function failed(\Exception $e)
    {
        Log::error('SaveTcpLog job failed after all retries: ' . $e->getMessage());
    }
}
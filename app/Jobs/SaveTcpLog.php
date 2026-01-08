<?php

namespace App\Jobs;

use App\Models\deviceGateModel;
use App\Models\userLogModel;
use App\Models\userProfileModel;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SaveTcpLog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 20, 30];

    protected $tappingTime;
    protected $clientIp;
    protected $cardNumber;
    protected $cardNumber2;
    protected $nodeId;
    protected $userid;
    protected $deviceType;
    protected $card;

    public function __construct(
        $tappingTime,
        $clientIp,
        $cardNumber,
        $cardNumber2,
        $nodeId,
        $userid,
        $deviceType,
        $card
    ) {
        $this->tappingTime = $tappingTime;
        $this->clientIp    = $clientIp;
        $this->cardNumber  = $cardNumber;
        $this->cardNumber2 = $cardNumber2;
        $this->nodeId      = $nodeId;
        $this->userid      = $userid;
        $this->deviceType  = $deviceType;
        $this->card        = $card;
    }

    public function handle()
    {
        // ===================================================================
        // 1. HANYA TOLAK USER_ADDR = 0 → TIDAK DISIMPAN SAMA SEKALI
        // ===================================================================
        if (!$this->userid || $this->userid <= 0) {
            Log::info('Tapping DIIABAIKAN → USER_ADDR = 0 (kartu tidak terdaftar)', [
                'card_raw'  => $this->card,
                'card_dec'  => $this->cardNumber,
                'card_hex'  => $this->cardNumber2,
                'nodeid'    => $this->nodeId,
                'ip'        => $this->clientIp,
                'time'      => $this->tappingTime,
            ]);
            return; // TIDAK DISIMPAN → sesuai permintaan
        }

        // ===================================================================
        // 2. Semua USER_ADDR > 0 → TETAP DIPROSES & DISIMPAN (termasuk expired)
        // ===================================================================

        // Cari device
        $device = deviceGateModel::where('nodeid', $this->nodeId)->first();
        $deviceSn = $device?->sn ?? $this->nodeId;

        if (empty($deviceSn) || $deviceSn === '0') {
            $deviceSn = $this->nodeId;
            Log::warning("Device SN kosong untuk nodeid {$this->nodeId}, pakai nodeid sebagai DEVICESN");
        }

        // Default status = 0 (aktif)
        $status = 0;

        // Cek masa berlaku kartu (hanya untuk log & stat)
        $userProfile = userProfileModel::where('ID', $this->userid)->first();

        if ($userProfile && $userProfile->END_DATE) {
            $endDate     = Carbon::parse($userProfile->END_DATE)->endOfDay();
            $tappingTime = Carbon::parse($this->tappingTime);

            if ($tappingTime->gt($endDate)) {
                $status = 1;
                Log::info("Kartu EXPIRED tapi tetap disimpan", [
                    'user_id'      => $this->userid,
                    'name'         => $userProfile->NAME ?? '-',
                    'end_date'     => $endDate->toDateString(),
                    'tapping_time' => $tappingTime->toDateTimeString(),
                ]);
            }
        } elseif (!$userProfile) {
            $status = 1;
            Log::warning("User ID {$this->userid} TIDAK DITEMUKAN di usersprofile → tetap disimpan dengan stat=1");
        } elseif (is_null($userProfile?->END_DATE)) {
            $status = 1;
            Log::info("User ID {$this->userid} TIDAK PUNYA END_DATE → stat=1");
        }

        // ===================================================================
        // 3. SIMPAN KE DATABASE (selalu, selama USER_ADDR > 0)
        // ===================================================================
        userLogModel::create([
            'USER_ADDR'  => $this->userid,
            'TM_EVENT'   => $this->tappingTime,
            'DEVICESN'   => $deviceSn,
            'IMGPATH'    => '',
            'devicetype' => $this->deviceType,
            'card'       => $this->cardNumber2 ?? $this->cardNumber,
            'stat'       => $status, // 0 = aktif, 1 = expired / tidak valid
        ]);

        Log::info('TCP Log DISIMPAN', [
            'user_id'    => $this->userid,
            'name'       => $userProfile->NAME ?? 'Unknown',
            'card'       => $this->cardNumber2 ?? $this->cardNumber,
            'device'     => $device?->name ?? 'Unknown',
            'nodeid'     => $this->nodeId,
            'ip'         => $this->clientIp,
            'status'     => $status === 1 ? 'EXPIRED / INVALID' : 'ACTIVE',
            'time'       => $this->tappingTime->format('Y-m-d H:i:s'),
        ]);
    }
}
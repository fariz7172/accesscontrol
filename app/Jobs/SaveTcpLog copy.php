<?php

namespace App\Jobs;

use App\Models\userLogModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SaveTcpLog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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

        userLogModel::create([
            'USER_ADDR' => $this->userid ?? 0, // jika nilai null maka nial 0
            'TM_EVENT' => $this->tappingTime,
            'DEVICESN' => $this->nodeId,
            'IMGPATH' => "", // Sesuaikan dengan kebutuhan
            'devicetype' => $this->deviceType,
            'card' => $this->cardNumber, // Simpan nilai card type decimal
        ]);
    }
}

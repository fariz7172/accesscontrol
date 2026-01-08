<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class userLogModel extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $table = 'tbl_userlog';
    public $timestamps = false;
    protected $dates = ['TM_EVENT'];

    protected $appends = ['device_name'];

    // === TAMBAHKAN INI ===
    protected $fillable = [
        'USER_ADDR',
        'TM_EVENT',
        'DEVICESN',
        'IMGPATH',
        'devicetype',
        'card',
        'stat',
    ];


    public function userProfile()
    {
        return $this->hasOne(userProfileModel::class, 'ID', 'USER_ADDR')
            ->withCasts(['ID' => 'string']);
    }

    public function deviceGate()
    {
        if (is_numeric($this->DEVICESN)) {
            return $this->belongsTo(deviceGateModel::class, 'DEVICESN', 'nodeid');
        }
        return $this->belongsTo(deviceGateModel::class, 'DEVICESN', 'sn');
    }

    public function getDeviceNameAttribute()
    {
        return $this->deviceGate?->name ?? 'N/A';
    }
}
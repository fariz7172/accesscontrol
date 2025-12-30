<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class deviceGateModel extends Model
{
    use HasFactory;

    // Nama tabel
    protected $table = 'devicegate';
    protected $primaryKey = 'id';
    // Nonaktifkan timestamps
    public $timestamps = false;

    // Field yang dapat diisi secara massal
    protected $fillable = ['name', 'number', 'flagstatus', 'type', 'sn', 'ip', 'nodeid', 'description', 'groupid', 'stat', 'Lift1', 'Lift2', 'Lift3', 'Lift4'];
    // Relasi One-to-Many dengan UserProfile
    public function userProfiles()
    {
        return $this->hasMany(userProfileModel::class, 'ID', 'id');
    }

    public function deviceGroup()
    {
        return $this->belongsTo(DeviceGroupModel::class, 'groupid', 'id');
    }

    public function getStatusAttribute()
    {
        // Ambil TM_EVENT terbesar (terakhir) berdasarkan DEVICESN == sn
        $latestEventTime = deviceEvent::where('DEVICESN', $this->sn)->max('TM_EVENT');

        if (!$latestEventTime) {
            return 'Not Connected';
        }

        $eventTime = Carbon::parse($latestEventTime);
        $diffInMinutes = $eventTime->diffInMinutes(now());

        if ($diffInMinutes > 5) {
            return 'Not Connected';
        }

        return 'Connected';
    }

    public function weekzoneDevices()
    {
        return $this->hasMany(WeekzoneDevice::class, 'deviceid', 'id');
    }
}
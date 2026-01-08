<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceGroupModel extends Model
{
    use HasFactory;

    protected $table = 'devicegroup';
    // Nonaktifkan timestamps
    public $timestamps = false;

    // Field yang dapat diisi secara massal
    protected $fillable = ['id', 'number', 'name', 'description'];

    public function deviceGates()
    {
        return $this->hasMany(deviceGateModel::class, 'groupid', 'id');
    }


    public function userProfiles()
    {
        return $this->hasMany(userProfileModel::class, 'ID', 'id');
    }
}

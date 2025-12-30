<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceJoinGate extends Model
{
    use HasFactory;

    protected $table = 'devicegroup_devicegate';
    public $timestamps = false;
    protected $primaryKey = 'id';

    protected $fillable = [

        'devicegroup_id',
        'devicegate_id',

    ];
}

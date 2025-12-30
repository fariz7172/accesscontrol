<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class deviceEvent extends Model
{
    use HasFactory;

    protected $table = 'tbl_deviceevent';
    // Nonaktifkan timestamps
    public $timestamps = false;

    // Field yang dapat diisi secara massal
    protected $fillable = ['DEVICESN', 'TM_EVENT'];
}
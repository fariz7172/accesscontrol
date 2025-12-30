<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PtSchedule extends Model
{
    use HasFactory;

    protected $table = 'pt_schedule';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'PT_ID',
        'MEMBER_ID',
        'START_TIME',
        'END_TIME',
        'BOOKED_AT',
        'STATUS',
        'NOTE',
        'CREATED_AT',
    ];

    public function personalTrainer()
    {
        return $this->belongsTo(userProfileModel::class, 'PT_ID', 'ID');
    }

    public function member()
    {
        return $this->belongsTo(userProfileModel::class, 'MEMBER_ID', 'ID');
    }

    public function getStatusTextAttribute()
    {
        switch ($this->STATUS) {
            case 0:
                return 'Booked';
            case 1:
                return 'Selesai';
            case 2:
                return 'Batal';
            default:
                return 'Unknown';
        }
    }
}
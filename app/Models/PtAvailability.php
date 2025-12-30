<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PtAvailability extends Model
{
    use HasFactory;

    protected $table = 'pt_availability';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'PT_ID',
        'DOW',
        'START_TIME',
        'END_TIME',
        'IS_ACTIVE',
    ];

    public function personalTrainer()
    {
        return $this->belongsTo(userProfileModel::class, 'PT_ID', 'ID');
    }

    public function getDayNameAttribute()
    {
        $days = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];
        return $days[$this->DOW] ?? 'Tidak Diketahui';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeekzoneDetail extends Model
{
    protected $table = 'weekzonedetail';
    protected $primaryKey = 'ID';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'ID',
        'wz',
        'day1',
        'day2',
        'day3',
        'day4',
        'day5',
        'day6',
        'day7'
    ];

    public function weekzone()
    {
        return $this->belongsTo(Weekzone::class, 'wz', 'ID');
    }

    public function day1()
    {
        return $this->belongsTo(Dayzone::class, 'day1', 'ID');
    }
    public function day2()
    {
        return $this->belongsTo(Dayzone::class, 'day2', 'ID');
    }
    public function day3()
    {
        return $this->belongsTo(Dayzone::class, 'day3', 'ID');
    }
    public function day4()
    {
        return $this->belongsTo(Dayzone::class, 'day4', 'ID');
    }
    public function day5()
    {
        return $this->belongsTo(Dayzone::class, 'day5', 'ID');
    }
    public function day6()
    {
        return $this->belongsTo(Dayzone::class, 'day6', 'ID');
    }
    public function day7()
    {
        return $this->belongsTo(Dayzone::class, 'day7', 'ID');
    }
}

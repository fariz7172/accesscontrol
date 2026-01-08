<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftPattern extends Model
{
    use HasFactory;

    protected $table = 'shiftpattern';
    public $timestamps = false;
    protected $primaryKey = 'Id';

    protected $fillable = [
        'PatternName',
        'PatternType',
        'pola1',
        'pola2',
        'pola3',
        'pola4',
        'pola5',
        'pola6',
        'pola7'
    ];

    // Relationships for each day
    public function shiftDay1()
    {
        return $this->belongsTo(Shift::class, 'pola1', 'id');
    }

    public function shiftDay2()
    {
        return $this->belongsTo(Shift::class, 'pola2', 'id');
    }

    public function shiftDay3()
    {
        return $this->belongsTo(Shift::class, 'pola3', 'id');
    }

    public function shiftDay4()
    {
        return $this->belongsTo(Shift::class, 'pola4', 'id');
    }

    public function shiftDay5()
    {
        return $this->belongsTo(Shift::class, 'pola5', 'id');
    }

    public function shiftDay6()
    {
        return $this->belongsTo(Shift::class, 'pola6', 'id');
    }

    public function shiftDay7()
    {
        return $this->belongsTo(Shift::class, 'pola7', 'id');
    }

    // Helper method to get shift for a specific day (1 to 7)
    public function getShiftForDay($day)
    {
        $field = 'pola' . $day;
        return $this->belongsTo(Shift::class, $field, 'id')->first();
    }
}

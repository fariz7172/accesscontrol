<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;


    protected $table = 'shift';
    public $timestamps = false;
    protected $primaryKey = 'id';

    protected $fillable = [
        'ShiftNo',
        'ShiftName',
        'Begin_Time',
        'Start_In',
        'Range_In',
        'Break_Time',
        'Start_Break',
        'Range_Break',
        'Resume_Time',
        'Start_Resume',
        'Range_Resume',
        'Out_time',
        'Start_Out',
        'Range_Out',
        'Tipe',
        'DDay'
    ];
}

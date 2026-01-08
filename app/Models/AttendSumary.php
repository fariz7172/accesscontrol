<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendSumary extends Model
{
    use HasFactory;

    protected $table = 'attendsummary';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = [
        'Period',
        'StartPeriod',
        'EndPeriod',
        'EmployeeID',
        'WorkingDays',
        'Present',
        'Absent',
        'LateIn',
        'EarlyOut',
        'LateInMinute',
        'EarlyOutMinute',
        'TotalWorktime',
        'TotalWorkHour',
        'OT',
        'OTMinute',
        'EarlyWork',
        'EarlyWorkMinute',
        'Ncheckin',
        'Ncheckout',
        'LeaveTaken',
        'D1',
        'D2',
        'D3',
        'D4',
        'D5',
        'D6',
        'D7',
        'D8',
        'D9',
        'D10',
        'D11',
        'D12',
        'D13',
        'D14',
        'D15',
        'D16',
        'D17',
        'D18',
        'D19',
        'D20',
    ];

    public function userProfile()
    {
        return $this->belongsTo(userProfileModel::class, 'EmployeeID', 'ID');
    }
}

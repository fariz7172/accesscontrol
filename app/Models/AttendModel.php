<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendModel extends Model
{
    use HasFactory;

    protected $table = 'attend';
    protected $primaryKey = 'id';
    protected $fillable = [
        'EmployeeID',
        'AttDate',
        'PatternID',
        'ShiftCode',
        'DayType',
        'Time_In',
        'Time_Break',
        'Time_Resume',
        'Time_Out',
        'Time_InShort',
        'Time_BreakShort',
        'Time_ResumeShort',
        'Time_OutShort',
        'WorkTime',
        'EarlyWork',
        'TotalWorkHour',
        'TotalOT',
        'Remark',
        'DutyProcessID',
        'Present'
    ];

    public $timestamps = false;

    public function userProfile()
    {
        return $this->belongsTo(userProfileModel::class, 'EmployeeID', 'ID');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'ShiftCode', 'id');
        //   return $this->belongsTo(Shift::class, 'ShiftCode', 'ShiftNo');
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'DutyProcessID', 'Id');
    }

    public function getDisplayLeaveTypeAttribute()
    {
        if ($this->Remark === 'Holiday') {
            return 'N/A';
        }

        if ($this->leaveType) {
            return $this->leaveType->Name;
        }

        return 'N/A';
    }
}

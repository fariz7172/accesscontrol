<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveProcess extends Model
{
    use HasFactory;

    protected $table = 'leaveprocess';
    public $timestamps = false;
    protected $primaryKey = 'Id';

    protected $fillable = [
        'leaveid',
        'EmplID',
        'FromDate',
        'ToDate',
        'Notes',
        'approver_id',
        'approved_at',
        'created_at',
        'updated_at',
        'STATUS',
        'priv'

    ];

    public function userProfile()
    {
        return $this->belongsTo(userProfileModel::class, 'EmplID', 'ID');
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leaveid', 'Id');
    }
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id', 'id');
    }

    public function getStatusTextAttribute()
    {
        return match ($this->STATUS) {
            0 => 'Pending',
            1 => 'Approved',
            2 => 'Rejected',
            3 => 'Cancelled',
            default => 'Unknown',
        };
    }
}

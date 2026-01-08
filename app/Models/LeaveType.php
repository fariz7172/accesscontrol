<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;

    protected $table = 'leavetype';
    public $timestamps = false;
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Name',
        'Type'
    ];

    public function leaveProcesses()
    {
        return $this->hasMany(LeaveProcess::class, 'leaveid', 'Id');
    }
}

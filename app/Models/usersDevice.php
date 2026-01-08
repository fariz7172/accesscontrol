<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class usersDevice extends Model
{
    use HasFactory;

    protected $table = 'usersxdevice';
    protected $fillable = [
        'userId',
        'gateId',
        'Lift1',
        'Lift2',
        'Lift3',
        'Lift4'
    ];
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(userProfileModel::class, 'userId', 'ID');
    }
    public function device()
    {
        return $this->belongsTo(deviceGateModel::class, 'gateId', 'id');
    }
}

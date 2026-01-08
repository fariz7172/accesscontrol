<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class userDataModel extends Model
{
    use HasFactory;

    protected $table = 'tbl_usersdata';
    public $timestamps = false;
    protected $primaryKey = 'fid';

    protected $fillable = [
        'fid',
        'Type',
        'Picture',
        'FaceNo',
        'Fp',
    ];

    public function userProfile()
    {
        return $this->belongsTo(userProfileModel::class, 'fid', 'ID');
    }

    // public function faceNoData()
    // {
    //     return $this->hasMany(userDataModel::class, 'fid', 'fid');
    // }

    public function deviceGate()
    {
        return $this->belongsTo(deviceGateModel::class, 'FaceNo', 'sn');
    }
    // public function tags()
    // {
    //     return $this->hasMany(tagModel::class, 'fid', 'fid');
    // }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeekzoneUser extends Model
{
    use HasFactory;

    protected $table = 'weekzoneuser';
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = 'id';

    // Tambahkan 'deviceid' ke fillable
    protected $fillable = ['id', 'wzid', 'userid', 'deviceid'];

    public function weekzone()
    {
        return $this->belongsTo(Weekzone::class, 'wzid', 'ID');
    }

    public function user()
    {
        return $this->belongsTo(userProfileModel::class, 'userid', 'ID');
    }

    public function device()
    {
        return $this->belongsTo(deviceGateModel::class, 'deviceid', 'id');
    }
}

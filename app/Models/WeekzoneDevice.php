<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeekzoneDevice extends Model
{
    use HasFactory;

    protected $table = 'weekzonedevice';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $fillable = ['wzid', 'deviceid'];
    public $timestamps = false;

    public function weekzone()
    {
        return $this->belongsTo(Weekzone::class, 'wzid', 'ID');
    }
}

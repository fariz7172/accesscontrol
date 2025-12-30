<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Weekzone extends Model
{
    use HasFactory;

    protected $table = 'weekzone';
    protected $primaryKey = 'ID';
    public $incrementing = false;
    protected $fillable = ['ID', 'Name'];
    public $timestamps = false;

    public function weekzoneDetails()
    {
        return $this->hasMany(WeekzoneDetail::class, 'wz', 'ID');
    }
}
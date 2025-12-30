<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HolidayCal extends Model
{
    use HasFactory;

    protected $table = 'holidaycal';
    public $timestamps = false;
    protected $primaryKey = 'ID';

    protected $fillable = [
        'Name',
        'StartDate',
        'EndDate'
    ];
}

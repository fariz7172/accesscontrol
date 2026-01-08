<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dayzone extends Model
{
    use HasFactory;

    protected $table = 'dayzone';
    protected $primaryKey = 'ID';
    public $incrementing = false;
    protected $fillable = ['ID', 'Name'];

    public $timestamps = false;
}

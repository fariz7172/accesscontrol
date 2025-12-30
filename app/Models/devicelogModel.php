<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class devicelogModel extends Model
{
    use HasFactory;
    // Nama tabel
    protected $table = 'devicelog';
    // Nonaktifkan timestamps
    public $timestamps = false;

    // Field yang dapat diisi secara massal
    protected $fillable = ['log_date', 'modul', 'desc', 'status'];
}
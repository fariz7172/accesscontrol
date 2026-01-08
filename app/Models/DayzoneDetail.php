<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DayzoneDetail extends Model
{
    use HasFactory;

    protected $table = 'dayzonedetail';
    protected $primaryKey = 'ID';
    public $incrementing = false;
    protected $fillable = ['ID', 'dz', 'Stz1', 'Etz1', 'Stz2', 'Etz2', 'Stz3', 'Etz3', 'Stz4', 'Etz4', 'Stz5', 'Etz5'];
    public $timestamps = false;

    public function dayzone()
    {
        return $this->belongsTo(Dayzone::class, 'dz', 'ID');
    }
}

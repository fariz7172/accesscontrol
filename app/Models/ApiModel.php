<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiModel extends Model
{
    use HasFactory;
    protected $table = 'api';
    protected $fillable = ['name', 'desc'];
    protected $primaryKey = 'id';
    public $timestamps = false;
}

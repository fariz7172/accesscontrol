<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;
    protected $table = 'branch';
    protected $fillable = ['number', 'name', 'description'];
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function userProfiles()
    {
        return $this->hasMany(userProfileModel::class, 'Branchid', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class departmentModel extends Model
{
    use HasFactory;


    protected $table = 'departemen';
    // Nonaktifkan timestamps
    public $timestamps = false;
    protected $primaryKey = 'id';
    // Field yang dapat diisi secara massal
    protected $fillable = [

        'number',
        'name',
        'description'
    ];
    // Relasi One-to-Many dengan UserProfile
    public function userProfiles()
    {
        return $this->hasMany(userProfileModel::class, 'Depid', 'id');
    }
}

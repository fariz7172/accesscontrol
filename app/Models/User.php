<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'userlogin';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'username',
        'password',
        'priv',
        'bactive',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<intCopy code, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'bactive' => 'array', // Cast bactive as an array for JSON handling
    ];

    /**
     * Get access permission for a specific key.
     *
     * @param string $key
     * @return bool
     */
    public function hasAccess($key)
    {
        return isset($this->bactive[$key]) && $this->bactive[$key];
    }
}

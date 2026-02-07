<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'phone',
        'image',
        'role',
        'status',
        'address',
        'date_of_birth',
        'facebook',
        'twitter',
        'instagram',
        'linkedin',
        'about',
        'description',
        'education',
        'experience',
        'skill',
        'achievement',
        'philosophy',
        'children',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Auto-generate UUID on create
     */
    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
        });
    }
}

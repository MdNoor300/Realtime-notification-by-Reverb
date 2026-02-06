<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = [
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
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'logo',
        'dark_logo',
        'email',
        'phone',
        'address',
        'copyright',
        'facebook',
        'twitter',
        'instagram',
        'youtube',
        'linkedin',
        'whatsapp',
        'ai_key',
        'theme',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    protected $fillable = ['settings'];
    protected $casts = [
        'settings' => 'array',
    ];
}

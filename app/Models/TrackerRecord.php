<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackerRecord extends Model
{
    // указываем, что ID здесь не числовой и не автоинкрементный
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'user_id', 'mode', 'date_key', 'description', 'notes',
        'val1', 'val2', 'val3', 'weight', 'is_completed', 'sort_order'
    ];
}

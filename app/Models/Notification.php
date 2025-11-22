<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'message',
        'role',
        'read',
        'time',
    ];

    protected $casts = [
        'read' => 'boolean',
        'time' => 'datetime',
    ];
}

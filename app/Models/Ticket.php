<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    // ✅ Allow mass assignment
    protected $fillable = [
        'title',
        'description',
        'priority',
        'status',     // 🔹 include this field (since your frontend filters by status)
        'user_id',
    ];

    // ✅ Default attribute values (optional but recommended)
    protected $attributes = [
        'status' => 'open', // newly created tickets default to "open"
    ];

    // ✅ Relationship: each ticket belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

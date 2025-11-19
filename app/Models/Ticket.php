<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    // ✅ Allow mass assignment
    protected $fillable = [
        'ticket_id',  // ✅ Added
        'title',
        'description',
        'priority',
        'status',
        'user_id',
    ];

    // ✅ Default attribute values
    protected $attributes = [
        'status' => 'open',
    ];

    // ✅ Relationship: each ticket belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Auto-generate ticket_id when creating a new ticket
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (empty($ticket->ticket_id)) {
                // Get user initials
                $user = $ticket->user;
                $initials = 'XX'; // Default if no user

                if ($user && $user->name) {
                    $nameParts = explode(' ', trim($user->name));
                    if (count($nameParts) >= 2) {
                        // First and last name initials
                        $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1));
                    } else {
                        // Just first two letters of name
                        $initials = strtoupper(substr($user->name, 0, 2));
                    }
                }

                // Generate format: TKT-XX-YYYYMMDD-HHMMSS
                $ticket->ticket_id = 'TKT-' . $initials . '-' . date('Ymd-His');

                // Ensure uniqueness
                $counter = 1;
                $baseTicketId = $ticket->ticket_id;
                while (static::where('ticket_id', $ticket->ticket_id)->exists()) {
                    $ticket->ticket_id = $baseTicketId . '-' . $counter;
                    $counter++;
                }
            }
        });
    }
}

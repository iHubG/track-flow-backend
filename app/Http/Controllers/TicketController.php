<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Notification; // your custom model

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $tickets = Ticket::where('user_id', $user->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $tickets,
        ]);
    }

    public function allTickets(Request $request)
    {
        $user = $request->user();

        if (!$user->hasRole(['support', 'admin'])) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $tickets = Ticket::latest()->get();

        return response()->json([
            'data' => $tickets,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|min:5',
            'description' => 'required',
            'priority' => 'required|in:low,medium,high',
        ]);

        $validated['user_id'] = $request->user()->id;

        $ticket = Ticket::create($validated);

        // 🔔 Notify support & admin
        $recipients = User::role(['support', 'admin'])->get();
        foreach ($recipients as $recipient) {
            Notification::create([
                'user_id' => $recipient->id,
                'message' => $request->user()->name . " created a new ticket.",
                'role'    => $recipient->roles->first()->name,
                'read'    => false,
            ]);
        }

        return response()->json([
            'message' => 'Ticket created successfully.',
            'data' => $ticket,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json([
                'message' => 'Ticket not found.',
            ], 404);
        }

        if ($user->hasRole(['admin', 'support'])) {
            $validated = $request->validate([
                'status' => 'required|in:open,in_progress,closed',
            ]);

            $ticket->update([
                'status' => $validated['status'],
            ]);

            // 🔔 Notify the ticket owner
            Notification::create([
                'user_id' => $ticket->user_id,
                'message' => $user->name . " updated your ticket status to " . $validated['status'],
                'role'    => 'user',
                'read'    => false,
            ]);

            return response()->json([
                'message' => 'Ticket status updated.',
                'data' => $ticket,
            ]);
        }
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json([
                'message' => 'Ticket not found.',
            ], 404);
        }

        if ($user->hasRole(['admin', 'support'])) {
            $ticket->delete();

            return response()->json([
                'message' => 'Ticket deleted.',
            ]);
        }

        if ($ticket->user_id !== $user->id) {
            return response()->json([
                'message' => 'Forbidden.',
            ], 403);
        }

        $ticket->delete();

        return response()->json([
            'message' => 'Ticket deleted.',
        ]);
    }
}

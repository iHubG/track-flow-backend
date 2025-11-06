<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;

class TicketController extends Controller
{
    /**
     * 🧾 List tickets belonging to the authenticated user.
     */
    public function index(Request $request)
    {
        // ✅ Get the authenticated user
        $user = $request->user();

        // ✅ Fetch only their tickets, newest first
        $tickets = Ticket::where('user_id', $user->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $tickets,
        ]);
    }

    /**
     * 📨 Create a new ticket linked to the authenticated user.
     */
    public function store(Request $request)
    {
        // ✅ Validate request input
        $validated = $request->validate([
            'title' => 'required|min:5',
            'description' => 'required',
            'priority' => 'required|in:low,medium,high',
        ]);

        // ✅ Add user_id automatically
        $validated['user_id'] = $request->user()->id;

        // ✅ Create and save
        $ticket = Ticket::create($validated);

        return response()->json([
            'message' => 'Ticket created successfully.',
            'data' => $ticket,
        ], 201);
    }

    // Delete a ticket function

    public function destroy(Request $request, $id)
    {
        // ✅ Get the authenticated user
        $user = $request->user();

        // ✅ Find the ticket by ID
        $ticket = Ticket::find($id);

        // ✅ Check if the ticket exists and belongs to the user
        if (!$ticket || $ticket->user_id !== $user->id) {
            return response()->json([
                'message' => 'Ticket not found or access denied.',
            ], 404);
        }

        // ✅ Delete the ticket
        $ticket->delete();

        return response()->json([
            'message' => 'Ticket deleted successfully.',
        ]);
    }
}

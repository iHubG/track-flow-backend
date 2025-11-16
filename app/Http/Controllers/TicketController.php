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
     * 🧾 List ALL tickets (for support/admin only).
     */
    public function allTickets(Request $request)
    {
        // ✅ Get the authenticated user
        $user = $request->user();

        // ✅ Check if user has support or admin role
        if (!$user->hasRole(['support', 'admin'])) {
            return response()->json([
                'message' => 'Unauthorized to view all tickets'
            ], 403);
        }

        // ✅ Fetch all tickets, newest first
        $tickets = Ticket::latest()->get();

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

    // Update ticket status function
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json([
                'message' => 'Ticket not found.',
            ], 404);
        }

        // --- Allow admin/support to update ANY ticket ---
        if ($user->hasRole(['admin', 'support'])) {
            $validated = $request->validate([
                'status' => 'required|in:open,in_progress,closed',
            ]);
            $ticket->update([
                'status' => $validated['status'],
            ]);
            return response()->json([
                'message' => 'Ticket status updated successfully.',
                'data' => $ticket,
            ]);
        }
    }



    // Delete a ticket function
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json([
                'message' => 'Ticket not found.',
            ], 404);
        }

        // --- Allow admin/support to delete ANY ticket ---
        if ($user->hasRole(['admin', 'support'])) {
            $ticket->delete();

            return response()->json([
                'message' => 'Ticket deleted successfully.',
            ]);
        }

        // --- Normal user: can only delete their own ticket ---
        if ($ticket->user_id !== $user->id) {
            return response()->json([
                'message' => 'You are not allowed to delete this ticket.',
            ], 403);
        }

        $ticket->delete();

        return response()->json([
            'message' => 'Ticket deleted successfully.',
        ]);
    }
}

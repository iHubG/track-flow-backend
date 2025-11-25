<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // Get notifications based on logged-in user role
    public function index(Request $request)
    {
        $user = $request->user();

        // Simply get the latest 10 notifications for the authenticated user
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json($notifications);
    }


    // Mark a notification as read
    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();
        $notification = Notification::findOrFail($id);

        // Authorization check
        if ($notification->user_id !== $user->id && $notification->role !== $user->role) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->update(['read' => true]);

        return response()->json([
            'message' => 'Notification marked as read.',
            'notification' => $notification
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        Notification::where('role', $user->role)
            ->orWhere('user_id', $user->id)
            ->update(['read' => true]);

        return response()->json(['message' => 'All notifications marked as read']);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $dashboardData = Cache::remember('dashboard_stats', 60, function () {
            $today = now()->startOfDay();
            $yesterday = now()->subDay()->startOfDay();

            // Current data
            $totalTickets = Ticket::count();
            $totalSupport = User::role('support')->count();
            $totalUsers = User::role('user')->count();
            $newTicketsToday = Ticket::whereDate('created_at', $today)->count();
            $resolvedTicketsToday = Ticket::where('status', 'closed')
                ->whereDate('updated_at', $today)
                ->count();
            $activeTicketsToday = Ticket::whereIn('status', ['open', 'in_progress'])
                ->whereDate('updated_at', $today)
                ->count();

            // Yesterday's data for comparison
            $totalTicketsYesterday = Ticket::whereDate('created_at', '<', $today)->count();
            $totalSupportYesterday = User::role('support')
                ->whereDate('created_at', '<', $today)
                ->count();
            $totalUsersYesterday = User::role('user')
                ->whereDate('created_at', '<', $today)
                ->count();
            $newTicketsYesterday = Ticket::whereDate('created_at', $yesterday)->count();
            $resolvedTicketsYesterday = Ticket::where('status', 'closed')
                ->whereDate('updated_at', $yesterday)
                ->count();
            $activeTicketsYesterday = Ticket::whereIn('status', ['open', 'in_progress'])
                ->whereDate('updated_at', $yesterday)
                ->count();

            return [
                // Support Dashboard
                'total_tickets' => $totalTickets,
                'tickets_change' => $this->calculateChange($totalTicketsYesterday, $totalTickets),

                'pending' => Ticket::where('status', 'open')->count(),
                'in_progress' => Ticket::where('status', 'in_progress')->count(),
                'resolved' => Ticket::where('status', 'closed')->count(),
                'active' => Ticket::whereIn('status', ['open', 'in_progress'])->count(),
                'completed' => Ticket::where('status', 'closed')->count(),

                // Admin Dashboard
                'total_support' => $totalSupport,
                'support_change' => $this->calculateChange($totalSupportYesterday, $totalSupport),

                'total_users' => $totalUsers,
                'users_change' => $this->calculateChange($totalUsersYesterday, $totalUsers),

                'new_tickets_today' => $newTicketsToday,
                'new_change' => $this->calculateChange($newTicketsYesterday, $newTicketsToday),

                'resolved_tickets_today' => $resolvedTicketsToday,
                'resolved_change' => $this->calculateChange($resolvedTicketsYesterday, $resolvedTicketsToday),

                'active_tickets_today' => $activeTicketsToday,
                'active_change' => $this->calculateChange($activeTicketsYesterday, $activeTicketsToday),
            ];
        });

        return response()->json([
            'data' => $dashboardData,
        ]);
    }

    /**
     * Calculate percentage change between two values
     */
    private function calculateChange($oldValue, $newValue)
    {
        if ($oldValue == 0) {
            return $newValue > 0 ? 100 : 0;
        }

        $change = (($newValue - $oldValue) / $oldValue) * 100;
        return round($change, 1);
    }
}

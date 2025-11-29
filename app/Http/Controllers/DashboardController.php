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
            $totalTicketsAssigned = Ticket::where('assigned_to', auth()->id())->count();
            $newTicketsAssigned = Ticket::where('assigned_to', auth()->id())->whereDate('created_at', $today)->count();
            $pendingTicketsAssigned = Ticket::where('assigned_to', auth()->id())->where('status', 'open')->count();
            $activeTicketsAssigned = Ticket::where('assigned_to', auth()->id())
                ->whereIn('status', ['open', 'in_progress'])
                ->count();
            $resolvedTicketsAssignedToday = Ticket::where('assigned_to', auth()->id())
                ->where('status', 'closed')
                ->whereDate('updated_at', $today)
                ->count();
            $totalCompletedAssigned = Ticket::where('assigned_to', auth()->id())
                ->where('status', 'closed')
                ->count();

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
            $totalTicketsAssignedYesterday = Ticket::where('assigned_to', auth()->id())
                ->whereDate('created_at', '<', $today)
                ->count();
            $newTicketsAssignedYesterday = Ticket::where('assigned_to', auth()->id())
                ->whereDate('created_at', $yesterday)
                ->count();
            $pendingTicketsAssignedYesterday = Ticket::where('assigned_to', auth()->id())
                ->where('status', 'open')
                ->whereDate('created_at', '<', $today)
                ->count();
            $activeTicketsAssignedYesterday = Ticket::where('assigned_to', auth()->id())
                ->whereIn('status', ['open', 'in_progress'])
                ->whereDate('created_at', '<', $today)
                ->count();
            $resolvedTicketsAssignedYesterday = Ticket::where('assigned_to', auth()->id())
                ->where('status', 'closed')
                ->whereDate('updated_at', $yesterday)
                ->count();
            $totalCompletedAssignedYesterday = Ticket::where('assigned_to', auth()->id())
                ->where('status', 'closed')
                ->whereDate('created_at', '<', $today)
                ->count();
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
                'total_assigned_tickets' => $totalTicketsAssigned,
                'assigned_change_tickets' => $this->calculateChange($totalTicketsAssignedYesterday, $totalTicketsAssigned),
                'new_assigned_tickets_today' => $newTicketsAssigned,
                'new_assigned_change' => $this->calculateChange($newTicketsAssignedYesterday, $newTicketsAssigned),
                'pending_assigned_tickets' => $pendingTicketsAssigned,
                'pending_assigned_change' => $this->calculateChange($pendingTicketsAssignedYesterday, $pendingTicketsAssigned),
                'active_assigned_tickets' => $activeTicketsAssigned,
                'active_assigned_change' => $this->calculateChange($activeTicketsAssignedYesterday, $activeTicketsAssigned),
                'resolved_assigned_tickets_today' => $resolvedTicketsAssignedToday,
                'resolved_assigned_change' => $this->calculateChange($resolvedTicketsAssignedYesterday, $resolvedTicketsAssignedToday),
                'total_completed_assigned_tickets' => $totalCompletedAssigned,
                'total_completed_assigned_change' => $this->calculateChange($totalCompletedAssignedYesterday, $totalCompletedAssigned),


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

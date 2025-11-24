<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{

    public function index()
    {
        $dashboardData = Cache::remember('dashboard_stats', 60, function () {
            return [
                'total_tickets' => Ticket::count(),
                'pending' => Ticket::where('status', 'open')->count(),
                'in_progress' => Ticket::where('status', 'in_progress')->count(),
                'resolved' => Ticket::where('status', 'closed')->count(),
                'active' => Ticket::whereIn('status', ['open', 'in_progress'])->count(),
                'completed' => Ticket::where('status', 'closed')->count(),
            ];
        });

        return response()->json([
            'data' => $dashboardData,
        ]);
    }
}

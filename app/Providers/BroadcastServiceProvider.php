<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Load channels.php
        Broadcast::routes([
            'middleware' => ['auth:sanctum'], // ← IMPORTANT
        ]);

        require base_path('routes/channels.php');
    }
}

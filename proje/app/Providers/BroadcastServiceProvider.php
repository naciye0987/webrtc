<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Broadcast::routes(['middleware' => []]);

        // Presence channel için auth kuralını güncelle
        Broadcast::channel('presence-video-room-*', function ($user = null) {
            return [
                'id' => uniqid(),
                'name' => request()->input('username', 'Anonim')
            ];
        });
    }
}

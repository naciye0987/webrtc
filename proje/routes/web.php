<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
use Pusher\Pusher;

// CORS için OPTIONS request'lerine izin ver
Route::options('/broadcasting/auth', function () {
    return response('', 200)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'POST, GET, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, X-Auth-Token, Origin, Authorization');
});

// Broadcasting auth route'unu düzeltelim
Route::match(['get', 'post'], '/broadcasting/auth', function (Request $request) {
    try {
        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true,
                'encrypted' => true
            ]
        );

        $socketId = $request->socket_id;
        $channelName = $request->channel_name;
        
        // Presence data
        $presenceData = [
            'user_id' => uniqid(),
            'user_info' => [
                'name' => $request->input('username', 'Anonim'),
                'room_id' => $request->input('room_id')
            ]
        ];

        // Auth string oluştur
        $auth = $pusher->presence_auth(
            $channelName,
            $socketId,
            $presenceData['user_id'],
            $presenceData['user_info']
        );

        return response($auth)
            ->header('Content-Type', 'application/json')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'POST, GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, X-Auth-Token, Origin, Authorization');
            
    } catch (\Exception $e) {
        Log::error('Pusher Auth Error: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
})->withoutMiddleware(['web', 'csrf']);

// Ana sayfa route'u
Route::get('/', function () {
    return view('login');
});

// Oda katılım route'u
Route::post('/join-room', function (Request $request) {
    $username = $request->input('username');
    $roomId = $request->input('room_id');
    
    return view('video-room', [
        'username' => $username,
        'roomId' => $roomId
    ]);
})->name('join.room');

// Broadcasting routes
Broadcast::routes(['middleware' => []]);

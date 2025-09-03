<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function showLoginForm()
    {
        return view('room.login');
    }

    public function joinRoom(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'room_id' => 'required|string|max:50'
        ]);

        session([
            'user_name' => $request->name,
            'room_id' => $request->room_id
        ]);

        return redirect()->route('room.chat');
    }

    public function chat()
    {
        if (!session('user_name') || !session('room_id')) {
            return redirect()->route('room.login');
        }

        return view('room.chat', [
            'userName' => session('user_name'),
            'roomId' => session('room_id')
        ]);
    }
} 
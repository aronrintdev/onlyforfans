<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\Timeline;
use App\Models\User;

class MessageController extends Controller
{
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->middleware('auth');
    }
    public function index()
    {
        return Message::all();
    }
    public function fetchUsers(Request $request)
    {
        $sessionUser = $request->user();
        $timeline = Timeline::where('user_id', $sessionUser->id)->first();
        $followingUserIDs = $timeline->user->followedtimelines->pluck('id');
        $users = Timeline::with(['user', 'avatar'])->get();
        return [
            'followers' => $timeline->followers,
            'following' => $users
        ];
    }
}

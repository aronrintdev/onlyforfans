<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\Timeline;
use App\Models\User;
use function _\sortBy;
use function _\filter;

class MessageController extends Controller
{
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->middleware('auth');
    }
    public function index()
    {
        return Message::with('user', 'receiver')->get();
    }
    public function fetchUsers(Request $request)
    {
        $sessionUser = $request->user();
        // Get contacts 
        $receivers = Message::where('user_id', $sessionUser->id)->pluck('receiver_id')->toArray();

        $timeline = Timeline::where('user_id', $sessionUser->id)->first();
        $followingUserIDs = $timeline->user->followedtimelinesa->pluck('id');
        $users = Timeline::with(['user', 'avatar'])->whereIn('id', $followingUserIDs)->whereNotIn('user_id', $receivers)->get()->makeVisible(['user']);
        $users->each(function ($user) {
            $user->username = $user->user->username;
            $user->id = $user->user->id;
        });
    
        $followers = $timeline->followers->whereNotIn('id', $receivers)->all();  
        $arr = [];
        foreach ($followers as $follower) {
            array_push($arr, $follower);
        } 
     

        return [
            'followers' => $arr,
            'following' => $users
        ];
    }
    public function fetchContacts(Request $request)
    {
        $receivers = Message::with('receiver')
            ->whereHas('receiver', function($query) use(&$request) {
                $sessionUser = $request->user();
                $searchText = $request->query('name');

                $query->where('user_id', $sessionUser->id)
                    ->where('username', 'like', '%' . $searchText . '%');
            })
            ->orWhere(function($query) use(&$request) {
                $sessionUser = $request->user();
                $searchText = $request->query('name');

                $query->where('user_id', $sessionUser->id)
                    ->where('receiver_name', 'like', '%' . $searchText . '%');
            })
            ->pluck('receiver_id')
            ->toArray();

        $contacts = array();
        foreach($receivers as $receiver) {
            $lastMessage = Message::with(['receiver'])->where('receiver_id', $receiver)->orWhere('user_id', $receiver)->latest()->first();
            $user = Timeline::with(['user', 'avatar'])->where('user_id', $receiver)->first()->makeVisible(['user']);
            $user->username = $user->user->username;
            $user->id = $user->user->id;
            array_push($contacts, [
                'last_message' => $lastMessage,
                'profile' => $user
            ]);
        }

        // SortBy
        $sortBy = $request->query('sort');

        if ($sortBy == 'recent') {
            $contacts = sortBy($contacts, [function($o) { return $o['last_message']['created_at']; }]);
            $contacts = array_reverse($contacts);   
        } else if ($sortBy == 'unread_first') {
            $arr1 = filter($contacts, function($o) { return $o['last_message']['is_unread']; });
            $arr1 = array_reverse($arr1);
            $arr2 = filter($contacts, function($o) { return !$o['last_message']['is_unread']; });
            $arr2 = array_reverse($arr2);
            $contacts = array_merge($arr1, $arr2);
        } else if ($sortBy == 'oldest_unread_first') {
            $arr1 = filter($contacts, function($o) { return $o['last_message']['is_unread']; });
            $arr2 = filter($contacts, function($o) { return !$o['last_message']['is_unread']; });
            $arr2 = array_reverse($arr2);
            $contacts = array_merge($arr1, $arr2);
        }
        return $contacts;
    }
    public function store(Request $request)
    {
        $user = $request->user();

        $message = $user->messages()->create([
            'message' => $request->input('message'),
            'receiver_id' => $request->input('user_id'),
            'receiver_name' => $request->input('name'),
        ]);

        // broadcast(new MessageSentEvent($message, $user))->toOthers();

        return [
            'message' => $message,
            'user' => $user,
        ];
    }
    public function clearUser(Request $request, $id)
    {
        $deleted = Message::where('receiver_id', $id)->delete();
        if ($deleted) {
            return [
                'status' => 200
            ];
        }
        return [
            'status' => 400
        ];
    }
}

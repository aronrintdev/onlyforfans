<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Message;
use App\Models\Timeline;
use App\Models\User;
use App\Events\MessageSentEvent;
use function _\sortBy;
use function _\filter;
use function _\uniq;

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
        $receivers = Message::where('user_id', $sessionUser->id)->orWhere('receiver_id', $sessionUser->id)->pluck('receiver_id')->toArray();

        $timeline = Timeline::where('user_id', $sessionUser->id)->first();
        $followingUserIDs = $timeline->user->followedtimelines->pluck('id');
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
        $sessionUser = $request->user();
        $userSetting = $sessionUser->settings;
        $cattrs = $userSetting->cattrs;
        if ( !array_key_exists('blocked', $cattrs) ) {
            $blockedUsers = [];
        } else {
            $blockedUsers = $cattrs['blocked']['usernames'];
        }
        $blockers = User::whereIn('username', $blockedUsers)->pluck('id')->toArray();
        $receivers = Message::where(function($query) use(&$request, &$blockers) {
                $sessionUser = $request->user();
                $searchText = $request->query('name');

                $query->where('user_id', $sessionUser->id)
                    ->whereNotIn('receiver_id', $blockers)
                    ->where('receiver_name', 'like', '%' . $searchText . '%');
            })
            ->pluck('receiver_id')
            ->toArray();
        // Senders
        $senders = Message::with(['user'])
            ->whereHas('user', function($query) use(&$request, &$blockers) {
                $sessionUser = $request->user();
                $searchText = $request->query('name');

                $query->where('receiver_id', $sessionUser->id)
                    ->whereNotIn('user_id', $blockers)
                    ->where('username', 'like', '%' . $searchText . '%');
            })
            ->pluck('user_id')
            ->toArray();
        $receivers = uniq(array_merge($receivers, $senders));

        $userSettings = $sessionUser->settings;

        $contacts = array();
        foreach($receivers as $receiver) {
            $lastMessage = Message::with(['receiver'])
                ->where(function($query) use(&$request, &$receiver) {
                    $sessionUser = $request->user();
                    $query->where('user_id', $sessionUser->id)
                        ->where('receiver_id', $receiver);
                })
                ->orWhere(function($query) use(&$request, &$receiver) {
                    $sessionUser = $request->user();
                    $query->where('user_id', $receiver)
                        ->where('receiver_id', $sessionUser->id);
                })
                ->latest()->first();
            $user = Timeline::with(['user', 'avatar'])->where('user_id', $receiver)->first()->makeVisible(['user']);
            $cattrs = $userSettings->cattrs;
            if ( array_key_exists('display_name', $cattrs) ) {
                if ( array_key_exists($receiver, $cattrs['display_name']) ) {
                    $user->display_name = $cattrs['display_name'][$receiver];
                }
            }
            if ( array_key_exists('muted', $cattrs) ) {
                $index = array_search($receiver, $cattrs['muted']);
                if ( $index !== false ) {
                    $user->muted = true;
                }
            }
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
    public function fetchcontact(Request $request, $id) {
        $sessionUser = $request->user();

        $offset = $request->query('offset');
        $limit = $request->query('limit');
        $messages = Message::with(['receiver'])
            ->where(function($query) use(&$request, &$id) {
                $sessionUser = $request->user();
                $query->where('user_id', $sessionUser->id)
                    ->where('receiver_id',  $id);
            })
            ->orWhere(function($query) use(&$request, &$id) {
                $sessionUser = $request->user();
                $query->where('receiver_id', $sessionUser->id)
                    ->where('user_id',  $id);
            })
            ->orderBy('created_at', 'DESC')
            ->skip($offset)
            ->take($limit)
            ->get();
        $user = Timeline::with(['user', 'avatar'])->where('user_id', $id)->first()->makeVisible(['user']);
        $user->username = $user->user->username;
        $userSetting = $sessionUser->settings;
        $cattrs = $userSetting->cattrs;
        if ( array_key_exists('display_name', $cattrs) ) {
            if ( array_key_exists($id, $cattrs['display_name']) ) {
                $user->display_name = $cattrs['display_name'][$id];
            }
        }
        if ( array_key_exists('muted', $cattrs) ) {
            $index = array_search($id, $cattrs['muted']);
            if ( $index !== false ) {
                $user->muted = true;
            }
        }
        $lists = DB::table('lists_users')->where('user_id', $id)->get();
        $user->hasLists = sizeof($lists) > 0;
        $user->id = $user->user->id;
        return [
            'messages' => $messages,
            'profile' => $user,
            'currentUser' => $request->user()
        ];
    }
    public function store(Request $request)
    {
        $sessionUser = $request->user();
        $receiver = User::where('id', $request->input('user_id'))->first();

        $message = $sessionUser->messages()->create([
            'message' => $request->input('message'),
            'media_id' => $request->input('media_id'),
            'receiver_id' => $request->input('user_id'),
            'receiver_name' => $request->input('name'),
            'is_unread' => !$receiver->is_online
        ]);

        broadcast(new MessageSentEvent(Message::with(['receiver', 'media'])->where('id', $message->id)->first(), $sessionUser))->toOthers();

        return [
            'message' => Message::with(['receiver', 'media'])->where('id', $message->id)->first(),
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
    public function markAsRead(Request $request, $id) {
        $sessionUser = $request->user();

        $messages = Message::where('user_id', $sessionUser->id)
            ->where('receiver_id',  $id)
            ->update(['is_unread' => 0]);
        return ['status' => 200];
    }
    public function markAllAsRead(Request $request) {
        $sessionUser = $request->user();

        $messages = Message::where('user_id', $sessionUser->id)
            ->update(['is_unread' => 0]);
        return ['status' => 200];
    }
    public function filterMessages(Request $request, $id) {
        $sessionUser = $request->user();

        $messages = Message::with(['receiver'])
            ->where(function($query) use(&$request, &$id) {
                $sessionUser = $request->user();
                $filter = $request->query('query');

                $query->where('user_id', $sessionUser->id)
                    ->where('receiver_id',  $id)
                    ->where('message', 'like', '%'.$filter.'%');
            })
            ->orWhere(function($query) use(&$request, &$id) {
                $sessionUser = $request->user();
                $filter = $request->query('query');

                $query->where('receiver_id', $sessionUser->id)
                    ->where('user_id',  $id)
                    ->where('message', 'like', '%'.$filter.'%');
            })
            ->orderBy('created_at', 'DESC')
            ->pluck('id')
            ->toArray();
        return $messages;
    }
    public function mute(Request $request, $id) {
        $sessionUser = $request->user();
        $userSetting = $sessionUser->settings;
        $cattrs = $userSetting->cattrs;
        if ( !array_key_exists('muted', $cattrs) ) {
            $muted = [];
        } else {
            $muted = $cattrs['muted'];
        }
        array_push($muted, $id);
        $cattrs['muted'] = $muted;
        $userSetting->cattrs = $cattrs;
        $userSetting->save();
        return;
    }
    public function unmute(Request $request, $id) {
        $sessionUser = $request->user();
        $userSetting = $sessionUser->settings;
        $cattrs = $userSetting->cattrs;
        if ( !array_key_exists('muted', $cattrs) ) {
            return;
        }
        $muted = $cattrs['muted']; // pop
        $index = array_search($id, $muted);
        if ( $index !== false ) {
            array_splice($muted, $index, 1);
        }
        $cattrs['muted'] = $muted; 
        $userSetting->cattrs = $cattrs;
        $userSetting->save();
        return;
    }
    public function setCustomName(Request $request, $id) {
        $sessionUser = $request->user();
        $userSetting = $sessionUser->settings;
        $cattrs = $userSetting->cattrs;
        if ( !array_key_exists('display_name', $cattrs) ) {
            $display_name = [];
        } else {
            $display_name = $cattrs['display_name'];
            unset($display_name[$id]);
        }
        $new[$id] = $request->input('name');
        $display_name += $new;
        $cattrs['display_name'] = $display_name;
        $userSetting->cattrs = $cattrs;
        $userSetting->save();
        return;
    }
    public function listMediafiles(Request $request, $id) {
        $sessionUser = $request->user();
        $messages = Message::with('media')
            ->where(function($query) use(&$request, &$id) {
                $sessionUser = $request->user();
                $query->where('user_id', $sessionUser->id)
                    ->where('receiver_id', $id);
            })
            ->orWhere(function($query) use(&$request, &$id) {
                $sessionUser = $request->user();
                $query->where('user_id', $id)
                    ->where('receiver_id', $sessionUser->id);
            })
            ->get()
            ->toArray();
        $mediafiles = [];
        foreach ($messages as $message) {
            if ($message['media']) {
                array_push($mediafiles, $message['media']);
            }
        };
        return $mediafiles;
    }
}

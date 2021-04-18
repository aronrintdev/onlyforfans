<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Message;
use App\Models\ChatThread;
use App\Models\Timeline;
use App\Models\User;
use App\Events\MessageSentEvent;
use App\Enums\MediafileTypeEnum;
use function _\sortBy;
use function _\orderBy;
use function _\filter;
use function _\uniq;
use function _\uniqBy;

class MessageController extends Controller
{
    public function __construct(Request $request)
    {  
        $this->request = $request;
        $this->middleware('auth');
    }
    public function index()
    {
        return ChatThread::with('sender', 'receiver')->get();
    }
    public function fetchUsers(Request $request)
    {
        $sessionUser = $request->user();
        // Get contacts 
        $receivers = ChatThread::where('sender_id', $sessionUser->id)->orWhere('receiver_id', $sessionUser->id)->pluck('receiver_id')->toArray();

        $timeline = Timeline::where('user_id', $sessionUser->id)->first();
        $followingUserIDs = $timeline->user->followedtimelines->pluck('id');
        $users = Timeline::with(['user', 'avatar'])->whereIn('id', $followingUserIDs)->whereNotIn('user_id', $receivers)->get()->makeVisible(['user'])->toArray();
        $followings = [];
        foreach ($users as $user) {
            $user['username'] = $user['user']['username'];
            $user['id'] = $user['user']['id'];
            array_push($followings, $user);
        };
    
        $followers = $timeline->followers->whereNotIn('id', $receivers)->all();     
        $arr = [];
        foreach ($followers as $follower) {
            array_push($arr, $follower);    
        }
        $users = uniqBy(array_merge($followings, $arr), 'id');
     
        // Sort
        $sortBy = $request->query('sort');
        $dir = $request->query('dir');
        $dir = isset($dir) ? $dir : 'asc'; 

        switch ($sortBy) {
            case 'recent':
                $users = orderBy($users, ['created_at'], [$dir]);
                $users = array_map(function($user) {
                    $user = $user['value'];
                    return $user;
                }, $users);
                break;
            case 'name':
                $users = orderBy($users, ['name'], [$dir]);
                $users = array_map(function($user) {
                    $user = $user['value'];
                    return $user;
                }, $users);
                break;
            case 'available':
                $users = orderBy($users, ['is_online', 'id'], [$dir, $dir]);
                $users = array_map(function($user) {
                    $user = $user['value'];
                    return $user;
                }, $users);
                break;
        }

        return [
            'users' => $users,
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

        $searchText = $request->query('name');
        $receivers = ChatThread::with(['receiver' => function($query) { 
                    $query->where('name', 'like', '%' . $searchText . '%');
                }
            ])
            ->where('sender_id', $sessionUser->id)
            ->whereNotIn('receiver_id', $blockers)
            ->pluck('receiver_id')
            ->toArray();
        // Senders
        $senders = ChatThread::with(['sender'])
            ->whereHas('sender', function($query) use(&$request, &$blockers) {
                $sessionUser = $request->user();
                $searchText = $request->query('name');

                $query->where('receiver_id', $sessionUser->id)
                    ->whereNotIn('sender_id', $blockers)
                    ->where('username', 'like', '%' . $searchText . '%');
            })
            ->pluck('sender_id')
            ->toArray();
        $receivers = uniq(array_merge($receivers, $senders));

        $userSettings = $sessionUser->settings;

        $contacts = array();
        foreach($receivers as $receiver) {
            $lastChatThread = ChatThread::where(function($query) use(&$request, &$receiver) {
                    $sessionUser = $request->user();
                    $query->where('sender_id', $sessionUser->id)
                        ->where('receiver_id', $receiver);
                })
                ->orWhere(function($query) use(&$request, &$receiver) {
                    $sessionUser = $request->user();
                    $query->where('sender_id', $receiver)
                        ->where('receiver_id', $sessionUser->id);
                })
                ->orderBy('created_at', 'desc')
                ->first();
            $messages = $lastChatThread->messages()->with('mediafile')->orderBy('mcounter', 'desc')->get();
            $hasMediafile = false;
            $messages->each(function($msg) use(&$hasMediafile) {
                if (isset($msg->mediafile)) {
                    $hasMediafile = true;
                }
            });
            $lastMessage = $messages->first();
            $lastMessage->hasMediafile = $hasMediafile;
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
        $chatthreads = ChatThread::with(['receiver'])
            ->where(function($query) use(&$request, &$id) {
                $sessionUser = $request->user();
                $query->where('sender_id', $sessionUser->id)
                    ->where('receiver_id',  $id);
            })
            ->orWhere(function($query) use(&$request, &$id) {
                $sessionUser = $request->user();
                $query->where('receiver_id', $sessionUser->id)
                    ->where('sender_id',  $id);
            })
            ->orderBy('created_at', 'DESC')
            ->skip($offset)
            ->take($limit)
            ->get();
        $chatthreads->each(function($chatthread) {
            $chatthread->messages = $chatthread->messages()->with('mediafile')->orderBy('mcounter', 'asc')->get();
        });
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
            'messages' => $chatthreads,
            'profile' => $user,
            'currentUser' => $request->user()
        ];
    }
    public function store(Request $request)
    {
        $sessionUser = $request->user();
        $receiver = User::where('id', $request->input('user_id'))->first();

        $chatthread = $sessionUser->chatthreads()->create([
            'receiver_id' => $request->input('user_id'),
        ]);

        $files = $request->file('mediafile');
        if ($files) {
            $index = 1;
            foreach ($files as $file) {
                $message = $chatthread->messages()->create([
                    'mcontent' => '',
                    'is_unread' => 1,
                    'mcounter' => $index,
                ]);
                $message->mediafile()->create([
                    'resource_type' => 'messages',
                    'filename' => $file->store('./galleries', 's3'),
                    'mfname' => $file->getClientOriginalName(),
                    'mftype' => MediafileTypeEnum::GALLERY,
                    'mimetype' => $file->getMimeType(),
                    'orig_filename' => $file->getClientOriginalName(),
                    'orig_ext' => $file->getClientOriginalExtension(),
                ]);
                $index++;
            }
            $mcontent = $request->input('message');
            if (isset($mcontent)) {
                $chatthread->messages()->create([
                    'mcontent' => $mcontent,
                    'is_unread' => 1,
                    'mcounter' => $index,
                ]);
            }
        } else {
            $chatthread->messages()->create([
                'mcontent' => $request->input('message'),
                'is_unread' => 1,
            ]);
        }

        $chatthread->messages = $chatthread->messages()->with('mediafile')->orderBy('mcounter', 'asc')->get();

        broadcast(new MessageSentEvent($chatthread, $sessionUser))->toOthers();

        return [
            'message' => $chatthread,
        ];
    }
    public function clearUser(Request $request, $id)
    {
        $deleted = ChatThread::where(function($query) use(&$request, &$id) {
                $sessionUser = $request->user();
                $query->where('sender_id', $sessionUser->id)
                    ->where('receiver_id', $id);
            })
            ->orWhere(function($query) use(&$request, &$id) {
                $sessionUser = $request->user();
                $query->where('sender_id', $id)
                    ->where('receiver_id', $sessionUser->id);
            })
            ->delete();
        if ($deleted) {
            return;
        }
        abort(400);
    }
    public function markAsRead(Request $request, $id) {
        $sessionUser = $request->user();
        $chatthreads = ChatThread::where('sender_id', $id)
            ->where('receiver_id', $sessionUser->id)
            ->get();
        $chatthreads->each(function($thread) {
            $thread->messages()
                ->update(['is_unread' => 0]);
        });
        return ['status' => 200];
    }
    public function markAllAsRead(Request $request) {
        $sessionUser = $request->user();

        $chatthreads = ChatThread::where('receiver_id', $sessionUser->id)->get();
        $chatthreads->each(function($thread) {
            $thread->messages()
                ->update(['is_unread' => 0]);
        });
        return ['status' => 200];
    }
    public function filterMessages(Request $request, $id) {
        $sessionUser = $request->user();

        $chatthreads = ChatThread::where(function($query) use(&$request, &$id) {
                $sessionUser = $request->user();
                $query->where('sender_id', $sessionUser->id)
                    ->where('receiver_id', $id);
            })
            ->orWhere(function($query) use(&$request, &$id) {
                $sessionUser = $request->user();
                $query->where('sender_id', $id)
                    ->where('receiver_id', $sessionUser->id);
            })
            ->get();
        $messages = [];
        $filter = $request->query('query');
        $chatthreads->each(function($thread) use(&$messages, &$filter) {
            $messages += $thread->messages()->where('mcontent', 'like', '%'.$filter.'%')->get()->toArray();
        });
        $messages = orderBy($messages, ['created_at'], ['desc']);
        $messages = array_map(function($message) {
            return $message['value']['id'];
        }, $messages);
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
    public function listMediafiles(Request $request, $receiver) {
        $chatthreads = ChatThread::where(function($query) use(&$request, &$receiver) {
                $sessionUser = $request->user();
                $query->where('sender_id', $sessionUser->id)
                    ->where('receiver_id', $receiver);
            })
            ->orWhere(function($query) use(&$request, &$receiver) {
                $sessionUser = $request->user();
                $query->where('sender_id', $receiver)
                    ->where('receiver_id', $sessionUser->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();
        $messages = [];
        $chatthreads->each(function($chatthread) use(&$messages) {
            $messages = array_merge($messages, $chatthread->messages()->with('mediafile')->orderBy('mcounter', 'asc')->get()->toArray());
        });
        $mediafiles = [];
        foreach ($messages as $message) {
            if ($message['mediafile']) {
                array_push($mediafiles, $message['mediafile']);
            }
        };
        return $mediafiles;
    }
}

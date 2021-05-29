<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\ChatThread;
use App\Models\Timeline;
use App\Models\User;
use App\Models\Mediafile;
use App\Models\Diskmediafile;
use App\Events\MessageSentEvent;
use App\Events\MessageRemoveEvent;
use App\Enums\MediafileTypeEnum;
use function _\sortBy;
use function _\orderBy;
use function _\filter;
use function _\uniq;
use function _\uniqBy;
use Carbon\Carbon;

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
            case 'online':
                $users = orderBy($users, ['is_online', 'created_at'], [$dir === 'asc' ? 'desc' : 'asc', $dir]);
                $users = array_map(function($user) {
                    $user = $user['value'];
                    return $user;
                }, $users);
                break;
            case 'offline':
                $users = orderBy($users, ['is_online', 'created_at'], [$dir, $dir]);
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

        $receivers = ChatThread::with(['receiver'])
            ->where('sender_id', $sessionUser->id)
            ->whereNotIn('receiver_id', $blockers)
            ->pluck('receiver_id')
            ->toArray();
        // Senders
        $senders = ChatThread::with(['sender'])
            ->where('receiver_id', $sessionUser->id)
            ->whereNotIn('sender_id', $blockers)
            ->pluck('sender_id')
            ->toArray();
        $receivers = uniq(array_merge($receivers, $senders));

        $userSettings = $sessionUser->settings;

        $searchText = $request->query('name');
        if (!isset($searchText)) {
            $searchText = '';
        }
        $contacts = array();
        foreach($receivers as $receiver) {
            $chatThreads = ChatThread::where(function($query) use(&$request, &$receiver) {
                    $sessionUser = $request->user();
                    $query->where('sender_id', $sessionUser->id)
                        ->where('receiver_id', $receiver)
                        ->where('schedule_datetime', null);
                })
                ->orWhere(function($query) use(&$request, &$receiver) {
                    $sessionUser = $request->user();
                    $query->where('sender_id', $receiver)
                        ->where('receiver_id', $sessionUser->id)
                        ->where('schedule_datetime', null);
                })
                ->orderBy('created_at', 'desc')
                ->get();
            $lastChatThread = $chatThreads->first();
            if ($lastChatThread) {
                $messages = $lastChatThread->messages()->with('mediafile')->orderBy('mcounter', 'desc')->get();
                $hasMediafile = false;
                $messages->each(function($msg) use(&$hasMediafile) {
                    if (isset($msg->mediafile)) {
                        $hasMediafile = true;
                    }
                });
                $lastMessage = $messages->first();
                if (!isset($lastMessage)) {
                    $lastMessage = (object)[];
                }
                $lastMessage->sender_id = $lastChatThread->sender_id;
                $lastMessage->unread_messages_count = ChatThread::where('is_unread', 1)
                    ->where('sender_id', $receiver)
                    ->where('receiver_id', $sessionUser->id)
                    ->where('schedule_datetime', null)
                    ->get()->count();
                $lastMessage->receiver_id = $lastChatThread->receiver_id;
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
                if ((isset($user->display_name) && preg_match("/{$searchText}/i", $user->display_name)) ||
                    (isset($user->name) && preg_match("/{$searchText}/i", $user->name))) {
                    array_push($contacts, [
                        'last_message' => $lastMessage,
                        'profile' => $user
                    ]);
                }
            }
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

    // fetches a specific convo between session user and user specified by $id
    public function fetchcontact(Request $request, $contactPKID) {
        $sessionUser = $request->user();

        $offset = $request->query('offset');
        $limit = $request->query('limit');
        $chatthreads = ChatThread::with(['receiver'])
            ->where(function($query) use(&$request, $contactPKID) {
                $sessionUser = $request->user();
                $query->where('sender_id', $sessionUser->id)
                    ->where('receiver_id',  $contactPKID)
                    ->where('schedule_datetime', null);
            })
            ->orWhere(function($query) use(&$request, $contactPKID) {
                $sessionUser = $request->user();
                $query->where('receiver_id', $sessionUser->id)
                    ->where('sender_id',  $contactPKID)
                    ->where('schedule_datetime', null);
            })
            ->orderBy('created_at', 'DESC')
            ->skip($offset)
            ->take($limit)
            ->get();

        $chatthreads->each(function($chatthread) {
            $chatthread->messages = $chatthread->messages()->with('mediafile')->orderBy('mcounter', 'asc')->get();
        });
        $timeline = Timeline::with(['user', 'avatar'])->where('user_id', $contactPKID)->first()->makeVisible(['user']);
        $timeline->username = $timeline->user->username;
        $userSetting = $sessionUser->settings;
        $cattrs = $userSetting->cattrs;
        if ( array_key_exists('display_name', $cattrs) ) {
            if ( array_key_exists($contactPKID, $cattrs['display_name']) ) {
                $timeline->display_name = $cattrs['display_name'][$contactPKID];
            }
        }
        if ( array_key_exists('muted', $cattrs) ) {
            $index = array_search($contactPKID, $cattrs['muted']);
            if ( $index !== false ) {
                $timeline->muted = true;
            }
        }
        $lists = DB::table('list_user')->where('user_id', $contactPKID)->get();
        $timeline->hasLists = sizeof($lists) > 0;
        $timeline->id = $timeline->user->id; // %NOTE: we are actually passing the user pkid as the timeline pkid
        return [
            'messages' => $chatthreads,
            'profile' => $timeline,
            'currentUser' => $request->user()
        ];
    }

    public function store(Request $request)
    {
        //dd($request->all());
        $vrules = [
            'user_id' => 'required|uuid|exists:users,id', // reciever of message
            'tip_price' => 'numeric',
            'schedule_datetime' => 'nullable|numeric', // sent as epoch time
        ];
        if ( $request->has('vaultfiles') ) {
            $vrules['vaultfiles'] = 'array';
            $vrules['vaultfiles.*'] = 'uuid|exists:mediafiles,id'; // fk id array to mediafiles (in vault)
        }
        $request->validate($vrules);

        $sessionUser = $request->user();
        $receiver = User::where('id', $request->input('user_id'))->first();

        $schedule_datetime = $request->input('schedule_datetime');

        $chatthread = $sessionUser->chatthreads()->create([
            'receiver_id' => $request->input('user_id'),
            'tip_price' => $request->input('tip_price'),
            'schedule_datetime' => $schedule_datetime,
            'is_unread' => 1,
            'is_like' => 0,
        ]);

        $mediafiles = $request->file('mediafile');
        $vaultfiles = $request->input('vaultfiles');
        if ($mediafiles) {
            // Media / File via upload form
            $index = 1;
            foreach ($mediafiles as $mf) {
                $message = $chatthread->messages()->create([
                    'mcontent' => '',
                    'mcounter' => $index,
                ]);
                if ($mf) {
                    $subPath = './'.$sessionUser->id;
                    $s3Path = $mf->store($subPath, 's3');
                    $mediafile = Diskmediafile::doCreate([
                        'owner_id'        => $sessionUser->id,
                        'filepath'        => $s3Path,
                        'mimetype'        => $mf->getMimeType(),
                        'orig_filename'   => $mf->getClientOriginalName(),
                        'orig_ext'        => $mf->getClientOriginalExtension(),
                        'mfname'          => $mf->getClientOriginalName(),
                        'mftype'          => MediafileTypeEnum::GALLERY,
                        'resource_id'     => $message->id,
                        'resource_type'   => 'messages',
                    ]);
                }
                $index++;
            }
            $mcontent = $request->input('message');
            if (isset($mcontent)) {
                $chatthread->messages()->create([
                    'mcontent' => $mcontent,
                    'mcounter' => $index,
                ]);
            }
        } else if ($vaultfiles) {
            // Media / File pulled from vault
            $index = 1;
            foreach ($vaultfiles as $mfPKID) {
                $message = $chatthread->messages()->create([
                    'mcontent' => '',
                    'mcounter' => $index,
                ]);
                if ($mfPKID) {
                    $srcMediafile = Mediafile::where('resource_type', 'vaultfolders')
                                    ->where('is_primary', true)
                                    ->findOrFail($mfPKID);
                    $refMediafile = $srcMediafile->diskmediafile->createReference(
                        'messages',    // $resourceType
                        $message->id,  // $resourceID
                        'New Message', // $mfname - could be optionally passed as a query param %TODO
                        MediafileTypeEnum::GALLERY // $mftype
                    );
                }
                $index++;
            }
            $mcontent = $request->input('message');
            if (isset($mcontent)) {
                $chatthread->messages()->create([
                    'mcontent' => $mcontent,
                    'mcounter' => $index,
                ]);
            }
        } else {
            // Simple text message with no media
            $chatthread->messages()->create([
                'mcontent' => $request->input('message'),
            ]);
        }

        if (!$schedule_datetime) {
            $chatthread->messages = $chatthread->messages()->with('mediafile')->orderBy('mcounter', 'asc')->get();

            broadcast(new MessageSentEvent($chatthread, $sessionUser, $receiver))->toOthers();

            return [
                'message' => $chatthread,
            ];
        }
        return [];

    } // store()

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
            ->where('schedule_datetime', null)
            ->get();
        $chatthreads->each(function($thread) {
            $thread->update(['is_unread' => 0]);
        });
        return ['status' => 200];
    }

    public function markAllAsRead(Request $request) {
        $sessionUser = $request->user();

        $chatthreads = ChatThread::where('receiver_id', $sessionUser->id)
            ->where('schedule_datetime', null)
            ->get();
        $chatthreads->each(function($thread) {
            $thread->update(['is_unread' => 0]);
        });
        return ['status' => 200];
    }

    public function filterMessages(Request $request, $id) {
        $chatthreads = ChatThread::where(function($query) use(&$request, &$id) {
                $sessionUser = $request->user();
                $query->where('sender_id', $sessionUser->id)
                    ->where('receiver_id', $id)
                    ->where('schedule_datetime', null);
            })
            ->orWhere(function($query) use(&$request, &$id) {
                $sessionUser = $request->user();
                $query->where('sender_id', $id)
                    ->where('receiver_id', $sessionUser->id)
                    ->where('schedule_datetime', null);
            })
            ->get();
        $messages = [];
        $filter = $request->query('query');
        $chatthreads->each(function($thread) use(&$messages, &$filter) {
            $messages = array_merge($messages, $thread->messages()->where('mcontent', 'like', '%'.$filter.'%')->get()->toArray());
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
                    ->where('receiver_id', $receiver)
                    ->where('schedule_datetime', null);
            })
            ->orWhere(function($query) use(&$request, &$receiver) {
                $sessionUser = $request->user();
                $query->where('sender_id', $receiver)
                    ->where('receiver_id', $sessionUser->id)
                    ->where('schedule_datetime', null);
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

    public function getUnreadMessagesCount(Request $request) {
        $sessionUser = $request->user();
        //$unread_threads = ChatThread::where('receiver_id', $sessionUser->id)
        //    ->where('is_unread', 1)
        //    ->where('schedule_datetime', null)
        //    ->pluck('sender_id')->toArray();
        //$unread_threads = uniq($unread_threads);
        //return ["unread_messages_count" => count($unread_threads)];
        return ["unread_messages_count" => 1]; // %PSG temp workaround while this controller is deprecated
    }

    public function removeThread(Request $request, $id, $threadId) {
        $sessionUser = $request->user();
        $deleted = ChatThread::where('id', $threadId)->delete();
        $receiver = User::where('id', $id)->get()->first();
        if ($deleted) {
            broadcast(new MessageRemoveEvent($threadId, $sessionUser, $receiver))->toOthers();
            return;
        }
        abort(400);
    }

    public function setLike(Request $request, $id, $threadId) {
        $updated = ChatThread::where('id', $threadId)->update(['is_like' => true]);
        if ($updated) {
            return;
        }
        abort(400);
    }

    public function setUnlike(Request $request, $id, $threadId) {
        $updated = ChatThread::where('id', $threadId)->update(['is_like' => false]);
        if ($updated) {
            return;
        }
        abort(400);
    }

    public function fetchScheduled(Request $request)
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

        $chatThreads = ChatThread::with(['receiver'])
            ->where('sender_id', $sessionUser->id)
            ->whereNotIn('receiver_id', $blockers)
            ->where('schedule_datetime', '>', Carbon::now('UTC')->timestamp)
            ->get();

        $chatThreads->each(function ($chatthread) {
            $chatthread->messages = $chatthread->messages()->with('mediafile')->orderBy('mcounter', 'desc')->get();
            $user = Timeline::with(['user', 'avatar'])->where('user_id', $chatthread->receiver_id)->first()->makeVisible(['user']);
            $user->username = $user->user->username;
            $user->id = $user->user->id;
            $chatthread->profile = $user;
        });

        return $chatThreads->toArray(); 
    }

    public function removeScheduleThread(Request $request, $threadId) {
        $deleted = ChatThread::where('id', $threadId)->delete();
        if ($deleted) {
            return ['status' => 200];
        }
        abort(400);
    }

    public function editScheduleThread(Request $request, $threadId) {
        $chatThread = ChatThread::where('id', $threadId)->first();
        $chatThread->schedule_datetime = $request->input('schedule_datetime');
        $chatThread->save();
        return ['status' => 200];
    }

}

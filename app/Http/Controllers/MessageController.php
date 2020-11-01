<?php

namespace App\Http\Controllers;

use App\Events\MessagePublished;
use App\Hashtag;
use App\Setting;
use App\User;
use Carbon\Carbon;
use Cmgmyr\Messenger\Models\Message;
use Cmgmyr\Messenger\Models\Participant;
use Cmgmyr\Messenger\Models\Thread;
use Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Teepluss\Theme\Facades\Theme;
use Validator;

class MessageController extends Controller
{
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->checkCensored();
        
        $this->middleware('auth');
    }

    protected function checkCensored()
    {
        $messages['not_contains'] = 'The :attribute must not contain banned words';
        if($this->request->method() == 'POST') {
            // Adjust the rules as needed
            $this->validate($this->request, 
                [
                  'name'          => 'not_contains',
                  'about'         => 'not_contains',
                  'title'         => 'not_contains',
                  'description'   => 'not_contains',
                  'tag'           => 'not_contains',
                  'email'         => 'not_contains',
                  'body'          => 'not_contains',
                  'link'          => 'not_contains',
                  'address'       => 'not_contains',
                  'website'       => 'not_contains',
                  'display_name'  => 'not_contains',
                  'key'           => 'not_contains',
                  'value'         => 'not_contains',
                  'subject'       => 'not_contains',
                  'username'      => 'not_contains',
                  'email'         => 'email',
                ],$messages);
        }
    }

    /**
     * Show all of the message threads to the user.
     *
     * @return mixed
     */
    public function index()
    {
        $trending_tags = Hashtag::orderBy('count', 'desc')->get();
        if (count($trending_tags) > 0) {
            if (count($trending_tags) > (int) Setting::get('min_items_page')) {
                $trending_tags = $trending_tags->random((int) Setting::get('min_items_page'));
            }
        } else {
            $trending_tags = '';
        }

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
        $theme->setTitle(trans('common.messages').' '.Setting::get('title_seperator').' '.Setting::get('site_title').' '.Setting::get('title_seperator').' '.Setting::get('site_tagline'));

        return $theme->scope('messenger.index', compact('trending_tags'))->render();
    }

    /**
     * Shows a message thread.
     *
     * @param $id
     *
     * @return mixed
     */
    public function show($id)
    {
        try {
            $thread = Thread::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            Session::flash('error_message', 'The thread with ID: '.$id.' was not found.');

            return redirect('messages');
        }

        // don't show the current user in list
        $userId = Auth::user()->id;
        $users = User::whereNotIn('id', $thread->participantsUserIds($userId))->get();

        $thread->markAsRead($userId);

        $messages = [];
        $thread->conversationMessages = $thread->messages()->orderBy('created_at', 'ASC')->latest()->with('user')->paginate(10);

        // $thread->conversationMessages->sortBy('created_at', 'desc');

        // dd($thread->conversationMessages->toArray());


//         SELECT * FROM (
//     SELECT * FROM messages WHERE thread_id=1 LIMIT 5
// ) sub
// ORDER BY created_at ASC


        return response()->json(['status' => '200', 'data' => $thread]);

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');

        return $theme->scope('messenger.show', compact('thread', 'users'))->render();
    }

    /**
     * Creates a new message thread.
     *
     * @return mixed
     */
    public function create()
    {
        $users = User::where('id', '!=', Auth::id())->get();

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
        $theme->setTitle(trans('common.create_message').' '.Setting::get('title_seperator').' '.Setting::get('site_title').' '.Setting::get('title_seperator').' '.Setting::get('site_tagline'));

        return $theme->scope('messenger.create', compact('users'))->render();
    }

    /**
     * Stores a new message thread.
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'recipients' => 'required',
            'message'    => 'required',
        ]);


        if ($validator->fails()) {
            return response()->json(['status' => '200', 'data' => $validator->errors()]);
        }

        $recipients = explode(',', $input['recipients']);
        $recipients[1] = (string) Auth::id();
        $thread = Thread::whereHas('participants', function ($query) use ($recipients) {
            $query->whereIn('user_id', $recipients)
                ->groupBy('thread_id')
                ->havingRaw('COUNT(thread_id)='.count($recipients));
        })->first();


        if (!$thread) {
            $thread = Thread::create(
                [
                    'subject' => isset($input['subject']) ? $input['subject'] : '',
                ]
            );

              // Sender
            Participant::create(
                [
                    'thread_id' => $thread->id,
                    'user_id'   => Auth::user()->id,
                    'last_read' => new Carbon(),
                ]
            );

            // Recipients
            if (Input::has('recipients')) {
                $recipients = explode(',', $input['recipients']);
                $thread->addParticipant($recipients);
            }
        }

        $message = new Message([
                'user_id'   => Auth::user()->id,
                'body'      => $input['message'],
            ]);

        $thread->messages()->save($message);



        $thread = Thread::findOrFail($thread->id);


        $thread->lastMessage = $thread->latestMessage;

        $participants = $thread->participants()->get();

        foreach ($participants as $key => $participant) {
            if (Auth::id() != $participant->user->id) {
                // echo $participant->user->id;
                Event::fire(new MessagePublished($message, $participant->user));
            }
            if ($participant->user->id != Auth::user()->id) {
                $thread->user = $participant->user;
            }
        }

        return response()->json(['status' => '200', 'data' => $thread]);
    }

    /**
     * Adds a new message to a current thread.
     *
     * @param $id
     *
     * @return mixed
     */
    public function update($id)
    {
        $thread = Thread::findOrFail($id);

        // Message
        $message = Message::create(
            [
                'thread_id' => $thread->id,
                'user_id'   => Auth::id(),
                'body'      => Input::get('message'),
            ]
        );

        $message->user = $message->user;

        $thread->activateAllParticipants();
        // activate all participants
        $participants = $thread->participants()->withTrashed()->get();
        foreach ($participants as $participant) {
            $participant->restore();
            if (Auth::id() != $participant->user->id) {
                // echo $participant->user->id;
                Event::fire(new MessagePublished($message, $participant->user));
            }
        }


        // Add replier as a participant
        $participant = Participant::firstOrCreate(
            [
                'thread_id' => $thread->id,
                'user_id'   => Auth::user()->id,
            ]
        );
        $participant->last_read = new Carbon();
        $participant->save();

        // Recipients
        if (Input::has('recipients')) {
            $thread->addParticipant(Input::get('recipients'));
        }



        return response()->json(['status' => '200', 'data' => $message]);
    }

    // Custom classes for Vuejs

    public function getMessages(Request $request)
    {
        $input = $request->all();
        $currentUserId = Auth::user()->id;

            // All threads that user is participating in
        $threads = Thread::with('users.timeline')->forUser($currentUserId)->latest('updated_at');

        $threads->when(isset($input['date_joined']) && $input['date_joined'] == 'true' && $input['search'] != '', function (Builder $q) use ($input) {

            $validator = Validator::make($input, [
                'search' => 'date_format:Y-m-d',
            ]);
            
            if ($validator->fails()) {
                return;
            }
            
            $date = Carbon::createFromFormat('Y-m-d', $input['search'])->toDateString();
            $q->whereHas('users', function (Builder $q) use ($date){
                $q->whereRaw('date(users.created_at) = ?', $date);
            });
        });
        
        $threads->when(isset($input['location']) && $input['location'] == 'true' && $input['search'] != '', function (Builder $q) use ($input) {
            $q->whereHas('participants.user', function (Builder $q) use ($input){
                $q->where('users.city', 'like', "%{$input['search']}%");
                $q->orWhere('users.country', 'like', "%{$input['search']}%");
            });
        });
        
        $threads->when(isset($input['favourite_users']) && $input['favourite_users'], function (Builder $q) use ($input) {
            $favouriteUsers = Auth::user()->favouriteUsers()->pluck('favourite_user_id')->toArray();
            $q->whereHas('participants', function (Builder $q) use ($input, $favouriteUsers){
                $q->whereIn('user_id', $favouriteUsers);
                $q->where('user_id', '!=', Auth::id());
            });
            
            $q->whereHas('participants.user.timeline', function (Builder $q) use ($input){
                $search = strtolower($input['search']);
                $q->whereRaw('lower(name) like ? ', "%{$search}%");
                $q->orWhereRaw('lower(username) like  ?', "%{$search}%");
            });
        });
        
        $threads->when(isset($input['name_username']) && $input['name_username'] == 'true' && $input['search'] != '', function (Builder $q) use ($input) {
            $q->whereHas('participants', function (Builder $q) use ($input){
                $q->where('user_id', '!=', Auth::id());
            });
            $q->whereHas('participants.user.timeline', function (Builder $q) use ($input){
                $search = strtolower($input['search']);
                $q->whereRaw('lower(name) like ? ', "%{$search}%");
                $q->orWhereRaw('lower(username) like  ?', "%{$search}%");
            });
        });

        $threads = $threads->paginate(30);
        
        foreach ($threads as $index => $thread) {
            $thread->unread = $thread->isUnread($currentUserId);

            $thread->lastMessage = $thread->latestMessage;

            $participants = $thread->participants()->get();

            if (isset($input['location']) && $input['location'] && !empty($input['search'])) {
                $participants = $thread->participants()->whereHas('user', function (Builder $q) use($input) {
                    $q->where('users.city', 'like', "%{$input['search']}%");
                    $q->orWhere('users.country', 'like', "%{$input['search']}%");
                })->get();
            }
            if (isset($input['name_username']) && $input['name_username'] == 'true' && $input['search'] != '') {
                $participants = $thread->participants()
                    ->whereHas('user.timeline', function (Builder $q) use ($input){
                    $search = strtolower($input['search']);
                    $q->whereRaw('lower(name) like ? ', "%{$search}%");
                    $q->orWhereRaw('lower(username) like  ?', "%{$search}%");
                })->get();
            }

            if (isset($input['favourite_users']) && $input['favourite_users'] && $input['search'] != '') {
                $participants = $thread->participants()
                    ->whereHas('user.timeline', function (Builder $q) use ($input){
                        $search = strtolower($input['search']);
                        $q->whereRaw('lower(name) like ? ', "%{$search}%");
                        $q->orWhereRaw('lower(username) like  ?', "%{$search}%");
                    })->get();
            }
            
            if (empty($participants)) {
                unset($threads[$index]);
            }

            $matched = false;
            if (count($participants) > 0) {
                foreach ($participants as $key => $participant) {
                    if ($participant && $participant->user->id != Auth::user()->id && $participant->user) {
                        $thread->user = $participant->user;
                        $thread->user->is_favourite = Auth::user()->favouriteUsers->contains($participant->user->id) ? true : false;
                        $matched = true;
                        break;
                    }
                }
            }
            
            if (!$matched) {
                unset($threads[$index]);
            }
        }
            // dd($threads);
            return response()->json(['status' => '200', 'data' => $threads]);
    }

    public function getMessage($id)
    {
        $thread = Thread::findOrFail($id);

        $thread->lastMessage = $thread->latestMessage;
        $thread->unread = true;

        $participants = $thread->participants()->get();

        foreach ($participants as $key => $participant) {
            if ($participant->user->id != Auth::user()->id) {
                $thread->user = $participant->user;
                break;
            }
        }

        return response()->json(['status' => '200', 'data' => $thread]);
    }

    public function getUnreadMessages()
    {
        return response()->json(['status' => '200', 'unread_conversations' => Auth::user()->newThreadsCount()]);
    }

    public function getPrivateConversation($userId)
    {
        //echo "Message route received";
        $recipients = [$userId, Auth::id()];
        $recipients[1] = (string) Auth::id();

        $thread = Thread::whereHas('participants', function ($query) use ($recipients) {
            $query->whereIn('user_id', $recipients)
                ->groupBy('thread_id')
                ->havingRaw('COUNT(thread_id)='.count($recipients));
        })->first();

        $messages = [];

        if (!$thread) {
            $thread = Thread::create(
                [
                    'subject' => isset($input['subject']) ? $input['subject'] : '',
                ]
            );

              // Sender
            Participant::create(
                [
                    'thread_id' => $thread->id,
                    'user_id'   => Auth::user()->id,
                    'last_read' => new Carbon(),
                ]
            );

            // Recipients
            $thread->addParticipant($recipients);
        }

        // $message = new Message([
        //         'user_id'   => Auth::user()->id,
        //         'body'      => "you are now chatting with ",
        // ]);

        // $thread->messages()->save($message);

        $thread = Thread::findOrFail($thread->id);

        foreach ($thread->messages as $key => $value) {
            $messages[$key] = $value;
            $messages[$key]['user'] = $value->user;
        }

        $thread->conversationMessages = collect($messages);


        $participants = $thread->participants()->get();

        foreach ($participants as $key => $participant) {
            if ($participant->user->id != Auth::user()->id) {
                $thread->user = $participant->user;
            }
        }

        return response()->json(['status' => '200', 'data' => $thread]);
    }
    
    public function favouriteUser(Request $request)
    {
        $favUserId = $request->get('favourite_user_id');
        $currentUser = Auth::user();
        
        if ($currentUser->favouriteUsers->contains($favUserId)) {
            $currentUser->favouriteUsers()->detach($favUserId);

            return response()->json(['status' => '200', 'Add to favourites' => true, 'message' => 'successfully added to favourite list']);
        } else {
            $currentUser->favouriteUsers()->attach($favUserId);

            return response()->json(['status' => '200', 'Remove from favourites' => true, 'message' => 'successfully removed from favourite list']);
        }
    }
}

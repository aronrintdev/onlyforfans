<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Throwable;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\UnauthorizedException;

use Carbon\Carbon;

use App\Payments\PaymentGateway;

use App\Notifications\TimelineFollowed;
use App\Notifications\TipReceived;
//use App\Notifications\TimelineSubscribed;

use App\Http\Resources\PostCollection;
use App\Http\Resources\TimelineCollection;
use App\Http\Resources\MediafileCollection;
use App\Http\Resources\Timeline as TimelineResource;

use App\Models\Financial\Account;
use App\Models\Casts\Money as CastsMoney;
use App\Models\Financial\SegpayCall;
use App\Models\Mediafile;
use App\Models\Mycontact;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Storyqueue;
use App\Models\Subscription;
use App\Models\Timeline;
use App\Models\Tip;
use App\Models\User;

use App\Enums\PostTypeEnum;
use App\Enums\PaymentTypeEnum;
use App\Enums\MediafileTypeEnum;

class TimelinesController extends AppBaseController
{
    public function index(Request $request)
    {
        if ( !$request->user()->isAdmin() ) {
            abort(403); // only admin access for now
        }

        $request->validate([
            // filters
            'user_id' => 'uuid|exists:users,id',
            'verified' => 'boolean',
            'is_follow_for_free' => 'boolean',
        ]);

        $filters = $request->only([ 'user_id', 'verified', 'is_follow_for_free' ]) ?? [];

        // Init query
        $query = User::query();

        // Apply filters
        foreach ($filters as $key => $f) {
            switch ($key) {
            case 'verified':
            case 'is_follow_for_free':
                $query->where($key, true);
                break;
            default:
                $query->where($key, $f);
            }
        }

        $data = $query->paginate( $request->input('take', Config::get('collections.defaultMax', 10)) );
        return new TimelineCollection($data);
    }

    public function show(Request $request, Timeline $timeline)
    {
        $this->authorize('view', $timeline);
        //$timeline->load(['avatar', 'cover']);
        //$timeline->userstats = $request->user()->getStats();
        //return [ 'timeline' => $timeline, ];
        return new TimelineResource($timeline);
    }

    // Display my home timeline
    public function homefeed(Request $request)
    {
        $sessionUser = request()->user();
        $query = Post::with('mediafiles', 'user')
                    ->withCount(['comments', 'likes'])
                    ->where('active', 1)
                    ->where('schedule_datetime', null)
                    ->where(function ($query) {
                        $query->where('expire_at', '>', Carbon::now('UTC'))
                              ->orWhere('expire_at', null);
                    });
        $query->homeTimeline()->sort( $request->input('sortBy', 'default') );
        // %NOTE: we could also just remove post-query, as the feed will auto-update to fill length of page (?)
        if ( $request->boolean('hideLocked') ) {
            $query->where( function($q1) use(&$sessionUser) {
                $q1->where('type', PostTypeEnum::FREE)
                   ->orWhere( function($q2) use(&$sessionUser) {
                       $q2->where('type', PostTypeEnum::PRICED)
                          ->whereDoesntHave('sharees', function($q3) use(&$sessionUser) {
                              $q3->where('users.id', $sessionUser->id);
                          });
                       $q2->where('type', PostTypeEnum::SUBSCRIBER)
                          ->whereDoesntHave('timeline.subscribers', function($q3) use(&$sessionUser) {
                              $q3->where('users.id', $sessionUser->id);
                          });
                   });
            });
        }
        if ( $request->boolean('hidePromotions') ) {
        }
        $data = $query->paginate( $request->input('take', Config::get('collections.defaultMax', 10)) );
        return new PostCollection($data);
    }

    // Get a list of items that make up a timeline feed, typically posts but
    //  keep generic as we may want to throw in other things
    //  %TODO: 
    //  ~ [ ] trending tags
    //  ~ [ ] announcements
    //  ~ [ ] hashtag search
    public function feed(Request $request, Timeline $timeline)
    {
        //$this->authorize('view', $timeline); // must be follower or subscriber
        //$filters = [];
        $query = Post::with('mediafiles', 'user')
                    ->withCount(['comments', 'likes'])
                    ->where('active', 1)
                    ->where('schedule_datetime', null)
                    ->where(function ($query) {
                        $query->where('expire_at', '>', Carbon::now('UTC'))
                              ->orWhere('expire_at', null);
                    });
        $query->byTimeline($timeline->id)->sort( $request->input('sortBy', 'default') );
        $data = $query->paginate( $request->input('take', Config::get('collections.max.posts', 10)) );
        return new PostCollection($data);
    }

    // 'Photos' Feed
    // %TODO: move to mediafiles controller (?)
    public function photos(Request $request, Timeline $timeline)
    {
        $query = Mediafile::with('resource')
            ->isImage()
            //->whereIn('mimetype', ['image/jpeg', 'image/jpg', 'image/png'])
            ->where('mftype', MediafileTypeEnum::POST);
        $query->whereHasMorph( 'resource', [Post::class], function($q1) use(&$timeline) {
            $q1->where('postable_type', 'timelines')->where('postable_id', $timeline->id);
        });
        $query->orderByDesc(Post::select('created_at')
            ->whereColumn('posts.id', 'resource_id'));
        $data = $query->paginate( $request->input('take', Config::get('collections.max.posts', 10)) );
        return new MediafileCollection($data);
    }

    // 'Photos' Feed
    // %TODO: move to mediafiles controller (?)
    public function videos(Request $request, Timeline $timeline)
    {
        $query = Mediafile::with('resource')
            ->isVideo()
            ->where('mftype', MediafileTypeEnum::POST);
        $query->whereHasMorph( 'resource', [Post::class], function($q1) use(&$timeline) {
            $q1->where('postable_type', 'timelines')->where('postable_id', $timeline->id);
        });
        $data = $query->paginate( $request->input('take', Config::get('collections.max.posts', 10)) );
        return new MediafileCollection($data);
    }

    // 'Photos & Videos' Feed Count
    // %TODO: move to mediafiles controller (?)
    public function getPhotosVideosCount(Request $request, Timeline $timeline)
    {
        // Get Videos
        $query1 = Mediafile::with('resource')
            ->isVideo()
            ->where('mftype', MediafileTypeEnum::POST);
        $query1->whereHasMorph( 'resource', [Post::class], function($q1) use(&$timeline) {
            $q1->where('postable_type', 'timelines')->where('postable_id', $timeline->id);
        });
        $videos = $query1->count();

        // Get Photos
        $query2 = Mediafile::with('resource')
            ->isImage()
            //->whereIn('mimetype', ['image/jpeg', 'image/jpg', 'image/png'])
            ->where('mftype', MediafileTypeEnum::POST);
        $query2->whereHasMorph( 'resource', [Post::class], function($q1) use(&$timeline) {
            $q1->where('postable_type', 'timelines')->where('postable_id', $timeline->id);
        });
        $query2->orderByDesc(Post::select('created_at')
            ->whereColumn('posts.id', 'resource_id'));
        $photos = $query2->count();
        return [
            'photos' => $photos,
            'videos' => $videos,
        ];
    }

    // Get suggested users (list/index)
    public function suggested(Request $request)
    {
        $MAX = 6*5; // $request->input('take', 5);
        $followedIDs = $request->user()->followedtimelines->pluck('id');
        $query = Timeline::with(['user', 'avatar', 'cover'])->take($MAX)->inRandomOrder();
        $query->whereHas('user', function($q1) use(&$request, &$followedIDs) {
            $q1->where('id', '<>', $request->user()->id); // skip myself
            // skip timelines I'm already following
            $q1->whereDoesntHave('followedtimelines', function($q2) use(&$followedIDs) {
                $q2->whereIn('shareable_id', $followedIDs);
            });
            // no-include admin users
            $q1->isAdmin(true);
        });

        // Apply filters
        if ( $request->boolean('paid_only') ) {
            $query->where('is_follow_for_free', false);
        }

        $data = $query->get();
        return new TimelineCollection($data);
    }

    // %FIXME: better way to do this is to pull down a set of mediafiles associated with posts on this timeline (?)
    public function previewPosts(Request $request, Timeline $timeline)
    {
        $TAKE = $request->input('take', $request->limit);
        $query = Post::with('mediafiles', 'user')
            ->whereHas('mediafiles', function($q1) {
                $q1->isImage();
            })
            //->withCount('likes')->orderBy('likes_count', 'desc')
            ->where('active', 1)
            ->where(function ($query) {
                $query->where('expire_at', '>', Carbon::now('UTC'))
                      ->orWhere('expire_at', null);
            });
        $query->where('postable_type', 'timelines')->where('postable_id', $timeline->id);
        $data = $query->take($TAKE)->latest()->get();
        return new PostCollection($data);
    }


    // toggles, returns set state
    public function follow(Request $request, Timeline $timeline)
    {
        $this->authorize('follow', $timeline);

        $request->validate([
            'sharee_id' => 'required|uuid|exists:users,id',
        ]);

        $sessionUser = $request->user();
        $follower = $sessionUser;
        if ( $request->sharee_id != $follower->id ) {
            abort(403);
        }

        $cattrs = [];
        if ( $request->has('notes') ) {
            $cattrs['notes'] = $request->notes;
        }

        $existing = $timeline->followers->contains($follower->id); // currently following?

        if ($existing) {
            $timeline->followers()->detach($follower->id);
            $isFollowing = false;
        } else {
            $timeline->followers()->attach($follower->id, [ // will sync work here?
                'shareable_type' => 'timelines',
                'shareable_id' => $timeline->id,
                'is_approved' => 1, // %FIXME
                'access_level' => 'default',
                'cattrs' => json_encode($cattrs),
            ]); //
            $isFollowing = true;

            // Add message contacts if they don't already exist
            Mycontact::addContacts(new Collection([
                $follower,
                $timeline->getOwner()->first(),
            ]));
        }

        $timeline->user->notify(new TimelineFollowed($timeline, $follower));

        $timeline->refresh();
        return response()->json([
            'is_following' => $isFollowing,
            'timeline' => $timeline,
            'follower_count' => $timeline->followers->count(),
        ]);
    }

    public function subscribe(Request $request, Timeline $timeline, PaymentGateway $paymentGateway)
    {
        $this->authorize('follow', $timeline);

        $request->validate([
            'account_id' => 'required|uuid',
            'amount' => 'required|numeric',
            'currency' => 'required',
        ]);

        $price = CastsMoney::toMoney($request->amount, $request->currency);

        $account = Account::with('resource')->find($request->account_id);
        $this->authorize('subscribe', $account);

        // Verify subscription has not already been created
        if (Subscription::isSubscribed($request->user(), $timeline)) {
            abort(400, 'Already have subscription');
        }

        // Verify not resubscribing within waiting period
        if (Subscription::canResubscribe($request->user(), $timeline)) {
            abort(400, 'Too soon to resubscribe');
        }

        return $paymentGateway->subscribe($account, $timeline, $price);
    }

    /**
     * Unsubscribes user from a timeline.
     * @param Request $request
     * @param Timeline $timeline
     * @return Response
     */
    public function unsubscribe(Request $request, Timeline $timeline)
    {
        $this->authorize('follow', $timeline);

        $user = Auth::user();

        if ($request->has('subscription_id')) {
            $subscription = Subscription::find($request->subscription_id);
            if ($subscription->user_id !== $user->getKey()) {
                throw new UnauthorizedException('User is not authorized to cancel subscription');
            }

        } else {
            $subscription = Subscription::where('user_id', $user->getKey())
                ->where('subscribable_id', $timeline->getKey())
                ->active()->first();
        }

        if (!isset($subscription)) {
            return [
                'message' => 'No subscriptions to cancel',
            ];
        }

        if (isset($subscription->canceled_at)) {
            return [
                'message' => 'Subscription has already been canceled',
                'endsAt' => $subscription->next_payment_at,
                'daysRemaining' => $subscription->next_payment_at->diffInDays(Carbon::now()),
                'timeline' => new TimelineResource($timeline),
            ];
        }

        $subscription->cancel();

        return [
            'message' => 'Unsubscribed',
            'endsAt' => $subscription->next_payment_at,
            'daysRemaining' => $subscription->next_payment_at->diffInDays(Carbon::now()),
            'timeline' => new TimelineResource($timeline),
        ];
    }

    public function tip(Request $request, Timeline $timeline, PaymentGateway $paymentGateway)
    {
        $this->authorize('tip', $timeline);

        $request->validate([
            'account_id' => 'required|uuid',
            'amount' => 'required|numeric',
            'currency' => 'required',
        ]);

        $price = CastsMoney::toMoney($request->amount, $request->currency);

        $account = Account::with('resource')->find($request->account_id);
        $this->authorize('purchase', $account);

        $tip = Tip::create([
            'sender_id'       => $request->user()->getKey(),
            'receiver_id'     => $timeline->getOwner()->first()->getKey(),
            'tippable_type'   => $timeline->getMorphString(),
            'tippable_id'     => $timeline->getKey(),
            'account_id'      => $account->getKey(),
            'currency'        => $request->currency,
            'amount'          => $request->amount,
            'period'          => $request->period ?? 'single',
            'period_interval' => $request->period_interval ?? 1,
            'message'         => $request->message ?? null,

        ]);

        return $paymentGateway->tip($account, $tip, $price);
    }

    // Display my home scheduled timeline
    public function homeScheduledfeed(Request $request)
    {
        $query = Post::with('mediafiles', 'user')
            ->withCount(['comments', 'likes'])
            ->where('active', 1)
            ->where('schedule_datetime', '>', Carbon::now('UTC'))
            ->where(function ($query) {
                $query->where('expire_at', '>', Carbon::now('UTC'))
                      ->orWhere('expire_at', null);
            })
            ->orderBy('created_at', 'desc');

        // Admin override to view all scheduled posts
        if ($request->user()->isAdmin()) {
            // Filter by user_id
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }
        } else {
            // Limiting Query to only posts that the user owns
            $query->where('user_id', $request->user()->id);
        }

        // %NOTE: we could also just remove post-query, as the feed will auto-update to fill length of page (?)
        $data = $query->paginate( $request->input('take', Config::get('collections.max.posts', 10)) );
        return new PostCollection($data);
    }

    // returns a list of followed timelines with associated story groupings (ignores timeline if it has no active stories)
    // %TODO DEPRECATE (?)
    public function myFollowedStories(Request $request)
    {
        $data = Storyqueue::viewableTimelines($request->user());
        return response()->json([
            'data' => $data,
        ]);
        //return new TimelineCollection($data);
    }

    // returns Photos & Videos from free posts in a randomized way.
    public function publicFeed(Request $request)
    {
        $user = Auth::user();

        $query = Post::with('mediafiles')
            ->withCount(['comments', 'likes'])
            ->where('active', 1)
            ->where('type', 'free')
            ->where('user_id', '!=', $user->id)
            ->where('schedule_datetime', null)
            ->where(function ($query) {
                $query->where('expire_at', '>', Carbon::now('UTC'))
                      ->orWhere('expire_at', null);
            })
            ->whereHas('mediafiles', function($q) {
                $q->with('diskmediafile')->whereHas('diskmediafile', function($q1) {
                    $q1->where('mimetype', 'like', '%video/%')->orWhere('mimetype', 'like', '%image/%');
                });
            })
            ->has('mediafiles', '>' , 0)
            ->orderBy('created_at', 'desc');
        // %NOTE: we could also just remove post-query, as the feed will auto-update to fill length of page (?)
        $data = $query->paginate( $request->input('take', 18) );
        return new PostCollection($data);
    }
}

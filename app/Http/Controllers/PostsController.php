<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

use App\Models\Favorite;
use App\Models\Comment;
use App\Models\Financial\Account;
use App\Models\Mediafile;
use App\Models\Post;
use App\Models\Timeline;
use App\Models\Tip;
use App\Models\Casts\Money as CastsMoney;

use App\Enums\ContenttagAccessLevelEnum;
use App\Enums\PostTypeEnum;
use App\Enums\MediafileTypeEnum;

use App\Rules\InEnum;

use App\Http\Resources\PostCollection;
use App\Http\Resources\Post as PostResource;

use App\Payments\PaymentGateway;

class PostsController extends AppBaseController
{
    public function index(Request $request)
    {
        $request->validate([
            // filters
            'timeline_id' => 'uuid|exists:timelines,id', // if admin only
            'user_id' => 'uuid|exists:users,id', // if admin only
            'qsearch' => 'string',
        ]);
        $filters = $request->only(['timeline_id', 'user_id', 'is_flagged']) ?? [];

        // Init query
        $query = Post::query(); 

        // Check permissions
        if ( !$request->user()->isAdmin() ) {
            $query->where('postable_type', 'timelines')->where('postable_id', $request->user()->timeline->id);
            unset($filters['user_id']);
            unset($filters['timeline_id']);
        }

        // Apply any filters
        foreach ($filters as $key => $f) {
            switch ($key) {
            case 'is_flagged':
                if ($f) {
                    $query->has('contentflags', '>', 0);
                }
                break;
            default:
                $query->where($key, $f);
                break;
            }
        }

        if ( $request->has('qsearch') && (strlen($request->qsearch)>2) ) {
            $query->orWhere( function($q1) use(&$request) {
                $q1->where('description', 'LIKE', '%'.$request->qsearch.'%');
                $q1->orWhere('id', 'LIKE', $request->qsearch.'%');
            });
        }

        // Sorting
        switch ($request->sortBy) {
        case 'created_at':
        case 'updated_at':
        case 'type':
        case 'price':
            $sortDir = $request->sortDir==='asc' ? 'asc' : 'desc';
            $query->orderBy($request->sortBy, $sortDir);
            break;
        default:
            $query->orderBy('updated_at', 'desc');
        }

        $data = $query->paginate( $request->input('take', Config::get('collections.max.posts', 10)) );
        return new PostCollection($data);
    }

    public function show(Request $request, Post $post)
    {
        $this->authorize('view', $post);
        if ( $request->user()->can('contentView', $post) ) {
            $post->load('mediafiles');
        }
        return new PostResource($post);
    }

    public function store(Request $request)
    {
        $sessionUser = $request->user();
        $vrules = [
            'timeline_id' => 'required|uuid|exists:timelines,id',
            'type' => [ 'sometimes', 'required', new InEnum(new PostTypeEnum()) ],
            'price' => 'sometimes|required|integer',
            'price_for_subscribers' => 'sometimes|required|integer',
            'mediafiles' => 'array',
            'mediafiles.*.*' => 'integer|uuid|exists:mediafiles',
            'expiration_period' => 'nullable|integer',
            'schedule_datetime' => 'sometimes|date',
        ];

        if ( !$request->has('mediafiles') ) {
            $vrules['description'] = 'string'; // %FIXME: can't make this required in this case as mediafiles may be attached in subsequent call
        }

        $request->validate($vrules);

        $timeline = Timeline::find($request->timeline_id); // timeline being posted on

        // $this->authorize('update', $timeline); // create post considered timeline update
        if ($sessionUser->id !== $timeline->user_id && $sessionUser->canCreatePostForTimeline($timeline) == false) {
            abort(403, 'Authorize Error');
        }

        $attrs = $request->except(['timeline_id', 'description']); // timeline_id is now postable
        $attrs['user_id'] = $timeline->user->id; // %FIXME: remove this field, redundant
        $attrs['active'] = $request->input('active', 1);
        $attrs['type'] = $request->input('type', PostTypeEnum::FREE);

        $privateTags = collect();
        $publicTags = collect();
        if ( $request->has('description') ) { // extract & collect any tags
            //$regex = '/\B#\w\w+(!)?/';
            $regex = '/(#\w+!?)/';
            $origStr = Str::of($request->description);
            $allTags = $origStr->matchAll($regex);
            $allTags->each( function($str) use(&$privateTags, &$publicTags) {
                $accessLevel = (substr($str,-1)==='!') ? ContenttagAccessLevelEnum::MGMTGROUP : ContenttagAccessLevelEnum::OPEN;
                switch ( $accessLevel ) {
                    case ContenttagAccessLevelEnum::MGMTGROUP:
                        $privateTags->push($str);
                        break;
                    case ContenttagAccessLevelEnum::OPEN:
                        $publicTags->push($str);
                        break;
                }
            });
            $attrs['description'] = trim($origStr->remove($privateTags->toArray(), false)); // keep public, remove private tags
        }
   
        if ($request->input('schedule_datetime')) {
            $attrs['schedule_datetime'] = $request->input('schedule_datetime');
        }
           
        if ($request->input('expiration_period')) {
            $attrs['expire_at'] = Carbon::now('UTC')->addDays($request->input('expiration_period'));
        }

        // %TODO: DB transaction (?)
        $post = $timeline->posts()->create($attrs);

        if ( $request->has('mediafiles') ) {
            // %FIXME: if this is indeed used by the Vue client code, we should change/refactor it to 
            // instead upload using POST mediafiles.store calls that follow this posts.store
            foreach ( $request->mediafiles as $mfID ) {
                $refMF = Mediafile::where('resource_type', 'vaultfolders')
                    ->where('is_primary', true)
                    ->findOrFail($mfID) // fail => 404
                    ->diskmediafile->createReference(
                        'posts',    // $resourceType
                        $post->id,  // $resourceID
                        'New Post', // $mfname - could be optionally passed as a query param %TODO
                        MediafileTypeEnum::POST // $mftype
                    );
            }
        }

        $post->addTag($publicTags, ContenttagAccessLevelEnum::OPEN); // batch add
        $post->addTag($privateTags, ContenttagAccessLevelEnum::MGMTGROUP); // batch add
        /*
        $allTags->each( function($str) use(&$post) {
            $accessLevel = (substr($str,-1)==='!') ? ContenttagAccessLevelEnum::MGMTGROUP : ContenttagAccessLevelEnum::OPEN;
            $str = trim($str, '#!'); // remove hashtag and possible '!' at end indicating private/mgmt tag
            $post->addTag($str, $accessLevel); // add 1-by-1
        });
         */

        $post->refresh();

        return response()->json([
            'post' => $post,
        ], 201);
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        if ($post->price->isPositive() && $post->sharees()->count() > 0) {
            abort(403, 'Post has sharees');
        }

        $request->validate([
            'description' => 'required',
            'type' => [ 'sometimes', 'required', new InEnum(new PostTypeEnum()) ],
            'price' => 'sometimes|required|integer',
            'price_for_subscribers' => 'sometimes|required|integer',
            'mediafiles' => 'array',
            'mediafiles.*.*' => 'integer|uuid|exists:mediafiles',
            'schedule_datetime' => 'nullable|date',
        ]);

        $privateTags = collect();
        $publicTags = collect();
        if ( $request->has('description') ) { // extract & collect any tags
            $regex = '/(#\w+!?)/';
            $origStr = Str::of($request->description);
            $allTags = $origStr->matchAll($regex);
            $allTags->each( function($str) use(&$privateTags, &$publicTags) {
                $accessLevel = (substr($str,-1)==='!') ? ContenttagAccessLevelEnum::MGMTGROUP : ContenttagAccessLevelEnum::OPEN;
                switch ( $accessLevel ) {
                    case ContenttagAccessLevelEnum::MGMTGROUP:
                        $privateTags->push($str);
                        break;
                    case ContenttagAccessLevelEnum::OPEN:
                        $publicTags->push($str);
                        break;
                }
            });
            $post->description = trim($origStr->remove($privateTags->toArray(), false));
        }

        if ($request->has('type')) {
            $post->type = $request->type;
        }

        if ($request->has('price')) {
            if ($request->price !== $post->price) {
                $post->price = $request->price;
            }
        }

        if ($request->has('price_for_subscribers')) {
            if ($request->price_for_subscribers !== $post->price_for_subscribers) {
                $post->price_for_subscribers = $request->price_for_subscribers;
            }
        }

        if ($request->has('mediafiles')) {
            foreach ($request->mediafiles as $mfID) {
                // %NOTE: require src mediafile to be in vault
                $refMF = Mediafile::where('resource_type', 'vaultfolders')
                    ->where('is_primary', true)
                    ->findOrFail($mfID)
                    ->diskmediafile->createReference(
                        'posts',    // $resourceType
                        $post->id,  // $resourceID
                        'New Post', // $mfname - could be optionally passed as a query param %TODO
                        MediafileTypeEnum::POST // $mftype
                    );
                $post->refresh(); // %FIXME: is this necessary here? Is done below
            }
        }

        if ($request->has('schedule_datetime')) {
            if ($request->schedule_datetime !== $post->schedule_datetime) {
                $post->schedule_datetime = $request->schedule_datetime;
            }
        }

        $post->save();

        // Since we are updating tags as a batch, to effect a 'delete' we first need to remove all attached tags, and then add
        //   whatever is sent via the post, which is a complete set that includes any pre-existing tags that haven't been removed
        $post->contenttags()->detach();
        $post->addTag($publicTags, ContenttagAccessLevelEnum::OPEN); // batch add
        $post->addTag($privateTags, ContenttagAccessLevelEnum::MGMTGROUP); // batch add

        $post->refresh();

        return response()->json([
            'post' => $post,
        ]);
    }

    public function destroy(Request $request, Post $post)
    {
        $this->authorize('delete', $post);
        if ($post->sharees()->count() > 0 ) {
            abort(403, 'Post has sharees');
        }
        $post->delete();
        return response()->json([]);
    }

    public function favorite(Request $request, Post $post)
    {
        $this->authorize('favorite', $post);
        $favorite = Favorite::create([
            'user_id' => $request->user()->id,
            'favoritable_type' => 'posts',
            'favoritable_id' => $post->id,
        ]);
        $post->refresh();
        return new PostResource($post);
    }

    /**
     * Tips a post
     *
     * @param Request $request
     * @param Post $post
     * @return void
     */
    public function tip(Request $request, Post $post, PaymentGateway $paymentGateway)
    {
        $this->authorize('tip', $post);

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
            'receiver_id'     => $post->getOwner()->first()->getKey(),
            'tippable_type'   => $post->getMorphString(),
            'tippable_id'     => $post->getKey(),
            'account_id'      => $account->getKey(),
            'currency'        => $request->currency,
            'amount'          => $request->amount,
            'period'          => $request->period ?? 'single',
            'period_interval' => $request->period_interval ?? 1,
            'message'         => $request->message ?? null,
        ]);

        return $paymentGateway->tip($account, $tip, $price);
    }

    /**
     * Purchase a post
     *
     * @param Request $request
     * @param Post $post
     * @param PaymentGateway $paymentGateway
     * @return array
     */
    public function purchase(Request $request, Post $post, PaymentGateway $paymentGateway)
    {
        $this->authorize('purchase', $post);

        $request->validate([
            'account_id' => 'required|uuid',
            'amount' => 'required|numeric',
            'currency' => 'required',
        ]);

        $price = CastsMoney::toMoney($request->amount, $request->currency);
        if ($post->verifyPrice($price) === false) {
            abort(400, 'Invalid Price');
        }

        $account = Account::with('resource')->find($request->account_id);
        $this->authorize('purchase', $account);

        return $paymentGateway->purchase($account, $post, $price);
    }

    public function indexComments(Request $request, Post $post)
    {
        $this->authorize('indexComments', $post);
        //$filters = $request->input('filters', []);
        $comments = Comment::with(['user'])
            ->withCount('likes')
            ->withCount('replies')
            ->where('commentable_id', $post->id)
            ->where('parent_id', null)
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ( $comments as &$comment ) {
            $comment->prepFor('post', 'all');
        }

        return response()->json([
            'comments' => $comments,
        ]);
    }

}

<?php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Rules\InEnum;
use App\Models\Comment;
use App\Models\Favorite;
use App\Models\Timeline;
use App\Models\Mediafile;
use App\Enums\PostTypeEnum;
use Illuminate\Http\Request;
use App\Enums\MediafileTypeEnum;
use App\Payments\PaymentGateway;
use App\Models\Financial\Account;
use App\Http\Resources\PostCollection;
use App\Models\Casts\Money as CastsMoney;
use App\Http\Resources\Post as PostResource;
use App\Models\Tip;
use Carbon\Carbon;

class PostsController extends AppBaseController
{
    public function index(Request $request)
    {
        $request->validate([
            // filters
            'timeline_id' => 'uuid|exists:timelines,id', // if admin only
            'user_id' => 'uuid|exists:users,id', // if admin only
        ]);
        $filters = $request->only(['timeline_id', 'user_id']) ?? [];

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
            default:
                $query->where($key, $f);
                break;
            }
        }

        $data = $query->paginate( $request->input('take', env('MAX_POSTS_PER_REQUEST', 10)) );
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

        $this->authorize('update', $timeline); // create post considered timeline update

        $attrs = $request->except('timeline_id'); // timeline_id is now postable
        $attrs['user_id'] = $timeline->user->id; // %FIXME: remove this field, redundant
        $attrs['active'] = $request->input('active', 1);
        $attrs['type'] = $request->input('type', PostTypeEnum::FREE);
   
        if ($request->input('schedule_datetime')) {
            $attrs['schedule_datetime'] = $request->input('schedule_datetime');
        }
           
        if ($request->input('expiration_period')) {
            $attrs['expire_at'] = Carbon::now('UTC')->addDays($request->input('expiration_period'));
        }

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
        $post->refresh();

        return response()->json([
            'post' => $post,
        ], 201);
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        if ($post->sharees()->count() > 0) {
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

        $post->fill($request->only([
            'description',
        ])); // %TODO

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
                $post->refresh();
            }
        }

        if ($request->has('schedule_datetime')) {
            if ($request->schedule_datetime !== $post->schedule_datetime) {
                $post->schedule_datetime = $request->schedule_datetime;
            }
        }

        $post->save();

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
            ->get();

        foreach ( $comments as &$comment ) {
            $comment->prepFor('post', 'all');
        }

        return response()->json([
            'comments' => $comments,
        ]);
    }

}

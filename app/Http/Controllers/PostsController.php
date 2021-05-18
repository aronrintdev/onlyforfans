<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Http\Resources\Post as PostResource;
use App\Http\Resources\PostCollection;
use App\Notifications\TipReceived;
use App\Notifications\ResourcePurchased;
use App\Models\Favorite;
use App\Models\Post;
use App\Rules\InEnum;
use App\Models\Comment;
use App\Models\Timeline;
use App\Models\Mediafile;
use App\Models\Diskmediafile;
use App\Enums\PostTypeEnum;
use App\Enums\PaymentTypeEnum;
use App\Enums\MediafileTypeEnum;

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
        $request->validate([
            'timeline_id' => 'required|uuid|exists:timelines,id',
            'mediafiles' => 'array', // present when existing mediafile is attached (ie from vault)
            'mediafiles.*.*' => 'integer|uuid|exists:mediafiles',
        ]);

        $timeline = Timeline::find($request->timeline_id); // timeline being posted on

        $this->authorize('update', $timeline); // create post considered timeline update

        $attrs = $request->except('timeline_id'); // timeline_id is now postable
        $attrs['postable_type'] = 'timelines'; // %FIXME: hardcoded
        $attrs['postable_id'] = $timeline->id; // %FIXME: hardcoded
        $attrs['user_id'] = $timeline->user->id; // %FIXME: remove this field, redundant
        $attrs['active'] = $request->input('active', 1);
        $attrs['type'] = $request->input('type', PostTypeEnum::FREE);

        $post = Post::create($attrs);
        if ( $request->has('mediafiles') ) {
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

        $request->validate([
            'description' => 'required',
            'type' => [ 'sometimes', 'required', new InEnum(new PostTypeEnum()) ],
            'price' => 'sometimes|required|integer',
            'mediafiles' => 'array',
            'mediafiles.*.*' => 'integer|uuid|exists:mediafiles',
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

        $post->save();

        return response()->json([
            'post' => $post,
        ]);
    }

    public function attachMediafile(Request $request, Post $post, Mediafile $mediafile)
    {
        // require mediafile to be in vault (?)
        if ( empty($mediafile->resource) ) {
            abort(400, 'source file must have associated resource');
        }
        if ( $mediafile->resource_type !== 'vaultfolders' ) {
            abort(400, 'source file associated resource type must be vaultfolder');
        }
        $this->authorize('update', $post);
        $this->authorize('update', $mediafile);
        $this->authorize('update', $mediafile->resource);

        $refMF = Mediafile::where('resource_type', 'vaultfolders')
            ->where('is_primary', true)
            ->findOrFail($mediafile->id)->diskmediafile->createReference(
                'posts',    // $resourceType
                $post->id,  // $resourceID
                'Attached File', // $mfname - could be optionally passed as a query param %TODO
                MediafileTypeEnum::POST // $mftype
            );
        $post->refresh();

        return response()->json([
            'post' => $post,
        ]);
    }

    public function destroy(Request $request, Post $post)
    {
        $this->authorize('delete', $post);
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

    public function tip(Request $request, Post $post)
    {
        $this->authorize('tip', $post);

        $request->validate([
            'base_unit_cost_in_cents' => 'required|numeric',
        ]);

        $cattrs = [];
        if ( $request->has('notes') ) {
            $cattrs['notes'] = $request->notes;
        }

        try {
            $post->receivePayment(
                PaymentTypeEnum::TIP,
                $request->user(), // sender of tip
                $request->base_unit_cost_in_cents,
                $cattrs,
            );
        } catch(Exception | Throwable $e){
            return response()->json(['message'=>$e->getMessage()], 400);
        }

        $post->user->notify(new TipReceived($post, $request->user(), ['amount'=>$request->base_unit_cost_in_cents]));

        return response()->json([
            'post' => $post,
        ]);
    }

    // %TODO: check if already purchased? -> return error
    public function purchase(Request $request, Post $post)
    {
        $this->authorize('purchase', $post);
        $purchaser = $request->user();
        $cattrs = [ 'notes' => $request->note ?? '' ];
        try {
            $post->receivePayment(
                PaymentTypeEnum::PURCHASE,
                $purchaser, // payment *sender*
                $post->price,
                $cattrs
            );
            $purchaser->sharedposts()->attach($post->id, [
                'cattrs' => json_encode($cattrs ?? []),
            ]);
        } catch(Exception | Throwable $e) {
            throw $e;
            return response()->json(['message'=>$e->getMessage()], 400);
        }

        $post->user->notify(new ResourcePurchased($post, $purchaser, ['amount'=>$post->price]));

        return response()->json([
            'post' => $post ?? null,
        ]);
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

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
use App\Models\Post;
use App\Models\Comment;
use App\Models\Timeline;
use App\Models\Mediafile;
use App\Enums\PostTypeEnum;
use App\Enums\PaymentTypeEnum;

class PostsController extends AppBaseController
{
    public function index(Request $request)
    {
        $request->validate([
            'filters' => 'array',
            'filters.timeline_id' => 'uuid|exists:timelines,id', // if admin only
            'filters.user_id' => 'uuid|exists:users,id', // if admin only
        ]);

        $filters = $request->filters ?? [];

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
                //case 'postable_id':
                case 'timeline_id':
                case 'user_id':
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
        return new PostResource($post);
    }

    public function store(Request $request)
    {
        $request->validate([
            'timeline_id' => 'required|uuid|exists:timelines,id',
            'mediafiles' => 'array',
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
                $cloned = Mediafile::find($mfID)->doClone('posts', $post->id);
                $post->mediafiles()->save($cloned);
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
        $post->fill($request->only([ 'description' ])); // %TODO
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

        $mediafile->doClone('posts', $post->id);
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

    /* NOT SURE WHAT THIS DOES (?)
    public function saves(Request $request)
    {
        $saves = $request->user()->sharedmediafiles->map( function($mf) {
            $mf->foo = 'bar';
            //$mf->owner = $mf->getOwner()->first(); // %TODO
            $mf->owner = $mf->getOwner()->first()->only('username', 'name', 'avatar');
            return $mf;
        });

        return response()->json([
            'shareables' => [
                'mediafiles' => $mediafiles,
                'vaultfolders' => $request->user()->sharedvaultfolders,
            ],
        ]);
    }
     */

    public function tip(Request $request, Post $post)
    {
        $this->authorize('tip', $post);

        $request->validate([
            'base_unit_cost_in_cents' => 'required|numeric',
        ]);

        try {
            $post->receivePayment(
                PaymentTypeEnum::TIP,
                $request->user(), // send of tip
                $request->base_unit_cost_in_cents,
                [ 'notes' => $request->note ?? '' ]
            );
        } catch(Exception | Throwable $e){
            return response()->json(['message'=>$e->getMessage()], 400);
        }

        return response()->json([
            'post' => $post,
        ]);
    }

    // %TODO: check if already purchased? -> return error
    // %NOTE: post price in DB is in dollars not cents %FIXME
    public function purchase(Request $request, Post $post)
    {
        $this->authorize('purchase', $post);
        $sender = $request->user();
        $cattrs = [ 'notes' => $request->note ?? '' ];
        try {
            $post->receivePayment(
                PaymentTypeEnum::PURCHASE,
                $sender,
                $post->price,
                $cattrs
            );
            $sender->sharedposts()->attach($post->id, [
                'cattrs' => json_encode($cattrs ?? []),
            ]);
        } catch(Exception | Throwable $e) {
            return response()->json(['message'=>$e->getMessage()], 400);
        }

        return response()->json([
            'post' => $post ?? null,
        ]);
    }

    public function indexComments(Request $request, Post $post)
    {
        $this->authorize('view', $post);
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

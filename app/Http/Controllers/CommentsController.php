<?php
namespace App\Http\Controllers;

use DB;
use Exception;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

class CommentsController extends AppBaseController
{
    // %TODO: refactor with scopes
    // %TODO: make this version timeline-owner only; followers call posts controller to get
    //         comments on posts they follow/purchased etc
    public function index(Request $request)
    {
        //$this->authorize('index', Comment::class);
        $request->validate([
            'post_id' => 'uuid|exists:posts,id',
            'user_id' => 'uuid|exists:users,id',
            'parent_id' => 'uuid|exists:comments,id',
        ]);

        $query = Comment::with('user', 'replies.user');
        if ( !$request->user()->isAdmin() ) {
            $query->where('user_id', $request->user()->id); // non-admin: only view own comments
        } else if ( $request->has('user_id') ) {
            $query->where('user_id', $request->user_id); // admin can optionally filter by any user
        }

        if ( $request->has('post_id') ) { // for specific post
            $post = Post::find($request->post_id);
            $query->where('post_id', $post->id);
            if ( !$request->user()->isAdmin() ) {
                $this->authorize('update', $post); // must own post unless admin
            }
            /* %FIXME: broken
            if ( !$request->has('include_replies') ) {
                $query->whereNull('parent_id'); // only grab 1st level (%NOTE)
            }
             */
        }


        /* %TODO: subgroup under 'filters' (need to update axios.GET calls as well in Vue)
        // Apply filters
        if ( $request->has('filters') ) {
            if ( $request->has('post_id', 'filters.user_id') ) { // comment author by post
                $query->where('user_id', $request->filters['user_id']);
            }
        }
         */

        return response()->json([
            'comments' => $query->get(),
        ]);
    }

    public function show(Request $request, Comment $comment)
    {
        $this->authorize('view', $comment);
        return response()->json([
            'comment' => $comment,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'post_id' => 'required|uuid|exists:posts,id',
            'user_id' => 'required|uuid|exists:users,id',
            'parent_id' => 'nullable|uuid|exists:comments,id',
            'description' => 'required|string|min:3',
        ]);

        $post = Post::where('postable_type', 'timelines')
                    ->where('postable_id', $request->post_id)
                    ->first();
        $this->authorize('comment', $post);

        $attrs = $request->except('post_id'); // timeline_id is now postable
        $attrs['commentable_type'] = 'posts'; // %FIXME: hardcoded
        $attrs['commentable_id'] = $post->id;

        $comment = Comment::create($attrs);

        return response()->json([
            'comment' => $comment,
        ], 201);
    }

    public function update(Request $request, Comment $comment)
    {
        $this->authorize('update', $comment);
        $request->validate([
            'description' => 'required|string|min:1',
        ]);
        $attrs = $request->only([ 'description' ]);
        $comment->fill($attrs);
        $comment->save();

        return response()->json([
            'comment' => $comment,
        ]);
    }

    public function destroy(Request $request, Comment $comment)
    {
        $this->authorize('delete', $comment);
        $comment->delete();
        return response()->json([]);
    }

}

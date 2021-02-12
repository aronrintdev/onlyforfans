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
    public function index(Request $request)
    {
        if ( !$request->hasAny('user_id', 'post_id') ) {
            $this->authorize('index', Comment::class);
        } else {
            if ( $request->has('post_id') ) {
                $post = Post::find($request->post_id); // %FIXME: this version probably should be on Posts controller
                $this->authorize('view', $post);
            }
            if ( $request->has('user_id') && $request->user()->id!==$request->user_id ) {
                abort(403);
            }
        }

        $query = Comment::with('user', 'replies.user');

        if ( $request->has('post_id') ) { // for specific post
            $query->where('post_id', $request->post_id);
            /* %FIXME: broken
            if ( !$request->has('include_replies') ) {
                $query->whereNull('parent_id'); // only grab 1st level (%NOTE)
            }
             */
        }

        if ( $request->has('user_id') ) {
            $query->where('user_id', $request->user_id);
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
            //'parent_id' => 'exists:comments,id', // %TODO
            'description' => 'required|string|min:1',
        ]);
        //$attrs = $request->all();
        $attrs = $request->except('post_id'); // timeline_id is now postable
        $attrs['commentable_type'] = 'commentable'; // %FIXME: hardcoded
        $attrs['commentable_id'] = $request->post_id; // %FIXME: hardcoded

        try {
            $comment = Comment::create($attrs);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json([
            'comment' => $comment,
        ], 201);
    }

    public function update(Request $request, Comment $comment)
    {
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
        $comment->delete();
        return response()->json([]);
    }

}

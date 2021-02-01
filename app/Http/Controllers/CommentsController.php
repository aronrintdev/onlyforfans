<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Comment;
//use App\Post;

class CommentsController extends AppBaseController
{
    // %TODO: refactor with scopes
    public function index(Request $request)
    {
        $sessionUser = Auth::user();
        //dd($request->all());

        // %TODO: convert to validator (?)
        if ( !$sessionUser->isAdmin() && !$request->hasAny('post_id', 'user_id') ) { // must have user or post id
            abort(403);
        }
        if ( !$sessionUser->isAdmin() && !$request->has('post_id') && $request->has('user_id') ) {
            if ( $sessionUser->id != $request->user_id ) { // must have user_id equal to session user 
                abort(403);
            }
        }
        // must have post_id and post must be accessible %TODO

        $query = Comment::with('user', 'replies.user');

        if ( $request->has('post_id') ) { // for specific post
            $query->where('post_id', $request->post_id);
            if ( !$request->has('include_replies') ) {
                $query->whereNull('parent_id'); // only grab 1st level (%NOTE)
            }
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
        $sessionUser = Auth::user();
        //dd('here', $sessionUser->id);
        if ( !$sessionUser->isAdmin() ) { // admin can view all comments
            $isCommentOwner = ($sessionUser->id === $comment->user_id ); // can see own comments
            $isPostOwner = ($sessionUser->id === $comment->post->user_id ); // can see comments on own post
            $isFollowedTimeline = $sessionUser->followedtimelines->contains($comment->post->timeline_id); // can see comments on followed timeline's posts
            //dd( 'co: '.$isCommentOwner, 'po'.$isPostOwner, 'ft'.$isFollowedTimeline);
            if ( !$isCommentOwner && !$isPostOwner && !$isFollowedTimeline ) {
                abort(403);
            }
        }
        return response()->json([
            'comment' => $comment,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'user_id' => 'required|exists:users,id',
            //'parent_id' => 'exists:comments,id', // %TODO
            'description' => 'required|string|min:1',
        ]);
        $attrs = $request->all();

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

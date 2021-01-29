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
    public function index(Request $request)
    {
        $query = Comment::with('user', 'replies.user');

        // Apply filters
        if ( $request->has('post_id') ) {
            $query->where('post_id', $request->post_id)->whereNull('parent_id'); // only grab 1st level (%NOTE)
        }

        return response()->json([
            //'comments' => $query->take(5)->get(),
            'comments' => $query->get(),
        ]);
    }

    public function show(Request $request, Comment $comment)
    {
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
        ]);
    }

    public function update(Request $request, Comment $comment)
    {
        $request->validate([
            'description' => 'required|string|min:1',
        ]);
        $attrs = $request->only('description');

        try {
            $comment = Comment::create($attrs);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json([
            'comment' => $comment,
        ]);
    }

    public function destroy(Request $request, Comment $comment)
    {
        $comment->delete();
        return response()->json([]);
    }

    /*
    public function toggleLike(Request $request, Comment $comment)
    {
        $sessionUser = Auth::user();

        // %TODO: notify user
        if ( !$comment->likes->contains($sessionUser->id) ) { // like
            $comment->likes()->attach($sessionUser->id);
            $isLikedBySessionUser = true;
        } else { // unlike
            $comment->likes()->detach($sessionUser->id);
            $isLikedBySessionUser = false;
        }

        $comment->refresh();

        return response()->json([
            'comment' => $comment,
            'is_liked_by_session_user' => $isLikedBySessionUser,
            'like_count' => $comment->likes()->count(),
        ]);
    }
     */

}

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

        if ( $request->has('post_id') ) { // for specific post
            $query->where('post_id', $request->post_id);
            if ( !$request->has('include_replies') ) {
                $query->whereNull('parent_id'); // only grab 1st level (%NOTE)
            }
        }

        // Apply filters
        if ( $request->has('filters') ) {
            if ( $request->has('user_id') ) { // comment author
                $query->where('user_id', $request->user_id);
            }
        }

        return response()->json([
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
        ], 201);
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

}

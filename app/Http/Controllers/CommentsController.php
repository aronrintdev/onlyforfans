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
        $query = Comment::with('user');

        // Apply filters
        if ( $request->has('post_id') ) {
            $query->where('post_id', $request->post_id)->whereNull('parent_id'); // only grab 1st level (%NOTE)
        }
        return response()->json([
            'comments' => $query->take(5)->get(),
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'user_id' => 'required|exists:users,id',
            //'parent_id' => 'exists:comments,id',
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

}

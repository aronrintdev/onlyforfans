<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Post;
use App\Timeline;
use App\Enums\PaymentTypeEnum;
use App\Enums\PostTypeEnum;

class PostsController extends AppBaseController
{
    public function index(Request $request)
    {
        $sessionUser = Auth::user();
        $sessionTimeline = $sessionUser->timeline;

        $filters = $request->input('filters', []);

        $query = Post::query();
        if ( !$sessionUser->isAdmin() ) {
            $query->where('timeline_id', $sessionTimeline->id);
        }

        foreach ($filters as $f) {
            switch ($f['key']) {
                case 'ptype':
                    $query->where('type', $f['val']);
                    break;
            }
        }
        $posts = $query->get();

        return response()->json([
            'posts' => $posts,
        ]);
    }

    public function show(Request $request, Post $post)
    {
        $sessionUser = Auth::user();
        //$post->load('mediafiles', 'user', 'timeline');
        return response()->json([
            'post' => $post,
            //'is_liked_by_session_user' => $post->users_liked->contains($sessionUser->id),
            //'like_count' => $post->users_liked()->count(),
        ]);
    }


    public function store(Request $request)
    {
        $sessionUser = Auth::user();

        $request->validate([
            'timeline_id' => 'required|exists:timelines,id',
            // [ ] 'description': , // text COLLATE utf8_unicode_ci NOT NULL,
            // [/] 'user_id': , // int(10) unsigned NOT NULL,
            // [/] 'active': , // tinyint(1) NOT NULL DEFAULT '1',
            // [ ] 'soundcloud_title': , // varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
            // [ ] 'soundcloud_id': , // varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
            // [ ] 'youtube_title': , // varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
            // [ ] 'youtube_video_id': , // varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
            // [ ] 'location': , // varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
            // [/] 'type': , // varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
            // [ ] 'price': , // varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
            // [ ] 'shared_post_id': , // int(10) unsigned DEFAULT NULL,
            // [ ] 'publish_date': , // date DEFAULT NULL,
            // [ ] 'publish_time': , // time DEFAULT NULL,
            // [ ] 'expiration_date': , // date DEFAULT NULL,
            // [ ] 'expiration_time': , // time DEFAULT NULL,
        ]);

        $timeline = Timeline::find($request->timeline_id); // timeline being posted on

        if ( $sessionUser->id !== $timeline->user->id ) { // can only post on own home page
            abort(403, 'Unauthorized');
        }

        $attrs = $request->all();
        $attrs['user_id'] = $timeline->user->id; // %FIXME: remove this field, redundant
        $attrs['active'] = $request->input('active', 1);
        $attrs['type'] = $request->input('type', PostTypeEnum::FREE);

        try {
            $post = Post::create($attrs);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json([
            'post' => $post,
        ]);
    }

    public function destroy(Request $request, Post $post)
    {
        $sessionUser = Auth::user();
        if ( $post->user->id !== $sessionUser->id ) {
            abort(403);
        }
        $post->delete();
        return response()->json([]);
    }

    public function saves(Request $request)
    {
        $sessionUser = Auth::user();

        $saves = $sessionUser->sharedmediafiles->map( function($mf) {
            $mf->foo = 'bar';
            //$mf->owner = $mf->getOwner(); // %TODO
            //dd( 'owner', $mf->owner->only('username', 'name', 'avatar') ); // HERE
            $mf->owner = $mf->getOwner()->only('username', 'name', 'avatar');
            return $mf;
        });

        return response()->json([
            'shareables' => [
                'mediafiles' => $mediafiles,
                'vaultfolders' => $sessionUser->sharedvaultfolders,
            ],
        ]);
    }

    /*
    public function toggleLike(Request $request, Post $post)
    {
        $sessionUser = Auth::user();

        // %TODO: notify user

        if ( !$post->likes->contains($sessionUser->id) ) { // like
            $post->likes()->attach($sessionUser->id);
            $isLikedBySessionUser = true;
        } else { // unlike
            $post->likes()->detach($sessionUser->id);
            $isLikedBySessionUser = false;
        }

        $post->refresh();

        return response()->json([
            'post' => $post,
            'is_liked_by_session_user' => $isLikedBySessionUser,
            'like_count' => $post->users_liked()->count(),
        ]);
    }

    public function tip(Request $request, $id)
    {
        $sessionUser = Auth::user(); // sender of tip
        try {
            $post = Post::findOrFail($id);
            $post->receivePayment(
                PaymentTypeEnum::TIP,
                $sessionUser,
                $request->amount*100,
                [ 'notes' => $request->note ?? '' ]
            );
    
        } catch(Exception | Throwable $e){
            Log::error(json_encode([
                'msg' => 'PostsController::tip() - error',
                'emsg' => $e->getMessage(),
            ]));
            //throw $e;
            return response()->json(['status'=>'400', 'message'=>$e->getMessage()]);
        }

        return response()->json([
            'post' => $post ?? null,
        ]);
    }

    // %TODO: check if already purchased? -> return error
    // %NOTE: post price in DB is in dollars not cents %FIXME
    public function purchase(Request $request, $id)
    {
        $sessionUser = Auth::user(); // purchaser
        try {
            $post = Post::findOrFail($id);
            $post->receivePayment(
                PaymentTypeEnum::PURCHASE,
                $sessionUser,
                ( $request->has('amount') ? $request->amount : $post->price ) * 100, // option to override post price via request (?)
                [ 'notes' => $request->note ?? '' ]
            );
    
        } catch(Exception | Throwable $e){
            Log::error(json_encode([
                'msg' => 'PostsController::tip() - error',
                'emsg' => $e->getMessage(),
            ]));
            throw $e;
            return response()->json(['status'=>'400', 'message'=>$e->getMessage()]);
        }

        return response()->json([
            'post' => $post ?? null,
        ]);
    }
     */
}

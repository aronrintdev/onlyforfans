<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Post;
use App\Mediafile;
use App\Timeline;
use App\Enums\PaymentTypeEnum;
use App\Enums\PostTypeEnum;

class PostsController extends AppBaseController
{
    public function index(Request $request)
    {
        $filters = $request->input('filters', []);

        $query = Post::query();
        if ( !$request->user()->isAdmin() ) {
            $query->where('timeline_id', $request->user()->timeline->id);
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
        if ( $request->user()->cannot('view', $post) ) {
            abort(403);
        }
        return response()->json([
            'post' => $post,
        ]);
    }


    public function store(Request $request)
    {
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

        if ( $request->user()->id !== $timeline->user->id ) { // can only post on own home page
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
        ], 201);
    }

    public function update(Request $request, Post $post)
    {
        $attrs = $request->only([ 'description' ]);
        $post->fill($attrs);
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
        if ( $post->user->id !== $request->user()->id ) {
            abort(403);
        }
        $post->delete();
        return response()->json([]);
    }

    public function saves(Request $request)
    {
        $saves = $request->user()->sharedmediafiles->map( function($mf) {
            $mf->foo = 'bar';
            //$mf->owner = $mf->getOwner()->first(); // %TODO
            //dd( 'owner', $mf->owner->only('username', 'name', 'avatar') ); // HERE
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
        try {
            $post->receivePayment(
                PaymentTypeEnum::PURCHASE,
                $request->user(),
                $post->price*100, // %FIXME: should be on timeline
                [ 'notes' => $request->note ?? '' ]
            );
    
        } catch(Exception | Throwable $e) {
            return response()->json(['message'=>$e->getMessage()], 400);
        }

        return response()->json([
            'post' => $post ?? null,
        ]);
    }
    /*
     */
}

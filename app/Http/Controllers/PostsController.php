<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Post;
use App\Enums\PaymentTypeEnum;

class PostsController extends AppBaseController
{
    public function store(Request $request)
    {
        $attrs = $request->validate([
            'post_video_upload' => 'max:512000',
        ]);
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
}

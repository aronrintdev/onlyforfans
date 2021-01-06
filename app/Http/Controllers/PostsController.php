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
                'msg' => 'TimlineController::sendTipPost() - Could not send notification',
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

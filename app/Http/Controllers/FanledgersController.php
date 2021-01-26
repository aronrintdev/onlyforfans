<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Fanledger;
use App\Post;
use App\Timeline;
use App\Enums\PaymentTypeEnum;
use App\Enums\PostTypeEnum;

class FanledgersController extends AppBaseController
{
    public function store(Request $request)
    {
        $sessionUser = Auth::user();

        $request->validate([
            'timeline_id' => 'required|exists:timelines,id',
            'fltype' => 'required|alpha_dash',
            'seller_id' => 'required|exists:users,id',
            'purchaseable_id' => 'required|numeric',
            'purchaseable_type' => 'required|alpha_dash',
            'base_unit_cost_in_cents' => 'required|numeric',
        ]);

        $attrs = $request->only([
            'timeline_id',
            'fltype',
            'seller_id',
            'purchaseable_id',
            'purchaseable_type',
            'amount',
        ]);

        $attrs['purchaser_id'] = $sessionUser->id;
        $attrs['qty'] = 1;
        $attrs['cattrs'] = [];
        $attrs['cattrs']['notes'] = $request->has('notes') && !empty($request->notes) ? $request->notes : '';

        try {
            $obj = Fanledger::create($attrs);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json([
            'fanledger' => $obj,
        ]);
    }

    /*
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

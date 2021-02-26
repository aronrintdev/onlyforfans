<?php
namespace App\Http\Controllers;

use App;
use DB;
use Auth;
use Exception;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Models\User;
use App\Models\Fanledger;
use App\Enums\PaymentTypeEnum;

class UsersController extends AppBaseController
{
    public function index(Request $request)
    {
        $query = User::query();

        // Apply filters
        //  ~ %TODO

        return response()->json([
            'users' => $query->get(),
        ]);
    }

    public function updateSettings(Request $request, User $user)
    {
        dd($request->all());
        $this->authorize('update', $user);
        $request->validate([
            'city' => 'string|min:2',
        ]);
        $attrs = $request->only([ 'city' ]);
        $userSetting->fill($attrs);
        $userSetting->save();

        return response()->json([
            'user_setting' => $userSetting,
        ]);
    }

    /**
     * Retrieves information about logged in user
     * Route: `users.me`
     */
    public function me(Request $request)
    {
        $sessionUser = Auth::user(); // sender of tip

        $sales = Fanledger::where('seller_id', $sessionUser->id)->sum('total_amount');

        $timeline = $sessionUser->timeline;
        $timeline->userstats = [ // %FIXME DRY
            'post_count'       => $timeline->posts->count(),
            'like_count'       => $timeline->user->likedposts->count(),
            'follower_count'   => $timeline->followers->count(),
            'following_count'  => $timeline->user->followedtimelines->count(),
            'subscribed_count' => 0, // %TODO $sessionUser->timeline->subscribed->count()
            'earnings'         => $sales,
        ];

        /** Flags for the common UI elements */
        $uiFlags = [
            'isAdmin'          => $sessionUser->can('admin.dashboard'),
            'isCreator'        => $sessionUser->settings->is_creator ?? false,
            'hasBanking'       => false, // TODO: Add Logic when banking is setup
            'hasEarnings'      => $sales > 0,
            'hasPaymentMethod' => false, // TODO: Add Logic when payment method is setup
        ];

        return [
            'session_user' => $sessionUser,
            'timeline'     => $timeline,
            'uiFlags'      => $uiFlags,
        ];
    }

    public function tip(Request $request, $id)
    {
        $sessionUser = Auth::user(); // sender of tip
        try {
            $tippee = User::findOrFail($id);
            $tippee->receivePayment(
                PaymentTypeEnum::TIP,
                $sessionUser,
                $request->amount,
                [ 'notes' => $request->note ?? '' ]
            );

        } catch(Exception | Throwable $e){
            Log::error(json_encode([
                'msg' => 'UsersController::tip() - error',
                'emsg' => $e->getMessage(),
            ]));
            //throw $e;
            return response()->json(['status'=>'400', 'message'=>$e->getMessage()]);
        }

        return response()->json([
            'tippee' => $tippee ?? null,
        ]);
    }

    public function match(Request $request)
    {
        if ( !$request->ajax() ) {
            \App::abort(400, 'Requires AJAX');
        }

        $term = $request->input('term',null);

        if ( empty($term) ) {
            return [];
        }

        $collection = User::where( function($q1) use($term) {
                         //$q1->where('first_name', 'like', $term.'%')->orWhere('last_name', 'like', $term.'%');
                         $q1->where('email', 'like', $term.'%');
                      })
                      //->where('estatus', EmployeeStatusEnum::ACTIVE) // active users only
                      ->get();

        //return \Response::json([ 'collection'=> $collection, ]);
        $field = $request->has('field') ? $request->field : null;

        return response()->json( $collection->map( function($item,$key) use($field) {
            $attrs = [
                    'id' => $item->id,
                    'value' => $item->id,
                    'label' => $field ? $item->{$field} : $item->email,
                    //'value' => $field ? $item->{$field} : $item->slug, // default to username/slug
                    //'label' => $item->renderName(),
                ];
                return $attrs;
        }) );

    }
}

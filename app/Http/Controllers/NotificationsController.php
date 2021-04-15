<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\User;
//use App\Models\Timeline;
use App\Enums\PaymentTypeEnum;
use App\Http\Resources\NotificationCollection;
//use App\Http\Resources\NotificationResource;

class NotificationsController extends AppBaseController
{
    public function index(Request $request)
    {
        $request->validate([
            //'type' => 'string|in:App\Notifications\TimelineTipped',
            //'purchaser_id' => 'uuid|exists:users,id',
        ]);

        $sessionUser = $request->user();

        // Filters

        if ( !$request->user()->isAdmin() ) { // Check permissions

            //$notifications = $sessionUser->notifications;

            // non-admin can only view own resources, option to filter by seller and/or purchaser, defaults to seller
            $filters = [];
            /*
            if ( $request->has('seller_id') && ($request->seller_id === $request->user()->id) ) {
                $filters['seller_id'] = $request->seller_id;
            }
            if ( $request->has('purchaser_id') && ($request->purchaser_id === $request->user()->id) ) {
                $filters['purchaser_id'] = $request->purchaser_id;
            }
            if ( !count($filters) ) { // if none set, default to seller...
                $filters['seller_id'] = $request->user()->id;
            }
             */
        } else {
            //$filters = $request->only(['seller_id', 'purchaser_id']);
        }

        // Query 

        /*
        foreach ($filters as $key => $f) { // Apply any filters
            // %TODO: subgroup under 'filters' (need to update axios.GET calls as well in Vue)
            switch ($key) {
            case 'seller_id':
            case 'purchaser_id':
                $query->where($key, $f);
                break;
            }
        }
         */

        //$data = $query->latest()->paginate( $request->input('take', env('MAX_DEFAULT_PER_REQUEST', 10)) );
        $data = $sessionUser->notifications()
                            ->latest()
                            //->where('type', 'App\Notifications\TimelineTipped')
                            //->where('type', 'App\Notifications\PostTipped')
                            ->where('type', 'App\Notifications\ResourceLiked')
                            ->paginate( $request->input('take', env('MAX_DEFAULT_PER_REQUEST', 10)) );
        return new NotificationCollection($data);
    }

}

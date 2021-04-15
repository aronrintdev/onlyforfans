<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Notification as NotificationModel;
use App\Models\User;
use App\Enums\PaymentTypeEnum;
use App\Http\Resources\NotificationCollection;

class NotificationsController extends AppBaseController
{
    public function index(Request $request)
    {
        $request->validate([
            'type' => 'string|in:PostPurchased,PostTipped,TimelineTipped,ResourceLiked,TimelineFollowed,TimelineSubscribed',
            //'purchaser_id' => 'uuid|exists:users,id',
        ]);

        $sessionUser = $request->user();
        $query = NotificationModel::query();

        if ( !$request->user()->isAdmin() ) { // Check permissions

            // if non-admin, only return notifications owned by session user
            $query->where('notifiable_type', 'users')->where('notifiable_id', $sessionUser->id);
        }

        // Apply any filters
        if ( $request->has('type') ) {
            $query->where('type', 'App\\Notifications\\'.$request->type);
        }

        $data = $query->latest()->paginate( $request->input('take', env('MAX_DEFAULT_PER_REQUEST', 10)) );
        return new NotificationCollection($data);
    }

    public function dashboard(Request $request)
    {
    }

}

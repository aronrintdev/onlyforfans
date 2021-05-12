<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
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
            'type' => 'string|in:ResourcePurchased,TipReceived,ResourceLiked,TimelineFollowed,TimelineSubscribed,CommentReceived',
        ]);
        $filters = $request->only(['type']) ?? [];

        // Init query
        $query = NotificationModel::query();

        // Check permissions
        if ( !$request->user()->isAdmin() ) {
            // non-admin: can only view own
            $query->whereHasMorph(
                'notifiable',
                [User::class],
                function (Builder $q1, $type) use(&$request) {
                    switch ($type) {
                    case User::class:
                        //$query->where('notifiable_type', 'users')->where('notifiable_id', $request->user()->id);
                        $q1->where('id', $request->user()->id);
                        break;
                    default:
                        throw new Exception('Invalid morphable type for notifiable: '.$type);
                    }
                }
            );
            unset($filters['user_id']);
        }

        // Apply filters
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

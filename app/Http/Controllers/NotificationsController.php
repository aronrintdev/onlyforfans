<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
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
        $sessionUser = $request->user();

        $request->validate([
            'type' => 'string|in:ResourcePurchased,TipReceived,ResourceLiked,TimelineFollowed,TimelineSubscribed,CommentReceived,MessageReceived,StaffSettingsChanged,UserTagged,InviteStaffManager,InviteStaffMember',
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

        /*
        // Check if notification has valid actors or sender or requesters
        $users = User::pluck('username')->toArray();
        $query->where(function ($q) use (&$users) {
            dd($users);
            $q->whereIn('data->actor->username', $users)->orWhereIn('data->sender->username', $users)->orWhereIn('data->requester->username', $users);
        });
         */

        // Mark all my notifications as 'read' if I access this route as sesson user
        $sessionUser->unreadNotifications()->update(['read_at' => now()]);

        $unreadCount = NotificationModel::whereNull('read_at')
            ->where('notifiable_type', 'users')
            ->where('notifiable_id', $sessionUser->id)
            ->count();

        $data = $query->latest()->paginate( $request->input('take', Config::get('collections.defaultMax', 10)) );
        return new NotificationCollection($data);
    }

    public function getTotalUnreadCount(Request $request)
    {
        $sessionUser = $request->user();
        $unreadNotifications = NotificationModel::whereNull('read_at')
            ->where('notifiable_type', 'users')
            ->where('notifiable_id', $sessionUser->id)
            ->get();
        return response()->json([
                'total_unread_count' => $unreadNotifications->count(),
                'unread_notifications' => $unreadNotifications,
            ]);
    }

}

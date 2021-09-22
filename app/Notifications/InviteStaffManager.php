<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Notifications\Notification;

use App\Apis\Sendgrid\Api as SendgridApi;
use App\Models\Staff;
use App\Models\User;

class InviteStaffManager extends Notification
{
    use NotifyTraits, Queueable;

    public $staff;
    public $inviter;

    public function __construct(Staff $staff, User $inviter, $invitee=null) {
        $this->staff = $staff;
        $this->inviter = $inviter;
        $this->invitee = $invitee;

        // workaround for bug in Illuminate\Notifications\NotificationSender
        if ( empty($this->id) ) {
            $this->id = false;
        }
    }

    public function via($notifiable)
    {
        $channels = ['database'];
        $channels[] = $this->getMailChannel();
        return $channels;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('Hi '.$this->staff->invitee_fullname)
            ->line("You've been invited to become a manager of ".$this->inviter->name."'s profile.")
            ->line("To confirm, click the link below to accept the invite.")
            ->action('Accept Invite', $this->staff->invite_landing_url);
    }

    // For case where the invitee already has an AllFans account
    //  ~ $notifiable is the invitee
    public function toSendgrid($notifiable)
    {
        return [
            'template_id' => 'invite-staff-manager',
            'to' => [
                'email' => $this->staff->email,
                'name' => $this->staff->invitee_fullname, // 'display name'
            ],
            'dtdata' => [
                'manager_name' => $this->staff->first_name.' '.$this->staff->last_name, // invitee
                'username' => $this->inviter->name,
                'login_url' => $this->staff->invite_landing_url, // %FIXME: key should be accept_url
                'home_url' => url('/'),
                'referral_url' => url('/referrals'),
                'privacy_url' => url('/privacy'),
                'manage_preferences_url' => url( route('users.showSettings', $this->invitee->username)),
                'unsubscribe_url' => url( route('users.showSettings', $this->invitee->username)),
            ],
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'actor' => [
                'username' => $this->inviter->username,
                'name' => $this->inviter->name,
                'avatar' => $this->inviter->avatar->filepath ?? null,
            ],
            'inviter' => [
                'username' => $this->inviter->username,
                'name' => $this->inviter->name,
                'avatar' => $this->inviter->avatar->filepath ?? null,
            ],
            'invitee' => [ // manager
                'manager_name' => $this->staff->first_name.' '.$this->staff->last_name, // invitee
                'manager_email' => $this->staff->email,
                'manager_role' => $this->staff->role,
            ],
            'user_id' => $this->staff->user_id ?? null,
            'creator_id' => $this->staff->creator_id ?? null,
            'owner_id' => $this->staff->ownwer_id ?? null,
            'invite_landing_url' => $this->staff->invite_landing_url,
            'invite_action_url' => $this->staff->invite_action_url,
        ];
    }

    // For case where the invitee does not yet have an AllFans account
    public static function sendGuestInvite(Staff $staff, User $inviter)
    {
        $notify = new InviteStaffManager($staff, $inviter);

        $to = [ $inviter->email => $inviter->name ];

        $via = $notify->via(null);

        if ( in_array('mail', $via) ) {
            //$r = $notify->toMail(null);
            NotificationFacade::route('mail', $to)->notify(new InviteStaffManager($staff, $inviter));
        } else if ( in_array('App\Channels\SendgridChannel', $via) ) {
            Notification::route('App\Channels\SendgridChannel', $to)->notify(new InviteStaffManager($staff, $inviter));
        }

    }

}

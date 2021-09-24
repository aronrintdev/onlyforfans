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

class InviteStaffMember extends Notification
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
            ->line("You've been added as a staff member of ".$this->inviter->name."'s profile.")
            ->line("To confirm, click the link below to accept the invite.")
            ->action('Accept Invite', $this->staff->invite_landing_url);
    }

    // For case where the invitee already has an AllFans account
    //  ~ $notifiable is the invitee
    public function toSendgrid($notifiable)
    {
        return [
            'template_id' => 'invite-staff-member',
            'to' => [
                'email' => $this->staff->email,
                'name' => $this->staff->invitee_fullname, // 'display name'
            ],
            'dtdata' => [
                'staff_name' => $this->staff->first_name.' '.$this->staff->last_name, // invitee
                'username' => $this->inviter->name,
                'login_url' => $this->staff->invite_landing_url, // %FIXME: SG key should be accept_url
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
            'invitee' => [ // staff member
                'staff_member_name' => $this->staff->first_name.' '.$this->staff->last_name, // invitee
                'staff_member_email' => $this->staff->email,
                'staff_member_role' => $this->staff->role,
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
        $notify = new InviteStaffMember($staff, $inviter);

        $to = [ $staff->email => $staff->first_name.' '.$staff->last_name ]; // invitee's email & name info (no user yet)
        $via = $notify->via(null);

        if ( in_array('mail', $via) ) {
            //$r = $notify->toMail(null);
            NotificationFacade::route('mail', $to)->notify(new InviteStaffMember($staff, $inviter));
        } else if ( in_array('App\Channels\SendgridChannel', $via) ) {
            Notification::route('App\Channels\SendgridChannel', $to)->notify(new InviteStaffMember($staff, $inviter));
        }

    }

}

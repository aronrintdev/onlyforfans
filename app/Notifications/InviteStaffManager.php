<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Staff;
use App\Models\User;

class InviteStaffManager extends Notification
{
    use Queueable;

    public $staff;
    public $inviter;

    public function __construct(Staff $staff, User $inviter, User $invitee=null) {
        $this->staff = $staff;
        $this->inviter = $inviter;
        $this->invitee = $invitee;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('Hi '.$this->inviter->name)
            ->line("You've been invited to become a manager of ".$this->inviter->name."'s profile.")
            ->line("To confirm, click the link below to accept the invite.")
            ->action('Accept Invite', $this->staff->invite_url);
    }

    public function toSendgrid($notifiable)
    {
        return [
            'template_id' => 'invite-staff-manager',
            'to' => [
                'email' => $notifiable->email,
                'name' => $notifiable->name, // 'display name'
            ],

            'dtdata' => [
                'manager_name' => $this->staff->first_name.' '.$this->staff->last_name, // invitee
                'username' => $this->inviter->name,
                'login_url' => $this->staff->invite_url, // %FIXME: key should be accept_url
                'home_url' => url('/'),
                'referral_url' => url('/referrals'),
                'privacy_url' => url('/privacy'),
                'manage_preferences_url' => $this->invitee ? url( route('users.showSettings', $this->invitee->username)) : url('/'),
                'unsubscribe_url' => $this->invitee ? url( route('users.showSettings', $this->invitee->username)) : url('/'),
            ],
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'inviter' => [ // follower
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
            'ownwer_id' => $this->staff->ownwer_id ?? null,
        ];
    }
}

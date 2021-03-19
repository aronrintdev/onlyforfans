<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Invite;

class ShareableInvited extends Mailable
{
    use Queueable, SerializesModels;

    public $invite;

    public function __construct(Invite $invite)
    {
        $this->invite = $invite;
    }

    public function build()
    {
        return $this->markdown('emails.invited.shared');
    }
}

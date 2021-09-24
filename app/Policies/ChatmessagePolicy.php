<?php
namespace App\Policies;

use App\Models\Chatmessage;
use App\Models\User;
use App\Policies\Traits\OwnablePolicies;

class ChatmessagePolicy extends BasePolicy
{
    use OwnablePolicies;

    protected $policies = [
        'viewAny'     => 'permissionOnly',
        'view'        => 'isOwner:pass isBlockedByOwner:fail',
        'update'      => 'isOwner:pass:fail',
        'delete'      => 'isOwner:pass',
        //'forceDelete' => 'isOwner:pass:fail',
        //'restore'     => 'isOwner:pass:fail',
    ];

    protected function view(User $user, Chatmessage $chatmessage)
    {
        return $chatmessage->chatthread->participants->contains($user->id);
    }

    protected function delete(User $user, Chatmessage $chatmessage)
    {
        return $chatmessage->isOwner($user);
    }

    protected function purchase(User $user, Chatmessage $chatmessage)
    {
        return $chatmessage->chatthread->participants->contains($user->id);
    }

}

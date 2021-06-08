<?php
namespace App\Policies;

use App\Policies\Traits\OwnablePolicies;
use App\Models\Chatthread;
use App\Models\User;

class ChatthreadPolicy extends BasePolicy
{
    use OwnablePolicies;

    protected $policies = [
        'viewAny'     => 'permissionOnly',
        'view'        => 'isOwner:pass isBlockedByOwner:fail',
        'update'      => 'isOwner:pass:fail',
        'delete'      => 'fail', // 'isOwner:pass',
        //'forceDelete' => 'isOwner:pass:fail',
        //'restore'     => 'isOwner:pass:fail',
    ];

    protected function view(User $user, Chatthread $chatthread)
    {
        return $chatthread->participants->contains($user->id);
    }
}

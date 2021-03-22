<?php
namespace App\Policies;

use App\Models\Bookmark;
use App\Models\User;
use App\Policies\Traits\OwnablePolicies;

class BookmarkPolicy extends BasePolicy
{
    use OwnablePolicies;

    protected $policies = [
        'viewAny'     => 'permissionOnly',
        'view'        => 'isOwner:pass isBlockedByOwner:fail',
        'delete'      => 'isOwner:pass',
        'forceDelete' => 'isOwner:pass:fail',
        'restore'     => 'isOwner:pass:fail',
    ];

    /*
    protected function view(User $user, Bookmark $bookmark)
    {
        dd($user->toArray(), $bookmark->toArray());
        return true; // essentially allow viewing of free posts by 'public' (non-followers)
    }
     */
}

<?php
namespace App\Policies;

use App\Models\Favorite;
use App\Models\User;
use App\Policies\Traits\OwnablePolicies;

class FavoritePolicy extends BasePolicy
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
    protected function view(User $user, Favorite $favorite)
    {
        dd($user->toArray(), $favorite->toArray());
        return true; // essentially allow viewing of free posts by 'public' (non-followers)
    }
     */
}

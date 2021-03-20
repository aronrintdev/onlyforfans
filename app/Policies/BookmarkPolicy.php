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
        'store'       => 'isOwner:pass:fail',
        'delete'      => 'isOwner:pass',
        'forceDelete' => 'isOwner:pass:fail',
        'restore'     => 'isOwner:pass:fail',
    ];
}

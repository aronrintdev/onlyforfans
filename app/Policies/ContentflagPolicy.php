<?php
namespace App\Policies;

use App\Models\User;
use App\Models\Story;
use App\Policies\Traits\OwnablePolicies;

class StoryPolicy extends BasePolicy
{
    use OwnablePolicies;

    protected $policies = [
        'viewAny'     => 'permissionOnly',
        'view'        => 'isOwner:pass isBlockedByOwner:fail',
        'update'      => 'isOwner:pass',
        'delete'      => 'isOwner:pass',
        'restore'     => 'isOwner:pass',
        'forceDelete' => 'permissionOnly',
    ];

}

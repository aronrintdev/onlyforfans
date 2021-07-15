<?php
namespace App\Policies;

use App\Policies\Traits\OwnablePolicies;
use App\Models\Campaign;
use App\Models\User;

class CampaignPolicy extends BasePolicy
{
    use OwnablePolicies;

    protected $policies = [
        'create'        => 'permissionOnly',
    ];

    protected function create(User $user)
    {
        return true;
    }
}

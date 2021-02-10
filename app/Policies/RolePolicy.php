<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy extends BasePolicy
{
    protected $policies = [
        'viewAny'     => 'permissionOnly',
        'create'      => 'permissionOnly',
        'view'        => 'permissionOnly',
        'update'      => 'permissionOnly',
        'delete'      => 'permissionOnly',
        'restore'     => 'permissionOnly',
        'forceDelete' => 'permissionOnly',
    ];
}

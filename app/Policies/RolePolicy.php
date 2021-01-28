<?php

namespace App\Policies;

use App\Role;
use App\User;

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

<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

class PermissionPolicy extends BasePolicy
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

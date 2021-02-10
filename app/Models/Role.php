<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * See https://spatie.be/docs/laravel-permission/v3/basic-usage/basic-usage
 *   for usage
 */
class Role extends SpatieRole
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];


    /**
     * Add a set of permissions to a Role.
     * @param  Collection  $permissions | array of permissions, must at least contain name of permission
     */
    public function addPermissions(Collection $permissions)
    {
        DB::transaction(function () use ($permissions) {
            $permissions->each(function ($permission, $key) {
                $this->givePermissionTo($permission->name);
            });
        });
    }

    /**
     * Removes a set of permissions to a Role.
     * @param  Collection  $permissions | array of permissions, must at least contain name of permission
     */
    public function removePermissions(Collection $permissions)
    {
        DB::transaction(function () use ($permissions) {
            $permissions->each(function ($permission, $key) {
                $this->revokePermissionTo($permission['name']);
            });
        });
    }
}

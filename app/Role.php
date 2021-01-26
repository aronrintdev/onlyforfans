<?php

namespace App;

use Spatie\Permission\Models\Role as ParentRole;

/**
 * See https://spatie.be/docs/laravel-permission/v3/basic-usage/basic-usage
 *   for usage
 */
class Role extends ParentRole
{
    /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
    protected $fillable = [];
}

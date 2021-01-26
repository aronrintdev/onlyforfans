<?php

namespace App;

use Spatie\Permission\Models\Permission as ParentPermission;

/**
 * See https://spatie.be/docs/laravel-permission/v3/basic-usage/basic-usage
 *   for usage
 */
class Permission extends ParentPermission
{
    /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
    protected $fillable = [];
}

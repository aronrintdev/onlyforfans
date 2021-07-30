<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * See https://spatie.be/docs/laravel-permission/v3/basic-usage/basic-usage
 *   for usage
 */
class Permission extends SpatiePermission
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];


    /**
     * Checks if object/collection passed will edit any fields
     */
    public function willBeEditedBy(Collection $data): bool
    {
        return $data->contains(function ($value, $key) {
            return $this->{$key} !== $value;
        });
    }
}

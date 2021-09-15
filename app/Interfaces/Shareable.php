<?php

namespace App\Interfaces;

use App\Models\User;
use Illuminate\Support\Collection;
use App\Enums\ShareableAccessLevelEnum;

/**
 * This model is shareable
 *
 * @package App\Interfaces
 */
interface Shareable extends IsModel
{
    /**
     * Get the name of the shareablesTable
     * @return string
     */
    public function getShareableTable(): string;

    /**
     * Grants access to this resource for a user
     *
     * @param User $user
     * @param string $accessLevel
     * @param array $cattrs  Custom attributes
     * @param array $meta  Metadata
     * @return void
     */
    public function grantAccess(User $user, string $accessLevel, $cattrs = [], $meta = []): void;

    /**
     * Grants access to this resource to a collection of users
     *
     * @param Collection $users
     * @param string $accessLevel
     * @param array $cattrs  Custom attributes
     * @param array $meta  Metadata
     * @return void
     */
    public function grantAccessFor(Collection $users, string $accessLevel, $cattrs = [], $meta = []): void;

    /**
     * Checks if user has access to Shareable Item at access level
     * @param User $user
     * @param string $accessLevel
     * @return bool
     */
    public function checkAccess(User $user, string $accessLevel = ShareableAccessLevelEnum::PREMIUM): bool;

    /**
     * Revokes access to this resource for a user
     *
     * @param User $user
     * @param array $cattrs  Custom attributes
     * @param array $meta  Metadata
     * @return void
     */
    public function revokeAccess(User $user, $cattrs = [], $meta = []): void;


    /**
     * Revokes access to this resource for a collection of users
     *
     * @param Collection $users
     * @param string $accessLevel
     * @param array $cattrs  Custom attributes
     * @param array $meta  Metadata
     * @return void
     */
    public function revokeAccessFor(Collection $users, string $accessLevel, $cattrs = [], $meta = []): void;
}

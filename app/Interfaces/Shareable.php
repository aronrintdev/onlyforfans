<?php

namespace App\Interfaces;

use App\Models\User;

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
     * Revokes access to this resource for a user
     *
     * @param User $user
     * @param array $cattrs  Custom attributes
     * @param array $meta  Metadata
     * @return void
     */
    public function revokeAccess(User $user, $cattrs = [], $meta = []): void;
}

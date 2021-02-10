<?php

namespace App\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Allows users to block resources
 */
interface Blockable
{
    /** MorphToMany relationship */
    public function blockedBy(): MorphToMany;
    /** Is a user blocked by a user */
    public function isBlockedBy(User $user): bool;
}
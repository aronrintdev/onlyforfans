<?php

namespace App\Policies;

use App\Models\Tip;
use App\Models\User;

class TipPolicy extends BasePolicy
{

    protected $policies = [
        'delete'      => 'permissionOnly',
        'restore'     => 'permissionOnly',
        'forceDelete' => 'permissionOnly',
    ];

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Tip  $tip
     * @return mixed
     */
    public function view(User $user, Tip $tip)
    {
        return $user->id === $tip->sender_id || $user->id === $tip->receiver_id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Tip  $tip
     * @return mixed
     */
    public function update(User $user, Tip $tip)
    {
        return $user->id === $tip->sender_id;
    }

}

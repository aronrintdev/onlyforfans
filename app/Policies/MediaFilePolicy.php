<?php

namespace App\Policies;

use App\MediaFile;
use App\User;
use App\Policies\Addons\IsBlockedByOwner;
use App\Policies\Addons\IsOwner;

class MediaFilePolicy extends BasePolicy
{
    use IsBlockedByOwner;
    use IsOwner;

    protected $policies = [
        'viewAny'     => 'permissionOnly',
        'view'        => 'isBlockedByOwner:fail',
        'update'      => 'isOwner:pass',
        'delete'      => 'isOwner:pass',
        'restore'     => 'isOwner:pass',
        'forceDelete' => 'isOwner:pass',
        'toggleLike'  => 'isBlockedByOwner:fail',
        'tip'         => 'isBlockedByOwner:fail',
    ];

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\User  $user
     * @param  \App\MediaFile  $mediaFile
     * @return mixed
     */
    public function view(User $user, MediaFile $mediaFile)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\User  $user
     * @param  \App\MediaFile  $mediaFile
     * @return mixed
     */
    public function update(User $user, MediaFile $mediaFile)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\MediaFile  $mediaFile
     * @return mixed
     */
    public function delete(User $user, MediaFile $mediaFile)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\User  $user
     * @param  \App\MediaFile  $mediaFile
     * @return mixed
     */
    public function restore(User $user, MediaFile $mediaFile)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\MediaFile  $mediaFile
     * @return mixed
     */
    public function forceDelete(User $user, MediaFile $mediaFile)
    {
        //
    }
}

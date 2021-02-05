<?php
namespace App\Interfaces;

use App\User;
use Illuminate\Support\Collection;

interface Ownable {

    /**
     * Must be owned by user, multiple owners supported by collection
     */
    public function getOwner() : ?Collection;

    /**
     * Check if a user is an owner
     */
    public function isOwner(User $user) : bool;

}

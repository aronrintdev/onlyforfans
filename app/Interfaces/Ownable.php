<?php
namespace App\Interfaces;

use App\User;

interface Ownable {

    // Must be owned by user
    public function getOwner() : ?User;

}

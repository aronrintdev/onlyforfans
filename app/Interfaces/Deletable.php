<?php
namespace App\Interfaces;

interface Deletable {

    public function canBeDeleted() : bool;

}

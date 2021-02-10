<?php
namespace App\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface Cloneable {

    public function doClone(string $resourceType, int $resourceId) : ?Model;

}

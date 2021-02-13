<?php
namespace App\Interfaces;

use App\Models\Model;

interface Cloneable {

    public function doClone(string $resourceType, string $resourceId) : ?Model;

}

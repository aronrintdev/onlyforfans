<?php

namespace App\Models\Traits;

use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid as GoldSpecDigitalUuid;

trait Uuid
{
    use GoldSpecDigitalUuid;
    protected $keyType = 'string';
    protected $incrementing = false;
}

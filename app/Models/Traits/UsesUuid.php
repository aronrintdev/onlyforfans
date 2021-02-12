<?php

namespace App\Models\Traits;

use App\Libs\UuidGenerator;
use Exception;
use Illuminate\Support\Str;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\Generator\CombGenerator;
use Ramsey\Uuid\Codec\TimestampFirstCombCodec;
use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid as GoldSpecDigitalUuid;

trait UsesUuid
{
    use GoldSpecDigitalUuid;

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }

    protected function generateUuid(): string
    {
        return UuidGenerator::generateCombV4Uuid();
    }
}

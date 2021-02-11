<?php

namespace App\Models\Traits;

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
        /**
         * Generate a TimestampFirstCombCodec uuid for DB speed with large numbers of UUIDs
         * See https://uuid.ramsey.dev/en/latest/customize/timestamp-first-comb-codec.html for more details.
         */
        $factory = new UuidFactory();
        $codec = new TimestampFirstCombCodec($factory->getUuidBuilder());
        $factory->setCodec($codec);
        $factory->setRandomGenerator(new CombGenerator(
            $factory->getRandomGenerator(),
            $factory->getNumberConverter()
        ));
        return $factory->uuid4()->toString();
    }
}

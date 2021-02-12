<?php

namespace App\Libs;

use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\Generator\CombGenerator;
use Ramsey\Uuid\Codec\TimestampFirstCombCodec;

class UuidGenerator
{
    public static function generateCombV4Uuid(): string
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
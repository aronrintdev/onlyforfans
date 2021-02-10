<?php

namespace App\Models\Traits;

use App\Models\Casts\ShortUuid as ShortUuidCast;
use Exception;
use Ramsey\Uuid\Uuid;
use PascalDeVink\ShortUuid\ShortUuid;

trait UsesShortUuid
{

    protected $casts = [
        'id' => ShortUuidCast::class,
    ];

    public function fromShortId(string $value): string
    {
        $shortUuid = new ShortUuid();
        $uuid = $shortUuid->decode($value);
        return $uuid->toString();
    }

    /**
     * Convert a id to the short URL safe version
     */
    public function toShortId(string $value): string
    {
        if (Uuid::isValid($value) === false) {
            throw new Exception('value (' . $value . ') provided to toShortId on' . class_basename($this) . ' is not a valid Uuid.');
        }
        $shortUuid = new ShortUuid();
        return $shortUuid->encode(Uuid::fromString($value));
    }
}
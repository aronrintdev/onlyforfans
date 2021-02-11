<?php

namespace App\Models\Traits;

use Exception;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Arr;
use PascalDeVink\ShortUuid\ShortUuid;
use App\Models\Casts\ShortUuid as ShortUuidCast;

trait UsesShortUuid
{

    public function getCasts()
    {
        if (!isset($this->casts['id'])) {
            return Arr::add($this->casts, 'id', ShortUuidCast::class);
        }
        return $this->casts;
    }

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
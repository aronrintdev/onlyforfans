<?php

namespace App\Interfaces;

interface ShortUuid
{
    public function fromShortId(string $value): string;
    public function toShortId(string $value): string;
}
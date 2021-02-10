<?php

namespace App\Models\Casts;

use App\Interfaces\ShortUuid as InterfacesShortUuid;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class ShortUuid implements CastsAttributes
{
    public function get($model, $key, $value, $attributes)
    {
        if ($model instanceof InterfacesShortUuid === false) {
            throw new Exception(class_basename($model) . ' should implement App\\Interfaces\\ShortUuid');
        }
        return $model->toShortId($value);
    }

    public function set($model, $key, $value, $attributes)
    {
        if ($model instanceof InterfacesShortUuid === false) {
            throw new Exception(class_basename($model) . ' should implement App\\Interfaces\\ShortUuid');
        }
        if (Str::length($value) < 36) {
            try {
                return $model->fromShortId($value);
            } catch (Exception $e) {
                return false;
            }
        }
        return $value;
    }
}
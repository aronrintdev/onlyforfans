<?php

namespace App\Helpers;

use App\Helpers\Models;
use App\Interfaces\Tippable as InterfacesTippable;

/**
 * Helper functions for Purchaseable Interface
 * @package App\Helpers
 */
class Tippable
{
    public static function getTippableItem($item): ?InterfacesTippable
    {
        if ($item instanceof InterfacesTippable) {
            return $item;
        }

        $models = getModels([InterfacesTippable::class]);
        foreach ($models as $model) {
            $foundItem = app($model)::find($item);
            if (isset($foundItem)) {
                return $foundItem;
            }
        }
        return $item;
    }
}

<?php

namespace App\Helpers;

use App\Interfaces\Subscribable as InterfacesSubscribable;

/**
 * Helper functions for Subscribable Interface
 * @package App\Helpers
 */
class Subscribable
{
    public static function getSubscribableItem($item): ?InterfacesSubscribable
    {
        if ($item instanceof InterfacesSubscribable) {
            return $item;
        }

        $models = getModels([InterfacesSubscribable::class]);
        foreach ($models as $model) {
            $foundItem = app($model)::find($item);
            if (isset($foundItem)) {
                return $foundItem;
            }
        }
        return $item;
    }
}

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
            $item = app($model)::find($item);
            if (isset($item)) {
                return $item;
            }
        }
        return $item;
    }
}

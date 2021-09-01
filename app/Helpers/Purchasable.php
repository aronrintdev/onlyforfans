<?php
namespace App\Helpers;

use App\Interfaces\Purchaseable;
use App\Helpers\Models;

/**
 * Helper functions for Purchaseable Interface
 * @package App\Helpers
 */
class Purchasable
{
    public static function getPurchasableItem($item): ?Purchaseable
    {
        if ($item instanceof Purchaseable) {
            return $item;
        }

        $models = getModels([ Purchaseable::class ]);
        foreach ($models as $model) {
            $foundItem = app($model)::find($item);
            if (isset($foundItem)) {
                return $foundItem;
            }
        }
        return $item;
    }
}
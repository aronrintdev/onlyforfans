<?php

namespace App\Interfaces;

/**
 * Function expected to be on app models
 * @package App\Interfaces
 */
interface IsModel
{
    /**
     * Eloquent Model Method, This is so intellisense works with the interface.
     * Gets model primary key.
     */
    public function getKey();

    /**
     * Model Method, this is so intellisense works with interface.
     * Gets the Morph string for model.
     */
    public function getMorphString(): string;

    public function withoutRelations();

    public function refresh();
}

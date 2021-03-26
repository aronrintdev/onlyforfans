<?php

namespace App\Interfaces;

interface Tippable
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

    /**
     * The string used in the transaction description.
     * e.i. "Purchase of `{This methods return}`"
     *
     * @return string
     */
    public function getDescriptionNameString(): string;

}
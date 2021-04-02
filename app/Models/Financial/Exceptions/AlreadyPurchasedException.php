<?php

namespace App\Models\Financial\Exceptions;

use App\Interfaces\Purchaseable;
use App\Models\User;
use RuntimeException;

class AlreadyPurchasedException extends RuntimeException
{

    public $item;
    public $purchaser;

    public function __construct(Purchaseable $item, User $purchaser)
    {
        $this->make($item, $purchaser);
    }

    public function make(Purchaseable $item, User $purchaser)
    {
        $this->item = $item;
        $this->purchaser = $purchaser;

        $this->message = "Item has already been purchased by user.";
    }
}

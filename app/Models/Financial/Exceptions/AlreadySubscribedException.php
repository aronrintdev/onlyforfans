<?php

namespace App\Models\Financial\Exceptions;

use App\Interfaces\Subscribable;
use App\Models\User;
use RuntimeException;

class AlreadySubscribedException extends RuntimeException
{

    public $item;
    public $subscriber;

    public function __construct(Subscribable $item, User $subscriber)
    {
        $this->make($item, $subscriber);
    }

    public function make(Subscribable $item, User $subscriber)
    {
        $this->item = $item;
        $this->subscriber = $subscriber;

        $this->message = "Item has already been subscribed to by user.";
    }
}

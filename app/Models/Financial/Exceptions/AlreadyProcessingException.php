<?php

namespace App\Models\Financial\Exceptions;

use RuntimeException;

class AlreadyProcessingException extends RuntimeException
{
    public function __construct()
    {
        $this->make();
    }

    public function make()
    {
        $this->message = "Item is already processing.";
    }
}

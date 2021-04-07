<?php

namespace App\Models\Financial\Exceptions;

use RuntimeException;

/**
 * Thrown when currency is not allowed to be used.
 *
 * @package App\Models\Financial\Exceptions
 */
class CurrencyNotAllowedException extends RuntimeException
{
    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $system;

    public function __construct($code, $system)
    {
        $this->make($code, $system);
    }

    public function make($code, $system)
    {
        $this->code = $code;
        $this->system = $system;

        $this->message = "Currency [{$code}] is not allowed for uses on financial system [{$system}]";
    }

}

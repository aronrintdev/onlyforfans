<?php

namespace App\Models\Financial\Exceptions;

use Illuminate\Support\Facades\Config;
use RuntimeException;

class InvalidFinancialSystemException extends RuntimeException
{
    protected $system;
    protected $model;

    public function __construct($system = null, $model = null)
    {
        $this->make($system, $model);
    }

    public function make($system, $model)
    {
        $this->system = $system;
        $this->model = $model;

        $validSystems = implode(', ', array_keys(Config::get('transactions.systems')));

        $this->message = "Invalid Financial System [{$system}]. Valid Systems: [{$validSystems}]";
    }
}

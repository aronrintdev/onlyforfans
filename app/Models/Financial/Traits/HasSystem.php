<?php

namespace App\Models\Financial\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use App\Models\Financial\Exceptions\InvalidFinancialSystemException;

trait HasSystem
{
    /**
     * System this model is working under
     */
    public function getSystemAttribute()
    {
        if (!isset($this->attributes['system'])) {
            $this->attributes['system'] = Config::get('transactions.default');
        }
        return $this->attributes['system'];
    }

    /* ----------------------- Verification Functions ----------------------- */
    /**
     * Checks if a system is valid in App config
     */
    public function isSystemValid(string $system = null): bool
    {
        if (!isset($system)) {
            $system = $this->system;
        }
        return Arr::exists(Config::get('transactions.systems', []), $system);
    }

    /**
     * Verifies if a system is valid to be used
     *
     * @throws InvalidFinancialSystemException
     */
    public function verifySystemIsValid(string $system = null)
    {
        if (!$this->isSystemValid($system)) {
            throw new InvalidFinancialSystemException($system);
        }
    }
}
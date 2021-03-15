<?php

namespace App\Models\Financial\Traits;

trait HasSystemByAccount
{
    /**
     * System this model is working under
     */
    public function getSystemAttribute()
    {
        return $this->account->system;
    }
}

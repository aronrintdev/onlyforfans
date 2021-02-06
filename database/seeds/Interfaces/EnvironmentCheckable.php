<?php

namespace Database\Seeders\Interfaces;
/**
 * @property  array  $environments
 */
interface EnvironmentCheckable
{
    public function shouldRun(): bool;
}
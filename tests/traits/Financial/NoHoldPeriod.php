<?php

namespace Tests\traits\Financial;

use Illuminate\Support\Facades\Config;

/**
 * Sets Hold period to 0 for tests that don't need a hold period.
 */
trait NoHoldPeriod
{
    protected $savedHoldPeriod;

    public function setUp(): void
    {
        parent::setUp();
        $system = Config::get('transactions.default');
        $this->savedHoldPeriod = Config::get("transactions.systems.{$system}.defaults.holdPeriod");
        Config::set("transactions.systems.{$system}.defaults.holdPeriod", 0);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $system = Config::get('transactions.default');
        Config::set("transactions.systems.{$system}.defaults.holdPeriod", $this->savedHoldPeriod);
    }
}
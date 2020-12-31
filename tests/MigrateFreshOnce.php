<?php
namespace Tests;
use Illuminate\Support\Facades\Artisan;

trait MigrateFreshOnce
{
    /**
     * If true, setup has run at least once
     */
    protected static $setUpHasRunOnce = false;

    /**
     * After first run of setUp, run migrate:fresh
     */
    public function setUp(): void {
        parent::setUp();
        if(!static::$setUpHasRunOnce) {
            Artisan::call('migrate:fresh');
            static::$setUpHasRunOnce = true;
        }
    }
}

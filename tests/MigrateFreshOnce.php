<?php
namespace Tests;

use Illuminate\Support\Facades\Artisan;

trait MigrateFreshOnce
{
    protected static $setUpHasRunOnce = false;

    public function setUp(): void {
        parent::setUp();
        if(!static::$setUpHasRunOnce) {
            Artisan::call('migrate:fresh');
            static::$setUpHasRunOnce = true;
        }
    }
}

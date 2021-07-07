<?php
namespace Tests;

use Illuminate\Support\Facades\Artisan;

// see: https://chrisduell.com/speeding-up-unit-tests-in-php
trait MigrateFreshSeedOnce
{
    protected static $setUpHasRunOnce = false;

    protected function setUp() : void
    {
        parent::setUp();
        if (!static::$setUpHasRunOnce) {
            Artisan::call('migrate:fresh');
            Artisan::call(
                'db:seed', ['--class' => 'TestDatabaseSeeder']
            );
            static::$setUpHasRunOnce = true;
         }
    }
}

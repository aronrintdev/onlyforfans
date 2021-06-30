<?php
namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Asserts\CustomAsserts;

abstract class TestCase extends BaseTestCase
{
    use CustomAsserts;
    //use CreatesApplication;

    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        return $app;
    }

    protected static $isSetup = false;

    protected function setUp() : void
    {
        if ( !self::$isSetup ) {
            $this->setupDatabase();
            self::$isSetup = true;
         }

        parent::setUp();

    }

    public function ajaxJSON($method, $uri, array $data=[]) {
        return $this->json($method,$uri,$data,[
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
            //'Accept' => 'application/json',
        ]);
         /*
         return $this->withHeaders([
             'X-Requested-With' => 'XMLHttpRequest',
             'X-Accept' => 'application/json',
         ])->json($method,$uri,$data);
          */
    }
    public function setupDatabase() {
        exec('rm '.__DIR__.'/../database/tmp4test.sqlite');
        exec('cp '.__DIR__.'/../database/template.sqlite '.__DIR__.'/../database/tmp4test.sqlite');
    }
}

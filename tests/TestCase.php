<?php
namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    //use CreatesApplication;
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

     public function ajaxJSON($method, $uri, array $data=[]) {
         /*
         return $this->json($method,$uri,$data,[
             'HTTP_X-Requested-With' => 'XMLHttpRequest',
             //'Accept' => 'application/json',
             ]);
          */
         return $this->withHeaders([
             'X-Requested-With' => 'XMLHttpRequest',
             'X-Accept' => 'application/json',
         ])->json($method,$uri,$data);
     }
}

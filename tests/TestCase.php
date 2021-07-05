<?php
namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
//use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Console\Output\ConsoleOutput;

use Tests\Asserts\CustomAsserts;
use Database\Seeders\TestDatabaseSeeder;

abstract class TestCase extends BaseTestCase
{
    use CustomAsserts;
    //use RefreshDatabase;
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
        parent::setUp();

        if ( !self::$isSetup ) {
            //$this->output->writeln('TestCase -- calling setupDatabase...');
            dump('TestCase -- calling setupDatabase...');
            $this->setupDatabase();
            self::$isSetup = true;
         }
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

    protected function setupDatabase() {
        if (1) { 
            //$this->output->writeln('setupDatabase -- copy template sqlite to test file...');
            dump('setupDatabase -- copy template sqlite to test file...');
            // use File:copy(?)
            //  ~ see : https://laracasts.com/discuss/channels/laravel/unittest-with-pre-seeded-database-and-persistent-content

            //exec('rm '.__DIR__.'/../database/tmp4test.sqlite');
            //exec('cp '.__DIR__.'/../database/template.sqlite '.__DIR__.'/../database/tmp4test.sqlite');
            exec('cp '.__DIR__.'/../database/template.sqlite '.__DIR__.'/../storage/logs/tmp4test.sqlite');
            //exec('chmod 777 '.__DIR__.'/../database/tmp4test.sqlite');
        } else {
            //$this->output->writeln('setupDatabase -- seed memory database...');
            dump('setupDatabase -- migrate memory database...');
            \Artisan::call('migrate');
            dump('setupDatabase -- seed memory database...');
            $this->seed(TestDatabaseSeeder::class);
        }
    }
}

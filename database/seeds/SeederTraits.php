<?php
namespace Database\Seeders;

use Faker\Factory;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Output\ConsoleOutput;

trait SeederTraits {

    private $output; // %TODO: move to trait
    private $faker;

    private function initSeederTraits($name) {
        $this->output = new ConsoleOutput();
        $this->faker = Factory::create();
        $this->appEnv = Config::get('app.env');

        if ( $this->appEnv !== 'testing' ) {
            $this->output->writeln('Running Seeder: '.$name.', env: '.$this->appEnv);
        }

    }

}

<?php
namespace Database\Seeders;

use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Support\Facades\Config;

trait SeederTraits {

    private $output; // %TODO: move to trait
    private $faker;

    private function initSeederTraits($name) {
        $this->output = new ConsoleOutput();
        $this->faker = \Faker\Factory::create();
        $this->appEnv = Config::get('app.env');

        if ( $this->appEnv !== 'testing' ) {
            $this->output->writeln('Running Seeder: '.$name.', env: '.$this->appEnv);
        }

    }

}

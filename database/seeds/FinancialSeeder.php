<?php

namespace Database\Seeders;

use App;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Run this seeder after truncating data in the financial list
 * @package Database\Seeders
 */
class FinancialSeeder extends Seeder
{
    /** Will run in all environments */
    protected $environments = ['local', 'dev', 'testing'];

    public function run()
    {
        $output = new ConsoleOutput();
        $env = App::environment();
        $output->writeln("Running Financial DB seeder, env: $env...");

        $this->call([
            PaymentMethodsSeeder::class,
            AchAccountSeeder::class,
            ShareablesTableSeeder::class,
            CommentsTableSeeder::class, // must be after shareables as requires followers
            TipsTableSeeder::class,
        ]);
    }
}

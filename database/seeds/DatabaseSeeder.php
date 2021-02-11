<?php
namespace Database\Seeders;

use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Support\Facades\Config;

class DatabaseSeeder extends Seeder
{
    /** Will run in all environments */
    protected $environments = [ 'all' ];

    public function run()
    {
        $output = new ConsoleOutput();
        $output->writeln('Running DB seeder...');

        $this->call([
            UsernameRulesSeeder::class,
            RolesTableSeeder::class,
            UsersTableSeeder::class,
            StoriesTableSeeder::class,
            PostsTableSeeder::class,
        ]);
    }

}

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
            ShareablesTableSeeder::class,
            PostsTableSeeder::class, // must be after shareables as requires followers
            CommentsTableSeeder::class, // must be after shareables as requires followers
            StoriesTableSeeder::class,
        ]);
    }

}

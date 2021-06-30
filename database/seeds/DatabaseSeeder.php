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
            CountriesTableSeeder::class,
            UsstatesTableSeeder::class,
            UsernameRulesSeeder::class,
            RolesTableSeeder::class,
            UsersTableSeeder::class,
            PostsTableSeeder::class,
            PaymentMethodsSeeder::class,
            AchAccountSeeder::class,
            ShareablesTableSeeder::class,
            CommentsTableSeeder::class, // must be after shareables as requires followers
            StoriesTableSeeder::class,
            FavoritesTableSeeder::class,
            LikeablesTableSeeder::class,
            TipsTableSeeder::class,
            RandomTimestampSeeder::class,
            ChatmessagesTableSeeder::class,
            MediafilesTableSeeder::class,
        ]);
    }

}

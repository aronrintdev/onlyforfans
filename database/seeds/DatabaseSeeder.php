<?php
namespace Database\Seeders;

use App;
use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Support\Facades\Config;

class DatabaseSeeder extends Seeder
{
    /** Will run in all environments */
    protected $environments = [ 'local', 'dev', 'testing' ];

    public function run()
    {
        $output = new ConsoleOutput();
        $env = App::environment();
        $output->writeln("Running DB seeder, env: $env...");

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
            PermissionsTableSeeder::class,
            StaffTableSeeder::class,

            ContenttagsTableSeeder::class,
        ]);
    }

}

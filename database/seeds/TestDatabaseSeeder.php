<?php
namespace Database\Seeders;

use App;
use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Seeder as ParentSeeder;

//class TestDatabaseSeeder extends Seeder %TODO
class TestDatabaseSeeder extends ParentSeeder
{
    /** Will run in all environments */
    protected $environments = [ 'testing' ];

    public function run()
    {
        throw new \Excetion('DEPRECATED - use DatabaseSeeder instead');
        $output = new ConsoleOutput();
        $output->writeln('Running Test DB seeder...');

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
            ChatmessagesTableSeeder::class,
            MediafilesTableSeeder::class,
            //TipsTableSeeder::class,
        ]);
    }

}

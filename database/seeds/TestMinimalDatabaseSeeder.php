<?php
namespace Database\Seeders;

use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Seeder as ParentSeeder;

class TestMinimalDatabaseSeeder extends ParentSeeder
{
    protected $environments = [ 'testing' ];

    public function run()
    {
        $output = new ConsoleOutput();
        $output->writeln('Running Test Minimal DB seeder...');

        $output->writeln('Mark.A');
        $this->call( CountriesTableSeeder::class);
        $output->writeln('Mark.B');
        $this->call( UsstatesTableSeeder::class);
        $output->writeln('Mark.C');
        $this->call( RolesTableSeeder::class);
        $output->writeln('Mark.D');
        $this->call( UsersTableSeeder::class);
        $output->writeln('Mark.E'); // <=
        $this->call( PostsTableSeeder::class);
        $output->writeln('Mark.F'); // <=
        $this->call( ShareablesTableSeeder::class);
        $output->writeln('Mark.G');
        $this->call( CommentsTableSeeder::class); // must be after shareables as requires followers
        $output->writeln('Mark.H'); // <=
        $this->call( StoriesTableSeeder::class);
        $output->writeln('Mark.I');
        $this->call( LikeablesTableSeeder::class);
        $output->writeln('Mark.J'); // <=
        $this->call( MediafilesTableSeeder::class);
        $output->writeln('Mark.K');

        /*
        $this->call([
            CountriesTableSeeder::class,
            UsstatesTableSeeder::class,
            //UsernameRulesSeeder::class,
            RolesTableSeeder::class,
            UsersTableSeeder::class,
            PostsTableSeeder::class,
            //PaymentMethodsSeeder::class,
            //AchAccountSeeder::class,
            ShareablesTableSeeder::class,
            CommentsTableSeeder::class, // must be after shareables as requires followers
            StoriesTableSeeder::class,
            //FavoritesTableSeeder::class,
            LikeablesTableSeeder::class,
            //ChatmessagesTableSeeder::class,
            MediafilesTableSeeder::class,
            //TipsTableSeeder::class,
        ]);
         */
    }

}

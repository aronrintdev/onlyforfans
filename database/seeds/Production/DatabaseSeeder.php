<?php
namespace Database\Seeders\Production;

use App;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;

use Symfony\Component\Console\Output\ConsoleOutput;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $output = new ConsoleOutput();
        $env = App::environment();
        $output->writeln("Running PRODUCTION DB seeder, env: $env...");

        $this->call([
            CountriesTableSeeder::class,
            UsstatesTableSeeder::class,
            UsernameRulesSeeder::class,
            RolesTableSeeder::class,
            UsersTableSeeder::class,
        ]);
    }

}
/*
-- SET FOREIGN_KEY_CHECKS=0;
delete from `vaultfolders`;
delete from `vaults`;	
delete from `user_settings`;
delete from `username_rules`;	
delete from role_has_permissions;
delete from `model_has_permissions`;
delete from `model_has_roles`;
delete from roles;
delete from permissions;
delete from users;
delete from sessions;
delete from countries;
delete from usstates;
-- SET FOREIGN_KEY_CHECKS=1;
 */

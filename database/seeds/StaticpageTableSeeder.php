<?php

namespace Database\Seeders;

use Faker\Factory;
use App\StaticPage;

class StaticpageTableSeeder extends Seeder
{
    /** Run in all environments */
    protected $environments = [ 'all' ];

    public function run()
    {
        $faker = Factory::create();
        $pages = ['about'            => 'about',
                        'privacy'    => 'privacy',
                        'disclaimer' => 'disclaimer',
                        'terms'      => 'terms', ];

        foreach ($pages as $key) {
            $account = StaticPage::firstOrNew(['title' => $key]);
            $account->description = $faker->paragraph;
            $account->active = 1;
            $account->save();
        }
    }
}

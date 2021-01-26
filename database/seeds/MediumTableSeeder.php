<?php

namespace Database\Seeders;

use App\Media;

class MediumTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Populate dummy medium
        Media::factory()->count(80)->create();
    }
}

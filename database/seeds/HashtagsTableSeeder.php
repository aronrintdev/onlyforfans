<?php

namespace Database\Seeders;

use App\Hashtag;

class HashtagsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Populate dummy hashtags
        Hashtag::factory()->count(30)->create();
    }
}

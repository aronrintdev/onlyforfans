<?php
use Illuminate\Database\Seeder;

use App\Enums\MediafileTypeEnum;
use App\Libs\FactoryHelpers;
use App\Story;
use App\User;

class StoriesTableSeeder extends Seeder
{

    public function run()
    {
        $this->command->info('Running Seeder: StoriesTableSeeder...');
        $faker = \Faker\Factory::create();

        // +++ Create ... +++

        $users = User::get();

        $users->each( function($u) use(&$faker) {

            $max = $faker->numberBetween(3,12);
            $this->command->info("  - Creating $max stories for user ".$u->name);

            collect(range(1,$max))->each( function() use(&$faker, &$u) {

                $stype = $faker->randomElement(['text','image','image']);
                $attrs = [
                    'content'     => $faker->text,
                    'stype'       => $stype,
                    'timeline_id' => $u->timeline->id,
                ];
                $story = factory(Story::class)->create($attrs);
                switch ($stype) {
                case 'text':
                    break;
                case 'image':
                    $mf = FactoryHelpers::createImage(MediafileTypeEnum::STORY, $story->id);
                    break;
                }
            });

        });
    }

}

<?php
namespace Database\Seeders;

use App\Enums\MediaFileTypeEnum;
use App\Libs\FactoryHelpers;
use App\Story;
use App\User;

class StoriesTableSeeder extends Seeder
{
    use SeederTraits;

    public function run()
    {
        $this->initSeederTraits('StoriesTableSeeder');

        // +++ Create ... +++

        $users = User::get();

        $users->each( function($u) {

            $max = $this->faker->numberBetween(3,12);
            if ( $this->appEnv !== 'testing' ) {
                $this->output->writeln("  - Creating $max stories for user ".$u->name);
            }

            collect(range(1,$max))->each( function() use(&$u) {

                $stype = ($this->appEnv==='testing')
                    ? 'text'
                    : $this->faker->randomElement(['text','image','image']);

                $attrs = [
                    'content'     => $this->faker->text,
                    'stype'       => $stype,
                    'timeline_id' => $u->timeline->id,
                ];
                $story = Story::factory()->create($attrs);
                switch ($stype) {
                case 'text':
                    break;
                case 'image':
                    $mf = FactoryHelpers::createImage(MediaFileTypeEnum::STORY, $story->id);
                    break;
                }
            });

        });
    }

}

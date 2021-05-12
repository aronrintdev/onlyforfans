<?php
namespace Database\Seeders;

use Exception;
use App\Enums\MediafileTypeEnum;
use App\Libs\FactoryHelpers;
use App\Models\Story;
use App\Models\User;
use App\Enums\StoryTypeEnum;

class StoriesTableSeeder extends Seeder
{
    use SeederTraits;

    protected $doS3Upload = false;

    public function run()
    {
        $this->initSeederTraits('StoriesTableSeeder');

        // +++ Create ... +++

        $users = User::get();

        if ( $this->appEnv !== 'testing' ) {
            $this->output->writeln("  - Users seeder: loaded ".$users->count()." users...");
        }

        $this->doS3Upload = ( $this->appEnv !== 'testing' );

        $users->each( function($u) {

            static $iter = 1;

            $count = $this->faker->numberBetween(3,12);
            if ( $this->appEnv !== 'testing' ) {
                $this->output->writeln("  - Creating $count stories for user ".$u->name." (iter $iter)");
            }

            collect(range(1,$count))->each( function() use(&$u) {

                $stype = ($this->appEnv==='testing')
                    ? StoryTypeEnum::TEXT
                    : $this->faker->randomElement([StoryTypeEnum::TEXT, StoryTypeEnum::PHOTO, StoryTypeEnum::PHOTO]);
                    //: $this->faker->randomElement(['text','image','image']);

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
                    $mf = FactoryHelpers::createImage(MediafileTypeEnum::STORY, $story->id, $this->doS3Upload);
                    break;
                }
            });

            $iter++;

        });
    }

}

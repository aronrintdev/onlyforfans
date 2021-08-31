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
            $this->output->writeln("  - Stories seeder: loaded ".$users->count()." users...");
        }

        $this->doS3Upload = ( $this->appEnv !== 'testing' );

        $users->each( function($u) {

            static $iter = 1;

            $count = $this->faker->numberBetween(3,12);
            if ( $this->appEnv !== 'testing' ) {
                $this->output->writeln("  - Creating $count stories for user ".$u->name." (iter $iter)");
            }

            collect(range(1,$count))->each( function() use(&$u) {

                /*
                $stype = ($this->appEnv==='testing')
                    ? StoryTypeEnum::TEXT
                    : $this->faker->randomElement([StoryTypeEnum::TEXT, StoryTypeEnum::PHOTO, StoryTypeEnum::PHOTO]);
                    //: $this->faker->randomElement(['text','image','image']);
                 */
                if ( $this->appEnv==='testing' ) {
                    $stype = $this->faker->randomElement([
                        StoryTypeEnum::TEXT, 
                        StoryTypeEnum::TEXT, 
                        StoryTypeEnum::TEXT, 
                        StoryTypeEnum::PHOTO,
                    ]);
                } else {
                    $stype = $this->faker->randomElement([
                        StoryTypeEnum::TEXT, 
                        StoryTypeEnum::PHOTO, 
                        StoryTypeEnum::PHOTO,
                    ]);
                }

                $attrs = [
                    'content'     => $this->faker->text,
                    'stype'       => $stype,
                    'timeline_id' => $u->timeline->id,
                ];
                $story = Story::create($attrs);

                // update to 'realistic' timestamps...
                $ts = $this->faker->dateTimeBetween($startDate='-3 months', $endDate='now');
                $story->created_at = $ts;
                $story->updated_at = $ts;
                $story->save();
                $story->storyqueues->each( function($sq) use($ts) {
                    $sq->created_at = $ts;
                    $sq->updated_at = $ts;
                    $sq->save();
                });

                switch ($stype) {
                case 'text':
                    break;
                case 'image':
                    try { 
                        $mf = FactoryHelpers::createImage(
                            $story->getPrimaryOwner(),
                            MediafileTypeEnum::STORY, 
                            $story->id, 
                            $this->doS3Upload
                        );
                    } catch (Exception $e) {
                        if ( $this->appEnv !== 'testing' ) {
                            $this->output->writeln("  - Could not create fake media for story id ".$story->id.", skipping - ".$e->getMessage() );
                        }
                    }
                    break;
                }
            });

            $iter++;

        });
    }

}

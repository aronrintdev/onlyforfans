<?php
namespace Database\Seeders;

use DB;
use Exception;
use App\Libs\FactoryHelpers;
use App\Models\Contenttag;
use App\Models\Mediafile;
use App\Models\Post;
use App\Models\Story;
use App\Models\Vaultfolder;
use App\Models\User;

class ContenttagsTableSeeder extends Seeder
{
    use SeederTraits;

    private $tagSet = [];

    // create a unique set of 'meaningful' tags to use (not lorem ipsum)
    private function buildSet() 
    {
        for ( $i = 0 ; $i < 50 ; $i++ ) {
            $str = $this->faker->catchPhrase();
            $str .= ' '.$this->faker->bs();
            $words = explode(' ', strtolower($str));
            $this->tagSet = array_unique(array_merge($this->tagSet, $words));
        }
    }

    public function run()
    {
        $this->initSeederTraits('ContenttagsTableSeeder');

        $this->buildSet();

        if ( $this->appEnv !== 'testing' ) {
            $this->output->writeln("  - Deleting existing tags...");
        }
        DB::table('contenttaggables')->delete();
        DB::table('contenttags')->delete();

        // +++ Create ... +++

        $mediafiles = Mediafile::get();
        $posts = Post::get();
        $stories = Story::get();
        $vaultfolders = Vaultfolder::get();

        if ( $this->appEnv !== 'testing' ) {
            $this->output->writeln("  - Contenttags seeder: loaded ...");
        }

        // ---

        if ( $this->appEnv !== 'testing' ) {
            $this->output->writeln("     # mediafiles: ".$mediafiles->count() );
        }
        $mediafiles->each( function($o) {
            static $iter = 1;
            $hasTags = $this->faker->boolean(70);
            if (!$hasTags) {
                return false;
            }
            $tagCnt = $this->faker->numberBetween(1,5);
            for ( $i = 0 ; $i < $tagCnt; $i++ ) {
                $tag = $this->faker->randomElement($this->tagSet);
                $o->addTag($tag);
            }
        });


        // ---

        /*

        if ( $this->appEnv !== 'testing' ) {
            $this->output->writeln("     # posts: ".$posts->count() );
        }
        $posts->each( function($o) {
            static $iter = 1;
        });

        if ( $this->appEnv !== 'testing' ) {
            $this->output->writeln("     # stories: ".$stories->count() );
        }
        $stories->each( function($o) {
            static $iter = 1;
        });

        if ( $this->appEnv !== 'testing' ) {
            $this->output->writeln("     # vaultfolders: ".$vaultfolders->count() );
        }
        $vaultfolders->each( function($o) {
            static $iter = 1;
        });


            $count = $this->faker->numberBetween(3,12);

            collect(range(1,$count))->each( function() use(&$u) {

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
                    $mf = FactoryHelpers::createImage(
                        $story->getPrimaryOwner(),
                        MediafileTypeEnum::STORY, 
                        $story->id, 
                        $this->doS3Upload
                    );
                    break;
                }
            });

            $iter++;

        });
*/
    }

}

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

    // used as callback in collection's each()
    private $fAddTags;

    public function run()
    {
        $this->initSeederTraits('ContenttagsTableSeeder');

        $this->fAddTags = function($o) {
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
        };

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

        // -- Mediafiles --

        if ( $this->appEnv !== 'testing' ) {
            $this->output->writeln("     # mediafiles: ".$mediafiles->count() );
        }
        $mediafiles->each( $this->fAddTags );

        // -- Posts --
        if ( $this->appEnv !== 'testing' ) {
            $this->output->writeln("     # posts: ".$posts->count() );
        }
        $posts->each( $this->fAddTags );

        // -- Stories --
        if ( $this->appEnv !== 'testing' ) {
            $this->output->writeln("     # stories: ".$stories->count() );
        }
        $stories->each( $this->fAddTags );

        // -- Vaultfolders --
        if ( $this->appEnv !== 'testing' ) {
            $this->output->writeln("     # vaultfolders: ".$vaultfolders->count() );
        }
        $vaultfolders->each( $this->fAddTags );

    }

}

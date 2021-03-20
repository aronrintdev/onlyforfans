<?php
namespace Database\Seeders;

use DB;
use Exception;
use Carbon\Carbon;
use App\Models\Post;
use App\Models\User;
//use App\Libs\UserMgr;
use App\Models\Bookmark;
use App\Libs\UuidGenerator;
use App\Libs\FactoryHelpers;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Output\ConsoleOutput;

class BookmarksTableSeeder extends Seeder
{
    use SeederTraits;

    public function run()
    {
        $this->initSeederTraits('BookmarksTableSeeder'); // $this->{output, faker, appEnv}

        // +++ Create ... +++

        $posts = Post::get();

        if ( $this->appEnv !== 'testing' ) {
            $this->output->writeln("  - Bookmarks seeder: loaded ".$posts->count()." posts...");
        }

        $max = intVal($posts->count() / 3); // do 33%

        $posts->take($max)->each( function($p) {

            static $iter = 1;

            // --- user pool ---

            $userPool = User::where('id', '<>', $p->user->id)->get(); // exclude post owner
            $ownerPool = $userPool;
            unset($userPool);

            // --- create some bookmarks ---

            $max = $this->faker->numberBetween( 1, min($ownerPool->count()-1, 5) );
            if ( $this->appEnv !== 'testing' ) {
                $this->output->writeln("  - Creating $max bookmarks for post ".$p->slug.", iter: $iter");
            }

            $ownerPool->random($max)->each( function($o) use(&$p) {
                $customAttributes = [ 'notes' => 'BookmarksTableSeeder.bookmark_some_posts' ];
                $bookmark = Bookmark::create([
                    'user_id' => $o->id,
                    'bookmarkable_type' => 'posts',
                    'bookmarkable_id' => $p->id,
                    'cattrs' => json_encode($customAttributes),
                ]);
            });

            $iter++;
        });

    } // run()

}

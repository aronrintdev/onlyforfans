<?php
namespace Database\Seeders;

use DB;
use Exception;
use Carbon\Carbon;
use App\Models\Post;
use App\Models\User;
//use App\Libs\UserMgr;
use App\Models\Favorite;
use App\Libs\UuidGenerator;
use App\Libs\FactoryHelpers;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Output\ConsoleOutput;

class FavoritesTableSeeder extends Seeder
{
    use SeederTraits;

    public function run()
    {
        $this->initSeederTraits('FavoritesTableSeeder'); // $this->{output, faker, appEnv}

        // +++ Create ... +++

        $posts = Post::get();

        if ( $this->appEnv !== 'testing' ) {
            $this->output->writeln("  - Favorites seeder: loaded ".$posts->count()." posts...");
        }

        $max = intVal($posts->count() / 3); // do 33%

        $posts->take($max)->each( function($p) {

            static $iter = 1;

            // --- user pool ---

            $userPool = User::where('id', '<>', $p->user->id)->get(); // exclude post owner
            $ownerPool = $userPool;
            unset($userPool);

            // --- create some favorites ---

            $max = $this->faker->numberBetween( 1, min($ownerPool->count()-1, 3) );
            if ( $this->appEnv !== 'testing' ) {
                $this->output->writeln("  - Creating $max favorites for post ".$p->slug.", iter: $iter");
            }

            $ownerPool->take($max)->each( function($o) use(&$p) { // use 'take' so it's always the same users
                $customAttributes = [ 'notes' => 'FavoritesTableSeeder.favorite_some_posts' ];
                $favorite = Favorite::create([
                    'user_id' => $o->id,
                    'favoritable_type' => 'posts',
                    'favoritable_id' => $p->id,
                    'cattrs' => json_encode($customAttributes),
                ]);
            });

            $iter++;
        });

    } // run()

}

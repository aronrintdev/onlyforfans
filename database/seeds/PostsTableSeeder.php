<?php
namespace Database\Seeders;

use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

use App\User;
use App\Post;
use App\Comment;
use App\Enums\MediafileTypeEnum;
use App\Enums\PostTypeEnum;
use App\Libs\FactoryHelpers;

class PostsTableSeeder extends Seeder
{
    private static $PROB_POST_HAS_IMAGE = 70; // 70, 1;
    private $output;

    public function run()
    {
        $this->output = new ConsoleOutput();

        $appEnv = Config::get('app.env');
        switch ($appEnv) {
            case 'testing':
                self::$PROB_POST_HAS_IMAGE = 0;
                $MAX_POST_COUNT = 5;
                break;
            case 'local':
                self::$PROB_POST_HAS_IMAGE = 70;
                $MAX_POST_COUNT = 20;
                break;
            default:
                self::$PROB_POST_HAS_IMAGE = 70;
                $MAX_POST_COUNT = 20;
        }

        $this->output->writeln('Running Seeder: PostsTableSeeder, env: '.$appEnv.', #: '.$MAX_POST_COUNT.' ...');

        $faker = \Faker\Factory::create();

        // +++ Create ... +++

        $users = User::get();

        $users->each( function($u) use(&$faker, &$users, $MAX_POST_COUNT) {

            // $u is the user who will own the post being created (ie, as well as timeline associated with the post)...

            $max = $faker->numberBetween(2,$MAX_POST_COUNT);
            $this->output->writeln("  - Creating $max posts for user ".$u->name);

            collect(range(1,$max))->each( function() use(&$faker, &$users, &$u) {

                $ptype = $faker->randomElement([
                    PostTypeEnum::SUBSCRIBER,
                    PostTypeEnum::PRICED,
                    PostTypeEnum::FREE,
                    PostTypeEnum::FREE,
                    PostTypeEnum::FREE,
                ]);
                $attrs = [
                    'description'  => $faker->text.' ('.$ptype.')',
                    'user_id'      => $u->id,
                    'timeline_id'  => $u->timeline->id,
                    'type'         => $ptype,
                ];

                if ( $ptype === PostTypeEnum::PRICED ) {
                    $attrs['price'] = $faker->randomFloat(2, 1, 300);
                }

                $post = Post::factory()->create($attrs);
                if ( $faker->boolean(self::$PROB_POST_HAS_IMAGE) ) { // % post has image
                    $mf = FactoryHelpers::createImage(MediafileTypeEnum::POST, $post->id);
                }

                // Set a realistic post date
                $ts = $faker->dateTimeThisDecade->format('Y-m-d H:i:s');
                \DB::table('posts')->where('id',$post->id)->update([
                    'created_at' => Carbon::parse($ts),
                    //'description' => 'foo',
                ]);

                /*
                // LIKES - Select random users to like this post...
                $likers = FactoryHelpers::parseRandomSubset($users, 20);
                $likee = $u;
                $likers->each( function($liker) use(&$post) {
                    if ( !$post->users_liked->contains($liker->id) ) {
                        $post->users_liked()->attach($liker->id);
                        //$post->notifications_user()->attach($liker->id);
                    }
                });

                // COMMENTS - Select random users to comment on this post...
                $commenters = FactoryHelpers::parseRandomSubset($users, 12);
                $likee = $u;
                $commenters->each( function($commenter) use(&$faker, &$post) {
                    $comment = Comment::create([
                        'post_id'     => $post->id,
                        'description' => $faker->realText( $faker->numberBetween(20,200) ),
                        'user_id'     => $commenter->id,
                        'parent_id'   => null, // %TODO: nested comments
                    ]);
                });

                // SAVES - Select random users to save this post...
                $savers = FactoryHelpers::parseRandomSubset($users, 10);
                $savee = $u;
                $savers->each( function($saver) use(&$post) {
                    if ( !$post->usersSaved->contains($saver->id) ) {
                        $post->usersSaved()->attach($saver->id);
                    }
                });

                // SHARES - Select random users to share this post with (on their timeline)...
                $sharers = FactoryHelpers::parseRandomSubset($users, 10);
                $sharee = $u;
                $sharers->each( function($sharer) use(&$post, &$sharee) {
                    if ( !$post->sharees->contains($sharee->id) ) {
                        $post->sharees()->attach($sharee->id);
                    }
                });

                // PINS - Select random users to pin this post (on their timeline ?)...
                $pinners = FactoryHelpers::parseRandomSubset($users, 7);
                $pinnee = $u;
                $pinners->each( function($pinner) use(&$post) {
                    if ( !$post->usersPinned->contains($pinner->id) ) {
                        $post->usersPinned()->attach($pinner->id);
                    }
                });
                 */

            });

        });
    }
}

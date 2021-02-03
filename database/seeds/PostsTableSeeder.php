<?php
namespace Database\Seeders;

use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

use App\User;
use App\Post;
use App\Enums\MediafileTypeEnum;
use App\Enums\PostTypeEnum;
use App\Libs\FactoryHelpers;

class PostsTableSeeder extends Seeder
{
    use SeederTraits;

    public function run()
    {
        $this->initSeederTraits('PostsTableSeeder'); // $this->{output, faker, appEnv}

        // +++ Create ... +++

        $users = User::get();

        $users->each( function($u) use(&$users) {

            // $u is the user who will own the post being created (ie, as well as timeline associated with the post)...

            $max = $this->faker->numberBetween(2, $this->getMax('posts'));

            if ( $this->appEnv !== 'testing' ) {
                $this->output->writeln("  - Creating $max posts for user ".$u->name);
            }

            collect(range(1,$max))->each( function() use(&$users, &$u) { // Post generation loop

                $ptype = $this->faker->randomElement([
                    PostTypeEnum::SUBSCRIBER,
                    PostTypeEnum::PRICED,
                    PostTypeEnum::FREE, PostTypeEnum::FREE, PostTypeEnum::FREE, 
                ]);
                $attrs = [
                    'description'  => $this->faker->text.' ('.$ptype.')',
                    'user_id'      => $u->id,
                    'timeline_id'  => $u->timeline->id,
                    'type'         => $ptype,
                ];

                if ( $ptype === PostTypeEnum::PRICED ) {
                    $attrs['price'] = $this->faker->randomFloat(2, 1, 300);
                }

                $post = Post::factory()->create($attrs);
                if ( $this->faker->boolean($this->getMax('prob_post_has_image')) ) { // % post has image
                    $mf = FactoryHelpers::createImage(MediafileTypeEnum::POST, $post->id);
                }

                // Set a realistic post date
                $ts = $this->faker->dateTimeThisDecade->format('Y-m-d H:i:s');
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

    private function getMax($param) : int
    {
        static $max = [
            'testing' => [
                'prob_post_has_image' => 0,
                'users' => 4,
                'posts' => 3,
            ],
            'local' => [
                'prob_post_has_image' => 70, // will create image and store in S3 (!)
                'users' => 10,
                'posts' => 20,
            ],
        ];

        return $max[$this->appEnv][$param];
    }

}

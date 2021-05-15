<?php
namespace Database\Seeders;

use DB;
use Exception;
use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Post;
use App\Enums\MediafileTypeEnum;
use App\Enums\PostTypeEnum;
use App\Libs\FactoryHelpers;

class PostsTableSeeder extends Seeder
{
    use SeederTraits;

    protected static $MIN_POSTS = 4;
    protected static $IMAGE_SIZES = [
        [ 'width' => 320, 'height' => 180 ], // 16:9
        [ 'width' => 320, 'height' => 240 ], // 4:3
        [ 'width' => 300, 'height' => 200], // 3:2
        [ 'width' => 200, 'height' => 300], // 2:3
        [ 'width' => 300, 'height' => 300], // 1:1
    ];

    protected $doS3Upload = false;

    public function run()
    {
        $this->initSeederTraits('PostsTableSeeder'); // $this->{output, faker, appEnv}

        Mail::fake();

        $this->doS3Upload = ( $this->appEnv !== 'testing' );

        // +++ Create ... +++

        $users = User::get();

        $users->each( function($u) use(&$users) {

            static $iter = 1;

            // $u is the user who will own the post being created (ie, as well as timeline associated with the post)...

            $count = $this->faker->numberBetween(self::$MIN_POSTS, $this->getMax('posts'));

            if ( $this->appEnv !== 'testing' ) {
                $this->output->writeln("  - Creating $count posts for user ".$u->name." (iter: $iter)");
            }

            collect(range(0,$count))->each( function() use(&$users, &$u) { // Post generation loop

                static $typesUsed = []; // guarantee one of each post type
                $ptype = $this->faker->randomElement([
                    PostTypeEnum::SUBSCRIBER, PostTypeEnum::SUBSCRIBER,
                    PostTypeEnum::PRICED, PostTypeEnum::PRICED,
                    PostTypeEnum::FREE,
                ]);
                $diff = array_diff( PostTypeEnum::getKeys(), $typesUsed );
                if ( count($diff) ) {
                    $ptype = array_pop($diff);
                }
                $typesUsed[] = $ptype;
                /*
                dump(
                    'typesUsed',
                    $typesUsed, 
                    'keys',
                    PostTypeEnum::getKeys(),
                    'diff',
                    $diff,
                    '-------------'
                );
                 */

                $ts = $this->faker->dateTimeBetween($startDate = '-5 years', $endDate = 'now');

                $attrs = [
                    'postable_type' => 'timelines',
                    'postable_id'   => $u->timeline->id,
                    'description'   => $this->faker->text.' ('.$ptype.')',
                    'user_id'       => $u->id,
                    'type'          => $ptype,
                    'created_at'    => $ts,
                    'updated_at'    => $ts,
                ];

                if ( $ptype === PostTypeEnum::PRICED ) {
                    $attrs['price'] = $this->faker->randomFloat(0, 100, 1000);
                }

                $post = Post::factory()->create($attrs);
                //$u->timeline->posts()->save($post);

                if ( $this->faker->boolean($this->getMax('prob_post_has_image')) ) { // % post has image
                    $numberOfImages = $this->faker->randomElement([1,1,1,2,3]); // multiple images per post
                    for ( $i = 0 ; $i < $numberOfImages ; $i++ ) {
                        $imgDim = $this->faker->randomElement(self::$IMAGE_SIZES);
                        $mf = FactoryHelpers::createImage(
                            $post->getPrimaryOwner(),
                            MediafileTypeEnum::POST,  // mftype
                            $post->id,  // resourceID
                            $this->doS3Upload, // true, // doS3Upload
                            [ 'width' => $imgDim['width'], 'height' => $imgDim['height'] ] // attrs
                        );
                    }
                }

                // Set a realistic post date
                $ts = $this->faker->dateTimeThisDecade->format('Y-m-d H:i:s');
                DB::table('posts')->where('id',$post->id)->update([
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

            $iter++;
        });
    }

    private function getMax($param) : int
    {
        static $max = [
            'testing' => [
                'prob_post_has_image' => 30, // 0,
                'users' => 4,
                'posts' => 7,
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

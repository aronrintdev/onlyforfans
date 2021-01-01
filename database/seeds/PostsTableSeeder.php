<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

use App\User;
use App\Post;
use App\Comment;
use App\Enums\MediafileTypeEnum;
use App\Enums\PostTypeEnum;
use App\Libs\FactoryHelpers;

class PostsTableSeeder extends Seeder
{
    private static $PROB_POST_HAS_IMAGE = 1; // 70;

    public function run()
    {
        $this->command->info('Running Seeder: PostsTableSeeder...');
        $faker = \Faker\Factory::create();

        // +++ Create ... +++

        $users = User::get();

        $users->each( function($u) use(&$faker, &$users) {

            $max = $faker->numberBetween(2,20);
            $this->command->info("  - Creating $max posts for user ".$u->name);

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

                $post = factory(Post::class)->create($attrs);
                if ( $faker->boolean(self::$PROB_POST_HAS_IMAGE) ) { // % post has image
                    $mf = FactoryHelpers::createImage(MediafileTypeEnum::POST, $post->id);
                }

                // Set a realistic post date
                $ts = $faker->dateTimeThisDecade->format('Y-m-d H:i:s');
                //$ts = $ts->format('Y-m-d H:i:s');
                //dd($ts);
                \DB::table('posts')->where('id',$post->id)->update([
                    'created_at' => Carbon::parse($ts),
                    //'description' => 'foo',
                ]);

                // LIKES - Select random users to like this post...
                $likers = FactoryHelpers::parseRandomSubset($users, 10);
                $likee = $u;
                $likers->each( function($liker) use(&$post) {
                    if ( !$post->users_liked->contains($liker->id) ) {
                        $post->users_liked()->attach($liker->id);
                        $post->notifications_user()->attach($liker->id);
                    }
                });

                // COMMENTS - Select random users to comment on this post...
                $commenters = FactoryHelpers::parseRandomSubset($users, 9);
                $likee = $u;
                $commenters->each( function($commenter) use(&$faker, &$post) {
                    $comment = Comment::create([
                        'post_id'     => $post->id,
                        'description' => $faker->realText( $faker->numberBetween(20,200) ),
                        'user_id'     => $commenter->id,
                        'parent_id'   => null, // %TODO: nested comments
                    ]);
                });

            });

        });
    }



    /*
    private function oldSeeder() 
    {
        //Populate dummy posts
        factory(Post::class, 260)->create();

        //Seeding post follows
        $faker = Faker\Factory::create();
        $posts = Post::all();

        foreach ($posts as $post) {
            $follows = $faker->randomElements(['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33', '34', '35', '36', '37', '38'], $faker->numberBetween(1, 3));

            $post->notifications_user()->sync($follows);
        }

        //Seeding post likes
        foreach ($posts as $post) {
            $likes = $faker->randomElements(['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33', '34', '35', '36', '37', '38'], $faker->numberBetween(1, 3));

            $post->users_liked()->sync($likes);
        }

        //Seeding post media
        foreach ($posts as $post) {
            $media = $faker->randomElements(['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33', '34', '35', '36', '37', '38'], $faker->numberBetween(1, 3));

            $post->images()->sync($media);
        }

        //Seeding post shares
        foreach ($posts as $post) {
            $shares = $faker->randomElements(['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33', '34', '35', '36', '37', '38'], $faker->numberBetween(1, 3));

            $post->users_shared()->sync($shares);
        }

        //Seeding post reports
        // foreach ($posts as $post) {

        //     $reports = $faker->randomElements(array ('1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31','32','33','34','35','36','37','38'), $faker->numberBetween(1,3));

        //     $syncReports = array();
        //     foreach ($reports as $key => $value) {
        //         $syncReports[$value]  = array('status'=> 'approved');
        //     }

        //     $post->reports()->sync($syncReports);

        // }
    }
     */
}

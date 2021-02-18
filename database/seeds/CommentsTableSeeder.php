<?php
namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use App\Libs\FactoryHelpers;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

class CommentsTableSeeder extends Seeder
{
    use SeederTraits;

    public function run()
    {
        $this->initSeederTraits('CommentsTableSeeder'); // $this->{output, faker, appEnv}

        $posts = Post::get();

        $posts->each( function($post) { // all posts will have comments

            // COMMENTS - Select random users to comment on this post...

            $users = $post->timeline->followers;
            if ( count($users) < 1 ) {
                return;
            }
            $numUsers = $this->faker->numberBetween( 1, min($users->count()-1, $this->getMax('users')) );
            $commenters = FactoryHelpers::parseRandomSubset($users, $numUsers);

             $commenters->each( function($commenter) use(&$post) {
                 $numComments = $this->faker->numberBetween( 1, $this->getMax('comments') );
                 collect(range(1,$numComments))->each( function() use(&$post, &$commenter) { // Comment generation loop
                     $comment = Comment::create([
                         'commentable_id'     => $post->id,
                         'commentable_type'   => 'posts',
                         'description' => $this->faker->realText( $this->faker->numberBetween(20,200) ),
                         'user_id'     => $commenter->id,
                         //'parent_id'   => null, // %TODO: nested comments
                     ]);
                 });
             });
        });
    }

    private function getMax($param) : int
    {
        static $max = [
            'testing' => [
                'users' => 4,
                'comments' => 3,
            ],
            'local' => [
                'users' => 10,
                'comments' => 3,
            ],
        ];
        return $max[$this->appEnv][$param];
    }

}

<?php
namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use App\Libs\FactoryHelpers;
use App\Models\Notification;
use App\Models\Timeline;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Likeable;
use App\Models\Shareable;

class RandomTimestampSeeder extends Seeder
{
    use SeederTraits;

    public function run()
    {
        $this->initSeederTraits('RandomTimestampSeeder'); // $this->{output, faker, appEnv}

        // [timelines]
        $timelines = Timeline::get();
        $max = $timelines->count();
        $timelines->each( function($t) use($max) {
            static $iter = 1;
            $this->output->writeln("  - Updating timelines timestamps $iter / $max");
            $ts = $this->faker->dateTimeBetween($startDate = '-5 years', $endDate = '-1 years');
            $t->created_at = $ts;
            $t->updated_at = $ts;
            $t->save();

            // [stories]
            $this->output->writeln("    ~ Updating timeline's stories ...");
            $t->stories->each( function($s) use($ts) {
                $ts2 = $this->faker->dateTimeBetween($ts, $endDate = 'now');
                $s->created_at = $ts2;
                $s->updated_at = $ts2;
                $s->save();
            });
            $iter++;
        });

        // [notifications]
        $notifications = Notification::get();
        $max = $notifications->count();
        $notifications->each( function($n) use($max) {
            static $iter = 1;
            if ( $this->appEnv !== 'testing' ) {
                $this->output->writeln("  - Updating notification timestamps $iter / $max");
            }
            $ts = $this->faker->dateTimeBetween($startDate = '-5 years', $endDate = 'now');
            $n->created_at = $ts;
            $n->updated_at = $ts;
            $n->save();
            $iter++;
        });

        // [likeables]
        $likeables = Likeable::get();
        $max = $likeables->count();
        $likeables->each( function($l) use($max) {
            static $iter = 1;
            if ( $this->appEnv !== 'testing' ) {
                $this->output->writeln("  - Updating likeables timestamps $iter / $max");
            }
            $ts = $this->faker->dateTimeBetween($startDate = '-5 years', $endDate = 'now');
            $l->created_at = $ts;
            $l->updated_at = $ts;
            $l->save();
            $iter++;
        });

        // [shareables]
        $shareables = Shareable::get();
        $max = $shareables->count();
        $shareables->each( function($s) use($max) {
            static $iter = 1;
            if ( $this->appEnv !== 'testing' ) {
                $this->output->writeln("  - Updating shareables timestamps $iter / $max");
            }
            $ts = $this->faker->dateTimeBetween($startDate = '-5 years', $endDate = 'now');
            $s->created_at = $ts;
            $s->updated_at = $ts;
            $s->save();
            $iter++;
        });

        // [posts]
        $posts = Post::get();
        $max = $posts->count();
        $posts->each( function($p) use($max) {
            static $iter = 1;
            if ( $this->appEnv !== 'testing' ) {
                $this->output->writeln("  - Updating posts timestamps $iter / $max");
            }
            $ts = $this->faker->dateTimeBetween($startDate = '-5 years', $endDate = 'now');
            $p->created_at = $ts;
            $p->updated_at = $ts;
            $p->save();
            $iter++;
        });

        // [comments]
        $comments = Shareable::get();
        $max = $comments->count();
        $comments->each( function($c) use($max) {
            static $iter = 1;
            if ( $this->appEnv !== 'testing' ) {
                $this->output->writeln("  - Updating comments timestamps $iter / $max");
            }
            $ts = $this->faker->dateTimeBetween($startDate = '-5 years', $endDate = 'now');
            $c->created_at = $ts;
            $c->updated_at = $ts;
            $c->save();
            $iter++;
        });
    }

}

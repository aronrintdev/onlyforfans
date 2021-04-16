<?php
namespace Database\Seeders;

use DB;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Models\Post;
use App\Models\User;
use App\Models\Timeline;
use App\Enums\PostTypeEnum;
use App\Libs\UuidGenerator;
use App\Libs\FactoryHelpers;
use App\Enums\PaymentTypeEnum;
use App\Enums\MediafileTypeEnum;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Output\ConsoleOutput;
use App\Notifications\ResourceLiked;

class LikeablesTableSeeder extends Seeder
{
    use SeederTraits;

    public function run()
    {
        $this->initSeederTraits('LikeablesTableSeeder');

        Mail::fake();

        // +++ Create ... +++

        $timelines = Timeline::get();

        if ( $this->appEnv !== 'testing' ) {
            $this->output->writeln("  - Likeables seeder: loaded ".$timelines->count()." timelines...");
        }

        // Remove a few timelines so we have some without any likes for testing...
        $timelines->pop();
        $timelines->pop();
        $timelines->pop();

        $timelines->each( function($t) {

            static $iter = 1;

            // --- user pool ---

            $followers = $t->followers;

            if ( $followers->count() > 3 ) { // remove a few for testing
                $followers->pop();
                $followers->pop();
            }

            $followers->each( function($f) use(&$t) {
                $max = min($t->posts->count()-1, 9);
                $now = \Carbon\Carbon::now();
                $t->posts->random($max)->each( function($p) use(&$f, $now) {
                    DB::table('likeables')->insert([
                        'id' => Str::uuid(),
                        'likee_id' => $f->id,
                        'likeable_type' => 'posts',
                        'likeable_id' => $p->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $p->user->notify(new ResourceLiked($p, $f));
                });
            });

            $iter++;
        });

    } // run()
}

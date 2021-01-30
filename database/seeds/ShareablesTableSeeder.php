<?php
namespace Database\Seeders;

use Exception;
use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

use App\User;
use App\Post;
use App\Timeline;
use App\Enums\PostTypeEnum;
use App\Enums\PaymentTypeEnum;
use App\Libs\FactoryHelpers;
use App\Libs\UserMgr;

class ShareablesTableSeeder extends Seeder
{
    public function run()
    {
        $this->output = new ConsoleOutput();

        $appEnv = Config::get('app.env');
        $MAX = [];
        switch ($appEnv) {
            case 'testing':
                $MAX['PURCHASED_COUNT'] = 3;
                $MAX['FOLLOWER_COUNT'] = 3;
                $MAX['SUBSCRIBER_COUNT'] = 3;
                break;
            case 'local':
                $MAX['PURCHASED_COUNT'] = 3;
                $MAX['FOLLOWER_COUNT'] = 3;
                $MAX['SUBSCRIBER_COUNT'] = 3;
                break;
            default:
                $MAX['PURCHASED_COUNT'] = 3;
                $MAX['FOLLOWER_COUNT'] = 3;
                $MAX['SUBSCRIBER_COUNT'] = 3;
        }

        if ( $appEnv !== 'testing' ) {
            $this->output->writeln('Running Seeder: ShareablesTableSeeder...');
        }

        $faker = \Faker\Factory::create();

        // +++ Create ... +++

        $followers = User::get();
        $followers->each( function($f) use(&$faker, $MAX) {

            // --- purchase some posts ---

            $purchaseablePosts = Post::where('type', PostTypeEnum::PRICED)
                ->where('timeline_id', '<>', $f->timeline->id) // exclude my own
                ->get();
            $max = $faker->numberBetween( 0, min($purchaseablePosts->count()-1, $MAX['PURCHASED_COUNT']) );
            $this->command->info("  - Creating $max purchased-posts for user ".$f->name);
            $purchaseablePosts->random($max)->each( function($p) use(&$f) {
                $p->receivePayment(
                    PaymentTypeEnum::PURCHASE, // PaymentTypeEnum
                    $f, // User $sender | follower | purchaser
                    $p->price*100, // int $amountInCents
                    [ 'notes' => 'ShareablesTableSeeder' ],
                );
            });

            // --- follow some free timelines ---

            $timelines = Timeline::where('id', '<>', $f->timeline->id) // exclude my own
                ->whereHas('User', function($q1) {
                    $q1->where('is_follow_for_free', 1);
                })->get();
            if ( $timelines->count() == 0 ) {
                throw new Exception('No free timelines found, please adjust user/timeline seeder and/or factory');
            }
            $max = $faker->numberBetween( 0, min($timelines->count()-1, $MAX['FOLLOWER_COUNT']) );
            $this->command->info("  - Following (default) $max timelines for user ".$f->name);
            $attrs = ['is_subscribe' => 0];
            $timelines->random($max)->each( function($t) use(&$f, $attrs) {
                $response = UserMgr::toggleFollow($f, $t, $attrs);
            });

            // --- subscribe to some timelines ---

            unset($timelines);
            $timelines = Timeline::where('id', '<>', $f->timeline->id) // exclude my own
                ->whereHas('User', function($q1) {
                    $q1->where('is_follow_for_free', 0);
                })->get();
            if ( $timelines->count() == 0 ) {
                throw new Exception('No paid timelines found, please adjust user/timeline seeder and/or factory');
            }
            $groups = $timelines->split(2);

            unset($timelines);
            $timelines = $groups[0];
            $max = $faker->numberBetween( 0, min($timelines->count()-1, $MAX['SUBSCRIBER_COUNT']) );
            $this->command->info("  - Following $max premium timelines for user ".$f->name);
            $attrs = ['is_subscribe' => 0];
            $timelines->random($max)->each( function($t) use(&$f, $attrs) {
                $response = UserMgr::toggleFollow($f, $t, $attrs);
            });

            unset($timelines);
            $timelines = $groups[1];
            $max = $faker->numberBetween( 0, min($timelines->count()-1, $MAX['SUBSCRIBER_COUNT']) );
            $this->command->info("  - Subscribing to $max premium timelines for user ".$f->name);
            $attrs = ['is_subscribe' => 1];
            $timelines->random($max)->each( function($t) use(&$f, $attrs) {
                $response = UserMgr::toggleFollow($f, $t, $attrs);
            });

        });
    }

}

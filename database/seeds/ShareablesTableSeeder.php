<?php
namespace Database\Seeders;

use DB;
use Exception;
use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use App\Fanledger;
use App\Post;
use App\Timeline;
use App\User;
use App\Enums\PostTypeEnum;
use App\Enums\PaymentTypeEnum;
use App\Libs\FactoryHelpers;
use App\Libs\UserMgr;

class ShareablesTableSeeder extends Seeder
{
    use SeederTraits;

    public function run()
    {
        $this->initSeederTraits('ShareablesTableSeeder'); // $this->{output, faker, appEnv}

        // +++ Create ... +++

        $followers = User::get();

        $followers->each( function($f) {

            // --- purchase some posts ---

            $purchaseablePosts = Post::where('type', PostTypeEnum::PRICED)
                ->where('timeline_id', '<>', $f->timeline->id) // exclude my own
                ->get();
            $max = $this->faker->numberBetween( 0, min($purchaseablePosts->count()-1, $this->getMax('purchased')) );
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
            $max = $this->faker->numberBetween( 0, min($timelines->count()-1, $this->getMax('follower')) );
            $this->command->info("  - Following (default) $max timelines for user ".$f->name);
            $attrs = ['is_subscribe' => 0];
            $timelines->random($max)->each( function($t) use(&$f, $attrs) {
                DB::table('shareables')->insert([
                    'sharee_id' => $f->id,
                    'shareable_type' => 'timelines',
                    'shareable_id' => $t->id,
                    'is_approved' => 1,
                    'access_level' => 'default',
                ]);
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
            $max = $this->faker->numberBetween( 0, min($timelines->count()-1, $this->getMax('subscriber')) );
            //$this->command->info("  - Following $max premium timelines for user ".$f->name);
            $timelines->random($max)->each( function($t) use(&$f, $attrs) {
                DB::table('shareables')->insert([
                    'sharee_id' => $f->id,
                    'shareable_type' => 'timelines',
                    'shareable_id' => $t->id,
                    'is_approved' => 1,
                    'access_level' => 'default',
                ]);
            });

            unset($timelines);
            $timelines = $groups[1];
            $max = $this->faker->numberBetween( 0, min($timelines->count()-1, $this->getMax('subscriber')) );
            //$this->command->info("  - Subscribing to $max premium timelines for user ".$f->name);
            $attrs = ['is_subscribe' => 1];
            $timelines->random($max)->each( function($t) use(&$f, $attrs) {
                DB::table('shareables')->insert([
                    'sharee_id' => $f->id, // fan
                    'shareable_type' => 'timelines',
                    'shareable_id' => $t->id,
                    'is_approved' => 1,
                    'access_level' => 'premium',
                ]);
                Fanledger::create([
                    'fltype' => PaymentTypeEnum::SUBSCRIPTION,
                    'purchaser_id' => $f->id, // fan
                    'seller_id' => $t->id,
                    'purchaseable_type' => 'timelines',
                    'purchaseable_id' => $t->id,
                    'qty' => 1,
                    'base_unit_cost_in_cents' => $t->user->price*100, // %FIXME: price should be on timeline not user
                    //'total_amount' => $t->user->price*100,
                    'cattrs' => [],
                    //'is_credit' => false,
                ]);
            });

        });
    }

    private function getMax($param) : int
    {
        static $max = [
            'testing' => [
                'purchased' => 3,
                'follower' => 3,
                'subscriber' => 3,
            ],
            'local' => [
                'purchased' => 3,
                'follower' => 3,
                'subscriber' => 3,
            ],
        ];
        return $max[$this->appEnv][$param];
    }


}

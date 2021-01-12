<?php
use Illuminate\Database\Seeder;
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
        $this->command->info('Running Seeder: ShareablesTableSeeder...');
        $faker = \Faker\Factory::create();

        // +++ Create ... +++

        $followers = User::get();
        $followers->each( function($f) use(&$faker) {

            // --- purchase some posts ---

            $purchaseablePosts = Post::where('type', PostTypeEnum::PRICED)
                ->where('timeline_id', '<>', $f->timeline->id) // exclude my own
                ->get();
            $max = $faker->numberBetween( 0, min($purchaseablePosts->count()-1, 7) );
            $this->command->info("  - Creating $max purchased-posts for user ".$f->name);
            $purchaseablePosts->random($max)->each( function($p) use(&$f) {
                $p->receivePayment(
                    PaymentTypeEnum::PURCHASE, // PaymentTypeEnum
                    $f, // User $sender | follower | purchaser
                    $p->price*100, // int $amountInCents
                    [ 'notes' => 'ShareablesTableSeeder' ],
                );
            });

            // --- follow some timelines ---

            $timelinesToFollow = Timeline::where('id', '<>', $f->timeline->id) // exclude my own
                ->get();
            $max = $faker->numberBetween( 0, min($timelinesToFollow->count()-1, 3) );
            $this->command->info("  - Following (default) $max timelines for user ".$f->name);
            $attrs = ['is_subscribe' => 0];
            $timelinesToFollow->random($max)->each( function($t) use(&$f, $attrs) {
                $response = UserMgr::toggleFollow($f, $t, $attrs);
            });

            // --- subscribe to some timelines ---

        });
    }

}

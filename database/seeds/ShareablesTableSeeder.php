<?php

namespace Database\Seeders;

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

            // --- follow some free timelines ---

            $timelines = Timeline::where('id', '<>', $f->timeline->id) // exclude my own
                ->whereHas('User', function($q1) {
                    $q1->where('is_follow_for_free', 1);
                })->get();
            $max = $faker->numberBetween( 0, min($timelines->count()-1, 3) );
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
            $groups = $timelines->split(2);

            unset($timelines);
            $timelines = $groups[0];
            $max = $faker->numberBetween( 0, min($timelines->count()-1, 3) );
            $this->command->info("  - Following $max premium timelines for user ".$f->name);
            $attrs = ['is_subscribe' => 0];
            $timelines->random($max)->each( function($t) use(&$f, $attrs) {
                $response = UserMgr::toggleFollow($f, $t, $attrs);
            });

            unset($timelines);
            $timelines = $groups[1];
            $max = $faker->numberBetween( 0, min($timelines->count()-1, 3) );
            $this->command->info("  - Subscribing to $max premium timelines for user ".$f->name);
            $attrs = ['is_subscribe' => 1];
            $timelines->random($max)->each( function($t) use(&$f, $attrs) {
                $response = UserMgr::toggleFollow($f, $t, $attrs);
            });

        });
    }

}

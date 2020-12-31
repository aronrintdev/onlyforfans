<?php

use Illuminate\Database\Seeder;
use Spatie\Referer\Referer;
use App\Libs\FactoryHelpers;

use App\User;
use App\Follower;

class FollowersTableSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Running Seeder: FollowersTableSeeder...');
        $faker = \Faker\Factory::create();

        // +++ Given a user, select a set of other users to follow +++

        $users = User::get();
        $users->each( function($follower) use(&$faker, &$users) {
            $max = min([ 10, $users->count()-1  ]);
            $num = $faker->numberBetween(0,$max);
            $following = $users->random($num);

            $following->each( function($followee) use(&$follower) {
                if ( ($followee->id != $follower->id) && !$followee->followers->contains($follower->id) ) { // exclude self & current followers
                    $referer = app(Referer::class)->get();
                    $followee->followers()->attach($follower->id, ['status' => 'approved', 'referral' => $referer]);
                }
            });
        });
    }
}

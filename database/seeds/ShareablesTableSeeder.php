<?php
use Illuminate\Database\Seeder;
use Carbon\Carbon;

use App\User;
use App\Post;
use App\Enums\PostTypeEnum;
use App\Enums\PaymentTypeEnum;
use App\Libs\FactoryHelpers;

class ShareablesTableSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Running Seeder: ShareablesTableSeeder...');
        $faker = \Faker\Factory::create();

        // +++ Create ... +++

        $users = User::get();
        $users->each( function($u) use(&$faker) {

            $purchaseablePosts = Post::where('type', PostTypeEnum::PRICED)
                ->where('timeline_id', '<>', $u->timeline->id) // exclude my own
                ->get();

            $max = $faker->numberBetween( 0, min($purchaseablePosts->count()-1, 7) );

            $this->command->info("  - Creating $max purchased-posts for user ".$u->name);
            $purchaseablePosts->random($max)->each( function($p) use(&$faker, &$u) {
                $p->receivePayment(
                    PaymentTypeEnum::PURCHASE, // PaymentTypeEnum
                    $u, // User $sender
                    $p->price*100, // int $amountInCents
                    [ 'notes' => 'ShareablesTableSeeder' ],
                );
            });

        });
    }

}

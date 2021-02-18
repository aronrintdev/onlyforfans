<?php
namespace Database\Seeders;

use DB;
use Exception;
use Carbon\Carbon;
use App\Models\Post;
use App\Models\User;
use App\Libs\UserMgr;
use App\Models\Timeline;
use App\Models\Fanledger;
use App\Enums\PostTypeEnum;
use App\Libs\UuidGenerator;
use App\Libs\FactoryHelpers;
use App\Enums\PaymentTypeEnum;
use App\Enums\MediafileTypeEnum;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Output\ConsoleOutput;

class ShareablesTableSeeder extends Seeder
{
    use SeederTraits;

    public function run()
    {
        $this->initSeederTraits('ShareablesTableSeeder'); // $this->{output, faker, appEnv}

        // +++ Create ... +++

        $freeTimelines = Timeline::where('is_follow_for_free', true)->get();
        $premiumTimelines = Timeline::where('is_follow_for_free', false)->get();

        if ( $this->appEnv !== 'testing' ) {
            $this->output->writeln("  - Shareables seeder: loaded ".$freeTimelines->count()." free timelines...");
            $this->output->writeln("  - Shareables seeder: loaded ".$preimumTimelines->count()." premium timelines...");
        }

        // Remove a few timelines so we have some without any followers for testing...
        //   ~ [ ] %TODO: timelines w/ followers but not subcribers, & vice-versa
        $freeTimelines->pop();
        $freeTimelines->pop();
        $premiumTimelines->pop();
        $premiumTimelines->pop();

        $freeTimelines->each( function($t) {

            static $iter = 1;

            // --- user pool ---

            $userPool = User::where('id', '<>', $t->user->id)->get(); // exclude timeline owner
            $followerPool = $userPool;
            unset($userPool);

            // --- create some followers ---

            $max = $this->faker->numberBetween( 1, min($followerPool->count()-1, $this->getMax('follower')) );
            if ( $max < 1 ) {
                throw new Exception('Requires at least 1 follower per free timeline - max:'.$max);
            }
            $this->command->info("  - Creating $max (non-premium) followers for timeline ".$t->name.", iter: $iter");

            $followerPool->random($max)->each( function($f) use(&$t) {
                $customAttributes = [ 'notes' => 'ShareablesTableSeeder.follow_some_free_timelines' ];
                DB::table('shareables')->insert([
                    'sharee_id' => $f->id,
                    'shareable_type' => 'timelines',
                    'shareable_id' => $t->id,
                    'is_approved' => 1,
                    'access_level' => 'default',
                    'cattrs' => json_encode($customAttributes),
                ]);

                // --- purchase some posts ---

                $max = $this->faker->numberBetween( 0, $this->getMax('purchased') );
                $purchaseablePosts = $t->posts()->where('type', PostTypeEnum::PRICED)->inRandomOrder($max)->get();
                $count = $purchaseablePosts->count();
                $this->command->info("  - Purchasing $count posts for follower ".$f->name." on timeline ".$t->name);
                if ( $count > 0 ) {
                    $purchaseablePosts->each( function($p) use(&$f) {
                        $customAttributes = [ 'notes' => 'ShareablesTableSeeder.purchase_some_posts' ];
                        // %FIXME: this should also update shareables, and be encapsualted in a method in model or class-lib
                        DB::table('shareables')->insert([
                            'sharee_id' => $f->id,
                            'shareable_type' => 'posts',
                            'shareable_id' => $p->id,
                            'is_approved' => 1,
                            'access_level' => 'premium', // ??
                            'cattrs' => json_encode($customAttributes),
                        ]);
                        $p->receivePayment(
                            PaymentTypeEnum::PURCHASE, // PaymentTypeEnum
                            $f, // User $sender | follower | purchaser
                            $p->price, // int $amountInCents
                            $customAttributes,
                        );
                    });
                }

            });

            $iter++;
        });

        $premiumTimelines->each( function($t) {

            static $iter = 1;

            // --- user pool ---

            $userPool = User::where('id', '<>', $t->user->id)->get();
            $groups = $userPool->split(2);
            unset($userPool);
            $followerPool = $groups[0];
            $subscriberPool = $groups[0];

            // --- create some subscribers ---

            $max = $this->faker->numberBetween( 1, min($subscriberPool->count()-1, $this->getMax('subscriber')) );
            if ( $max < 1 ) {
                throw new Exception('Requires at least 1 subscriber per premium timeline - max:'.$max);
            }
            $this->command->info("  - Creating $max (non-premium) subscribers for timeline ".$t->name.", iter: $iter");

            $subscriberPool->random($max)->each( function($f) use(&$t) {
                $customAttributes = [ 'notes' => 'ShareablesTableSeeder.subscribe_to_some_premium_timelines' ];
                DB::table('shareables')->insert([
                    'sharee_id' => $f->id,
                    'shareable_type' => 'timelines',
                    'shareable_id' => $t->id,
                    'is_approved' => 1,
                    'access_level' => 'premium',
                    'cattrs' => json_encode($customAttributes),
                ]);
                Fanledger::create([
                    //'from_account' => , // %TODO: see https://trello.com/c/LzTUmPCp
                    //'to_account' => ,
                    'fltype' => PaymentTypeEnum::SUBSCRIPTION,
                    'purchaser_id' => $f->id, // fan
                    'seller_id' => $t->user->id,
                    'purchaseable_type' => 'timelines',
                    'purchaseable_id' => $t->id,
                    'qty' => 1,
                    'base_unit_cost_in_cents' => $t->price,
                    'cattrs' => json_encode($customAttributes),
                ]);

                // --- purchase some posts ---

                $max = $this->faker->numberBetween( 0, $this->getMax('purchased') );
                $purchaseablePosts = $t->posts()->where('type', PostTypeEnum::PRICED)->inRandomOrder($max)->get();
                $count = $purchaseablePosts->count();
                $this->command->info("  - Purchasing $count posts for follower ".$f->name." on timeline ".$t->name);
                if ( $count > 0 ) {
                    $purchaseablePosts->each( function($p) use(&$f) {
                        $customAttributes = [ 'notes' => 'ShareablesTableSeeder.purchase_some_posts' ];
                        // %FIXME: this should also update shareables, and be encapsualted in a method in model or class-lib
                        DB::table('shareables')->insert([
                            'sharee_id' => $f->id,
                            'shareable_type' => 'posts',
                            'shareable_id' => $p->id,
                            'is_approved' => 1,
                            'access_level' => 'premium', // ??
                            'cattrs' => json_encode($customAttributes),
                        ]);
                        $p->receivePayment(
                            PaymentTypeEnum::PURCHASE, // PaymentTypeEnum
                            $f, // User $sender | follower | purchaser
                            $p->price, // int $amountInCents
                            $customAttributes,
                        );
                    });
                }
            });

            // --- create some followers (of premium timelines!) ---

            $max = $this->faker->numberBetween( 1, min($followerPool->count()-1, $this->getMax('follower')) );
            if ( $max < 1 ) {
                throw new Exception('Requires at least 1 follower per free timeline - max:'.$max);
            }
            $this->command->info("  - Creating $max (non-premium) followers for timeline ".$t->name.", iter: $iter");

            $followerPool->random($max)->each( function($f) use(&$t) {
                $customAttributes = [ 'notes' => 'ShareablesTableSeeder.follow_some_premium_timelines' ];
                DB::table('shareables')->insert([
                    'sharee_id' => $f->id,
                    'shareable_type' => 'timelines',
                    'shareable_id' => $t->id,
                    'is_approved' => 1,
                    'access_level' => 'default',
                    'cattrs' => json_encode($customAttributes),
                ]);

                // --- purchase some posts ---

                $max = $this->faker->numberBetween( 0, $this->getMax('purchased') );
                $purchaseablePosts = $t->posts()->where('type', PostTypeEnum::PRICED)->inRandomOrder($max)->get();
                $count = $purchaseablePosts->count();
                $this->command->info("  - Purchasing $count posts for follower ".$f->name." on timeline ".$t->name);
                if ( $count > 0 ) {
                    $purchaseablePosts->each( function($p) use(&$f) {
                        $customAttributes = [ 'notes' => 'ShareablesTableSeeder.purchase_some_posts' ];
                        // %FIXME: this should also update shareables, and be encapsualted in a method in model or class-lib
                        DB::table('shareables')->insert([
                            'sharee_id' => $f->id,
                            'shareable_type' => 'posts',
                            'shareable_id' => $p->id,
                            'is_approved' => 1,
                            'access_level' => 'premium', // ??
                            'cattrs' => json_encode($customAttributes),
                        ]);
                        $p->receivePayment(
                            PaymentTypeEnum::PURCHASE, // PaymentTypeEnum
                            $f, // User $sender | follower | purchaser
                            $p->price, // int $amountInCents
                            $customAttributes,
                        );
                    });
                }
            });

            $iter++;
        });

        // =====================================



    }

    private function getMax($param) : int
    {
        static $max = [
            'testing' => [
                'purchased' => 3,
                'follower' => 5,
                'subscriber' => 5,
            ],
            'local' => [
                'purchased' => 3,
                'follower' => 5,
                'subscriber' => 5,
            ],
        ];
        return $max[$this->appEnv][$param];
    }


}

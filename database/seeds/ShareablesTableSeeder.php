<?php
namespace Database\Seeders;

use DB;
use Exception;
use Carbon\Carbon;
use App\Models\Post;
use App\Models\User;
use App\Libs\UserMgr;
use RuntimeException;
use App\Models\Timeline;
use App\Models\Fanledger;
use App\Enums\PostTypeEnum;
use App\Libs\UuidGenerator;
use App\Libs\FactoryHelpers;
use App\Enums\PaymentTypeEnum;
use App\Enums\MediafileTypeEnum;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;
use App\Enums\ShareableAccessLevelEnum;
use App\Enums\Financial\AccountTypeEnum;
use App\Events\AccessGranted;
use App\Events\AccessRevoked;
use App\Events\ItemPurchased;
use App\Jobs\Financial\UpdateAccountBalance;
use App\Models\Financial\Account;
use Symfony\Component\Console\Output\ConsoleOutput;

class ShareablesTableSeeder extends Seeder
{
    use SeederTraits;

    protected $eventsToDelayOnPurchase = [
        UpdateAccountBalance::class,
        AccessGranted::class,
        AccessRevoked::class,
        ItemPurchased::class,
    ];

    public function run()
    {
        $this->initSeederTraits('ShareablesTableSeeder'); // $this->{output, faker, appEnv}

        // +++ Create ... +++

        $timelines = Timeline::get();

        if ( $this->appEnv !== 'testing' ) {
            $this->output->writeln("  - Shareables seeder: loaded ".$timelines->count()." timelines...");
        }

        // Remove a few timelines so we have some without any followers for testing...
        //   ~ [ ] %TODO: timelines w/ followers but not subcribers, & vice-versa
        $timelines->pop();
        $timelines->pop();
        $timelines->pop();


        $timelines->each( function($timeline) {

            static $iter = 1;

            // --- user pool ---

            $userPool = User::where('id', '<>', $timeline->user->id)->get(); // exclude timeline owner
            $followerPool = $userPool;
            unset($userPool);

            // --- create some followers (non-premium, will upgrade some later) ---

            $max = $this->faker->numberBetween( 2, min($followerPool->count()-1, $this->getMax('follower')+$this->getMax('subscriber')) );
            if ( $max < 2 ) {
                throw new Exception('Requires at least 2 followers per timeline - max:'.$max);
            }
            if ( $this->appEnv !== 'testing' ) {
                $this->output->writeln("  - Creating $max (non-premium) followers for timeline ".$timeline->name.", iter: $iter");
            }

            $followerPool->random($max)->each( function(User $follower) use(&$timeline) {
                $customAttributes = [ 'notes' => 'ShareablesTableSeeder.follow_some_free_timelines' ];
                DB::table('shareables')->insert([
                    'sharee_id' => $follower->id,
                    'shareable_type' => 'timelines',
                    'shareable_id' => $timeline->id,
                    'is_approved' => 1,
                    'access_level' => 'default',
                    'cattrs' => json_encode($customAttributes),
                ]);

                // --- purchase some posts ---

                $max = $this->faker->numberBetween( 0, $this->getMax('purchased') );
                $purchaseablePosts = $timeline->posts()->where('type', PostTypeEnum::PRICED)->inRandomOrder($max)->get();
                $count = $purchaseablePosts->count();
                if ( $this->appEnv !== 'testing' ) {
                    $this->output->writeln("  - Purchasing {$count} posts for follower {$follower->name} on timeline {$timeline->name}");
                }
                if ( $count > 0 ) {
                    $purchaseablePosts->each( function($post) use(&$follower) {
                        $customAttributes = [ 'notes' => 'ShareablesTableSeeder.purchase_post_as_follower_free_timeline' ];

                        $paymentAccount = $follower->financialAccounts()->firstOrCreate([
                            'type' => AccountTypeEnum::IN,
                            'name' => "{$follower->username} Seeder Account",
                            'verified' => true,
                            'can_make_transactions' => true,
                        ]);

                        Event::fakeFor(function() use ($paymentAccount, $post, $customAttributes) {
                            try {
                                $paymentAccount->purchase($post, $post->price, ShareableAccessLevelEnum::PREMIUM, $customAttributes);
                            } catch (RuntimeException $e) {
                                $exceptionClass = class_basename($e);
                                $this->output->writeln("Exception while purchasing Post [{$post->getKey()}] | {$exceptionClass} | {$e->getMessage()}");
                            }
                        }, $this->eventsToDelayOnPurchase);


                        // DB::table('shareables')->insert([
                        //     'sharee_id' => $follower->id,
                        //     'shareable_type' => 'posts',
                        //     'shareable_id' => $post->id,
                        //     'is_approved' => 1,
                        //     'access_level' => 'premium', // ??
                        //     'cattrs' => json_encode($customAttributes),
                        // ]);
                        // Fanledger::create([
                        //     //'from_account' => , // %TODO: see https://trello.com/c/LzTUmPCp
                        //     //'to_account' => ,
                        //     'fltype' => PaymentTypeEnum::PURCHASE, 
                        //     'purchaser_id' => $f->id, // fan
                        //     'seller_id' => $p->user->id,
                        //     'purchaseable_type' => 'posts',
                        //     'purchaseable_id' => $p->id,
                        //     'qty' => 1,
                        //     'base_unit_cost_in_cents' => $p->price,
                        //     'cattrs' => json_encode($customAttributes),
                        // ]);
                    });
                }
            });

            // --- Select some for upgrades ---

            $timeline->refresh();
            $followers = $timeline->followers;
            $max = $this->faker->numberBetween( 0, min($followers->count()-1, $this->getMax('subscriber')) );
            if ( $this->appEnv !== 'testing' ) {
                $this->output->writeln("  - Upgrading $max followers to subscribers for timeline ".$timeline->name.", iter: $iter");
            }

            $followers->random($max)->each( function($f) use(&$timeline) {

                // TODO: Switch this to subscribable transactions

                $customAttributes = [ 'notes' => 'ShareablesTableSeeder.upgraded_to_subscriber' ];
                DB::table('shareables')
                    ->where('sharee_id', $f->id)
                    ->where('shareable_type', 'timelines')
                    ->where('shareable_id', $timeline->id)
                    ->update([
                        'access_level' => 'premium', // ??
                        'cattrs' => json_encode($customAttributes),
                    ]);
                Fanledger::create([
                    //'from_account' => , // %TODO: see https://trello.com/c/LzTUmPCp
                    //'to_account' => ,
                    'fltype' => PaymentTypeEnum::PURCHASE, 
                    'purchaser_id' => $f->id, // fan
                    'seller_id' => $timeline->user->id,
                    'purchaseable_type' => 'timelines',
                    'purchaseable_id' => $timeline->id,
                    'qty' => 1,
                    'base_unit_cost_in_cents' => $timeline->price->getAmount(),
                    'cattrs' => json_encode($customAttributes),
                ]);
            });

            $iter++;
        });


        // Run update Balance on accounts now.
        $this->output->writeln("Updating Account Balances");
        Account::cursor()->each(function($account) {
            $this->output->writeln("Updating Balance for {$account->name}");
            $account->settleBalance();
        });

    } // run()

    private function getMax($param) : int
    {
        static $max = [
            'testing' => [
                'purchased' => 3,
                'follower' => 8,
                'subscriber' => 3,
            ],
            'local' => [
                'purchased' => 3,
                'follower' => 8,
                'subscriber' => 3,
            ],
        ];
        return $max[$this->appEnv][$param];
    }

}

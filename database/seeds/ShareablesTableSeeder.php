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
use App\Events\TipFailed;
use App\Events\ItemTipped;
use App\Enums\PostTypeEnum;
use App\Libs\UuidGenerator;
use Illuminate\Support\Str;
use App\Libs\FactoryHelpers;
use App\Events\AccessGranted;
use App\Events\AccessRevoked;
use App\Events\ItemPurchased;
use App\Enums\PaymentTypeEnum;
use App\Events\ItemSubscribed;
use App\Events\PurchaseFailed;
use App\Enums\MediafileTypeEnum;
use App\Models\Financial\Account;
use App\Events\SubscriptionFailed;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;
use App\Enums\ShareableAccessLevelEnum;
use App\Enums\Financial\AccountTypeEnum;
use App\Notifications\ResourcePurchased;
use App\Jobs\Financial\UpdateAccountBalance;
use Symfony\Component\Console\Output\ConsoleOutput;
use App\Notifications\TimelineFollowed as TimelineFollowedNotify;
use App\Notifications\TimelineSubscribed as TimelineSubscribedNotify;

class ShareablesTableSeeder extends Seeder
{
    use SeederTraits;

    protected $eventsToDelayOnPurchase = [
        UpdateAccountBalance::class,
        AccessGranted::class,
        AccessRevoked::class,
        ItemPurchased::class,
        PurchaseFailed::class,
        ItemTipped::class,
        TipFailed::class,
        ItemSubscribed::class,
        SubscriptionFailed::class,
    ];

    protected $showOutput = true;

    public function run()
    {
        $this->initSeederTraits('ShareablesTableSeeder'); // $this->{output, faker, appEnv}

        Mail::fake();

        // +++ Create ... +++

        $timelines = Timeline::get();
        $timelinesCount = Timeline::count();

        if ( $this->appEnv !== 'testing' || $this->showOutput ) {
            $this->output->writeln("  - Shareables seeder: loaded ".$timelines->count()." timelines...");
        }

        // Remove a few timelines so we have some without any followers for testing...
        //   ~ [ ] %TODO: timelines w/ followers but not subscribers, & vice-versa
        $timelines->pop();
        $timelines->pop();
        $timelines->pop();

        $timelines->each( function($timeline) use ($timelinesCount) {

            static $iter = 1;

            // --- user pool ---

            $userPool = User::where('id', '<>', $timeline->user->id)->get(); // exclude timeline owner
            $followerPool = $userPool;
            unset($userPool);

            // --- create some followers (non-premium, will upgrade some later) ---

            $max = $this->faker->numberBetween( 2, min($followerPool->count()-1, $this->getMax('follower')+$this->getMax('subscriber')) );
            if ( $max < 2 ) {
                throw new Exception('Requires at least 2 followers per timeline - max:' . $max);
            }
            if ( $this->appEnv !== 'testing' || $this->showOutput ) {
                $this->output->writeln("  -- {$iter} of {$timelinesCount} | Timeline: {$timeline->name}");
                $this->output->writeln("    - Creating $max (non-premium) followers for timeline {$timeline->name}, iter: $iter");
            }

            $followerPool->random($max)->each( function(User $follower) use(&$timeline) {
                $ts = $this->faker->dateTimeBetween($startDate = '-5 years', $endDate = 'now');
                $customAttributes = [ 'notes' => 'ShareablesTableSeeder.follow_some_free_timelines' ];
                DB::table('shareables')->insert([
                    //'id' =>  Str::uuid(),
                    'sharee_id' => $follower->id,
                    'shareable_type' => 'timelines',
                    'shareable_id' => $timeline->id,
                    'is_approved' => 1,
                    'access_level' => 'default',
                    'cattrs' => json_encode($customAttributes), // encode manually since we are using query builder
                    'created_at' => $ts,
                    'updated_at' => $ts,
                ]);
                // %PSG: Disable as this will trigger SendGrid emails
                //$timeline->user->notify(new TimelineFollowedNotify($timeline, $follower));

                // --- purchase some posts ---

                $max = $this->faker->numberBetween( 0, $this->getMax('purchased') );
                $purchaseablePosts = $timeline->posts()->where('type', PostTypeEnum::PRICED)->inRandomOrder($max)->get();
                $count = $purchaseablePosts->count();
                if ( $this->appEnv !== 'testing' || $this->showOutput ) {
                    $this->output->writeln("    - Purchasing {$count} posts for follower {$follower->name} on timeline {$timeline->name}");
                }
                if ( $count > 0 ) {
                    $purchaseablePosts->each( function($post) use(&$follower) {
                        $customAttributes = [ 'notes' => 'ShareablesTableSeeder.purchase_post_as_follower_free_timeline' ];

                        $ts = $this->faker->dateTimeBetween($startDate = '-28 day', $endDate = 'now');
                        Carbon::setTestNow(new Carbon($ts)); // Sets mocked time

                        $paymentAccount = $follower->financialAccounts()->firstOrCreate([
                            'type' => AccountTypeEnum::IN,
                            'system' => 'segpay',
                            'currency' => 'USD',
                            'name' => "{$follower->username} Seeder Account",
                        ]);
                        $paymentAccount->verified = true;
                        $paymentAccount->can_make_transactions = true;
                        $paymentAccount->save();

                        Event::fakeFor(function() use (&$paymentAccount, &$post, &$follower, $customAttributes) {
                            try {
                                $paymentAccount->purchase($post, $post->price, ShareableAccessLevelEnum::PREMIUM, $customAttributes);
                                // %PSG: Disable as this will trigger SendGrid emails
                                //$post->user->notify(new ResourcePurchased($post, $follower, ['amount'=>\App\Models\Casts\Money::doSerialize($post->price)]));
                            } catch (RuntimeException $e) {
                                //throw $e;
                                $exceptionClass = class_basename($e);
                                if ($this->appEnv !== 'testing' || $this->showOutput ) {
                                    $this->output->writeln("      Exception while purchasing Post [{$post->getKey()}] | {$exceptionClass} | {$e->getMessage()}");
                                }
                            }
                        }, $this->eventsToDelayOnPurchase);

                        Carbon::setTestNow(); // Clears mocked time

                    });
                }
            });

            // --- Select some for upgrades (ie to subscribe / "premium" follow) ---
            // Only upgrade if the timeline has a price
            if ($timeline->price) {
                $timeline->refresh();
                $followers = $timeline->followers;
                $max = $this->faker->numberBetween( 0, min($followers->count() - 1, $this->getMax('subscriber')) );
                if ( $this->appEnv !== 'testing' || $this->showOutput ) {
                    $this->output->writeln("    - Upgrading $max followers to subscribers for timeline ".$timeline->name.", iter: $iter");
                }

                $followers->random($max)->each( function($follower) use(&$timeline) {
                    // Set fake time to make subscription
                    $ts = $this->faker->dateTimeBetween($startDate = '-28 day', $endDate = 'now');
                    Carbon::setTestNow(new Carbon($ts)); // Sets mocked time

                    $customAttributes = [ 'notes' => 'ShareablesTableSeeder.upgraded_to_subscriber' ];

                    $paymentAccount = $follower->financialAccounts()->firstOrCreate([
                        'type' => AccountTypeEnum::IN,
                        'system' => 'segpay',
                        'currency' => 'USD',
                        'name' => "{$follower->username} Seeder Account",
                    ]);
                    $paymentAccount->verified = true;
                    $paymentAccount->can_make_transactions = true;
                    $paymentAccount->save();

                    Event::fakeFor(function () use (&$paymentAccount, &$timeline, &$follower, $customAttributes) {
                        try {
                            $subscription = $paymentAccount->createSubscription($timeline, $timeline->price, ShareableAccessLevelEnum::PREMIUM, $customAttributes);
                            $subscription->process();
                            // %PSG: Disable as this will trigger SendGrid emails
                            //$timeline->user->notify(new TimelineSubscribedNotify($timeline, $follower, ['amount' => \App\Models\Casts\Money::doSerialize($timeline->price)]));
                        } catch (RuntimeException $e) {
                            //throw $e;
                            $exceptionClass = class_basename($e);
                            if ($this->appEnv !== 'testing' || $this->showOutput) {
                                $this->output->writeln("      Exception while subscribing to Timeline [{$timeline->getKey()}] | {$exceptionClass} | {$e->getMessage()}");
                            }
                        }
                    }, $this->eventsToDelayOnPurchase);

                    Carbon::setTestNow(); // Clears mocked time
                });
            } else {
                if ($this->appEnv !== 'testing' || $this->showOutput) {
                    $this->output->writeln("    - Timeline does not have price. Skipping subscription seeding for timeline " . $timeline->name . ", iter: $iter");
                }
            }
            $iter++;
        });


        // Run update Balance on accounts now.
        // if ($this->appEnv !== 'testing') {
            $this->output->writeln("  -----------------------------");
            $this->output->writeln("  | Updating Account Balances |");
            $this->output->writeln("  -----------------------------");
        // }
        $count = Account::where('owner_type', '!=', 'financial_system_owner')->count();
        Account::where('owner_type', '!=', 'financial_system_owner')->get()->each(function($account) use ($count) {
            static $iter = 1;
            if ($this->appEnv !== 'testing' || $this->showOutput) {
                $this->output->writeln("  ({$iter} of {$count}): Updating Balance for {$account->name}");
            }
            $account->settleBalance();
            $iter++;
        });
        $this->output->writeln("  ------------------------");
        $this->output->writeln("  | Updating Fee Balances |");
        $this->output->writeln("  -------------------------");
        Account::where('owner_type', 'financial_system_owner')->each(function($account) {
            if ($this->appEnv !== 'testing' || $this->showOutput) {
                $this->output->writeln("  Updating Balance for {$account->name}");
            }
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

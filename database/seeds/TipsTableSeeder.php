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
use App\Libs\UuidGenerator;
use App\Libs\FactoryHelpers;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

use App\Enums\ShareableAccessLevelEnum;
use App\Enums\Financial\AccountTypeEnum;
use App\Enums\Financial\TransactionTypeEnum;
use App\Enums\PostTypeEnum;
use App\Enums\PaymentTypeEnum;
use App\Enums\MediafileTypeEnum;

use App\Events\AccessGranted;
use App\Events\AccessRevoked;
use App\Events\ItemPurchased;
use App\Jobs\Financial\UpdateAccountBalance;
use App\Models\Financial\Account;
use App\Notifications\TimelineFollowed;
use App\Notifications\TimelineSubscribed;
use App\Notifications\TipReceived;
use Symfony\Component\Console\Output\ConsoleOutput;

class TipsTableSeeder extends Seeder
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
        $this->initSeederTraits('TipsTableSeeder'); // $this->{output, faker, appEnv}

        Mail::fake();

        // +++ Create ... +++

        $timelines = Timeline::whereHas('posts', function($q1) {
            $q1->whereIn('type', [PostTypeEnum::FREE, PostTypeEnum::PRICED]);
        })->has('followers','>=',1)->get();

        if ( $this->appEnv !== 'testing' ) {
            $this->output->writeln("  - Tips seeder: loaded ".$timelines->count()." timelines...");
        }

        $timelines->take(25)->each( function($t) { // do max 25

            static $iter = 1;

            $now = \Carbon\Carbon::now();
            $t->followers->each( function(User $follower) use(&$t, $now) {
                if ( $this->faker->numberBetween(0,10) < 5 ) {
                    return; // skip: only N/10 processed
                }

                $paymentAccount = $follower->financialAccounts()->firstOrCreate([
                    'type' => AccountTypeEnum::IN,
                    'name' => "{$follower->username} Seeder Account",
                ]);
                $paymentAccount->verified = true;
                $paymentAccount->can_make_transactions = true;
                $paymentAccount->save();

                // Tip a timeline...
                Event::fakeFor(function() use (&$paymentAccount, &$t, &$follower ) {
                    if ( $this->appEnv !== 'testing' ) {
                        $this->output->writeln("  - Tips seeder: tipping timeline ".$t->slug);
                    }
                    try {
                        $tipAmount = $this->faker->numberBetween(500,10000);
                        //$paymentAccount->purchase($t, $tipAmount, ShareableAccessLevelEnum::DEFAULT, []);
                        // % @Erik workaround code 20210414
                        $paymentAccount->moveToInternal($tipAmount, [ 'type' => TransactionTypeEnum::TIP ]);
                        $internalAccount = $paymentAccount->getInternalAccount();
                        $internalAccount->moveTo($t->getOwner()->first()->getInternalAccount('segpay', 'USD'), $tipAmount, [
                            'type' => TransactionTypeEnum::TIP,
                            'ignoreBalance' => true,
                            'purchasable_id' => $t->getKey(),
                            'purchasable_type' => $t->getMorphString(),
                            'metadata' => [ 'notes' => 'TipsTableSeeder.tip_a_timeline' ],
                        ]);
                        $t->user->notify(new TipReceived($t, $follower));
                    } catch (RuntimeException $e) {
                        $exceptionClass = class_basename($e);
                        if ($this->appEnv !== 'testing') {
                            $this->output->writeln("Exception while tipping Timeline [{$t->getKey()}] | {$exceptionClass} | {$e->getMessage()}");
                        }
                    }
                }, $this->eventsToDelayOnPurchase);

                // Tip some posts...
                $posts = $t->posts->take( $this->faker->numberBetween(0,5) );
                $posts->each( function($p) use(&$paymentAccount, &$follower) {
                    Event::fakeFor(function() use (&$paymentAccount, &$p, &$follower ) {
                        if ( $this->appEnv !== 'testing' ) {
                            $this->output->writeln("  - Tips seeder: tipping post ".$p->slug);
                        }
                        try {
                            $tipAmount = $this->faker->numberBetween(500,7000);
                            //$paymentAccount->purchase($p, $tipAmount, ShareableAccessLevelEnum::DEFAULT, []);
                            // % @Erik workaround code 20210414
                            $paymentAccount->moveToInternal($tipAmount, [ 'type' => TransactionTypeEnum::TIP ]);
                            $internalAccount = $paymentAccount->getInternalAccount();
                            $internalAccount->moveTo($p->getOwner()->first()->getInternalAccount('segpay', 'USD'), $tipAmount, [
                                'type' => TransactionTypeEnum::TIP,
                                'ignoreBalance' => true,
                                'purchasable_id' => $p->getKey(),
                                'purchasable_type' => $p->getMorphString(),
                                'metadata' => [ 'notes' => 'TipsTableSeeder.tip_a_post' ],
                            ]);
                            $p->user->notify(new TipReceived($p, $follower));
                        } catch (RuntimeException $e) {
                            $exceptionClass = class_basename($e);
                            if ($this->appEnv !== 'testing') {
                                $this->output->writeln("Exception while tipping Post [{$p->getKey()}] | {$exceptionClass} | {$e->getMessage()}");
                            }
                        }
                    }, $this->eventsToDelayOnPurchase);
                }); // $posts->each( ... )

            }); // $t->followers->each( ... )

            $iter++;
        });


        // Run update Balance on accounts now.
        if ($this->appEnv !== 'testing') {
            $this->output->writeln("Updating Account Balances");
        }
        Account::cursor()->each(function($account) {
            if ($this->appEnv !== 'testing') {
                $this->output->writeln("Updating Balance for {$account->name}");
            }
            $account->settleBalance();
        });

    } // run()

}

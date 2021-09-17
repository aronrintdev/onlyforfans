<?php
namespace Database\Seeders;

use App\Models\Tip;
use App\Models\User;
use RuntimeException;
use App\Models\Timeline;
use App\Events\TipFailed;
use App\Events\ItemTipped;
use App\Enums\PostTypeEnum;
use App\Events\AccessGranted;
use App\Events\AccessRevoked;
use App\Events\ItemPurchased;
use App\Events\ItemSubscribed;
use App\Events\PurchaseFailed;
use Illuminate\Support\Carbon;
use App\Payments\PaymentGateway;
use App\Models\Financial\Account;
use App\Events\SubscriptionFailed;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Event;
use App\Enums\Financial\AccountTypeEnum;
use App\Jobs\Financial\UpdateAccountBalance;

class TipsTableSeeder extends Seeder
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

    public function run()
    {
        $this->initSeederTraits('TipsTableSeeder'); // $this->{output, faker, appEnv}

        Mail::fake();

        // +++ Create ... +++

        $timelines = Timeline::whereHas('posts', function($q1) {
            $q1->whereIn('type', [PostTypeEnum::FREE, PostTypeEnum::PRICED]);
        })->has('followers','>=',1)->get();

        if ( $this->appEnv !== 'testing' ) {
            $this->output->writeln("  - Tips seeder: loaded " . $timelines->count() . " timelines...");
        }

        // Was not generating tips on enough timelines with just 17, switched to all timelines with followers
        // $timelines->take(17)->each( function($t) {
        $timelines->each(function ($t) {

            static $iter = 1;

            //$now = \Carbon\Carbon::now();
            //$ts = $this->faker->dateTimeBetween($startDate = '-5 years', $endDate = 'now');
            $t->followers->each( function(User $follower) use(&$t) {
                if ( $this->faker->numberBetween(0,10) < 5 ) {
                    return false; // skip: only N/10 processed
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
                        $this->output->writeln("  - Tips seeder: tipping timeline " . $t->slug);
                    }

                    // Set fake time to make tip
                    $ts = $this->faker->dateTimeBetween($startDate = '-28 day', $endDate = 'now');
                    Carbon::setTestNow(new Carbon($ts)); // Sets mocked time

                    try {
                        $tipAmount = $this->faker->numberBetween(1, 20) * 500;
                        $tip = Tip::create([
                            'sender_id'       => $follower->getKey(),
                            'receiver_id'     => $t->getOwner()->first()->getKey(),
                            'tippable_type'   => $t->getMorphString(),
                            'tippable_id'     => $t->getKey(),
                            'account_id'      => $paymentAccount->getKey(),
                            'currency'        => 'USD',
                            'amount'          => $tipAmount,
                            'period'          => 'single',
                            'period_interval' => 1,
                            'message'         => '',
                        ]);
                        $paymentGateway = new PaymentGateway();
                        $paymentGateway->tip($paymentAccount, $tip, $tip->amount);
                    } catch (RuntimeException $e) {
                        $exceptionClass = class_basename($e);
                        if ($this->appEnv !== 'testing') {
                            $this->output->writeln("Exception while tipping Timeline [{$t->getKey()}] | {$exceptionClass} | {$e->getMessage()}");
                        }
                    }

                    Carbon::setTestNow(); // Clears mocked time

                }, $this->eventsToDelayOnPurchase);

                // Tip some posts...
                $posts = $t->posts->take( $this->faker->numberBetween(0,3) );
                $posts->each( function($p) use(&$paymentAccount, &$follower) {
                    Event::fakeFor(function() use (&$paymentAccount, &$p, &$follower ) {
                        if ( $this->appEnv !== 'testing' ) {
                            $this->output->writeln("  - Tips seeder: tipping post " . $p->slug);
                        }

                        $ts = $this->faker->dateTimeBetween($startDate = '-28 day', $endDate = 'now');
                        Carbon::setTestNow(new Carbon($ts)); // Sets mocked time

                        try {
                            $tipAmount = $this->faker->numberBetween(1, 20) * 500;
                            $tip = Tip::create([
                                'sender_id'       => $follower->getKey(),
                                'receiver_id'     => $p->getOwner()->first()->getKey(),
                                'tippable_type'   => $p->getMorphString(),
                                'tippable_id'     => $p->getKey(),
                                'account_id'      => $paymentAccount->getKey(),
                                'currency'        => 'USD',
                                'amount'          => $tipAmount,
                                'period'          => 'single',
                                'period_interval' => 1,
                                'message'         => '',
                            ]);
                            $paymentGateway = new PaymentGateway();
                            $paymentGateway->tip($paymentAccount, $tip, $tip->amount);
                        } catch (RuntimeException $e) {
                            $exceptionClass = class_basename($e);
                            if ($this->appEnv !== 'testing') {
                                $this->output->writeln("Exception while tipping Post [{$p->getKey()}] | {$exceptionClass} | {$e->getMessage()}");
                            }
                        }

                        Carbon::setTestNow(); // Clears mocked time

                    }, $this->eventsToDelayOnPurchase);
                }); // $posts->each( ... )

            }); // $t->followers->each( ... )

            $iter++;
        });


        // Run update Balance on accounts now.
        if ($this->appEnv !== 'testing') {
            $this->output->writeln("-------------------------");
            $this->output->writeln("Updating Account Balances");
            $this->output->writeln("-------------------------");
        }
        $count = Account::where('owner_type', '!=', 'financial_system_owner')->count();
        Account::where('owner_type', '!=', 'financial_system_owner')->get()->each(function ($account) use ($count) {
            static $iter = 1;
            if ($this->appEnv !== 'testing') {
                $this->output->writeln("({$iter} of {$count}): Updating Balance for {$account->name}");
            }
            $account->settleBalance();
            $iter++;
        });
        Account::where('owner_type', 'financial_system_owner')->each(function ($account) {
            if ($this->appEnv !== 'testing') {
                $this->output->writeln("Updating Balance for {$account->name}");
            }
            $account->settleBalance();
        });

    } // run()

}

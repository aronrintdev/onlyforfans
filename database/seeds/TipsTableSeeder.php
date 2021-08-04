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
use App\Models\Tip;
use App\Notifications\TimelineFollowed;
use App\Notifications\TimelineSubscribed;
use App\Notifications\TipReceived;
use App\Payments\PaymentGateway;
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
            $this->output->writeln("  - Tips seeder: loaded " . $timelines->count() . " timelines...");
        }

        $timelines->take(17)->each( function($t) {

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
                            'message'         => null,
                        ]);
                        $paymentGateway = new PaymentGateway();
                        $paymentGateway->tip($paymentAccount, $tip, $tip->amount);
                    } catch (RuntimeException $e) {
                        $exceptionClass = class_basename($e);
                        if ($this->appEnv !== 'testing') {
                            $this->output->writeln("Exception while tipping Timeline [{$t->getKey()}] | {$exceptionClass} | {$e->getMessage()}");
                        }
                    }
                }, $this->eventsToDelayOnPurchase);

                // Tip some posts...
                $posts = $t->posts->take( $this->faker->numberBetween(0,3) );
                $posts->each( function($p) use(&$paymentAccount, &$follower) {
                    Event::fakeFor(function() use (&$paymentAccount, &$p, &$follower ) {
                        if ( $this->appEnv !== 'testing' ) {
                            $this->output->writeln("  - Tips seeder: tipping post " . $p->slug);
                        }
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
                                'message'         => null,
                            ]);
                            $paymentGateway = new PaymentGateway();
                            $paymentGateway->tip($paymentAccount, $tip, $tip->amount);
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

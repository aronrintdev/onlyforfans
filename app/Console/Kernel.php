<?php
namespace App\Console;

use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Console\Scheduling\Schedule;
use App\Jobs\StartTransactionSummaryCreation;

use App\Jobs\Financial\StartUpdatePendingBalances;
use App\Enums\Financial\TransactionSummaryTypeEnum;
use App\Jobs\StartDeliverMessagesBatch;
use App\Models\Chatmessage;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\DeleteMediafileAssets::class,
        \App\Console\Commands\MakeBlurs::class,
        \App\Console\Commands\MakeThumbnails::class,
        \App\Console\Commands\SetMediafileBasename::class,
        \App\Console\Commands\UpdateCanceledSubscriptions::class,
        \App\Console\Commands\UpdateMediafilesNullResource::class,
        \App\Console\Commands\UpdateSlugs::class,
        \App\Console\Commands\WebhooksDispatch::class,
        \App\Console\Commands\WebhooksRetry::class,
        \App\Console\Commands\PublishScheduledPosts::class,
        \App\Console\Commands\PopulateContacts::class,
        \App\Console\Commands\PushTestEvent::class,
        \App\Console\Commands\SetmfSize::class,
        \App\Console\Commands\SetTimestamps::class,
        \App\Console\Commands\UpdateStoryqueues::class,

        \App\Console\Commands\Dev\TruncateData::class,
        \App\Console\Commands\Dev\PopulateChargebacks::class,

        // Financial Commands
        \App\Console\Commands\Financial\CreateTransactionSummaries::class,
        \App\Console\Commands\Financial\DispatchAccountBalanceUpdates::class,
        \App\Console\Commands\Financial\SettleFinancialAccounts::class,
        \App\Console\Commands\Financial\UpdateAccountNames::class,
        \App\Console\Commands\Financial\UpdatePendingBalances::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        Log::info('Schedule is being run');
        // Transaction Summaries Creations

        /* -------------------------------------------------------------------------- */
        /*                       TRANSACTION SUMMARIES CREATIONS                      */
        /* -------------------------------------------------------------------------- */
        #region Transaction summaries

        /* ---------------------------------- DAILY --------------------------------- */
        $schedule->call(function () {
            $queue = Config::get('transactions.summarizeQueue');
            $batch = Bus::batch([
                new StartTransactionSummaryCreation(TransactionSummaryTypeEnum::DAILY)
            ])->then(function (Batch $batch) {
                Log::info('Summarize Daily Transactions Finished');
            })->name('Summarize Daily Transactions')->onConnection($queue);
        })->dailyAt('0:01');

        /* --------------------------------- WEEKLY --------------------------------- */
        $schedule->call(function () {
            $queue = Config::get('transactions.summarizeQueue');
            $batch = Bus::batch([
                new StartTransactionSummaryCreation(TransactionSummaryTypeEnum::WEEKLY)
            ])->then(function (Batch $batch) {
                Log::info('Summarize Weekly Transactions Finished');
            })->name('Summarize Weekly Transactions')->onConnection($queue);
        })->weeklyOn(0, '0:01');

        /* --------------------------------- MONTHLY -------------------------------- */
        $schedule->call(function () {
            $queue = Config::get('transactions.summarizeQueue');
            $batch = Bus::batch([
                new StartTransactionSummaryCreation(TransactionSummaryTypeEnum::MONTHLY)
            ])->then(function (Batch $batch) {
                Log::info('Summarize Monthly Transactions Finished');
            })->name('Summarize Monthly Transactions')->onConnection($queue);
        })->monthlyOn(1, '0:01');

        /* --------------------------------- YEARLY --------------------------------- */
        $schedule->call(function () {
            $queue = Config::get('transactions.summarizeQueue');
            $batch = Bus::batch([
                new StartTransactionSummaryCreation(TransactionSummaryTypeEnum::YEARLY)
            ])->then(function (Batch $batch) {
                Log::info('Summarize Yearly Transactions Finished');
            })->name('Summarize Yearly Transactions')->onConnection($queue);
        })->yearlyOn(1, 1, '0:01');

        #endregion Transaction Summaries
        /* -------------------------------------------------------------------------- */


        /* -------------------------------------------------------------------------- */
        /*                       ACCOUNT PENDING BALANCES UPDATE                      */
        /* -------------------------------------------------------------------------- */
        $schedule->call(function () {
            $queue = Config::get('transactions.summarizeQueue');
            $batch = Bus::batch([
                new StartUpdatePendingBalances()
            ])->then(function (Batch $batch) {
                Log::info('Update Pending Balances Finished');
            })->name('Update Pending Balances')->onConnection($queue);

        // TODO: Determine reasonable interval
        })->everyFifteenMinutes();
        /* -------------------------------------------------------------------------- */

        /* -------------------------------------------------------------------------- */
        /*                         Deliver Scheduled Messages                         */
        /* -------------------------------------------------------------------------- */
        $schedule->call(function () {
            // Check for any schedule ready messages
            $count = Chatmessage::notDelivered()->scheduleReady()->count();
            if ($count === 0) {
                return;
            }
            Log::info("Starting Deliver Messages on [{$count}] due messages");
            $batch = Bus::batch([
                new StartDeliverMessagesBatch(),
            ])->then(function (Batch $batch) {
                Log::info('Deliver scheduled Messages Finished');
            });
        })->everyMinute();
        /* -------------------------------------------------------------------------- */


        // $schedule->command('subscription:update-canceled')->everyHour();
        $schedule->command('publish:schduled-posts')->everyMinute()->appendOutputTo(storage_path('logs/publish_posts.log'))->runInBackground();
        // $schedule->command('publish:posts')
        //           ->everyMinute()->appendOutputTo(storage_path('logs/publish_posts.log'));
        // $schedule->command('expire:post')
        //     ->everyMinute()->appendOutputTo(storage_path('logs/expire_posts.log'));
    }
    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}

<?php

namespace App\Console\Commands\Financial;

use Illuminate\Bus\Batch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use App\Jobs\Financial\StartUpdatePendingBalances;


class UpdatePendingBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'financial:update-pending {--system=segpay}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starts Batch Job to settle pending account balances';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Find Accounts with Pending Balance

        // Start Bus Batch to Deal with accounts with Pending Balance

        $queue = Config::get('transactions.summarizeQueue');

        $batch = Bus::batch([
            new StartUpdatePendingBalances($this->option('system'))
        ])->then(function (Batch $batch) {
            Log::info("Manually starting update pending balances");
            $finished = true;
        })->name("Update Pending Balances")->onQueue("$queue-low")
        ->allowFailures()->dispatch();

        $bar = $this->output->createProgressBar($batch->totalJobs);
        while (!$batch->finished() && !$batch->cancelled()) {
            $batch = $batch->fresh();
            $bar->setMaxSteps($batch->totalJobs);
            $bar->setProgress($batch->processedJobs());
            usleep(250000); // 0.25s
        }
        $bar->finish();

        $this->output->writeln('');
        $this->output->writeln('');
        if ($batch->finished()) {
            $this->output->writeln(' == Finished Processing Batch ==');
        } else if ($batch->canceled()) {
            $this->output->writeln(' == Batch was canceled ==');
        }

        return 0;
    }
}

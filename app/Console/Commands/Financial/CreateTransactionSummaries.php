<?php

namespace App\Console\Commands\Financial;

use Illuminate\Bus\Batch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use App\Jobs\StartTransactionSummaryCreation;
use App\Enums\Financial\TransactionSummaryTypeEnum as SummaryType;

class CreateTransactionSummaries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'financial:summary-start {type?} {back?} {recalculate?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starts the process of creating a type of transaction summary';

    protected $types = [
        SummaryType::DAILY,
        SummaryType::WEEKLY,
        SummaryType::MONTHLY,
        SummaryType::YEARLY,
    ];

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
        $type = $this->argument('type');
        if(!in_array($type, $this->types)) {
            $type = $this->choice('What type of summary are you creating?', $this->types, SummaryType::DAILY);
        }
        $back = $this->argument('back');
        if (!isset($back)) {
            $back = $this->ask('How many units back are you creating?');
        }
        $recalculate = $this->argument('recalculate');
        if (!isset($recalculate)) {
            $recalculate = $this->choice('Do you wish to recalculate these summaries if they already exist?', [ false, true ]);
        }

        $typeString = SummaryType::stringify($type);
        Log::info("Starting Summarize $typeString Transactions");
        $this->output->writeln(" == Starting Summarize $typeString Transactions ==");
        $this->output->writeln('');

        $queue = Config::get('transactions.summarizeQueue');

        $batch = Bus::batch([
            new StartTransactionSummaryCreation($type, $back)
        ])->then(function (Batch $batch) use ($typeString) {
            Log::info("Summarize $typeString Transactions Finished");
            $finished = true;
        })->name("Summarize $typeString Transactions")->onConnection($queue)
            ->allowFailures()->dispatch();

        $bar = $this->output->createProgressBar($batch->totalJobs);
        while(!$batch->finished() && !$batch->cancelled()) {
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

<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use App\Models\Financial\Account;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Enums\Financial\TransactionSummaryTypeEnum as SummaryType;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\Log;

class StartTransactionSummaryCreation implements ShouldQueue
{
    use Batchable,
        Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

    /**
     * What type of summaries this job is creating.
     *
     * @var string
     */
    protected $type;

    /**
     * The number of units back to go. e.i 7days start processes for last 7 days each
     * @var int
     */
    protected $amount;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $type, int $amount = 1)
    {
        $this->type = $type;
        $this->amount = $amount;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->batch()->cancelled()) {
            return;
        }

        // $queue = Config::get('transactions.summarizeQueue');

        // Create jobs for 'amount' number of time units going back
        for ( $i = 0; $i < $this->amount; $i++ ) {
            /* ------------------------------ DAILY ----------------------------- */
            if ($this->type === SummaryType::DAILY) {
                $start = Carbon::now()->startOfDay()->subDays($i + 1);
                $end = Carbon::now()->startOfDay()->subDays($i);
            }

            /* ----------------------------- WEEKLY ----------------------------- */
            if ($this->type === SummaryType::WEEKLY) {
                $start = Carbon::now()->startOfWeek()->startOfDay()->subWeeks($i + 1);
                $end   = Carbon::now()->startOfWeek()->startOfDay()->subWeeks($i);
            }

            /* ----------------------------- MONTHLY ---------------------------- */
            if ($this->type === SummaryType::MONTHLY) {
                $start = Carbon::now()->startOfMonth()->startOfDay()->subMonths($i + 1); // 00:00 of beginning of last month
                $end   = Carbon::now()->startOfMonth()->startOfDay()->subMonths($i);     // 00:00 of this month
            }

            /* ----------------------------- YEARLY ----------------------------- */
            if ($this->type === SummaryType::YEARLY) {
                $start = Carbon::now()->startOfYear()->startOfDay()->subYear($i + 1);
                $end   = Carbon::now()->startOfYear()->startOfDay()->subYear($i);
            }

            // Queue up summary creation on accounts with transaction in range
            $query = Account::whereHas('transactions', function (Builder $query) use ($start, $end) {
                $query->where('created_at', '>=', $start)
                    ->where('created_at', '<', $end);
            });

            $count = $query->count();
            Log::info("Starting Transaction Summary Creation for type [ $this->type ] summaries on [ $count ] accounts");

            $query->cursor()->each(function ($account) use ($start, $end) {
                $this->batch()->add(
                    new CreateTransactionSummary(
                        $account,
                        $this->type,
                        ['from' => $start, 'to' => $end]
                    )
                );
            });
        }

    }
}

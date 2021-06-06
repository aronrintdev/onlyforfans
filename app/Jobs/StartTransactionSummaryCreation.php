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
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $type)
    {
        $this->type = $type;
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

        /* ------------------------------ DAILY ----------------------------- */
        if ($this->type === SummaryType::DAILY) {
            $start = Carbon::yesterday();
            $end   = Carbon::today();
        }

        /* ----------------------------- WEEKLY ----------------------------- */
        if ($this->type === SummaryType::WEEKLY) {
            $start = Carbon::now()->startOfWeek()->startOfDay()->subWeek();
            $end   = Carbon::now()->startOfWeek()->startOfDay();
        }

        /* ----------------------------- MONTHLY ---------------------------- */
        if ($this->type === SummaryType::MONTHLY) {
            $start = Carbon::now()->startOfMonth()->startOfDay()->subMonth(); // 00:00 of beginning of last month
            $end   = Carbon::now()->startOfMonth()->startOfDay();             // 00:00 of this month
        }

        /* ----------------------------- YEARLY ----------------------------- */
        if ($this->type === SummaryType::YEARLY) {
            $start = Carbon::now()->startOfYear()->startOfDay()->subYear();
            $end   = Carbon::now()->startOfYear()->startOfDay();
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

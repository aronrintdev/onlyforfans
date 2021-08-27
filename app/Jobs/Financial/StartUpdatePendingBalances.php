<?php

namespace App\Jobs\Financial;

use Illuminate\Bus\Queueable;
use App\Models\Financial\Account;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\Log;

class StartUpdatePendingBalances implements ShouldQueue
{
    use Batchable,
        Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

    /**
     * Which system to update pending balances on
     *
     * @var string
     */
    protected $system;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $system = null)
    {
        if (!isset($system)) {
            $system = Config::get('transactions.default');
        }
        $this->system = $system;
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

        $count = Account::hasPending()->count();

        Log::info("Starting Pending Balances Update on $count accounts");

        Account::hasPending()->cursor()->each(function ($account) {
            $this->batch()->add(
                new UpdatePendingBalance($account)
            );
        });
    }
}

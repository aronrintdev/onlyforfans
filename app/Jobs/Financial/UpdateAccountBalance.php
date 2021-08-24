<?php

namespace App\Jobs\Financial;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class UpdateAccountBalance implements ShouldQueue, ShouldBeUnique
{
    use Batchable,
        Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

    public $uniqueUntilStart = true;
    public $tries = 1;
    public $backoff = [5];

    /**
     * Delete the job if its models no longer exist.
     *
     * @var bool
     */
    public $deleteWhenMissingModels = true;

    /**
     * Account instance
     * @var \App\Models\Financial\Account
     */
    protected $account;

    /**
     * Create a new job instance.
     *
     * @param  \App\Models\Financial\Account  $account
     * @return void
     */
    public function __construct($account)
    {
        $this->account = $account->withoutRelations();
        $this->onQueue('financial-transactions');
    }

    /**
     * Job's lock id
     */
    public function uniqueId()
    {
        return "update-account-balance-{$this->account->id}";
    }

    /**
     * Prevent multiple update jobs for same account.
     */
    public function middleware()
    {
        return [
            (new WithoutOverlapping($this->uniqueId()))->dontRelease(),
        ];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->account->settleBalance();
        return;
    }
}

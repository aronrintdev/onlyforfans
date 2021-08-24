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

class UpdatePendingBalance implements ShouldQueue, ShouldBeUnique
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
     * Account instance
     * @var \App\Models\Financial\Account
     */
    protected $account;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($account)
    {
        $this->account = $account->withoutRelations();
    }

    /**
     * Job's lock id
     */
    public function uniqueId()
    {
        return "update-pending-balance-{$this->account->id}";
    }

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
        if ($this->batch() !== null && $this->batch()->cancelled()) {
            return;
        }

        $this->account->updateBalance();
    }
}

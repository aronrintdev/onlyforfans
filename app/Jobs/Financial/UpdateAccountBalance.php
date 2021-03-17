<?php

namespace App\Jobs\Financial;

use App\Models\Financial\Account;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class UpdateAccountBalance implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $uniqueUntilStart = true;
    public $tries = 4;
    public $backoff = [ 5, 15, 60 ];

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
    public function __construct(Account $account)
    {
        $this->account = $account->withoutRelations();
        $this->onQueue('financial-transactions');
    }

    /**
     * Job's lock id
     */
    public function uniqueId()
    {
        return $this->account->id;
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
    }



}
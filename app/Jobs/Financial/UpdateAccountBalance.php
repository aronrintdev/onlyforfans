<?php

namespace App\Jobs\Financial;

use App\Models\Financial\Account;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;


class UpdateAccountBalance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $this->account = $account;
        $this->onQueue('financial-transactions');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
    }



}
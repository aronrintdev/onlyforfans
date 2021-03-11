<?php

namespace App\Jobs\Financial;

use App\Enums\Financial\AccountTypeEnum;
use App\Models\Financial\Account;
use App\Models\Financial\Transaction;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;

use Money\Currency;
use Money\Money;

class UpdateAccountBalance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Account instance
     * @var \App\Models\Financial\Account
     */
    protected $account;

    public $tries = 4;
    public $backoff = [ 5, 15, 60 ];

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
     * Prevent multiple update jobs for same account.
     */
    public function middleware()
    {
        return [new WithoutOverlapping($this->account->id)];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::transaction(function() {
            $account = Account::lockForUpdate()->find($this->account->getKey());
            $currency = new Currency($account->currency);

            // Process Pending Transactions
            Transaction::where('account_id', $account->getKey())
                ->whereNull('settled_at')
                ->whereNull('failed_at')
                ->with(['reference.account:type', 'account'])
                ->orderBy('created_at')
                ->lockForUpdate()
                ->chunkById(10, function($transactions) {
                    foreach($transactions as $transaction) {
                        if ( // From Internal to Internal
                            $transaction->reference->account->type === AccountTypeEnum::INTERNAL
                            && $transaction->account->type === AccountTypeEnum::INTERNAL
                        ) {
                            // Calculate Fees and Taxes On this transaction
                            $transaction->settleFees();
                            $transaction->save();
                        } else {
                            $transaction->settleBalance();
                            $transaction->save();
                        }

                    }
                });

            // Calculate balance and pending
            $balance = new Money($account->balance, $currency);
            $pending = new Money($account->pending, $currency);

        });
    }



}
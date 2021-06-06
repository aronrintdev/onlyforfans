<?php

namespace App\Jobs;

use App\Models\Financial\Account;
use App\Models\Financial\Transaction;
use App\Models\Financial\TransactionSummary;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class CreateTransactionSummary implements ShouldQueue, ShouldBeUnique
{
    use Batchable,
        Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

    public $uniqueUntilStart = true;
    public $tries = 4;
    public $backoff = [5, 15, 60];

    /**
     * Account instance
     * @var \App\Models\Financial\Account
     */
    protected $account;
    protected $type;
    protected $range;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Account $account, $type, $range = [ "from" => '', 'to' => '' ])
    {
        $this->account = $account->withoutRelations();
        $this->type = $type;
        $this->range = $range;
    }

    /**
     * Job's lock id
     */
    public function uniqueId()
    {
        return "{$this->account->id}-{$this->type}-{$this->range['from']}-{$this->range['to']}";
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
        if ($this->batch()->cancelled()) {
            return;
        }

        // Check if any transactions in range are pending, if so settle this account's balance before continuing.
        if ($this->account->transactions()->inRange($this->range)->pending()->exists()) {
            $this->account->settleBalance();
        }

        // Check if this summary has already been created
        if (
            TransactionSummary::where('account_id', $this->account->getKey())
                ->where('type', $this->type)->where('from', $this->range['from'])
                ->where('to', $this->range['to'])->exists()
        ) {
            return;
        }

        $summary = TransactionSummary::create([
            'account_id' => $this->account->getKey(),
            'from'       => $this->range['from'],
            'to'         => $this->range['to'],
            'type'       => $this->type,
        ]);

        $query = $this->account->transactions()->inRange($this->range)->settled();

        $summary->transactions_count = $query->count();
        $summary->credit_sum         = $query->sum('credit_amount');
        $summary->debit_sum          = $query->sum('debit_amount');
        $summary->credit_average     = round($query->avg('credit_amount'), 0, PHP_ROUND_HALF_EVEN);
        $summary->debit_average      = round($query->avg('debit_amount'),  0, PHP_ROUND_HALF_EVEN);

        $firstTrans = $query->orderBy('created_at', 'asc')->first();
        $summary->from_transaction_id = $firstTrans->getKey();

        $lastTrans = $query->orderBy('created_at', 'desc')->first();
        $summary->to_transaction_id = $lastTrans->getKey();

        $summary->balance = $lastTrans->balance;
        $summary->balance_delta = $summary->credit_sum->subtract($summary->debit_sum);

        $summary->finalized = true;
        $summary->save();

        return;
    }
}

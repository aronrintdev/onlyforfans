<?php

namespace App\Jobs;

use App\Models\Financial\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class CreateTransactionSummary implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $uniqueUntilStart = true;
    public $tries = 4;
    public $backoff = [5, 15, 60];

    /**
     * Account instance
     * @var \App\Models\Financial\Account
     */
    protected $account;
    protected $type;
    protected $for;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Account $account, $type, $for = '')
    {
        $this->account = $account->withoutRelations();
        $this->type = $type;
        $this->for = $for;
    }

    /**
     * Job's lock id
     */
    public function uniqueId()
    {
        return "{$this->account->id}-{$this->type}-{$this->for}";
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
        // TODO: Handle creating Transaction Summary
    }
}

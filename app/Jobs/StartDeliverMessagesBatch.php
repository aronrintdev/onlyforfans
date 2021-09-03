<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use App\Models\Financial\Account;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Enums\Financial\TransactionSummaryTypeEnum as SummaryType;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\Log;

class StartDeliverMessagesBatch implements ShouldQueue
{
    use Batchable,
        Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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

        // Start creating job batches
        // $query->cursor()->each(function ($chatmessage) {
        //     $this->batch()->add(

        //     );
        // } );


    }
}

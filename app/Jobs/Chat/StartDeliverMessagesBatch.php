<?php

namespace App\Jobs\Chat;

use App\Models\Chatmessage;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

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
        $query = Chatmessage::notDelivered()->scheduleReady();
        $query->cursor()->each(function ($chatmessage) {
            $this->batch()->add(
                DeliverChatmessage::dispatch($chatmessage)
            );
        });
    }
}

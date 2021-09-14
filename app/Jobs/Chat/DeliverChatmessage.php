<?php

namespace App\Jobs\Chat;

use App\Models\Chatmessage;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class DeliverChatmessage implements ShouldQueue, ShouldBeUnique
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
     * Delete the job if its models no longer exist.
     *
     * @var bool
     */
    public $deleteWhenMissingModels = true;

    /**
     * Account instance
     * @var \App\Models\Financial\Account
     */
    protected $chatmessage;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Chatmessage $chatmessage)
    {
        $this->chatmessage = $chatmessage->withoutRelations();
    }

    /**
     * Job's lock id
     */
    public function uniqueId()
    {
        return "deliver-chatmessage-{$this->chatmessage->id}";
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

        if ($this->chatmessage->is_delivered) {
            return;
        }

        if ($this->chatmessage->deliver()) {
            return;
        }

        // Message failed to deliver
        $this->fail();
    }
}

<?php

namespace App\Console\Commands;

use App\Enums\WebhookStatusEnum;
use App\Jobs\ProcessSegPayWebhook;
use App\Models\Webhook;
use Illuminate\Bus\Dispatcher;
use Illuminate\Console\Command;

class WebhooksDispatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webhooks:dispatch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatches jobs for unhandled webhooks';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Webhook::where('status', WebhookStatusEnum::UNHANDLED)->where('type', 'SegPay')->each(function(Webhook $webhook) {
            $this->info("Dispatching job for segpay webhook {$webhook->id}");
            app(Dispatcher::class)->dispatch(new ProcessSegPayWebhook($webhook));
            // ProcessSegPayWebhook::dispatch($webhook);
        });

        return 0;
    }
}

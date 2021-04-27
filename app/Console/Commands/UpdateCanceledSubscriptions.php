<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class UpdateCanceledSubscriptions extends Command
{
    protected $signature = 'subscription:update-canceled';

    protected $description = 'Sets access of active due subscriptions and deactivates them';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $count = Subscription::active()->canceled()->due()->count();
        if ($count > 0) {
            Log::info("Processing: {$count} canceled due subscriptions");
            if (Config::get('app.env') !== 'testing' ) {
                $this->info("Processing: {$count} canceled due subscriptions");
            }
            Subscription::active()->canceled()->due()
                ->with([ 'user', 'subscribable' ])
                ->cursor()->each(function(Subscription $subscription) {
                    $subscription->subscribable->revokeAccess($subscription->user);
                    $subscription->active = false;
                    $subscription->save();
            });
            Log::info("Processed: {$count} canceled due subscriptions");
            if (Config::get('app.env') !== 'testing') {
                $this->info("Processed: {$count} canceled due subscriptions");
            }
        }
        return 0;
    }
}
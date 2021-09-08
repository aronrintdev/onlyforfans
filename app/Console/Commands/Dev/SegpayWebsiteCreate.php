<?php

namespace App\Console\Commands\Dev;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

use App\Enums\Financial\AccountTypeEnum;
use App\Models\Financial\Account;
use App\Models\Financial\SegpayWebsite;
use App\Models\Financial\Transaction;
use App\Models\Timeline;

class SegpayWebsiteCreate extends Command
{
    protected $signature = 'segpay:website-create {timeline?}';

    protected $description = '[DEV only] test segpay website api sending for a timeline';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $timelineId = $this->argument('timeline');
        if (isset($timelineId)) {
            $timeline = Timeline::find($timelineId);
        }
        if (!isset($timeline)) {
            $timeline = Timeline::first();
        }

        $segpayWebsite = SegpayWebsite::createAndSendFor($timeline);
        dump($segpayWebsite);

        $this->output->writeln('Finished for timeline: ' . $timeline->id);
        return 0;
    }
}

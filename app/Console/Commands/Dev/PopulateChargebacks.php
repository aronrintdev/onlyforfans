<?php
namespace App\Console\Commands\Dev;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

use App\Enums\Financial\AccountTypeEnum;
use App\Models\Financial\Account;
use App\Models\Financial\Transaction;

class PopulateChargebacks extends Command
{
    protected $signature = 'populate:chargebacks';

    protected $description = '[DEV only] add some chargebacks for test/dev purposes';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $transactions = Transaction::whereHas( 'Account', function($q1) {
            $q1->where('type', AccountTypeEnum::IN);
        })->take(1)->get();

        $transactions->each( function($t) {
            $t->chargeback();
        });
        dd($transactions);

        return 0;
    }
}

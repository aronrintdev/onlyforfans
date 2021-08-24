<?php

namespace App\Console\Commands\Financial;

use Illuminate\Console\Command;
use App\Models\Financial\Account;
use Illuminate\Events\Dispatcher;
use App\Jobs\Financial\UpdateAccountBalance;
use App\Jobs\Financial\UpdatePendingBalance;

class DispatchAccountBalanceUpdates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'financial:dispatch-account-balance-updates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatches Account Balance Update';

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
        $query = Account::where('owner_type', '!=', 'financial_system_owner')
            ->whereHas( 'transactions', function ($q) {
                $q->pending();
            });
        $count = $query->count();
        $this->output->writeln('');
        $this->output->writeln(" == Dispatching updates for the balances of $count user accounts ==");
        $this->output->writeln('');
        $this->output->writeln(UpdateAccountBalance::class);
        $query->get()->each(function ($account) use ($count) {
            UpdateAccountBalance::dispatch($account);
            // app(Dispatcher::class)->dispatch(new UpdateAccountBalance($account));
            // app(Dispatcher::class)->dispatch(new UpdatePendingBalance($account));
        });

        $this->output->writeln('');
        $this->output->writeln(' == Finished ==');
        $this->output->writeln('');

        return 0;
    }
}

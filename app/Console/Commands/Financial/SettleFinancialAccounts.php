<?php

namespace App\Console\Commands\Financial;

use Illuminate\Console\Command;
use App\Models\Financial\Account;

class SettleFinancialAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'financial:settle_accounts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Settles the entire systems accounts';

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
        $count = Account::where('owner_type', '!=', 'financial_system_owner')->count();
        $this->output->writeln('');
        $this->output->writeln(" == Settling the balances of $count user accounts ==");
        $this->output->writeln('');
        Account::where('owner_type', '!=', 'financial_system_owner')->get()->each(function ($account) use ($count) {
            static $iter = 1;
            $this->output->writeln("({$iter} of {$count}): Updating Balance for {$account->name}");
            $account->settleBalance();
            $iter++;
        });

        $count = Account::where('owner_type', 'financial_system_owner')->count();
        $this->output->writeln('');
        $this->output->writeln(" == Settling the balances of $count system owner accounts (fees) ==");
        $this->output->writeln('');
        Account::where('owner_type', 'financial_system_owner')->each(function ($account) {
            $this->output->writeln("Updating Balance for {$account->name}");
            $account->settleBalance();
        });

        $this->output->writeln('');
        $this->output->writeln(' == Finished ==');
        $this->output->writeln('');

        return 0;
    }
}

<?php

namespace App\Console\Commands\Financial;

use Illuminate\Console\Command;
use App\Models\Financial\Account;
use App\Models\Financial\Earnings;
use App\Models\Financial\Wallet;

class UpdateAccountNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'financial:update-account-names';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates Internal Account Names';

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
        $query = Account::isInternal();
        $count = $query->count();
        $this->output->writeln('');
        $this->output->writeln(" == Updating names of $count accounts ==");
        $this->output->writeln('');
        $query->get()->each(function ($account) use ($count) {
            static $iter = 1;
            if ($account->resource_type === Wallet::getMorphStringStatic()) {
                $account->name = $account->owner->username . ' Wallet Account';
            } else if ($account->resource_type === Earnings::getMorphStringStatic()) {
                $account->name = $account->owner->username . ' Earnings Account';
            } else {
                $account->name = $account->owner->username . ' Balance Account';
            }

            $account->save();

            $this->output->writeln("({$iter} of {$count}): Updated Account Name: {$account->name}");
            $iter++;
        });

        $this->output->writeln('');
        $this->output->writeln(' == Finished ==');
        $this->output->writeln('');

        return 0;
    }
}

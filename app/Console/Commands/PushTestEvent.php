<?php

namespace App\Console\Commands;

use App\Events\AdminTest;
use Illuminate\Console\Command;

class PushTestEvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'event:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push a test admin event';

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
        AdminTest::dispatch('Test Event');

        return 0;
    }
}

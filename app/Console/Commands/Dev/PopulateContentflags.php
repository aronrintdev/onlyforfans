<?php
namespace App\Console\Commands\Dev;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

//use App\Enums\Financial\AccountTypeEnum;
//use App\Models\Post;
use App\Models\Mediafile;

class PopulateContentflags extends Command
{
    protected $signature = 'populate:contentflags';

    protected $description = '[DEV only] flag some content for test/dev purposes';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $mediafiles = Mediafile::inRandomOrder()->take(10)->get();

        $mediafiles->each( function($t) {
            $t->chargeback();
        });
        //dd($transactions);

        return 0;
    }
}

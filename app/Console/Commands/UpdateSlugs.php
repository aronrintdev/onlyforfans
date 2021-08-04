<?php
namespace App\Console\Commands;

use DB;
use App;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class UpdateSlugs extends Command
{
    protected $signature = 'update:slugs {model}';

    protected $description = 'Development-only script to update slugs in the model provided';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $isEnvLocal = App::environment(['local']);
        $isEnvTesting = App::environment(['testing']);
        $dbName = Config::get('database.connections.primary.database');
        $this->info( '%%% DB Name: '.$dbName);
        $this->info( '%%% Is env local?: '.($isEnvLocal?'true':'false') );
        $this->info( '%%% Is env testing?: '.($isEnvTesting?'true':'false') );
        if ( $dbName !== 'fansplat_dev_test' && !$isEnvTesting ) {
            throw new Exception('Environment not in whitelist: '.App::environment());
        }

        $model = $userId = $this->argument('model');

        $M = "App\\Models\\".$model;
        $count = $M::count();
        $this->info( ' - Setting '.$count.' slugs in model "'.$M.'"...');
        $objs = $M::get();
        $objs->each( function($o) {
            $o->slug = null;
            $o->save();
        });
        //DB::table($t)
    }

}


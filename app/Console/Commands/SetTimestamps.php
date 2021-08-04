<?php
namespace App\Console\Commands;

use Faker\Factory as Faker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App;
use DB;
use Exception;
use App\Models\Timeline;
use App\Models\Story;
use Illuminate\Support\Facades\Config;

class SetTimestamps extends Command
{
    protected $signature = 'set:timestamps {model}';

    protected $description = 'Development-only script to set meaningful timestamps for dev & testing';
    protected $faker;

    public function __construct()
    {
        parent::__construct();
        $this->faker = Faker::create();
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

        // [timelines]
        $timelines = Timeline::get();
        $max = $timelines->count();
        $timelines->each( function($t) use($max) {
            static $iter = 1;
            $this->output->writeln("  - Updating timelines timestamps $iter / $max");
            $ts = $this->faker->dateTimeBetween($startDate = '-5 years', $endDate = '-1 years');
            $t->created_at = $ts;
            $t->updated_at = $ts;
            $t->save();

            // [stories]
            $this->output->writeln("    ~ Updating timeline's stories ...");
            $t->stories->each( function($s) use($ts) {
                $ts2 = $this->faker->dateTimeBetween($ts, $endDate = 'now');
                $s->created_at = $ts2;
                $s->updated_at = $ts2;
                $s->save();
            });
            $iter++;
        });

    }

}


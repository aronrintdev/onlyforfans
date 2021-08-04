<?php
namespace App\Console\Commands;

use DB;
use App;
use Exception;
use App\Models\Story;
use App\Models\Timeline;
use App\Models\Storyqueue;
use Faker\Factory as Faker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class UpdateStoryqueues extends Command
{
    protected $signature = 'update:storyqueues';

    protected $description = 'Development-only script to fill [storyqueues] for followers x stories';
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
            //throw new Exception('Environment not in whitelist: '.App::environment());
        }

        // Delete existing storyqueues 
        DB::table('storyqueues')->delete();

        // [timelines]
        $timelines = Timeline::get();
        $max = $timelines->count();
        $timelines->each( function($t) use($max) {
            static $iter = 1;
            $followers = $t->followers;
            $this->output->writeln("  - Updating [storyqueus] for timeline: ".$t->slug.", # followers: ".$followers->count()." ($iter / $max)");
            $t->stories->each( function($s) use(&$t, &$followers) {
                $followers->each( function($f) use(&$t, &$s) {
                    DB::table('storyqueues')->insert([
                            'viewer_id' => $f->id,
                            'story_id' => $s->id,
                            'timeline_id' => $t->id,
                            'created_at' => $s->created_at, // %NOTE: use create date of *story* (!)
                            'updated_at' => $s->created_at,
                        ]);
                });
            });
            $iter++;
        });

    }

}


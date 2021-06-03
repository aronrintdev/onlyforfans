<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App;
use DB;
use Exception;
use App\Models\Mediafile;
use App\Models\User;
use App\Models\Timeline;

class UpdateMediafilesNullResource extends Command
{
    protected $signature = 'update:mediafiles_null_resource';

    protected $description = 'Development-only script to repair [mediafiles] records that have NULL for resource_type and resource_id fields';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $isEnvLocal = App::environment(['local']);
        $isEnvTesting = App::environment(['testing']);
        $dbName = env('DB_DATABASE');
        $this->info( '%%% DB Name: '.$dbName);
        $this->info( '%%% Is env local?: '.($isEnvLocal?'true':'false') );
        $this->info( '%%% Is env testing?: '.($isEnvTesting?'true':'false') );
        if ( $dbName !== 'fansplat_dev_test' && !$isEnvLocal ) {
            throw new Exception('Environment not in whitelist: '.App::environment());
        }

        $mediafiles = Mediafile::whereNull(['resource_type', 'resource_id'])
            ->whereIn('mftype', ['avatar', 'cover'])
            ->where(function ($query) {
                $query->whereNull('resource_type')->orWhereNull('resource_id');
            })->get(); // ->map->only('mftype', 'resource_type', 'resource_id');

        $mediafiles->each( function($mf) {
            switch ($mf->mftype) {
            case 'avatar':
                $timeline = Timeline::where('avatar_id', $mf->id)->first();
                break;
            case 'cover':
                $timeline = Timeline::where('cover_id', $mf->id)->first();
                break;
            }
            $mf->resource_type = 'users';
            $mf->resource_id = $timeline->user->id;
            $mf->save();
        });

        //dd($mediafiles);
        //dd($mediafiles->toArray());
    }

}


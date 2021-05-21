<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
//use Intervention\Image\Facades\Image;
use App;
use DB;
use Exception;
use App\Models\Mediafile;
use App\Enums\MediafileTypeEnum;

class DeleteMediafileAssets extends Command
{
    protected $signature = 'delete:mfassets {take?}';

    protected $description = 'A post-processing script on the [mediafiles] table to permanently delete images and other assets in S3 or other storage';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $take = $this->argument('take');
        $take = 5;
        $mediafiles = Mediafile::onlyTrashed()->take($take)->get();
        $mediafiles->each( function($mf) {
            if ( !$mf->trashed() ) {
                throw new Exception('not trashed!');
            }
            $mf->diskmediafile->forceDeleteAll();
            /*
            try {
            } catch (Exception $e) {
                $this->info( '% Could not prcoess file: '.$mf->slug.', '.$e->getMessage());
            }
            */
        });
        return 0;
    }
}

<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use App;
use DB;
use Exception;
use App\Models\Mediafile;
use App\Enums\MediafileTypeEnum;

class SetMediafileBasename extends Command
{
    protected $signature = 'setmf:basenames {take?}';

    protected $description = 'A post-processing script on the [mediafiles] table to set the basename field';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $take = $this->argument('take');
        $take = 1;
        $mediafiles = Mediafile::where('mimetype', 'image/png') ->whereNull('basename')->take($take)->get();
        $mediafiles->each( function($mf) {
            try {
                $parsedbase = parse_filebase($mf->filepath);
                if ( $parsedbase ) {
                    $mf->basename = $parsedbase;
                    $mf->save();
                }
                /*
                $basename = basename($mf->filepath);
                $parsed = explode('.', $basename);
                if ( $parsed[0] ) {
                    $mf->basename = $parsed[0];
                    $mf->save();
                }
                 */
            } catch (Exception $e) {
                $this->info( '% Could not prcoess file: '.$mf->slug.', '.$e->getMessage());
            }

        });
        return 0;
    }
}

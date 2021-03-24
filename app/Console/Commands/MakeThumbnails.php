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

class MakeThumbnails extends Command
{
    protected $signature = 'make:thumbs {take?}';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        //$MID_THRESHOLD = env('MID_IMG_SIZE_THRESHOLD', 500*1000);
        //$THUMB_THRESHOLD = env('THUMB_IMG_SIZE_THRESHOLD', 50*1000);
        $take = $this->argument('take');
        $take = 1;

        $mediafiles = Mediafile::where('mimetype', 'image/png')
            ->where( function($q1) {
                $q1->where('has_thumb', false)->orWhere('has_mid', false);
            })->take($take)->get();

        $mediafiles->each( function($mf) {
            try {
                $this->info( '% Processing file: '.$mf->slug);

                // Set basename if null
                if ( empty($mf->basename) ) {
                    $parsedbase = parse_filebase($mf->filepath);
                    if ( $parsedbase ) {
                        $mf->basename = $parsedbase;
                        $mf->save();
                    }
                }

                $url = Storage::disk('s3')->temporaryUrl( $mf->filename, now()->addMinutes(10) );

                $ogSize = Storage::disk('s3')->size( $mf->filename );
                $this->info( '    - size: '.$ogSize);

                if ( !$mf->has_thumb ) {
                    $img = Image::make($url);
                    //$img->resize(320, 240)->save('public/bar.jpg', 90, 'jpg');
                    $subFolder = MediafileTypeEnum::getSubfolder($mf->mftype);
                    $img->resize(320, null)->save($subFolder.'/thumb/'.$mf->basename.'.jpg', 90, 'jpg');
                }

                if ( !$mf->has_mid ) {
                    $img = Image::make($url);
                    //$img->resize(320, 240)->save('public/bar.jpg', 90, 'jpg');
                    $subFolder = MediafileTypeEnum::getSubfolder($mf->mftype);
                    $img->resize(1280, null)->save($subFolder.'/mid/'.$mf->basename.'.jpg', 90, 'jpg');
                }

            } catch (Exception $e) {
                $this->info( '% Could not minimize file: '.$mf->slug.', '.$e->getMessage());
            }

        });

        // remove any existing thumb or mid file

        return 0;
    }
}

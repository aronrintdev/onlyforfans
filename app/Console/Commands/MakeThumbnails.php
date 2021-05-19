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
        $take = $this->argument('take');
        //$take = 200;

        $query = Mediafile::whereIn('mimetype', ['image/png', 'image/jpg', 'image/jpeg'])
            ->where( function($q1) {
                $q1->where('has_thumb', false)->orWhere('has_mid', false);
            });
        if ($take) {
            $query->take($take);
        }
        $mediafiles = $query->get();

        $mediafiles->each( function($mf) use($take) {
            static $iter = 1;
            $take = $take ?? 'all';
            try {
                $this->info( "% Minimizing file: ".$mf->slug.", (iter $iter / $take)");

                /*
                // Set basename if null
                if ( empty($mf->basename) ) {
                    $parsedbase = parse_filebase($mf->filepath);
                    if ( $parsedbase ) {
                        $mf->basename = $parsedbase;
                        $mf->save();
                    }
                }
                 */

                $ogSize = Storage::disk('s3')->size( $mf->filename );
                $this->info( '    - size: '.$ogSize);

                if ( !$mf->has_thumb ) {
                    $mf->createThumbnail();
                }

                if ( !$mf->has_mid ) {
                    $mf->createMid();
                }

            } catch (Exception $e) {
                $this->info( '% Could not minimize file: '.$mf->slug.', '.$e->getMessage());
            }

            $iter++;
        });

        // remove any existing thumb or mid file

        return 0;
    }
}

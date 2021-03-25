<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use App;
use DB;
use Exception;
use App\Models\Mediafile;
use App\Models\Post;
use App\Enums\PostTypeEnum;
use App\Enums\MediafileTypeEnum;

class MakeBlurs extends Command
{
    protected $signature = 'make:blurs {take?}';

    protected $description = 'A post-processing script on the [mediafiles] table to create a blurred version of an image when necessary';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $take = $this->argument('take');

        $query = Mediafile::whereIn('mimetype', ['image/png', 'image/jpg', 'image/jpeg'])
            ->whereHasMorph('resource', Post::class, function($q1) {
                $q1->where('type', '<>', PostTypeEnum::FREE);
            })->where('has_blur', false);
        if ($take) {
            $query->take($take);
        }
        $mediafiles = $query->get();

        $mediafiles->each( function($mf) use($take) {
            // Use orig. image as basis, lower quality & blur
            static $iter = 1;
            $take = $take ?? 'all';
            try {
                $this->info( "% Blurring file: ".$mf->slug.", (iter $iter / $take)");

                if ( !$mf->has_blur ) {
                    $mf->createBlur();
                }

            } catch (Exception $e) {
                $this->info( '% Could not blur file: '.$mf->slug.', '.$e->getMessage());
            }

            $iter++;
        });
        return 0;
    }
}

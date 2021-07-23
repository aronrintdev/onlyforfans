<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use App;
use DB;
use Exception;
use App\Models\Mediafile;
use App\Models\Diskmediafile;
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

        $query = Mediafile::whereHas('diskmediafile', function($q1) {
            $q1->whereIn('mimetype', ['image/png', 'image/jpg', 'image/jpeg'])->where('has_blur', false);
        })->whereHasMorph('resource', Post::class, function($q2) {
            $q2->where('type', '<>', PostTypeEnum::FREE);
        });

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

                if ( !$mf->diskmediafile->has_blur ) {
                    $mf->diskmediafile->createBlur();
                }

            } catch (Exception $e) {
                $this->info( '% Could not blur diskmediafile: '.$mf->diskmediafile->slug.', '.$e->getMessage());
                throw $e;
            }

            $iter++;
        });
        return 0;
    }
}

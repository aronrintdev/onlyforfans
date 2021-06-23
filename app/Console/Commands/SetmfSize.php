<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Mediafile;
use App\Models\Diskmediafile;

class SetmfSize extends Command
{
    protected $signature = 'setmf:size';
    protected $description = 'Set orig_size field in [diskmediafiles] if null';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $diskmediafiles = Diskmediafile::whereNull('orig_size')->get();

        $max = $diskmediafiles->count();
        $this->info('SetmfSize - found '.$max.' diskmediafiles with null size...');

        $diskmediafiles->each( function($dmf) use($max) {
            static $iter = 0;
            //$renderUrl = $dmf->renderUrl();
            //$headers = get_headers($renderUrl, 1);
            //dd($headers);
            //$filesize = $headers['Content-Length'];
            $filesize = Storage::disk('s3')->size($dmf->filepath);
            $this->info('['.$iter.'/'.$max.'] Setting size for: '.$dmf->slug.' -> '.$filesize);
            $dmf->orig_size = $filesize;
            $dmf->save();
            $iter++;
        });

    } // handle()
}

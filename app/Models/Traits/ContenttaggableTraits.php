<?php
namespace App\Models\Traits;

use App\Models\Contenttag;
use App\Enums\ContenttagAccessLevelEnum;

trait ContenttaggableTraits
{
    // $_ctag can be a string or array of tags...
    public function addTag($_ctag, $accessLevel=ContenttagAccessLevelEnum::OPEN)
    {
        if ( is_string($_ctag) ) {
            $ctags = collect([$_ctag]);
        } else if ( is_array($_ctag) ) {
            $ctags = collect($_ctag);
        } else {
            return;
        }

        $ids = [];
        $ctags->each( function($str) use (&$ids, $accessLevel) {
            //$str = ltrim($str, '#');
            //$str = rtrim($str, '!');
            $str = trim($str, '#!');
            $ctrecord = Contenttag::where('ctag', $str)->firstOrCreate([
                'ctag' => $str,
            ]);
            $ids[$ctrecord->id] = [ 'access_level' => $accessLevel ];
        });
        $this->contenttags()->syncWithoutDetaching($ids);
    }

}

<?php
namespace App\Models\Traits;

use App\Models\Contenttag;
use App\Enums\ContenttagAccessLevelEnum;

trait ContenttaggableTraits
{
    // $_ctag can be a string or array of tags...
    // $NOTE: this will not remove any tags, that is the responsibility of the caller
    public function addTag($_ctag, $accessLevel=ContenttagAccessLevelEnum::OPEN)
    {
        if ( is_string($_ctag) ) {
            $ctags = collect([$_ctag]);
        } else if ( is_array($_ctag) ) {
            $ctags = collect($_ctag);
        } else if ( $_ctag instanceof \Illuminate\Support\Collection ) {
            $ctags = $_ctag;
        } else {
            return;
        }

        $ids = [];
        $ctags->each( function($str) use (&$ids, $accessLevel) {
            $str = trim($str, '#!'); // remove hashtag and possible '!' at end indicating private/mgmt tag
            $ctrecord = Contenttag::where('ctag', $str)->firstOrCreate([
                'ctag' => $str,
            ]);
            $ids[$ctrecord->id] = [ 'access_level' => $accessLevel ];
        });
        $this->contenttags()->syncWithoutDetaching($ids);
    }

}

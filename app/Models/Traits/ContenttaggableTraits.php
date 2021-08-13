<?php
namespace App\Models\Traits;

use App\Models\Contenttag;

trait ContenttaggableTraits
{
    // $_ctag can be a string or array of tags...
    public function addTag($_ctag)
    {
        if ( is_string($_ctag) ) {
            $ctags = collect([$_ctag]);
        } else if ( is_array($_ctag) ) {
            $ctags = collect($_ctag);
        } else {
            return;
        }

        $ids = [];
        $ctags->each( function($str) use (&$ids) {
            $ctrecord = Contenttag::where('ctag', $str)->firstOrCreate([
                'ctag' => $str,
            ]);
            $ids[] = $ctrecord->id;
            //$this->contenttags()->syncWithoutDetaching($ctrecord->id);
        });
        $this->contenttags()->syncWithoutDetaching($ids);
    }

}

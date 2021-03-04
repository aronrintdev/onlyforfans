<?php 
namespace App\Models;

use App\Interfaces\ShortUuid;
use App\Models\Traits\UsesUuid;
use Illuminate\Support\Collection;
use Cviebrock\EloquentSluggable\Sluggable;
use App\Models\Traits\UsesShortUuid;

class Country extends Model 
{
    use UsesUuid, Sluggable;

    protected $guarded = [ 'id', 'created_at', 'updated_at' ];
    protected $casts = [ 'cattrs' => 'array', 'meta' => 'array', ];

    public static $vrules = [
            'country_name' => 'required|alpha',
            'country_code' => 'required|alpha|between:2,3',
        ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => [ 'country_name' ]
            ]
        ];
    }

    //--------------------------------------------
    // Relations
    //--------------------------------------------

    //--------------------------------------------
    // Methods
    //--------------------------------------------

    // --- Implement Selectable Interface ---

    public static function getSelectOptions($includeBlank=true, $keyField='id', $filters=[]) : array
    {
        $records = self::all(); // %TODO : add filter capability
        $options = [];
        if ($includeBlank) {
            $options[''] = '';
        }
        foreach ($records as $i => $r) {
            $options[$r->{$keyField}] = $r->country_code;
            //$options[$r->{$keyField}] = $r->statcountrye;
        }
        return $options;
    }

}


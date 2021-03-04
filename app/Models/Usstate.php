<?php 
namespace App\Models;

use App\Interfaces\ShortUuid;
use App\Models\Traits\UsesUuid;
use Illuminate\Support\Collection;
use Cviebrock\EloquentSluggable\Sluggable;
use App\Models\Traits\UsesShortUuid;

class Usstate extends Model 
{
    use UsesUuid, Sluggable;

    protected $guarded = [ 'id', 'created_at', 'updated_at' ];
    protected $casts = [ 'cattrs' => 'array', 'meta' => 'array', ];

    public static $vrules = [
            'state_name' => 'required|alpha',
            'state_code' => 'required|alpha|between:2,3',
            'country' => 'required|alpha_dash',
        ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => [ 'state_name' ]
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
            $options[$r->{$keyField}] = $r->state_code;
            //$options[$r->{$keyField}] = $r->state_name;
        }
        return $options;
    }

}

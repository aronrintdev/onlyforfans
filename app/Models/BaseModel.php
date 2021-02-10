<?php

namespace App\Models;

use Ramsey\Uuid\Uuid;
use App\Interfaces\Guidable;

use App\Interfaces\Nameable;
use App\Interfaces\Sluggable;
use App\Models\Traits\ModelTraits;
use App\Interfaces\FieldRenderable;

abstract class BaseModel extends Model implements Nameable, FieldRenderable
{
    use ModelTraits;

    //--------------------------------------------
    // Boot
    //--------------------------------------------
    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if ( $model instanceOf Guidable ) {
                //$model->guid = (string) Str::uuid();
                $model->guid = (string) Uuid::uuid4();
            }
            if ( $model instanceOf Sluggable ) {
                $sluggableFields = self::sluggableFields(); //['string'=>'name'];
                $model->slug = $model->slugify($sluggableFields);
            }
        });
    }

    // Route-model binding custom key
    public function getRouteKeyName() {
        return request()->routeIs('admin.*') ? 'guid' : 'id';
    }

    /*
    // %TODO: test before using 20200521
    public function setJSONFields(array $fields, array $reqAttrs=[]) : array
    {
        foreach ($fields as $f) {
            if ( array_key_exists($f, $reqAttrs) ) {
                $cattrs = $this->{$f}; // current DB value
                // %CAREFUL: preserve any keys in the DB record not in the request
                foreach ($reqAttrs[$f] as $k => $v) {
                    $cattrs[$k] = $v; // overwrite from request
                }
                $reqAttrs[$f] = $cattrs;
            }
        }
        return $reqAttrs;
    }
     */

}

<?php
namespace App;

use Illuminate\Support\Str;
use DB;

trait SluggableTraits {

    // %FIXME: better implementation
    // SEE: https://stackoverflow.com/questions/32989034/laravel-handle-findorfail-on-fail
    public static function findBySlug($slug)
    {
        //return with(new static)->getTable();
        //$record = \App\Models\Scheduleditem::where('slug',$slug)->first();
        $record = self::where('slug',$slug)->first();
        if ( empty($record) ) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Could not find record with slug '.$slug);
        }
        return $record;
    }

    public function slugify(array $sluggableFields, string $slugField='slug', bool $makeUnique=true) : string
    {
        // Get actual contents of the sluggable fields...
        /*
        $sluggables = [];
        foreach ($sluggableFields as $f) {
            $sluggables[] = $this->{$f};
        }
         */
        $sluggables = array_map( function($f) {
            return $this->{$f};
        }, $sluggableFields);

        $tablename = self::getTablename();
        $dbc = $this->getConnectionName();

        return  self::slugifyByTable($tablename, $sluggables, $slugField, $makeUnique, $dbc);
    }

    // $sluggables is an array of strings, ints, values, etc  used to create the slug
    // Example usage of dynamic DB connection:
    //    $obj = new Content();
    //    $obj->setDBConnection($language->slug);
    //    $obj->fill($attrs);
    //    $obj->save();
    public static function slugifyByTable(string $tablename, array $sluggables, string $slugField='slug', bool $makeUnique=true, $dbconnection=null) : string
    {
        $slug = implode('-',$sluggables);
        $slug = preg_replace('~[^\\pL\d]+~u', '-', $slug); // replace non letter or digits by -
        $slug = trim($slug, '-'); // trim
        //$slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug); // transliterate
        $slug = strtolower($slug); // lowercase
        $slug = preg_replace('~[^-\w]+~', '', $slug); // remove unwanted characters
        //$slug = Str::of(implode(' ',$sluggables))->slug('-');
        if ($makeUnique) {
            $numMatches = empty($dbconnection)
                ? DB::table($tablename)->where($slugField, $slug)->count()
                : DB::connection($dbconnection)->table($tablename)->where($slugField, $slug)->count();
            if ($numMatches > 0) {
                $suffix = mt_rand(2,99999);
                $slug = $slug.'-'.$suffix;
                //$slug = Str::of($slug)->append('-'.$suffix);
            }
        }
        return $slug;
    }

    public static function slugifyModel() : string
    {
        // default implemenation, override as needed
        $s = strtolower(static::class);
        return substr( strrchr( $s, '\\' ), 1 );
    }

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

}

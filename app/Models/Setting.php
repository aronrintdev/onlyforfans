<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Schema;

/**
 * Site Settings Model
 */
class Setting extends Model
{
    public $table = 'settings';

    protected $guarded = [ 'id', 'created_at', 'updated_at' ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'    => 'integer',
        'key'   => 'string',
        'value' => 'string',
    ];

    /**
     * Validation rules.
     *
     * @var array
     */
    public static $rules = [];

    public static function set($key, $value = '')
    {
        if (is_array($key)) {
            foreach ($key as $array_key => $array_value) {
                $setting = self::firstOrNew(['key' => $array_key]);
                $setting->value = $array_value;
                $setting->save();
            }
        } else {
            $setting = self::firstOrNew(['key' => $key]);
            $setting->value = $value;
            $setting->save();
        }

        return true;
    }

    public static function get($key, $default = '')
    {
        $result = '';
        if (Schema::hasTable('settings')) {
            $value = DB::table('settings')->where('key', $key)->pluck('value');

            foreach ($value as $val) {
                $result = $val;
            }

            return count($value) > 0 ? $result : $default;
        }

        return $default;
    }

    public static function remove($key)
    {
        $setting = self::where('key', $key);
        $setting->delete();
    }
}

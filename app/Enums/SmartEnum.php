<?php
namespace App\Enums;

/*
Example Usage:

    class FooEnum extends BaseEnum {

        const HOTEL    = 'hotel';
        const FOXTROT  = 'foxtrot';

        public static $keymap = [
            self::HOTEL=>'Hotel,
            self::FOXTROT=>'Foxtrot,
        ];
    }

*/
abstract class SmartEnum {

    public static $keymap;

    public static function isValidKey(?string $key) : bool {
        return array_key_exists($key, static::$keymap);
    }

    // convert to string
    public static function stringify(?string $key) : string {
        if ( !self::isValidKey($key) ) {
            //throw new \Exception('Invalid key '.$key);
            return 'N/A';
        }
        return static::$keymap[$key];
    }

    public static function render(?string $key) : string {
        return self::stringify($key);
    }

    /*
    public static function getVal(?string $key) {
        if ( !self::isValidKey($key) ) {
            throw new \Exception('Invalid key '.$key);
        }
        return static::$keymap[$key];
    }
     */

    public static function slugify(string $key) : string {
        // basically we are just checking if the key is valid and if so returning it
        return self::isValidKey($key) ? $key : null;
    }

    public static function getKeymap() {
        return static::$keymap;
    }

    public static function getKeys() : array
    {
        return array_keys(static::$keymap);
    }

    public static function getKeysCsv() : string
    {
        return implode(',',self::getKeys());
    }

    public static function count() : int
    {
        return count(static::$keymap);
    }

    // Array for a select dropdown form element
    public static function getSelectOptions($includeBlank=false, $keyField='id', $filters=[]) : array
    {
        $list = [];
        if ($includeBlank) {
            $list[''] = '';
        }
        foreach (static::$keymap as $k => $o) {
            $list[$k] = !empty($o['title']) ? $o['title'] : $o;
        }
        return $list;
    }

}
    /*
    public static function description($key) {
        return empty(static::$keymap[$key]['description']) ? 'N/A' : static::$keymap[$key]['description'];
    }
     */

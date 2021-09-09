<?php
use Illuminate\Support\Str;
use App\Models\Model;
use Illuminate\Support\Collection;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\File;

if (!function_exists('nice_bool')) {
    function nice_bool($in, bool $isShort=true, bool $isYesNo=true) : string
    {
        $str = '';
        if ($isYesNo) {
            $str = empty($in) ? 'No' : 'Yes';
        } else {
            $str = empty($in) ? 'False' : 'True';
        }
        return $isShort ?  strtoupper(substr($str, 0, 1)) : $str;
    }
}

if (!function_exists('nice_currency')) {
    // isCents: is amount in cents or in decimal-dollars (assumes USD!)
    function nice_currency($str, $isCents=true) : string {
        $val = $isCents ? ( intval($str)/100 ) : ( intval($str) );
        return '$'.number_format($val, 2, '.',',');
    }
}

if (!function_exists('parse_filebase')) {
    function parse_filebase(string $filepath) : ?string
    {
        $basename = basename($filepath);
        $parsed = explode('.', $basename);
        return $parsed[0] ?? null;
    }
}

if (!function_exists('getModels')) {
    function getModels($interfaces = []): Collection
    {
        $models = collect(File::allFiles(app_path() . '/Models'))
            ->map(function ($item) {
                $path = $item->getRelativePathName();
                $class = sprintf(
                    '\%s%s%s',
                    Container::getInstance()->getNamespace(),
                    'Models\\',
                    strtr(substr($path, 0, strrpos($path, '.')), '/', '\\')
                );

                return $class;
            })
            ->filter(function ($class) use ($interfaces) {
                $valid = false;

                if (class_exists($class)) {
                    $reflection = new ReflectionClass($class);
                    $valid = $reflection->isSubclassOf(Model::class) && !$reflection->isAbstract();
                    if ($valid) {
                        foreach ($interfaces as $interface) {
                            if (!$reflection->implementsInterface($interface)) {
                                $valid = false;
                                break;
                            }
                        }
                    }
                }

                return $valid;
            });
        return $models->values();
    }
}

if (!function_exists('applyDiscount')) {
    function applyDiscount(int $origAmountInCents, int $discountPercent) : int
    {
        $discountInCents = $origAmountInCents * ($discountPercent/100);
        return $origAmountInCents - $discountInCents;
    }
}

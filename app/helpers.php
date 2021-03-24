<?php
use Illuminate\Support\Str;


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

function parse_filebase(string $filepath) : ?string
{
    $basename = basename($filepath);
    $parsed = explode('.', $basename);
    return $parsed[0] ?? null;
}

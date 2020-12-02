<?php
namespace App\Interfaces;

interface Selectable {

    public static function getSelectOptions($includeBlank=true, $keyField='id', $filters=[]) : array;

}

<?php
namespace App\Interfaces;

interface Likeable extends IsModel
{
    public function getPrimaryOwner();
}

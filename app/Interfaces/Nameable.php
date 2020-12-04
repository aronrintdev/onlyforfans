<?php
namespace App\Interfaces;

interface Nameable {

    // Returns a name representing the Model object eg... 
    //   User -> username
    //   Widget -> wname
    //   default ->  guid or slug
    public function renderName() : string;

}

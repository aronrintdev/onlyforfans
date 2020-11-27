<?php
namespace App\Http\Controllers;

use App\Setting;
use Teepluss\Theme\Facades\Theme;

class StoriesController extends AppBaseController
{
    protected function create()
    {
        $create = 'foo';
        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
        $theme->setTitle('Create Story');
        return $theme->scope('stories', compact('create'))
                     ->render();
    }

}

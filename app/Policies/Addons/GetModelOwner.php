<?php

namespace App\Policies\Addons;

trait GetModelOwner
{
    /**
     * Attempt to get the owner of a model
     * @param  object  $model
     */
    protected function getModelOwner($model)
    {
        if ( isset($model->owner) ) {
            return $model->owner;
        }
        if ( isset($model->user) ) {
            return $model->user;
        }
        return null;
    }
}
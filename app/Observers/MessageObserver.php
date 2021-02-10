<?php

namespace App\Observers;

use App\Events\MessagePublished;
use App\Models\User;
use Event;


/**
 * Observes the Users model.
 */
class MessageObserver
{
    /**
     * Function will be triggered when a Message is updated.
     *
     * @param Users $model
     */
    public function saved($model)
    {
        $model->user = $model->user;
        Event::fire(new MessagePublished($model));
    }
}

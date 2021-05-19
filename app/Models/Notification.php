<?php
namespace App\Models;

use Illuminate\Notifications\DatabaseNotification;

//use App\Interfaces\ShortUuid;
//use App\Models\Traits\UsesUuid;
use Illuminate\Support\Collection;
//use App\Models\Traits\UsesShortUuid;

class Notification extends DatabaseNotification
{
    //--------------------------------------------
    // Relations
    //--------------------------------------------

    public function users()
    {
        return $this->belongsTo(User::class, 'notifiable_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model of Session, Mainly used to view current session information for users online.
 */
class Session extends Model
{
    //
    protected $fillable = [
        'id',
        'user_id',
        'user_agent',
        // 'user_name',
        // 'browser',
        // 'os',
        // 'machine_name',
        // 'location',
        'ip_address',
        'last_activity',
        // 'date'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function user()
    {
        $this->belongsTo('App\Models\User');
    }

}

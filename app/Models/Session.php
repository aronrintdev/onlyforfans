<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Model of Session, Mainly used to view current session information for users online.
class Session extends Model
{
    protected $guarded = [ 'id', 'created_at', 'updated_at' ];
    protected $hidden = [];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

}

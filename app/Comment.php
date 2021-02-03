<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

//use Illuminate\Database\Eloquent\SoftDeletes;


class Comment extends Model
{
    //use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    //protected $dates = ['deleted_at'];

    protected $fillable = ['post_id', 'description', 'user_id', 'parent_id'];

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function likes() {
        return $this->morphToMany('App\User', 'likeable', 'likeables', 'likeable_id', 'likee_id')->withTimestamps();
    }

    public function post() {
        return $this->belongsTo('App\Post');
    }

    public function replies() {
        return $this->hasMany('App\Comment', 'parent_id', 'id');
    }
}

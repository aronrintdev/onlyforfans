<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\UsesUuid;

class Lists extends Model
{
    use HasFactory, UsesUuid;
    protected $guarded = [ 'id', 'created_at', 'updated_at' ];
    protected $fillable = ['name', 'creator_id'];

    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'lists_users', 'list_id', 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo('App\Models\User', 'creator_id');
    }
}

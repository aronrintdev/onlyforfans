<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Category extends Model
{
    use SoftDeletes;
    use HasFactory;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'parent_id', 'active',
    ];

    public function categories()
    {
        return $this->belongsTo('App\Page');
    }

    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }
}

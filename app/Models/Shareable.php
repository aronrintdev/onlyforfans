<?php 
namespace App\Models;

use Illuminate\Support\Collection;

class Shareable extends Model 
{
    protected $guarded = [ 'created_at', 'updated_at' ];

    public function shareable() {
        return $this->morphTo();
    }

    public function sharee()
    { 
        return $this->belongsTo(User::class, 'sharee_id');
    }

}


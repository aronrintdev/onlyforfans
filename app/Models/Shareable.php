<?php 
namespace App\Models;

use Illuminate\Support\Collection;
//use App\Interfaces\ShortUuid;
//use App\Models\Traits\UsesUuid;
//use App\Models\Traits\UsesShortUuid;

class Shareable extends Model 
{
    //use UsesUuid;

    protected $guarded = [ 'created_at', 'updated_at' ];

    //--------------------------------------------
    // %%% Relationships
    //--------------------------------------------

    public function shareable() {
        return $this->morphTo();
    }

    public function sharee()
    { 
        return $this->belongsTo(User::class, 'sharee_id');
    }

    //--------------------------------------------
    // %%% Scopes
    //--------------------------------------------

    /*
    public function scopeSort($query, $sortBy, $sortDir='desc')
    {
        $sortDir = $sortDir==='asc' ? 'asc' : 'desc';
        switch ($sortBy) {
        case 'slug':
            $query->orderBy('shareable.slug', $sortDir);
            break;
        case 'created_at':
            $query->orderBy($sortBy, $sortDir);
            break;
        default:
            $query->latest();
        }
        return $query;
    }
     */

}


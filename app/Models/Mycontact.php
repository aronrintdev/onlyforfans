<?php 
namespace App\Models;

use Illuminate\Support\Collection;
//use App\Interfaces\ShortUuid;
use App\Models\Traits\UsesUuid;
//use App\Models\Traits\UsesShortUuid;

class Mycontact extends Model 
{
    use UsesUuid;

    protected $guarded = [ 'created_at', 'updated_at' ];

    //--------------------------------------------
    // %%% Accessors/Mutators | Casts
    //--------------------------------------------

    protected $casts = [
        'cattrs'    => 'array',
        'meta'      => 'array',
    ];

    //--------------------------------------------
    // %%% Relationships
    //--------------------------------------------

    /*
    public function shareable() {
        return $this->morphTo();
    }
     */

    public function contact()
    { 
        return $this->belongsTo(User::class, 'contact_id');
    }

    public function owner()
    { 
        return $this->belongsTo(User::class, 'owner_id');
    }

}


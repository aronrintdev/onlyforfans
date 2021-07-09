<?php
namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Storyqueue extends Model
{
    use SoftDeletes;

    protected $guarded = [ 'id', 'created_at', 'updated_at', ];

    //--------------------------------------------
    // Accessors/Mutators | Casts
    //--------------------------------------------

    //--------------------------------------------
    // Relationships
    //--------------------------------------------

    public function timeline() {
        return $this->belongsTo(Timeline::class);
    }
    public function story() {
        return $this->belongsTo(Story::class);
    }
    public function viewer() {
        return $this->belongsTo(User::class, 'viewer_id');
    }

}

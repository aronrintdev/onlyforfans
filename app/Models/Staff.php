<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;

use App\Models\Traits\UsesUuid;

class Staff extends Model
{
    use UsesUuid, HasFactory;

    protected $guarded = [ 'id', 'created_at', 'updated_at' ];

    //--------------------------------------------
    // Relationships
    //--------------------------------------------
    public function owner() {
        return $this->belongsTo(User::class, 'owner_id');
    }

}

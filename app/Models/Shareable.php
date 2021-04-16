<?php 
namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Interfaces\ShortUuid;
use App\Models\Traits\UsesUuid;
use App\Models\Traits\UsesShortUuid;

class Shareable extends BaseModel 
{
    use UsesUuid, HasFactory;

    protected $guarded = [ 'created_at', 'updated_at' ];

    public function shareable() {
        return $this->morphTo();
    }

    public function sharee()
    { 
        return $this->belongsTo(User::class, 'sharee_id');
    }

}


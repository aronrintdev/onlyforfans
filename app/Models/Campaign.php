<?php
namespace App\Models;

use App\Interfaces\UuidId;

use App\Models\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model implements UuidId
{
    use UsesUuid;

    protected $guarded = [ 'id', 'created_at', 'updated_at' ];

    public function creator()
    {
        return $this->belongsTo(User::class);
    }
}

<?php
namespace App\Models;

use DB;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;

use Carbon\Carbon;
use Ramsey\Uuid\Uuid;

use App\Models\Traits\UsesUuid;
use App\Enums\MessagegroupTypeEnum;

class Chatmessagegroup extends Model
{
    use UsesUuid;

    protected $guarded = [ 'id', 'created_at', 'updated_at', ];

    //--------------------------------------------
    // Accessors/Mutators | Casts | Attributes
    //--------------------------------------------

    protected $casts = [ 'cattrs' => 'array', 'meta' => 'array', ];

    protected $attributes = [ // set defaults
        'mgtype' => MessagegroupTypeEnum::MASSMSG,
    ];

    //--------------------------------------------
    // Relationships
    //--------------------------------------------

    public function chatmessages() {
        return $this->hasMany(Chatmessage::class);
    }

    public function sender() { // aka 'originator'
        return $this->belongsTo(User::class, 'sender_id');
    }

}

<?php
namespace App\Models;

use DB;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;

use Carbon\Carbon;
use Ramsey\Uuid\Uuid;

use App\Enums\CfstatusTypeEnum;

class Contentflag extends Model
{
    use SoftDeletes;

    protected $guarded = [ 'id', 'created_at', 'updated_at', ];

    //--------------------------------------------
    // Accessors/Mutators | Casts | Attributes
    //--------------------------------------------

    protected $casts = [ 'cattrs' => 'array', 'meta' => 'array', ];

    protected $attributes = [ // set defaults
        'cfstatus' => CfstatusTypeEnum::PENDING,
    ];

    //--------------------------------------------
    // Relationships
    //--------------------------------------------

    // The user doing the flagging
    public function flagger() {
        return $this->belongsTo(User::class, flagger_id);
    }

    // The resource whose content is being flagged
    public function flaggable() {
        return $this->morphTo();
    }

}

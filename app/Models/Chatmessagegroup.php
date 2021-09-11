<?php
namespace App\Models;

use DB;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;

use Carbon\Carbon;
use Ramsey\Uuid\Uuid;

use App\Models\Casts\Money as CastsMoney;
use App\Models\Traits\UsesUuid;
use App\Enums\MessagegroupTypeEnum;

class Chatmessagegroup extends Model
{
    use UsesUuid;

    protected $guarded = [ 'id', 'created_at', 'updated_at', ];

    //--------------------------------------------
    // Boot
    //--------------------------------------------

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $price = $model->price->getAmount();
            if ( isset($price) && $price > 0 ) {
                $model->purchase_only = true;
            }
        });
    }

    //--------------------------------------------
    // Accessors/Mutators | Casts | Attributes
    //--------------------------------------------

    protected $casts = [
        'price'         => CastsMoney::class,
        'purchase_only' => 'boolean',
        'cattrs'        => 'array',
        'meta'          => 'array',
    ];

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

    public function scopeIsQueue($query) {
        return $query->withCount('chatmessages')
            ->addSelect(['read_count' => Chatmessage::selectRaw('COUNT(*)')
                ->whereColumn('chatmessagegroup_id', 'chatmessagegroups.id')
                ->where('is_read', 1)
            ])
            ->havingRaw('read_count <> chatmessages_count');
    }

}

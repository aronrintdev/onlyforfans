<?php
namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

use Carbon\Carbon;
use App\Interfaces\Ownable;
use App\Interfaces\UuidId;
use App\Models\Traits\OwnableTraits;
use App\Models\Traits\UsesUuid;

class Chatmessage extends Model implements UuidId, Ownable
{
    use UsesUuid, OwnableTraits;

    protected $guarded = [ 'id', 'created_at', 'updated_at' ];

    //--------------------------------------------
    // %%% Accessors/Mutators | Casts
    //--------------------------------------------

    protected $casts = [
        'cattrs'       => 'array',
        'is_delivered' => 'boolean',
    ];

    //--------------------------------------------
    // %%% Relationships
    //--------------------------------------------

    public function chatthread()
    {
        return $this->belongsTo(ChatThread::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function mediafile()
    {
        return $this->morphOne(Mediafile::class, 'resource');
    }

    //--------------------------------------------
    // %%% Methods
    //--------------------------------------------

    public function deliver()
    {
        // deliver this (scheduled) message (ie, 'unschedule' ?)
    }

    public static function deliverScheduled(int $take=null)
    {
        // deliver all (scheduled) messages if delivery date has passed
        $query = Chatmessage::where('deliver_at', '<=', Carbon::now())->where('is_delivered', 0);
        if ($take) {
            $query->take($take);
        }
        $chatmessages = $query->get();
        $chatmessages->each( function($cm) {
            $cm->is_delivered = true;
            $cm->save();
        });
    }

    public function getOwner(): ?Collection
    {
        return new Collection([$this->sender]);
    }

    public function getPrimaryOwner(): User
    {
        return $this->sender;
    }

    public function rescheduleMessage(int $deliverAt) : Chatmessage
    {
        if ( !$this->is_delivered ) {
            $this->deliver_at = Carbon::createFromTimestamp($deliverAt);
            $this->save();
        }
        return $this;
    }

}

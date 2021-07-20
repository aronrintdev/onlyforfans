<?php
namespace App\Models;

use Illuminate\Support\Collection;

use Carbon\Carbon;
use App\Interfaces\Ownable;
use App\Interfaces\UuidId;
use App\Models\Traits\OwnableTraits;
use App\Models\Traits\UsesUuid;
use Laravel\Scout\Searchable;

class Chatmessage extends Model implements UuidId, Ownable
{
    use UsesUuid, OwnableTraits, Searchable;

    protected $guarded = [ 'id', 'created_at', 'updated_at' ];

    //--------------------------------------------
    // %%% Accessors/Mutators | Casts
    //--------------------------------------------

    protected $casts = [
        'cattrs'       => 'collection',
        'is_delivered' => 'boolean',
    ];

    //--------------------------------------------
    // Boot
    //--------------------------------------------

    public static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            // set the 'updated_at' in corresponding thread
            $model->chatthread->touch();
        });
    }

    //--------------------------------------------
    // %%% Relationships
    //--------------------------------------------

    public function chatthread()
    {
        return $this->belongsTo(Chatthread::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function mediafile()
    {
        return $this->morphOne(Mediafile::class, 'resource');
    }

    public function mediafiles()
    {
        return $this->morphMany(Mediafile::class, 'resource');
    }

    /* ---------------------------------------------------------------------- */
    /*                               Searchable                               */
    /* ---------------------------------------------------------------------- */
    #region Searchable

    public function searchableAs()
    {
        return "chatmessage_index";
    }

    public function getScoutKey()
    {
        return $this->getKey();
    }

    public function getScoutKeyName()
    {
        return 'id';
    }

    /**
     * What model information gets stored in the search index
     * @return array
     */
    public function toSearchableArray()
    {
        return [
            'id' => $this->getKey(),
            'chatthread_id' => $this->chatthread_id,
            'mcontent' => $this->mcontent,
            'is_delivered' => $this->is_delivered,
        ];
    }

    #endregion Searchable
    /* ---------------------------------------------------------------------- */


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

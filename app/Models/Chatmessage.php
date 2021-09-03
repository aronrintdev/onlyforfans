<?php
namespace App\Models;

use Money\Money;
use Carbon\Carbon;

use App\Interfaces\UuidId;
use App\Interfaces\Ownable;
use Laravel\Scout\Searchable;
use App\Models\Traits\UsesUuid;
use App\Events\MessageSentEvent;
use App\Interfaces\Purchaseable;
use App\Models\Financial\Account;
use App\Models\Traits\FormatMoney;
use Illuminate\Support\Collection;
use App\Models\Traits\OwnableTraits;
use App\Models\Traits\ShareableTraits;
use App\Notifications\MessageReceived;
use App\Models\Casts\Money as CastsMoney;
use App\Models\Financial\Traits\HasCurrency;

/**
 * @property string     $id
 * @property string     $chatthread_id
 * @property string     $sender_id
 * @property string     $mcontent
 * @property bool       $purchase_only
 * @property Money      $price
 * @property string     $currency
 * @property Carbon     $deliver_at   - When the message is scheduled to be delivered at
 * @property Carbon     $delivered_at - When the message was delivered at
 * @property bool       $is_delivered
 * @property bool       $is_read
 * @property bool       $is_flagged
 * @property Collection $cattrs
 * @property Carbon     $created_at
 * @property Carbon     $updated_at
 *
 * @property Chatthread $chatthread
 * @property User       $sender
 * @property Mediafile  $mediafile
 * @property Collection $mediafiles
 *
 * @package App\Models
 */
class Chatmessage extends Model implements UuidId, Ownable, Purchaseable
{
    use UsesUuid,
        OwnableTraits,
        Searchable,
        OwnableTraits,
        ShareableTraits,
        HasCurrency,
        FormatMoney;

    protected $guarded = [ 'id', 'created_at', 'updated_at' ];

    //--------------------------------------------
    // %%% Accessors/Mutators | Casts
    //--------------------------------------------
    #region Model Properties

    protected $casts = [
        'price'         => CastsMoney::class,
        'purchase_only' => 'boolean',
        'cattrs'        => 'collection',
        'is_delivered'  => 'boolean',
    ];

    /**
     * Default attribute values
     */
    protected $attributes = [
        'mcontent' => '',
        'is_delivered' => false,
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

    #endregion Model Properties

    //--------------------------------------------
    // %%% Relationships
    //--------------------------------------------
    #region Relationships
    public function chatthread() {
        return $this->belongsTo(Chatthread::class);
    }

    public function sender() {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function sharees() {
        return $this->morphToMany(User::class, 'shareable', 'shareables', 'shareable_id', 'sharee_id')->withTimestamps();
    }

    public function mediafile() {
        return $this->morphOne(Mediafile::class, 'resource');
    }

    public function mediafiles() {
        return $this->morphMany(Mediafile::class, 'resource');
    }

    public function chatmessagegroup() {
        return $this->belongsTo(Chatmessagegroup::class);
    }

    #endregion Relationships

    /* -------------------------------------------------------------------------- */
    /*                                   Scopes                                   */
    /* -------------------------------------------------------------------------- */
    #region Scopes

    /**
     * Messages that are delivered
     */
    public function scopeDelivered($query)
    {
        return $query->where('is_delivered', true);
    }

    /**
     * Order by latest delivered
     */
    public function scopeLatestDelivered($query)
    {
        return $query->delivered()->latest('delivered_at');
    }

    /**
     * Messages that are not delivered
     */
    public function scopeNotDelivered($query)
    {
        return $query->where('is_delivered', false);
    }

    /**
     * Scheduled messages that are ready to be delivered
     */
    public function scopeScheduleReady($query)
    {
        return $query->whereNotNull('deliver_at')->where('deliver_at', '<=', Carbon::now());
    }

    #endregion Scopes

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


    /* -------------------------------------------------------------------------- */
    /*                                Purchaseable                                */
    /* -------------------------------------------------------------------------- */
    #region Purchaseable

    public function verifyPrice($amount, $currency = 'USD'): bool
    {
        if (!$amount instanceof Money) {
            $amount = CastsMoney::toMoney($amount, $currency);
        }
        return $this->price->equals($amount);
    }

    public function getOwnerAccount(string $system, string $currency): Account
    {
        return $this->getOwner()->first()->getEarningsAccount($system, $currency);
    }

    public function getDescriptionNameString(): string
    {
        return 'Comment';
    }

    #endregion Purchaseable
    /* ---------------------------------------------------------------------- */

    //--------------------------------------------
    // %%% Methods
    //--------------------------------------------

    /**
     * Add an array of mediafile attachments
     */
    public function addAttachments(array $attachments = null)
    {
        if (isset($attachments) && count($attachments)) {
            foreach($attachments ?? [] as $attachment) {
                $this->addAttachment($attachment);
            }
        }
    }

    // Add a mediafile attachment
    public function addAttachment($attachment)
    {
        if ($attachment instanceof Mediafile) {
            $diskmediafile = $attachment->diskmediafile;
        } else if ($attachment instanceof Diskmediafile) {
            $diskmediafile = $attachment;
        } else {
            if (gettype($attachment) === 'string') {
                $id = $attachment;
            } else if (gettype($attachment) === 'array') {
                $id = $attachment['id'];
            } else if (gettype($attachment) === 'object') {
                $id = $attachment->id;
            }
            $diskmediafile = Mediafile::with('diskmediafile')->find($id)->diskmediafile;
        }

        $diskmediafile->createReference(
            $this->getMorphString(),
            $this->getKey(),
            $attachment['mfname'],
            'messages'
        );
    }


    public function setPurchaseOnly($price, $currency) : Chatmessage
    {
        $this->purchase_only = true;
        $this->price = CastsMoney::toMoney($price, $currency);
        $this->currency = $currency;
        $this->save();
        return $this;
    }

    public function setDeliverAt(Carbon $deliver_at) : Chatmessage
    {
        $this->deliver_at = $deliver_at;
        $this->save();
        return $this;
    }

    /**
     * Deliver this message
     */
    public function deliver() : bool
    {
        if ( isset($this->deliver_at) && $this->deliver_at > Carbon::now() ) {
            // Message should not be sent yet
            return false;
        }

        $this->delivered_at = Carbon::now();
        $this->is_delivered = true;
        $this->save();

        // Dispatch Message Sent Event for broadcaster
        MessageSentEvent::dispatch($this);

        // send notifications
        foreach ($this->chatthread->participants as $participant) {
            // don't send notification to sender
            if ($participant->id === $this->sender_id) {
                if (isset($this->deliver_at)) {
                    // ?? Notify that scheduled message was sent here?
                }
                continue;
            }

            // check is_muted pivot field ON/OFF state
            if (!$participant->pivot->is_muted) {
                $participant->notify(new MessageReceived($this, $this->sender));
            }
        }

        return true;
    }

    public function getOwner(): ?Collection
    {
        return new Collection([$this->sender]);
    }

    public function getPrimaryOwner(): User
    {
        return $this->sender;
    }

    public function schedule(int $deliverAt) : Chatmessage
    {
        if ( !$this->is_delivered ) {
            $this->deliver_at = Carbon::createFromTimestamp($deliverAt);
            $this->save();
        }
        return $this;
    }

}

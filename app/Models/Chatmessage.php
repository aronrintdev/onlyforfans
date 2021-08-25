<?php
namespace App\Models;

use Money\Money;

use Carbon\Carbon;
use App\Interfaces\UuidId;
use App\Interfaces\Ownable;
use Laravel\Scout\Searchable;
use App\Models\Traits\UsesUuid;
use App\Interfaces\Purchaseable;
use App\Models\Financial\Account;
use App\Models\Traits\FormatMoney;
use Illuminate\Support\Collection;
use App\Models\Traits\OwnableTraits;
use App\Models\Traits\ShareableTraits;
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
 * @property Carbon     $deliver_at
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

    public function setPurchaseOnly($price, $currency) {
        $this->purchase_only = true;
        $this->price = CastsMoney::toMoney($price, $currency);
        $this->currency = $currency;
        $this->save();
    }


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

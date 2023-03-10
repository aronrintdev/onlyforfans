<?php

namespace App\Models;

use Money\Money;
use Carbon\Carbon;
use LogicException;
use App\Apis\Segpay\Api;
use App\Interfaces\Ownable;
use InvalidArgumentException;
use App\Enums\CampaignTypeEnum;
use App\Models\Traits\UsesUuid;
use App\Interfaces\Subscribable;
use App\Models\Financial\Account;
use Illuminate\Support\Collection;
use App\Models\Traits\OwnableTraits;
use Illuminate\Support\Facades\Config;
use App\Enums\Financial\AccountTypeEnum;
use App\Models\Casts\Money as CastsMoney;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Exceptions\InvalidCastException;
use App\Enums\Financial\TransactionTypeEnum;
use App\Models\Financial\Traits\HasCurrency;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Exceptions\InvalidIntervalException;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\InvalidCastException as EloquentInvalidCastException;

/**
 * Subscription model
 *
 * ===== Properties ========================================================== *
 * @property string $id
 * @property string $subscribable_id    - Id of subscribable item
 * @property string $subscribable_type  - Type of subscribable item
 * @property string $user_id            - User subscribing to item
 * @property string $account_id         - Id of account being charged
 * @property bool   $manual_charge      - If the application needs to take care of the charges or if it is done
 *                                          automatically by payment processor
 *
 * @property string|null $initial_period   - The initial period unit if there is an initial period
 * @property int|null    $initial_interval - The initial period interval if there is an initial period
 * @property Money|null  $initial_price    - The initial period price if there is an initial period
 *
 * @property string $period             - Period unit, e.i Daily, Monthly, Yearly
 * @property int    $period_interval    - Period Interval, 15 Daily would be every 15 days, 1 Monthly is every month
 * @property Money  $price              - Price of the subscription
 * @property string $currency           - Currency of the subscription price
 *
 * @property bool   $active             - If the subscription is currently active
 * @property Carbon $canceled_at        - When the subscription was canceled
 * @property string $access_level       - Access level of this subscription
 *
 * @property string|null $campaign_id   - Campaign id if this subscription was activated as part of a campaign
 *
 * @property array  $custom_attributes
 * @property array  $metadata
 *
 * @property string $last_transaction_id - The id of the last transaction for this subscription
 * @property Carbon $next_payment_at     - Timestamp of when the next payment transaction is due to occur.
 * @property Carbon $last_payment_at     - Timestamp of when the last payment transaction occurred.
 *
 * ===== Relations =========================================================== *
 * @property User         $user
 * @property Subscribable $subscribable
 * @property Account      $account
 *
 * ===== Scopes ============================================================== *
 * @method static Builder active()
 * @method static Builder canceled()
 * @method static Builder due()
 * @method static Builder inactive()
 *
 * @package App\Models
 */
class Subscription extends Model implements Ownable
{
    use UsesUuid,
        HasCurrency,
        SoftDeletes,
        HasCurrency,
        OwnableTraits;

    protected $table = 'subscriptions';

    protected $guarded = [];

    protected $dates = [
        'next_payment_at',
        'last_payment_at',
    ];

    protected $casts = [
        'initial_price' => CastsMoney::class,
        'price' => CastsMoney::class,
        'custom_attributes' => 'array',
    ];

    //--------------------------------------------
    // Boot
    //--------------------------------------------

    public static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            if ( $model->subscribable_type === Timeline::getMorphStringStatic() ) {
                // Add Contacts for subscribee and subscriber
                // Note: addContacts function prevents duplicates in Mycontacts
                Mycontact::addContacts(new Collection([
                    $model->user,
                    $model->subscribable->getOwner()->first(),
                ]));
            }
        });
    }

    /* --------------------------- Relationships ---------------------------- */
    #region Relationships

    /**
     * The item being subscribed to. e.i. Timeline
     * @return MorphTo
     *
     * %PSG -> %ERIK : is this the person (timeline) who 'owns' (receiveds) the subscripton? (subscribEE)?
     * %Erik : No, this is the item being subscribed to.
     */
    public function subscribable() 
    {
        return $this->morphTo();
    }

    /**
     * The user who is subscribing to the subscribable item
     * @return BelongsTo
     *
     * %PSG -> %ERIK : is this the person doing the subscribing (subscribER)?
     * %Erik : Yes, this is the subscriber.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The financial account that is being used for subscription payments
     * @return BelongsTo
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * The campaign this subscription was activated under
     * @return BelongsTo
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    #endregion Relationships

    /* ------------------------------- Scopes ------------------------------- */
    #region Scopes
    /**
     * Only return active subscriptions
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Only return canceled subscriptions
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeCanceled($query)
    {
        return $query->whereNotNull('canceled_at');
    }

    /**
     * Only return not canceled subscriptions
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeNotCanceled($query)
    {
        return $query->whereNull('canceled_at');
    }

    /**
     * Return only due subscriptions
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeDue($query)
    {
        return $query->where('next_payment_at', '<=', Carbon::now());
    }

    /**
     * Only return inactive subscriptions
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeInactive($query)
    {
        return $query->where('active', false);
    }

    /**
     * Items that are not canceled that are due for payment by a day or more
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeExpired($query)
    {
        return $query->notCanceled()->where('next_payment_at', '<=', Carbon::now()->addDay());
    }

    #endregion Scopes


    /* ------------------------------ Functions ----------------------------- */
    #region Functions

    /**
     * Cancels an active subscription
     *
     * @return void
     */
    public function cancel()
    {
        // Cancel Payments
        // if ( is segpay subscription ) {
        //     Call SRS cancel account
        // }
        if (isset($this->custom_attributes['segpay_purchase_id'])) {
            $response = Api::cancelSubscription($this->custom_attributes['segpay_purchase_id']);
            // TODO: Check response message from cancel
        }

        // Check if passed next bill date
        $this->canceled_at = Carbon::now();
        if ($this->isDue()) {
            $this->subscribable->revokeAccess($this->user);
            $this->active = false;
            $this->save();
            return;
        }
        $this->save();
        return;
    }

    public function reactivate()
    {
        $transactions = $this->process();
        if (isset($transactions)) {
            $this->canceled_at = null;
            $this->save();
        }
        return $transactions;
    }

    /**
     * Process a subscription transaction
     *
     * @param $force - If true, will ignore time sense last payment and period
     * @return Collection|bool
     */
    public function process($force = false, $options = [])
    {
        if ($force === false && !$this->isDue()) {
            return false;
        }

        if ($this->manual_charge) {
            $this->processManual();
        }

        if ($this->account->type === AccountTypeEnum::IN) {
            $inTransactions = $this->account->moveToWallet($this->price);
            $transactions = $this->account->getWalletAccount()->moveTo(
                $this->subscribable->getOwnerAccount($this->account->system, $this->account->currency),
                $this->price,
                [
                    'ignoreBalance' => true,
                    'type'          => TransactionTypeEnum::SUBSCRIPTION,
                    'resource_type' => $this->subscribable->getMorphString(),
                    'resource_id'   => $this->subscribable->getKey(),
                    'metadata'      => ['subscription' => $this->getKey()],
                ]
            );
            $this->last_transaction_id = $transactions['debit']->getKey();
            $this->last_payment_at = Carbon::now();
            $this->updateNextPayment();
            $this->active = true;
            $this->save();

            // Grant access
            $this->subscribable->grantAccess(
                $this->getOwner()->first(),
                $this->access_level,
                $options['access_cattrs'] ?? [],
                $options['access_meta'] ?? [],
            );

            if (isset($inTransactions)) {
                $transactions['inTransactions'] = $inTransactions;
            }

            return $transactions;
        } else if ($this->account->type === AccountTypeEnum::INTERNAL) {
            $transactions = $this->account->moveTo(
                $this->subscribable->getOwnerAccount($this->account->system, $this->account->currency),
                $this->price,
                [
                    'type'             => TransactionTypeEnum::SUBSCRIPTION,
                    'resource_type' => $this->subscribable->getMorphString(),
                    'resource_id'   => $this->subscribable->getKey(),
                    'metadata'         => ['subscription' => $this->getKey()],
                ]
            );
            $this->last_transaction_id = $transactions['debit'];
            $this->last_payment_at = Carbon::now();
            $this->updateNextPayment();
            $this->active = true;
            $this->save();

            // Grant access
            $this->subscribable->grantAccess(
                $this->getOwner()->first(),
                $this->access_level,
                $options['access_cattrs'] ?? [],
                $options['access_meta'] ?? [],
            );

            return $transactions;
        }
        return false;
    }

    /**
     * Process a subscription manually
     *
     * @return
     */
    public function processManual()
    {
        // TODO: kick off payment processing
    }

    /**
     * Updates the next_payment_at and saves
     *
     * @return void
     */
    public function updateNextPayment()
    {
        if (isset($this->next_payment_at) === false) {
            $this->next_payment_at = Carbon::now();
        }
        $this->next_payment_at = $this->next_payment_at->add($this->period, $this->period_interval);
        $this->save();
    }

    /**
     * Applies a campaigns rules to this subscription
     */
    public function applyCampaign(Campaign $campaign, $initial_period = 'daily', $initial_period_interval = 30)
    {
        if ($campaign->type === CampaignTypeEnum::DISCOUNT) {
            $this->initial_period = $initial_period;
            $this->initial_period_interval = $initial_period_interval;
            $this->initial_price = $campaign->getDiscountPrice($this->price)->getAmount();
            $this->campaign_id = $campaign->id;
            $this->save();
        }
        if ($campaign->type === CampaignTypeEnum::TRIAL) {
            $this->initial_period = $initial_period;
            $this->initial_period_interval = $campaign->trial_days;
            $this->initial_price = 0;
            $this->campaign_id = $campaign->id;
            $this->save();
        }
        $campaign->decrementRemaining();
        return $this;
    }

    #endregion Functions

    /* ------------------------------- Ownable ------------------------------ */
    #region Ownable

    public function getOwner(): ?Collection
    {
        return new Collection([ $this->user ]);
    }

    #endregion Ownable


    /* ---------------------------- Verifications --------------------------- */
    #region Verifications

    /**
     * Checks if user is currently subscribed to a subscribable item
     *
     * @param User $user
     * @param Subscribable $subscribable
     * @return bool
     */
    public static function isSubscribed(User $user, Subscribable $subscribable): bool
    {
        return Subscription::where('user_id', $user->getKey())
            ->where('subscribable_type', $subscribable->getMorphString())
            ->where('subscribable_id', $subscribable->getKey())
            ->whereNotNull('canceled_at')
            ->count() > 0;
    }

    public static function canResubscribe(User $user, Subscribable $subscribable): bool
    {
        return Subscription::where('user_id', $user->getKey())
            ->where('subscribable_type', $subscribable->getMorphString())
            ->where('subscribable_id', $subscribable->getKey())
            ->canceled()->where(
                'canceled_at',
                '<=',
                Carbon::now()->subtract(
                    Config::get('subscriptions.resubscribeWaitPeriod.unit'),
                    Config::get('subscriptions.resubscribeWaitPeriod.interval')
                )
            )->count() > 0;
    }

    /**
     * Checks if the subscription is due to be renewed
     * @return bool
     */
    public function isDue(): bool
    {
        if (isset($this->next_payment_at) === false) {
            return true;
        }
        return $this->next_payment_at->lessThanOrEqualTo(Carbon::now());
    }

    public function isActive(): bool
    {
        return (bool)$this->active;
    }

    public function isCanceled(): bool
    {
        return !isset($this->canceled_at);
    }

    #endregion Verification

}

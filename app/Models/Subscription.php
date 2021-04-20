<?php

namespace App\Models;

use App\Enums\Financial\AccountTypeEnum;
use App\Enums\Financial\TransactionTypeEnum;
use Money\Money;
use Carbon\Carbon;
use App\Models\Traits\UsesUuid;
use App\Interfaces\Subscribable;
use App\Models\Financial\Account;
use Illuminate\Support\Collection;
use App\Models\Casts\Money as CastsMoney;
use App\Models\Financial\Traits\HasCurrency;
use Carbon\Exceptions\InvalidCastException;
use Carbon\Exceptions\InvalidIntervalException;
use Illuminate\Database\Eloquent\SoftDeletes;
use InvalidArgumentException;

/**
 * A subscription
 *
 * @property string $id
 * @property string $subscribable_id    - Id of subscribable item
 * @property string $subscribable_type  - Type of subscribable item
 * @property string $user_id            - User subscribing to item
 * @property string $account_id         - Id of account being charged
 * @property bool   $manual_charge      - If the application needs to take care of the charges or if it is done
 *                                          automatically by payment processor
 * @property string $period             - Period unit, e.i Daily, Monthly, Yearly
 * @property int    $period_interval    - Period Interval, 15 Daily would be every 15 days, 1 Monthly is every month
 * @property Money  $price              - Price of the subscription
 * @property string $currency           - Currency of the subscription price
 * @property bool   $active             - If the subscription is currently active
 * @property string $access_level       - Access level of this subscription
 * @property array  $custom_attributes
 * @property array  $metadata
 * @property string $last_transaction_id - The id of the last transaction for this subscription
 * @property Carbon $next_payment_at    - Timestamp of when the next payment transaction is due to occur.
 * @property Carbon $last_payment_at    - Timestamp of when the last payment transaction occurred.
 *
 * Relations
 * @property User         $user
 * @property Subscribable $subscribable
 * @property Account      $account
 *
 * @package App\Models
 */
class Subscription extends Model
{
    use UsesUuid,
        HasCurrency,
        SoftDeletes,
        HasCurrency;

    protected $table = 'subscriptions';

    protected $guarded = [];

    protected $dates = [
        'next_payment_at',
        'last_payment_at',
    ];

    protected $casts = [
        'price' => CastsMoney::class,
    ];

    /* --------------------------- Relationships ---------------------------- */
    #region Relationships
    public function subscribable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function account()
    {
        return $this->hasOne(Account::class);
    }

    #endregion Relationships

    /* ------------------------------ Functions ----------------------------- */
    #region Functions

    /**
     * Process a subscription transaction
     *
     * @param $force - If true, will ignore time sense last payment and period
     * @return Collection|bool
     */
    public function process($force = false)
    {
        if ($force === false && $this->due()) {
            return false;
        }

        if ($this->manual_charge) {
            $this->processManual();
        }

        if ($this->account->type === AccountTypeEnum::IN) {
            $this->account->moveToInternal($this->price);
            $transactions = $this->account->getInternalAccount()->moveTo(
                $this->subscribable->getOwnerAccount($this->account->system, $this->account->currency),
                $this->price,
                [
                    'ignoreBalance'    => true,
                    'type'             => TransactionTypeEnum::SUBSCRIPTION,
                    'purchasable_type' => $this->subscribable->getMorphString(),
                    'purchasable_id'   => $this->subscribable->getKey(),
                    'metadata'         => ['subscription' => $this->getKey()],
                ]
            );
            $this->last_transaction_id = $transactions['debit'];
            $this->last_payment_at = Carbon::now();
            $this->updateNextPayment();
            return $transactions;
        } else if ($this->account->type === AccountTypeEnum::INTERNAL) {
            $transactions = $this->account->moveTo(
                $this->subscribable->getOwnerAccount($this->account->system, $this->account->currency),
                $this->price,
                [
                    'type'             => TransactionTypeEnum::SUBSCRIPTION,
                    'purchasable_type' => $this->subscribable->getMorphString(),
                    'purchasable_id'   => $this->subscribable->getKey(),
                    'metadata'         => ['subscription' => $this->getKey()],
                ]
            );
            $this->last_transaction_id = $transactions['debit'];
            $this->last_payment_at = Carbon::now();
            $this->updateNextPayment();
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

    #endregion Functions


    /* ---------------------------- Verifications --------------------------- */
    #region Verifications

    /**
     * Checks if the subscription is due to be renewed
     * @return bool
     */
    public function due(): bool
    {
        if (isset($this->next_payment_at) === false) {
            return true;
        }
        return $this->next_payment_at->greaterThanOrEqualTo(Carbon::now());
    }

    #endregion Verification

}

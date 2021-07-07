<?php

namespace App\Models;

use Exception;
use Money\Money;
use Carbon\Carbon;
use App\Interfaces\Tippable;
use App\Interfaces\Messagable;
use App\Models\Traits\UsesUuid;
use App\Events\MessageSentEvent;
use App\Models\Financial\Account;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Enums\SubscriptionPeriodEnum;
use Illuminate\Database\Eloquent\Model;
use App\Enums\Financial\AccountTypeEnum;
use App\Models\Casts\Money as CastsMoney;
use App\Enums\Financial\TransactionTypeEnum;
use App\Models\Financial\Traits\HasCurrency;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property string       $id                  - Primary Key
 * @property string       $sender_id           - User Id of tip sender
 * @property string       $receiver_id         - User Id of tip receiver
 * @property string|null  $tippable_type       - Type of Tippable Item
 * @property string|null  $tippable_id         - Id of Tippable Item
 * @property string|null  $message             - Optional Tip Message
 * @property string       $currency            - Currency of the tip
 * @property Money        $amount              - The integer value of the amount being tipped
 * @property string       $period              - The reoccurring period of the tip. Default: `single` for one time tip
 * @property integer|null $period_interval     - The reoccurring interval
 * @property boolean      $active              - If the reoccurring period is active or not. Default: `false`
 * @property boolean      $manual_charge       - If system is responsible for kicking of reoccurring tips when due
 * @property string       $last_transaction_id - Id of the last transaction for this tip
 * @property Carbon       $canceled_at         - Time when the reoccurring tip was canceled
 * @property Carbon       $next_payment_at     - Time when the next payment is due
 * @property Carbon       $last_payment_at     - Time at which the last payment occurred
 * @property Collection   $custom_attributes   - Custom Attributes Collection
 * @property Collection   $metadata            - Metadata Collection
 * @property Carbon       $created_at          - Created Timestamp
 * @property Carbon       $updated_at          - Updated Timestamp
 * @property Carbon|null  $deleted_at          - Deleted Timestamp
 *
 *
 * Relationships
 * @property User $sender            - Sender of tip
 * @property User $receiver          - Receiver of tip
 * @property Tippable|null $tippable - Tippable Item
 *
 * @package App\Models
 */
class Tip extends Model implements Messagable
{
    use UsesUuid,
        HasCurrency,
        SoftDeletes,
        HasFactory;

    /* ---------------------------------------------------------------------- */
    /*                            Model Properties                            */
    /* ---------------------------------------------------------------------- */
    #region Model Properties
    protected $table = 'tips';

    protected $guarded = [];

    protected $casts = [
        'custom_attributes' => 'collection',
        'metadata'          => 'collection',
        'amount'            => CastsMoney::class,
    ];

    protected $dates = [
        'canceled_at',
        'next_payment_at',
        'last_payment_at',
    ];



    #endregion Model Properties
    /* ---------------------------------------------------------------------- */

    /* ---------------------------------------------------------------------- */
    /*                              Relationships                             */
    /* ---------------------------------------------------------------------- */
    #region Relationships

    public function account()
    {
        return $this->hasOne(Account::class);
    }

    public function sender()
    {
        return $this->hasOne(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->hasOne(User::class, 'receiver_id');
    }

    public function tippable()
    {
        return $this->morphTo();
    }

    #endregion Relationships
    /* ---------------------------------------------------------------------- */

    /* ---------------------------------------------------------------------- */
    /*                                 Scopes                                 */
    /* ---------------------------------------------------------------------- */
    #region Scopes

    public function scopeOneTime($query)
    {
        return $query->where('period', 'single');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeCanceled($query)
    {
        return $query->whereNotNull('canceled_at');
    }

    public function scopeNotCanceled($query)
    {
        return $query->whereNull('canceled_at');
    }

    public function scopeDue($query)
    {
        return $query->where('next_payment_at', '<=', Carbon::now());
    }

    public function scopeExpired($query)
    {
        return $query->notCanceled()->where('next_payment_at', '<=', Carbon::now()->addDay());
    }

    #endregion Scopes
    /* ---------------------------------------------------------------------- */

    /* ---------------------------------------------------------------------- */
    /*                                Functions                               */
    /* ---------------------------------------------------------------------- */
    #region Functions

    /**
     * Process a tip transaction
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

        // Save financial account if one is not yet set for this tip
        if (!$this->has('account')) {
            if (!isset($options['account_id'])) {
                // TODO: Throw exception
                return false;
            }
            $this->account_id = $options['account_id'];
            $this->save();
        }

        // Create transaction record
        $ignoreBalance = false;
        if ($this->account->type === AccountTypeEnum::IN) {
            $inTransactions = $this->account->moveToInternal($this->amount);
            $ignoreBalance = true;
        }

        $transactions = $this->account->getInternalAccount()->moveTo(
            $this->receiver->getOwnerAccount($this->account->system, $this->account->currency),
            $this->amount,
            [
                'ignoreBalance'    => $ignoreBalance,
                'type'             => TransactionTypeEnum::TIP,
                'resource_type'    => $this->getMorphString(),
                'resource_id'      => $this->getKey(),
            ]
        );

        $this->last_transaction_id = $transactions['debit'];
        $this->last_payment_at = Carbon::now();
        if ($this->period !== SubscriptionPeriodEnum::SINGLE) {
            $this->updateNextPayment();
        }
        $this->active = true;
        $this->save();

        if (isset($inTransactions)) {
            $transactions['inTransactions'] = $inTransactions;
        }

        // Create a message for this tip processing
        $chatThread = Chatthread::findOrCreateDirectChat($this->sender, $this->receiver);
        $message = $chatThread->sendMessage($this->sender, '', new Collection([ 'tip_id' => $this->getKey() ]));
        try {
            MessageSentEvent::dispatch($message);
        } catch (Exception $e) {
            Log::warning('Tip->process()::sendMessage().broadcast Failed', [
                'msg' => $e->getMessage(),
            ]);
        }

        return $transactions;
    }

    /**
     * Process a reoccurring tip manually
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
    /* ---------------------------------------------------------------------- */

    /* ---------------------------------------------------------------------- */
    /*                              Verifications                             */
    /* ---------------------------------------------------------------------- */
    #region Verifications

    /**
     * Checks if the tip is due to be renewed
     * @return bool
     */
    public function isDue(): bool
    {
        if ($this->period === SubscriptionPeriodEnum::SINGLE) {
            // If transaction has not happened, then is Due
            return ! isset($this->last_transaction_id);
        }
        if (isset($this->next_payment_at) === false) {
            return true;
        }
        return $this->next_payment_at->lessThanOrEqualTo(Carbon::now());
    }

    #endregion Verification
    /* ---------------------------------------------------------------------- */
}

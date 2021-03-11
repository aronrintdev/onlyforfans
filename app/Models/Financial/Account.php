<?php

namespace App\Models\Financial;

use App\Enums\Financial\AccountTypeEnum;
use App\Interfaces\Ownable;
use App\Jobs\Financial\UpdateAccountBalance;

use App\Models\Traits\OwnableTraits;
use App\Models\Traits\UsesUuid;
use App\Models\Casts\Money;

use App\Models\Financial\Exceptions\Account\IncorrectTypeException;
use App\Models\Financial\Exceptions\InvalidTransactionAmountException;
use App\Models\Financial\Exceptions\Account\InsufficientFundsException;
use App\Models\Financial\Exceptions\Account\TransactionNotAllowedException;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Account extends Model implements Ownable
{
    use OwnableTraits,
        UsesUuid,
        HasFactory;

    protected $table = 'financial_accounts';

    protected $guarded = [
        'verified',
        'can_make_transaction',
    ];

    protected $dates = [
        'balance_last_updated_at',
        'pending_last_updated_at',
        'hidden_at',
    ];

    protected $casts = [
        'balance' => Money::class,
        'pending' => Money::class,
    ];

    protected static function booted()
    {
        static::creating(function (self $model): void {
            if (!isset($model->system)) {
                $model->system = Config::get('transaction.default', '');
            }
        });
    }


    /* ------------------------------ Relations ----------------------------- */
    /**
     * Owner of account
     */
    public function owner()
    {
        return $this->morphTo();
    }

    public function resource()
    {
        return $this->morphTo();
    }


    /* ------------------------------ Functions ----------------------------- */
    /**
     * Move funds to this owners internal account
     *
     * @return bool - Transaction created successfully
     */
    public function moveToInternal($amount, array $options = []): bool
    {
        if ($this->type !== AccountTypeEnum::IN) {
            throw new IncorrectTypeException($this, AccountTypeEnum::IN);
        }

        // Get internal account in this system and currency
        $internalAccount = $this->owner->getInternalAccount($this->system, $this->currency);

        return $this->moveTo($internalAccount, $amount, $options);
    }

    /**
     * Move funds from one account to another
     */
    public function moveTo($toAccount, int $amount, array $options = [])
    {
        // Options => string $description, $access = null, $metadata = null

        if ($amount <= 0) {
            throw new InvalidTransactionAmountException($amount, $this);
        }

        $this->verifySameCurrency($toAccount);

        // Verify that both accounts allowed to make transactions
        $this->verifyCanMakeTransactions();
        $toAccount->verifyCanMakeTransactions();

        // Make transactions
        DB::transaction(function() use($toAccount, $amount, $options) {
            $fromAccount = Account::lockForUpdate()->find($this->getKey());
            $amount = $this->asMoney($amount);

            // Verify from account has valid balance if it is an internal account
            if ($fromAccount->type === AccountTypeEnum::INTERNAL) {
                $balance = $fromAccount->balance->subtract($amount);
                $ignoreBalance = isset($options['ignoreBalance']) ? $options['ignoreBalance'] : false;
                if ($balance->isNegative() && !$ignoreBalance ) {
                    throw new InsufficientFundsException($fromAccount, $amount->getAmount(), $balance->getAmount());
                }
                $fromAccount->balance = $balance->getAmount();
                $fromAccount->balance_last_updated_at = Carbon::now();
                $fromAccount->save();
            }

            $commons = [
                'currency' => $fromAccount->currency,
                'description' => $options['description'] ?? null,
                'access_id' => isset($options['access']) ? $options['access']->getKey() : null,
                'metadata' => $options['metadata'] ?? null,
            ];

            $fromTransaction = Transaction::create(array_merge([
                'account_id' => $fromAccount->getKey(),
                'credit_amount' => 0,
                'debit_amount' => $amount->getAmount(),
            ], $commons));

            $toTransaction = Transaction::create(array_merge([
                'account_id' => $toAccount->getKey(),
                'credit_amount' => $amount->getAmount(),
                'debit_amount' => 0,
            ], $commons));

            // Add reference ids
            $fromTransaction->reference_id = $toTransaction->getKey();
            $toTransaction->reference_id = $fromTransaction->getKey();

            $fromTransaction->save();
            $toTransaction->save();

        }, 1);

        UpdateAccountBalance::dispatch($toAccount);
        return true;
    }


    /* ----------------------- Verification Functions ----------------------- */
    /**
     * Check if account can make a transactions
     */
    public function canMakeTransactions()
    {
        return $this->can_make_transactions;
    }

    /**
     * Verify that account can make a transaction
     */
    public function verifyCanMakeTransactions()
    {
        if (!$this->canMakeTransactions()) {
            throw new TransactionNotAllowedException($this);
        }
    }


    /* ------------------------------- Ownable ------------------------------ */
    public function getOwner(): Collection
    {
        return new Collection([ $this->owner ]);
    }


}

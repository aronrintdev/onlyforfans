<?php

namespace App\Models\Financial;

use App\Enums\Financial\AccountTypeEnum;
use App\Interfaces\Ownable;
use App\Models\Financial\Exceptions\Account\IncorrectTypeException;
use App\Models\Financial\Exceptions\Account\TransactionNotAllowedException;
use App\Models\Traits\OwnableTraits;
use App\Models\Financial\Exceptions\InvalidTransactionAmountException;
use App\Models\Traits\UsesUuid;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

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

        // TODO: Verify balance on transactions from internal accounts

        $this->verifySameCurrency($toAccount);

        // Verify that both accounts allowed to make transactions
        $this->verifyCanMakeTransactions();
        $toAccount->verifyCanMakeTransactions();

        // Make transactions
        DB::transaction(function() use($toAccount, $amount, $options) {
            $commons = [
                'currency' => $this->currency,
                'description' => $options['description'] ?? null,
                'access_id' => $options['access'] ? $options['access']->getKey() : null,
                'metadata' => $options['metadata'] ?? null,
            ];
            $fromTransaction = Transaction::create(Arr::collapse([
                'account_id' => $this->getKey(),
                'credit_amount' => 0,
                'debit_amount' => $amount,
            ], $commons));
            $toTransaction = Transaction::create(Arr::collapse([
                'account_id' => $toAccount->getKey(),
                'credit_amount' => $amount,
                'debit_amount' => 0,
            ], $commons));

            // Add reference ids
            $fromTransaction->reference_id = $toTransaction->getKey();
            $toTransaction->reference_id = $toTransaction->getKey();

            $fromTransaction->save();
            $toTransaction->save();
        }, 1);

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
        if (!$this->canMakeTransaction()) {
            throw new TransactionNotAllowedException($this);
        }
    }


    /* ------------------------------- Ownable ------------------------------ */
    public function getOwner(): Collection
    {
        return new Collection([ $this->owner ]);
    }


}

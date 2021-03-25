<?php

namespace App\Models\Financial;

use App\Apis\Segpay\Transaction;
use App\Enums\Financial\AccountTypeEnum;
use App\Interfaces\Ownable;
use App\Models\Traits\OwnableTraits;
use App\Models\Traits\UsesUuid;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;

class SegpayCard extends Model implements Ownable
{
    use OwnableTraits, UsesUuid;

    protected $table = 'segpay_card';

    protected $casts = [
        'token' => 'encrypted', // purchase id from webhook
        'card_type' => 'encrypted',
        'last_4' => 'encrypted',
    ];

    /* ------------------------------ Relations ----------------------------- */
    #region Relations
    /**
     * Owner of account
     */
    public function owner()
    {
        return $this->morphTo();
    }

    /**
     * Financial Account Linked to this card
     */
    public function account()
    {
        return $this->morphOne(Account::class, 'resource');
    }

    #endregion


    /* ------------------------------ Functions ----------------------------- */
    #region Functions
    /**
     * Get card by token value
     */
    public static function findByToken($token): ?SegpayCard
    {
        return SegpayCard::where('token', Crypt::encryptString($token))->first();
    }

    /**
     * Create new card and account from SegPay transaction data
     */
    public static function createFromTransaction(Transaction $transaction): SegpayCard
    {
        if (isset($transaction->user_id)) {
            $user = User::find($transaction->user_id);
        } else {
            $user = User::where('email', $transaction->username)->first();
        }

        // TODO: Verify user
        // SegPay seems to use some sort of username password system?

        // create Segpay card
        $card = SegpayCard::create([
            'owner_type' => $user->getMorphString(),
            'owner_id'   => $user->getKey(),
            'token'      => $transaction->transactionId,
            'nickname'   => $transaction->nickname ?? null,
            'card_type'  => $transaction->cardType,
            'last_4'     => $transaction->ccLast4,
        ]);

        // Create account
        $account = Account::create([
            'system' => 'segpay',
            'owner_type' => $user->getMorphString(),
            'owner_id' => $user->getKey(),
            'name' => $user->username . ' Segpay CC',
            'type' => AccountTypeEnum::IN,
            'currency' => $transaction->currencyCode,
            'resource_type' => $card->getMorphString(),
            'resource_id' => $card->getKey(),
        ]);
        $account->can_make_transactions = true;
        $account->save();

        return $card;
    }

    #endregion

    /* ------------------------------- Ownable ------------------------------ */
    #region Ownable
    public function getOwner(): Collection
    {
        return new Collection([$this->owner]);
    }

    #endregion

}

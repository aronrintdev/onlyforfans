<?php

namespace App\Models\Financial\Traits;

use Illuminate\Support\Carbon;
use App\Models\Financial\Wallet;
use App\Models\Financial\Account;
use App\Models\Financial\Earnings;
use App\Enums\Financial\AccountTypeEnum;

/**
 * Common Functions for Interface 'HasFinancialAccounts'
 */
trait HasFinancialAccounts
{
    public function getWalletAccount(string $system, string $currency): Account
    {
        $account = $this->financialAccounts()
            ->financialSystem($system, $currency)
            ->isInternal()->isWallet()
            ->first();
        if (isset($account)) {
            return $account;
        }
        return $this->createWalletAccount($system, $currency);
    }

    public function createWalletAccount(string $system, string $currency): Account
    {
        $wallet = Wallet::create([
            'owner_type' => $this->getMorphString(),
            'owner_id' => $this->getKey(),
        ]);

        $account = Account::create([
            'system' => $system,
            'owner_type' => $this->getMorphString(),
            'owner_id' => $this->getKey(),
            'name' => $this->username . ' Wallet Account',
            'type' => AccountTypeEnum::INTERNAL,
            'currency' => $currency,
            'balance' => 0,
            'balance_last_updated_at' => Carbon::now(),
            'pending' => 0,
            'pending_last_updated_at' => Carbon::now(),
        ]);
        $account->resource()->associate($wallet);
        $account->verified = true;
        $account->can_make_transactions = true;
        $account->save();
        return $account;
    }

    public function getEarningsAccount(string $system, string $currency): Account
    {
        $account = $this->financialAccounts()
            ->financialSystem($system, $currency)
            ->isInternal()->isEarnings()
            ->first();
        if (isset($account)) {
            return $account;
        }
        return $this->createEarningsAccount($system, $currency);
    }

    public function createEarningsAccount(string $system, string $currency): Account
    {
        $earnings = Earnings::create([
            'owner_type' => $this->getMorphString(),
            'owner_id' => $this->getKey(),
        ]);

        $account = Account::create([
            'system' => $system,
            'owner_type' => $this->getMorphString(),
            'owner_id' => $this->getKey(),
            'name' => $this->username . ' Earnings Account',
            'type' => AccountTypeEnum::INTERNAL,
            'currency' => $currency,
            'balance' => 0,
            'balance_last_updated_at' => Carbon::now(),
            'pending' => 0,
            'pending_last_updated_at' => Carbon::now(),
        ]);
        $account->resource()->associate($earnings);
        $account->verified = true;
        $account->can_make_transactions = true;
        $account->save();
        return $account;
    }
}
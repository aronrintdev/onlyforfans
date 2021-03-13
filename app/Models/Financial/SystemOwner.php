<?php

namespace App\Models\Financial;

use Carbon\Carbon;
use App\Models\Traits\UsesUuid;
use App\Enums\Financial\AccountTypeEnum;
use App\Interfaces\HasFinancialAccounts;
use App\Models\Financial\Traits\HasSystem;

class SystemOwner extends Model implements HasFinancialAccounts
{
    use UsesUuid,
        HasSystem;

    protected $table = 'financial_system_owners';

    protected $guarded = [];

    /* ---------------------------- Relationships --------------------------- */
    public function financialAccounts()
    {
        return $this->morphMany(Account::class, 'owner');
    }

    /* ------------------------ HasFinancialAccounts ------------------------ */
    public function getInternalAccount(string $system, string $currency): Account
    {
        $account = $this->financialAccounts->where('system', $system)
            ->where('currency', $currency)
            ->where('type', AccountTypeEnum::INTERNAL)
            ->first();
        if (isset($account)) {
            return $account;
        }
        return $this->createInternalAccount($system, $currency);
    }

    public function createInternalAccount(string $system, string $currency): Account
    {
        $account = Account::create([
            'system' => $system,
            'owner_type' => $this->getMorphString(),
            'owner_id' => $this->getKey(),
            'name' => $this->system . ' System ' . $this->name . ' Balance Account',
            'type' => AccountTypeEnum::INTERNAL,
            'currency' => $currency,
            'balance' => 0,
            'balance_last_updated_at' => Carbon::now(),
            'pending' => 0,
            'pending_last_updated_at' => Carbon::now(),
        ]);
        $account->verified = true;
        $account->can_make_transactions = true;
        $account->save();
        return $account;
    }
}

<?php

namespace App\Models\Financial;

use Carbon\Carbon;
use App\Models\Traits\UsesUuid;
use App\Enums\Financial\AccountTypeEnum;
use App\Interfaces\HasFinancialAccounts;
use App\Models\Financial\Traits\HasFinancialAccounts as HasFinancialAccountsTrait;
use App\Models\Financial\Traits\HasSystem;

class SystemOwner extends Model implements HasFinancialAccounts
{
    use UsesUuid,
        HasFinancialAccountsTrait,
        HasSystem;

    protected $connection = 'financial';
    protected $table = 'system_owners';

    protected $guarded = [];

    /* ---------------------------- Relationships --------------------------- */
    #region Relationships
    public function financialAccounts()
    {
        return $this->morphMany(Account::class, 'owner');
    }

    #endregion
}

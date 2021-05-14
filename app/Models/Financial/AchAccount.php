<?php

namespace App\Models\Financial;

use App\Models\User;
use App\Models\Traits\UsesUuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property string $id
 * @property string $user_id
 * @property string $type               individual or company
 * @property string $name               Name on account
 * @property string $residence_country  ISO 3166-2
 * @property string $beneficiary_name   Name of Beneficiary
 * @property string $bank_name          Name of the bank
 * @property string $routing_number     9 digit routing number
 * @property string $account_number     Account Number
 * @property string $account_type       checking or savings
 * @property string $bank_country       ISO 3166-2
 * @property string $currency           ISO 4217
 * @property array  $metadata           Additional metadata
 *
 * @property User $user     User that created this ach account
 *
 * @package App\Models\Financial
 */
class AchAccount extends Model
{
    use UsesUuid,
        HasFactory,
        SoftDeletes;

    /* -------------------------- Model Properties -------------------------- */
    #region Model Properties
    protected $connection = 'financial';
    protected $table = 'ach_accounts';

    protected $guarded = [];

    protected $casts = [
        'routing_number' => 'encrypted',
        'account_number' => 'encrypted',
    ];

    #endregion Model Properties
    /* ---------------------------------------------------------------------- */

    /* ---------------------------- Relationships --------------------------- */
    #region Relationships

    #endregion Relationships
    /* ---------------------------------------------------------------------- */

    /* ------------------------------ Functions ----------------------------- */
    #region Functions

    #endregion Functions
    /* ---------------------------------------------------------------------- */
}

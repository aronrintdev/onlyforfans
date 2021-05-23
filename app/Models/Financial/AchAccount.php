<?php

namespace App\Models\Financial;

use App\Models\User;
use App\Interfaces\Ownable;
use App\Models\Traits\UsesUuid;
use Illuminate\Support\Collection;
use App\Models\Traits\OwnableTraits;
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
class AchAccount extends Model implements Ownable
{
    use UsesUuid,
        OwnableTraits,
        HasFactory,
        SoftDeletes;

    /* -------------------------- Model Properties -------------------------- */
    #region Model Properties
    protected $connection = 'financial';
    protected $table = 'ach_accounts';

    protected $guarded = [ 'metadata' ];

    protected $casts = [
        'routing_number' => 'encrypted',
        'account_number' => 'encrypted',
        'metadata'       => 'array',
    ];

    #endregion Model Properties
    /* ---------------------------------------------------------------------- */

    /* ---------------------------- Relationships --------------------------- */
    #region Relationships

    public function account()
    {
        return $this->morphOne(Account::class, 'resource');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    #endregion Relationships
    /* ---------------------------------------------------------------------- */

    /* ------------------------------ Functions ----------------------------- */
    #region Functions

    #endregion Functions
    /* ---------------------------------------------------------------------- */

    /* ------------------------------- Ownable ------------------------------ */
    public function getOwner(): ?Collection
    {
        return new Collection([ $this->user ]);
    }


}

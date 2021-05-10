<?php

namespace App\Models\Financial;

use App\Models\Traits\UsesUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property string $id
 * @property string $type        - affiliates or payouts
 * @property array  $csv         - the csv lines
 * @property array  $notes       - Misc Notes about batch
 * @property string $assigned_to - Admin user that is assigned to handle this batch
 * @property string $settled_by  - Admin user that marked the batch as settled
 * @property Carbon $settled_at  - When the batch was marked as settled
 *
 * -- Relationships --
 * @property User $assigned
 * @property User $settledUser
 *
 * @package App\Models\Financial
 */
class PayoutBatch extends Model
{
    use UsesUuid;

    /* -------------------------- Model Properties -------------------------- */
    #region Model Properties
    protected $table = 'payout_batches';

    protected $guarded = [];

    protected $casts = [
        'csv' => 'array',
        'notes' => 'array',
    ];

    protected $dates = [
        'settled_at',
    ];

    #endregion Model Properties
    /* ---------------------------------------------------------------------- */

    /* ---------------------------- Relationships --------------------------- */
    #region Relationships

    /**
     * The admin user that is assigned to this batch
     * @return HasOne
     */
    public function assigned()
    {
        return $this->hasOne(User::class, 'assigned_to');
    }

    /**
     * The admin user that settled this batch
     * @return HasOne
     */
    public function settledUser()
    {
        return $this->hasOne(User::class, 'settled_by');
    }

    #endregion Relationships
    /* ---------------------------------------------------------------------- */

    /* ------------------------------ Functions ----------------------------- */
    #region Functions

    public function generateCSV()
    {
        //
    }

    #endregion Functions
    /* ---------------------------------------------------------------------- */


}

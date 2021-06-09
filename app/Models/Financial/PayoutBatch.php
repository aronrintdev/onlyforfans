<?php

namespace App\Models\Financial;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Config;

/**
 * @property string $id
 * @property string $type          - affiliates or payouts
 * @property Carbon $collect_until - Time at which this batch stops collecting transactions
 * @property array  $csv           - the csv lines
 * @property array  $notes         - Misc Notes about batch
 * @property string $assigned_to   - Admin user that is assigned to handle this batch
 * @property string $settled_by    - Admin user that marked the batch as settled
 * @property Carbon $settled_at    - When the batch was marked as settled
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
    protected $connection = 'financial';
    protected $table = 'payout_batches';

    protected $forceCombV4Uuid = true;

    protected $guarded = [];

    protected $casts = [
        'csv' => 'array',
        'notes' => 'array',
    ];

    protected $dates = [
        'collect_until',
        'settled_at',
    ];

    #endregion Model Properties
    /* ---------------------------------------------------------------------- */

    /* ---------------------------- Relationships --------------------------- */
    #region Relationships

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'resource');
    }

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

    /* ------------------------------- Scopes ------------------------------- */
    #region Scopes

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeCollecting($query)
    {
        return $query->whereNull('settled_at')->where('collect_until', '>=', Carbon::now());
    }

    #endregion Scopes
    /* ---------------------------------------------------------------------- */

    /* ------------------------------ Functions ----------------------------- */
    #region Functions

    /**
     * Get the currently collecting batch for batch type
     *
     * @param mixed $type
     * @return PayoutBatch
     */
    public static function currentBatch($type): PayoutBatch
    {
        $batch = PayoutBatch::type($type)->collecting()
            ->orderBy('created_at', 'asc')
            ->orderBy('collect_until', 'asc')
            ->first();

        if (!isset($batch)) {
            $batch = PayoutBatch::create([
                'type' => $type,
                'collect_until' => Carbon::now()->next(Config::get('payout.batch.rollover.time', '00:00'))
            ]);
        }

        if ($batch->collect_until->sub(
            Config::get('payout.batch.rollover.prep_at.amount', 10),
            Config::get('payout.batch.rollover.prep_at.unit', 'minutes')
        )) {
            if (PayoutBatch::type($type)->collecting()->count() < 2) {
                // Prep new Payout Batch
                PayoutBatch::create([
                    'type' => $type,
                    'collect_until' => Carbon::now()->next(Config::get('payout.batch.rollover.time', '00:00'))->addDay()
                ]);
            }
        }

        return $batch;
    }

    public function generateCSV()
    {
        //
    }

    #endregion Functions
    /* ---------------------------------------------------------------------- */


}

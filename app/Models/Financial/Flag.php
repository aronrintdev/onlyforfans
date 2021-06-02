<?php

namespace App\Models\Financial;

use App\Events\FinancialFlagRaised;
use App\Models\Traits\UsesUuid;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Admin flag for financial items
 *
 * @property string $id
 * @property string $model_type
 * @property string $model_id
 * @property string $column
 * @property string $delta_before
 * @property string $delta_after
 * @property string $description
 * @property string $notes
 * @property bool   $handled
 * @property string $handled_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 *
 * @package App\Models\Financial
 */
class Flag extends Model
{
    use UsesUuid;

    protected $connection = 'financial';
    protected $table = 'flags';

    protected $guarded = [ 'handled', 'handled_by' ];

    protected $casts = [
        'notes' => 'collection',
    ];

    /* ---------------------------- Relationships --------------------------- */
    #region Relationships
    public function model()
    {
        return $this->morphTo();
    }

    public function handled_by()
    {
        return $this->hasOne(User::class);
    }

    #endregion

    /* ------------------------------ Functions ----------------------------- */
    #region Functions
    public static function raise($model, array $attributes = []): Flag
    {
        $flag = Flag::create(array_merge([
            'model_id' => $model->getKey(),
            'model_type' => $model->getMorphString(),
        ], $attributes));

        // Dispatch event
        FinancialFlagRaised::dispatch($flag);

        return $flag;
    }


    /**
     * Mark flag as being handled
     */
    public function handle($notes): void
    {
        $this->handled = true;
        $this->handled_by = Auth::user();
        $this->notes;
        $this->save();
    }

    #endregion

}
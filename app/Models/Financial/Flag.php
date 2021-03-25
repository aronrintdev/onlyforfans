<?php

namespace App\Models\Financial;

use App\Events\FinancialFlagRaised;
use App\Models\Traits\UsesUuid;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Admin flag for financial items
 * @package App\Models\Financial
 */
class Flag extends Model
{
    use UsesUuid;


    protected $table = 'financial_flags';

    protected $guarded = [ 'handled', 'handled_by' ];

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
    public function handle(): void
    {
        $this->handled = true;
        $this->handled_by = Auth::user();
        $this->save();
    }

    #endregion

}
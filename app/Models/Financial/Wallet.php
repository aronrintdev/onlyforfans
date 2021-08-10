<?php

namespace App\Models\Financial;

use App\Models\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property string $id
 * @property string $owner_type
 * @property string $owner_id
 * @property string $name
 * @property Collection|null $custom_attributes
 * @property Collection|null $metadata
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property mixed $owner
 *
 * @package App\Models\Financial
 */
class Wallet extends Model
{
    use UsesUuid, SoftDeletes;


    /* -------------------------------------------------------------------------- */
    /*                               PROPERTIES/BOOT                              */
    /* -------------------------------------------------------------------------- */
    #region Properties
    protected $connection = 'financial';

    protected $table = 'wallets';

    protected $guarded = [];

    /**
     * Laravel Boot function
     * @return void
     */
    public static function boot()
    {
        parent::boot();
    }

    #endregion Properties

    /* -------------------------------------------------------------------------- */
    /*                                    CASTS                                   */
    /* -------------------------------------------------------------------------- */
    #region Casts

    protected $casts = [
        'custom_attributes' => 'collection',
        'metadata'          => 'collection',
    ];

    #endregion Casts

    /* -------------------------------------------------------------------------- */
    /*                                RELATIONSHIPS                               */
    /* -------------------------------------------------------------------------- */
    #region Relationships

    /**
     * Owner of this Wallet
     *
     * @return MorphTo
     */
    public function owner()
    {
        return $this->morphTo();
    }

    #endregion Relationships

    /* -------------------------------------------------------------------------- */
    /*                                   SCOPES                                   */
    /* -------------------------------------------------------------------------- */
    #region Scopes

    #endregion Scopes

    /* -------------------------------------------------------------------------- */
    /*                                  FUNCTIONS                                 */
    /* -------------------------------------------------------------------------- */
    #region Functions

    #endregion Functions
}

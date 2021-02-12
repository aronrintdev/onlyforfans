<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Interfaces\Guidable;
use App\Models\Traits\UsesUuid;

class Fanledger extends BaseModel implements Guidable
{
    use SoftDeletes, UsesUuid;

    protected $customAttributesField = 'cattrs';
    protected $metaField = 'meta';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public static $vrules = [];

    //--------------------------------------------
    // Boot
    //--------------------------------------------
    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->total_amount =
                ($model->base_unit_cost_in_cents * $model->qty)
                + $model->taxes_in_cents
                + $model->fees_in_cents;
        });
        static::updating(function ($model) {
            $model->total_amount =
                ($model->base_unit_cost_in_cents * $model->qty)
                + $model->taxes_in_cents
                + $model->fees_in_cents;
        });
    }

    //--------------------------------------------
    // Relationships
    //--------------------------------------------

    public function purchaseable()
    {
        return $this->morphTo();
    }
    public function purchaser()
    {
        return $this->belongsTo('App\Models\User', 'purchaser_id');
    }
    public function seller()
    {
        return $this->belongsTo('App\Models\User', 'seller_id');
    }

    //--------------------------------------------
    // Accessors/Mutators | Casts
    //--------------------------------------------

    protected $casts = [
        'cattrs' => 'array',
        'mdata' => 'array',
    ];

    //--------------------------------------------
    // Methods
    //--------------------------------------------

    // %%% --- Overrides in Model Traits (via BaseModel) ---

    public static function _renderFieldKey(string $key): string
    {
        $key = trim($key);
        switch ($key) {
            default:
                $key =  parent::_renderFieldKey($key);
        }
        return $key;
    }

    public function renderField(string $field): ?string
    {
        $key = trim($field);
        switch ($key) {
                /*
            case 'meta':
            case 'cattrs':
                return json_encode($this->{$key});
             */
            default:
                return parent::renderField($field);
        }
    }

    // %%% --- Nameable Interface Overrides (via BaseModel) ---

    public function renderName(): string
    {
        return $this->guid;
    }

    // %%% --- Other ---

}

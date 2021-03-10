<?php

namespace App\Models;

use App\Models\Traits\UsesUuid;

class Access extends Model
{
    use UsesUuid;

    protected $table = 'access';

    protected $dates = [
        'expires',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    /* ---------------------------- Relationships --------------------------- */
    /**
     * Model that has access
     */
    public function model()
    {
        return $this->morphTo();
    }

    /**
     * Resource model has access to
     */
    public function resource()
    {
        return $this->morphTo();
    }


}

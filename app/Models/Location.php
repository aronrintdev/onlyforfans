<?php

namespace App\Models;

use App\Models\Traits\UsesUuid;

/**
 * Location Model, contains gps data and/or address information.
 */
class Location extends Model
{
    use UsesUuid;

    protected $table = 'locations';
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'gps'               => 'array',
        'cattrs' => 'array',
        'meta'          => 'array',
    ];

    public function resource()
    {
        $this->morphTo();
    }


}

<?php

namespace App\Models;

use App\Models\Traits\UsesUuid;

/**
 * Link Model
 */
class Link extends Model
{
    use UsesUuid;

    protected $table = 'links';
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'cattrs' => 'array',
        'meta'          => 'array',
    ];

    public function resource()
    {
        return $this->morphTo();
    }


}

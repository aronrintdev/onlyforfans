<?php

namespace App\Models;

use App\Models\Traits\UsesUuid;

class UserSetting extends Model
{
    use UsesUuid;

    protected $table = 'user_settings';

    protected $casts = [
        'custom' => 'array',
    ];

    /* ---------------------------- Relationships --------------------------- */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
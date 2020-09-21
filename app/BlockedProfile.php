<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class BlockedProfile
 */
class BlockedProfile extends Model
{
    /**
     * @var string
     */
    public $table = 'blocked_profiles';

    /**
     * @var array
     */
    public $fillable = [
        'blocked_by',
        'ip_address',
        'country',
    ];

    public static $rules = [
        'ip_address' => 'required',
    ];

    /**
     * @return BelongsTo
     */
    public function blockedBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

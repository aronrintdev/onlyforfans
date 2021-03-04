<?php

namespace App\Models\Financial;

use App\Interfaces\Ownable;
use App\Models\Traits\OwnableTraits;
use App\Models\Traits\UsesUuid;
use Illuminate\Support\Collection;

class SegpayCard extends Model implements Ownable
{
    use OwnableTraits, UsesUuid;

    protected $table = 'segpay_card';

    protected $casts = [
        'token' => 'encrypted'
    ];

    /* ------------------------------ Relations ----------------------------- */
    /**
     * Owner of account
     */
    public function owner()
    {
        return $this->morphTo();
    }

    // Ownable functions
    public function getOwner(): Collection
    {
        return new Collection([$this->owner]);
    }

}

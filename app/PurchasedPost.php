<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchasedPost extends Model
{
    /**
     * @var string 
     */
    protected $table = 'purchased_posts';

    /**
     * @var string[] 
     */
    protected $fillable = [
        'purchased_by',
        'post_id',
        'amount',
        'stripe_id',
        'meta',
        'payment_status',
    ];

    /**
     * @var string[] 
     */
    protected $casts = [
        'purchased_by'   => 'integer',
        'post_id'        => 'integer',
        'amount'         => 'double',
        'stripe_id'      => 'string',
        'meta'           => 'array',
        'payment_status' => 'string',
    ];
}

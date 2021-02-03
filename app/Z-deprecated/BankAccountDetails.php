<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BankAccountDetails extends Model
{
    /**
     * @var string 
     */
    protected $table = 'bank_account_details';

    /**
     * @var array[] 
     */
    protected $fillable = [
        'user_id',
        'bank_name',
        'routing',
        'account',
    ];

    /**
     * @var array[] 
     */
    protected $casts = [
        'user_id' => 'integer',
        'bank_name' => 'string',
        'routing' => 'string',
        'account' => 'string',
    ];
}

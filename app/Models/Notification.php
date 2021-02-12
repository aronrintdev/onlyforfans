<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Notification Model.
 *
 * TODO: This needs to be remade
 */
class Notification extends Model
{
    use SoftDeletes;
    protected $guarded = [ 'id', 'created_at', 'updated_at' ];
}

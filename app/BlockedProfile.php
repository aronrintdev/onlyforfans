<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class BlockedProfile
 */
class BlockedProfile extends Model
{
    public $table = 'blocked_profiles';
    protected $guarded = ['id','created_at','updated_at'];
    public static $rules = [
        'ip_address' => 'required_without_all:country,blockee_id',
        'country'    => 'required_without_all:ip_address,blockee_id',
    ];

    public function blockedBy() {
        return $this->belongsTo(User::class, 'blocked_by');
    }
    public function blockee() {
        return $this->belongsTo(User::class, 'blockee');
    }
}

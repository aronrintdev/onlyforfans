<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

use App\Models\Traits\UsesUuid;

class Staff extends Model
{
    use UsesUuid, HasFactory;

    protected $guarded = [ 'id', 'created_at', 'updated_at' ];

    public function getInviteUrlAttribute($value) {
        $existing = User::where('email', $email)->first(); // does invitee have an account or not
        //$url  = '/staff/invitations/accept';
        $url  = route('staff.acceptInvite');
        $url .= '?token='.$this->token;
        $url .= '&email='.$this->email;
        $url .= '&inviter='.$this->owner->name;
        if (!$existing) {
            $url .= '&is_new=true';
        }
        return url($url);
    }

    //--------------------------------------------
    // Relationships
    //--------------------------------------------
    public function owner() {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function permissions()
    {
        return $this->morphToMany(Permission::class, 'permissibles');
    }
}

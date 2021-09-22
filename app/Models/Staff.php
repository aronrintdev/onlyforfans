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

    // -------------------- %%% Boot ----------------------

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->token = str_random(60);
        });
    }

    // -------------------- %%% Accessors/Mutators | Casts  ----------------------

    // the 'clickable' link to accept the invite...(POST)
    public function getInviteActionUrlAttribute($value) {
        $existing = User::where('email', $this->email)->first(); // does invitee have an account or not
        $isNew = !$existing;
        $url  = route('staff.acceptInvite');
        $url .= '?token='.$this->token;
        $url .= '&email='.$this->email;
        $url .= '&inviter='.$this->owner->name;
        if ($isNew) {
            $url .= '&is_new=true';
        }
        return url($url);
    }

    // the 'landing page' for the invite... (GET)
    public function getInviteLandingUrlAttribute($value) {
        $existing = User::where('email', $this->email)->first(); // does invitee have an account or not
        $isNew = !$existing;
        $url  = '/staff/invitations/accept';
        $url .= '?token='.$this->token;
        $url .= '&email='.$this->email;
        $url .= '&inviter='.$this->owner->name;
        if ($isNew) {
            $url .= '&is_new=true';
        }
        return url($url);
    }

    public function getInviteeFullnameAttribute($value) {
        return $this->first_name.' '.$this->last_name;
    }

    // -------------------- %%% Relationships  ----------------------

    // 'Owner' is the user that added new staff member...this could be a creator or a manager
    public function owner() {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function creator() {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function user() { // the 'invitee'
        return $this->belongsTo(User::class, 'user_id');
    }

    public function permissions() {
        return $this->morphToMany(Permission::class, 'permissibles');
    }
}

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

    public function getInviteUrlAttribute($value) {
        $existing = User::where('email', $this->email)->first(); // does invitee have an account or not
        return self::makeInviteUrl($this->owner->name, $this->email, $this->token, !$existing);
    }

    public function getInviteeFullnameAttribute($value) {
        return $this->first_name.' '.$this->last_name;
    }

    // helper function
    public static function makeInviteUrl(string $inviterName, string $inviteeEmail, string $token, bool $isNew=false) : string
    {
        //$url  = '/staff/invitations/accept';
        $url  = route('staff.acceptInvite');
        $url .= '?token='.$token;
        $url .= '&email='.$inviteeEmail;
        $url .= '&inviter='.$inviterName;
        if ($isNew) {
            $url .= '&is_new=true';
        }
        return url($url);
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

<?php
namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

use App\Interfaces\UuidId;
use App\Models\Traits\UsesUuid;

class Chatthread extends Model implements UuidId
{
    use UsesUuid;

    protected $guarded = [ 'id', 'created_at', 'updated_at' ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function messages()
    {
        return $this->morphMany(Message::class, 'messagable');
    }

    //--------------------------------------------
    // %%% Accessors/Mutators | Casts
    //--------------------------------------------

    //--------------------------------------------
    // %%% Relationships
    //--------------------------------------------

    public function messages()
    {
        return $this->hasMany(Chatmessage::class);
    }

    //--------------------------------------------
    // %%% Methods
    //--------------------------------------------

}

<?php
namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

use App\Interfaces\Ownable;
use App\Interfaces\UuidId;
use App\Models\Traits\OwnableTraits;
use App\Models\Traits\UsesUuid;

class Chatmessage extends Model implements UuidId, Ownable
{
    use UsesUuid, OwnableTraits;

    protected $guarded = [ 'id', 'created_at', 'updated_at' ];

    public function chatthread()
    {
        return $this->belongsTo(ChatThread::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    //--------------------------------------------
    // %%% Relationships
    //--------------------------------------------

    public function mediafile()
    {
        return $this->morphOne(Mediafile::class, 'resource');
    }

    //--------------------------------------------
    // %%% Methods
    //--------------------------------------------

    public function getOwner(): ?Collection
    {
        return new Collection([$this->sender]);
    }

    public function getPrimaryOwner(): User
    {
        return $this->sender;
    }

}

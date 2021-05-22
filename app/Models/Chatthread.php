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

    //--------------------------------------------
    // %%% Accessors/Mutators | Casts
    //--------------------------------------------

    //--------------------------------------------
    // %%% Relationships
    //--------------------------------------------

    public function chatmessages()
    {
        return $this->hasMany(Chatmessage::class);
        //return $this->hasMany(Chatmessage::class)->where('is_delivered', true);
    }

    public function originator()
    {
        return $this->belongsTo(User::class);
    }

    public function participants() // ie receivers, readers, etc; should include originator
    {
        return $this->belongsToMany(User::class, 'chatthread_user', 'chatthread_id', 'user_id');
    }


    //--------------------------------------------
    // %%% Methods
    //--------------------------------------------

    public static function startChat(User $originator) : Chatthread
    {
        // %TODO: use transaction
        $chatthread = Chatthread::create([
            'originator_id' => $originator->id,
        ]);
        $chatthread->addParticipant($originator->id);
        return $chatthread;
    }

    public function addParticipant($participantID)
    {
        $this->participants()->attach($participantID); // originator is already added
        return $this;
    }

    // %TODO: handle mediafiles
    public function sendMessage(User $sender, string $mcontent) : Chatmessage
    {
        return $this->chatmessages()->create([
              'sender_id' => $sender->id,
              'mcontent' => $mcontent,
        ]);
    }

    public function scheduleMessage(User $sender, string $mcontent, int $deliverAt) : Chatmessage
    {
        return $this->chatmessages()->create([
              'sender_id' => $sender->id,
              'mcontent' => $mcontent,
              'is_delivered' => false,
              'deliver_at' => Carbon::createFromTimestamp($deliverAt),
        ]);
    }

}

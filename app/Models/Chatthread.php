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
        return $this->hasMany(Message::class);
    }

    public function originator()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function participants() // ie receivers, readers, etc; should include originator
    {
        return $this->belongsToMany(User::class, 'user_id');
    }


    //--------------------------------------------
    // %%% Methods
    //--------------------------------------------

    public static function startChat(User $sender)
    {
        // %TODO: use transaction
        $chatthread = Chatthread::create([
            'originator_id' => $sender->id,
        ]);
        $chatthread->addParticipants($sender);
        //$chatthread->sendMessage($sender, $mcontent); // do in caller
    }

    public function addParticipant(User $participant)
    {
        $this->participants()->attach($participant->id);
    }

    // %TODO: handle mediafiles
    public function sendMessage(User $sender, string $mcontent, int $deliverAt=null)
    {
        $this->chatmessages()->create([
              'sender_id' => $sender->id,
              'mcontent' => $mcontent,
        ]);
    }

}

<?php
namespace App\Models;

use Exception;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

use App\Interfaces\UuidId;
use App\Models\Traits\UsesUuid;
//use App\Models\Traits\UsesShortUuid;

class Chatthread extends Model implements UuidId
{
    use UsesUuid;

    protected $guarded = [ 'id', 'created_at', 'updated_at' ];

    //--------------------------------------------
    // Boot
    //--------------------------------------------

    public static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            // add originator as a participant
            $model->participants()->attach($model->originator_id);
        });

        /*
        static::deleting(function ($model) {
            foreach ($model->vaultfolders as $o) {
                $o->delete();
            }
        });
         */
    }

    //--------------------------------------------
    // %%% Accessors/Mutators | Casts
    //--------------------------------------------

    //--------------------------------------------
    // %%% Relationships
    //--------------------------------------------

    public function chatmessages()
    {
        // %NOTE only return delivered
        return $this->hasMany(Chatmessage::class)->where('is_delivered', true);
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

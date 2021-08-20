<?php
namespace App\Models;

use Exception;
use Carbon\Carbon;
use App\Interfaces\UuidId;
use Laravel\Scout\Searchable;

use App\Models\Traits\UsesUuid;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
//use App\Models\Traits\UsesShortUuid;

class Chatthread extends Model implements UuidId
{
    use UsesUuid, Searchable;

    protected $guarded = [ 'id', 'created_at', 'updated_at' ];

    //------------------------------------------------------------------------//
    // Boot
    //------------------------------------------------------------------------//

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

    //------------------------------------------------------------------------//
    // %%% Accessors/Mutators | Casts
    //------------------------------------------------------------------------//

    protected $appends = ['isFavoritedByMe', 'note'];

    public function getIsFavoritedByMeAttribute($value)
    {
        $sessionUser = Auth::user();
        if (!$sessionUser) {
            return false;
        }
        $exists = Favorite::where('user_id', $sessionUser->id)
        ->where('favoritable_id', $this->id)
        ->where('favoritable_type', 'posts')
            ->first();
        return $exists ? true : false;
    }

    public function getNoteAttribute($value) {
        $sessionUser = Auth::user();
        $otherUser = $this->participants->filter( function($u) use(&$sessionUser) {
            return $u->id !== $sessionUser->id;
        })->first();

        return Note::where('user_id', $sessionUser->id)
            ->where('noticed_id', $otherUser->timeline->id)
            ->first();
    }

    //------------------------------------------------------------------------//
    // %%% Relationships
    //------------------------------------------------------------------------//
    #region Relationships

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
        return $this->belongsToMany(User::class, 'chatthread_user', 'chatthread_id', 'user_id')->withPivot('is_muted');
    }

    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    #endregion Relationships
    //------------------------------------------------------------------------//

    //------------------------------------------------------------------------//
    // %%% Searchable
    //------------------------------------------------------------------------//
    #region Searchable
    /**
     * Name of the search index associated with this model
     * @return string
     */
    public function searchableAs()
    {
        return "chatthread_index";
    }

    /**
     * Get value used to index the model
     * @return mixed
     */
    public function getScoutKey()
    {
        return $this->getKey();
    }

    /**
     * Get key name used to index the model
     * @return string
     */
    public function getScoutKeyName()
    {
        return 'id';
    }

    /**
     * What model information gets stored in the search index
     * @return array
     */
    public function toSearchableArray()
    {
        return [
            'id'           => $this->getKey(),
            'participants' => $this->participants->map(function($item, $key) { return $item->id; } )->all(),
            'participantUsernames' => $this->participants->map(function($item, $key) { return $item->username; } )->all(),
            'participantNames' => $this->participants->map(function($item, $key) { return $item->timeline->name; } )->all(),
        ];
    }

    #endregion Searchable
    //------------------------------------------------------------------------//

    //------------------------------------------------------------------------//
    // %%% Methods
    //------------------------------------------------------------------------//
    #region Methods

    public static function startChat(User $originator) : Chatthread
    {
        // %TODO: use transaction
        $chatthread = Chatthread::create([
            'originator_id' => $originator->id,
        ]);
        return $chatthread;
    }

    /**
     * Finds or creates a new chatthread for two users
     * @param User $originator
     * @param User $participant
     * @return Chatthread
     */
    public static function findOrCreateDirectChat(User $originator, User $participant)
    {
        $cts = $originator->chatthreads()->whereHas('participants', function ($query) use ($participant) {
            $query->where('user_id', $participant->id);
        })->withCount('participants')->get();

        // Where is only these 2 participants
        $ct = $cts->where('participants_count', 2)->first();

        if (!isset($ct)) {
            $ct = static::startChat($originator);
            $ct->addParticipant($participant->id);
        }
        return $ct;
    }

    public function addParticipant($participantID)
    {
        $this->participants()->attach($participantID); // originator is already added
        return $this;
    }

    // %TODO: handle mediafiles
    public function sendMessage(User $sender, string $mcontent, Collection $cattrs = null) : Chatmessage
    {
        if (!isset($cattrs)) {
            $cattrs = new Collection();
        }
        return $this->chatmessages()->create([
              'sender_id' => $sender->id,
              'mcontent'  => $mcontent,
              'cattrs'    => $cattrs,
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

    #endregion Methods
    //------------------------------------------------------------------------//

}

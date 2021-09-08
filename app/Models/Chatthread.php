<?php
namespace App\Models;

use DB;
use Exception;
use Carbon\Carbon;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

use Laravel\Scout\Searchable;

use App\Models\Casts\Money as CastsMoney;
use App\Interfaces\UuidId;

use App\Models\Traits\UsesUuid;
use App\Enums\MessagegroupTypeEnum;
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

    protected $appends = ['isFavoritedByMe', 'notes', 'totalSpent'];

    public function getIsFavoritedByMeAttribute($value)
    {
        $sessionUser = Auth::user(); // %FIXME - should not reference session user in a model!
        if (!$sessionUser) {
            return false;
        }
        $exists = Favorite::where('user_id', $sessionUser->id)
            ->where('favoritable_id', $this->id)
            ->where('favoritable_type', 'posts')
            ->first();
        return $exists ? true : false;
    }

    public function getNotesAttribute($value) {
        $sessionUser = Auth::user();
        if (!$sessionUser) {
            return null;
        }
        $otherUser = $this->participants->filter( function($u) use(&$sessionUser) {
            return $u->id !== $sessionUser->id;
        })->first();

        if (!isset($otherUser->timeline)) {
            return null;
        }

        return Notes::where('user_id', $sessionUser->id)
            ->where('notes_id', $otherUser->timeline->id)
            ->first();
    }

    public function getTotalSpentAttribute($value) {
        $sessionUser = Auth::user();
        if ( empty($sessionUser) ) {
            return;
        }
        $sentMessages = $this->chatmessages()->where('sender_id', $sessionUser->id);

        return intval($sentMessages->sum('price'));
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


    /**
     * Finds or creates a new chatthread for two users
     * @param User $originator
     * @param User $participant
     * @return Chatthread
     */
    public static function findOrCreateDirectChat(User $originator, User $participant) : ?Chatthread
    {
        $cts = $originator->chatthreads()->whereHas('participants', function ($query) use ($participant) {
            $query->where('user_id', $participant->id);
        })->withCount('participants')->get();

        // Where is only these 2 participants
        $ct = $cts->where('participants_count', 2)->first();

        if (!isset($ct)) {
            $ct = Chatthread::create([
                'originator_id' => $originator->id,
                 'is_tip_required' => 0, // possibly unused, oddly need to set this for it to show up in $ct (?)
            ]);
            $ct->addParticipant($participant->id);
        }
        return $ct;
    }

    // Finds an existing chatthread between the sender (originator) and receiver(s), otherwise creates one
    //   ~ 'thin-controlller-fat-model' refacator of code from chatthreads.store
    //   ~ %FIXME: addMessage should be done in caller not here
    public static function findOrCreateChat(
        User   $sender,
        string $originatorId, // %FIXME: may not be the same as sender if thread already exists(??)
        array  $participants, // array of user ids
        string $mcontent = null,
        int    $deliverAt = null,
        array  $attachments = null,
        int    $price = null,
        string $currency = null,
        array  $cattrs = null
    ) {

        if ( empty($mcontent) && empty($attachments??null) ) { 
            // can't send message without text content or media attached
            throw new Exception('New message requires content or media attached');
        }

        $cmGroup = null; // the created chat message group, if any (1 only)
        $chatmessages = collect();  // the created chat messages, one or more
        $currency = $currency ?? 'USD';

        //$chatthreads = DB::transaction( function() use(&$rattrs, &$cmGroup, &$chatmessages, &$sender) {  // breaks sqlite seeder when contains ->attach()! %FIXME

            $chatthreads = collect();
            $originator = User::find($originatorId);
            $isMassMessage = count($participants) > 1;

            if ($isMassMessage) {
                $cmgroupAttrs = [
                    'mgtype'          => MessagegroupTypeEnum::MASSMSG,
                    'sender_id'       => $sender->id,
                    'mcontent'        => $mcontent,
                    'price'           => CastsMoney::toMoney($price, $currency),
                    'currency'        => $currency,
                    'cattrs' => [
                        'sender_name'     => $sender->name,
                        'participants'    => $participants,
                        'deliver_at'      => $deliverAt, // %TODO: expect this to be an integer, UTC unix ts in s (?)
                        'attachments'     => $attachments,
                    ],
                ];
                $cmGroup = Chatmessagegroup::create($cmgroupAttrs);
            }

            foreach ($participants as $participantUserId) { // participants is an array of [users] primary keys, and should *not* include originator
                // Check if chat with participant already exits
                $ct = $originator->chatthreads()->whereHas('participants', function ($query) use($participantUserId) {
                    $query->where('user_id', $participantUserId);
                })->first();

                // Add participant to originator mycontacts if they are not already there
                if ($originator->mycontacts()->where('contact_id', $participantUserId)->doesntExist()) {
                    Mycontact::create([
                        'owner_id' => $originator->id,
                        'contact_id' => $participantUserId,
                    ]);
                }

                // Start new chat thread if one is not found
                if (!isset($ct)) {
                    $ct = Chatthread::create([
                        'originator_id' => $originator->id,
                        'is_tip_required' => 0, // possibly unused, oddly need to set this for it to show up in $ct (?)
                        //'cattrs' => $cattrs??[],
                    ]);
                    $ct->addParticipant($participantUserId);
                }

                $message = $ct->addMessage(
                    $sender,
                    $mcontent,
                    $attachments,
                    $price,
                    $currency,
                    $cattrs
                );

                if ( isset($deliverAt) ) {
                    // send at scheduled date
                    $message->schedule($deliverAt);
                } else {
                    // send now
                    $message->deliver();
                }

                if ($isMassMessage) {
                    $message->chatmessagegroup_id = $cmGroup->id;
                    $message->save();
                }

                $message->refresh();
                $chatthreads->push($ct);

                $message->refresh();
                $chatmessages->push($message);

            } // foreach

            //return $chatthreads;
        //});

        return [
            'chatthreads' => $chatthreads,
            'chatmessages' => $chatmessages,
            'chatmessagegroup' => $cmGroup,
        ];
    }

    public function addParticipant($participantID)
    {
        $this->participants()->attach($participantID); // originator is already added
        return $this;
    }

    public function addMessage(
        User   $sender,
        string $mcontent = null,
        array  $attachments = null,
        int    $price = null,
        string $currency = null,
        array  $cattrs = null
    ) {
        $message = $this->chatmessages()->create([
            'sender_id' => $sender->id,
            'mcontent'  => $mcontent,
            'cattrs'    => $cattrs ?? [],
        ]);
        // Create mediafile refs for any attachments
        $message->addAttachments($attachments);
        if (isset($price)) {
            $message->setPurchaseOnly($price, $currency); // %FIXME: should pull a default currency from config (?)
        }
        return $message;
    }

    #endregion Methods
    //------------------------------------------------------------------------//

}

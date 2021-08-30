<?php
namespace App\Models;

use DB;
use Exception;
use Carbon\Carbon;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

use Laravel\Scout\Searchable;

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

    protected $appends = ['isFavoritedByMe', 'notes'];

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
        $sessionUser = Auth::user(); // %FIXME - should not reference session user in a model!
        if (!$sessionUser) {
            return '';
        }
        $otherUser = $this->participants->filter( function($u) use(&$sessionUser) {
            return $u->id !== $sessionUser->id;
        })->first();

        return Notes::where('user_id', $sessionUser->id)
            ->where('notes_id', $otherUser->timeline->id)
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
            $ct = Chatthread::create([
                'originator_id' => $originator->id,
            ]);
            $ct->addParticipant($participant->id);
        }
        return $ct;
    }

    // 'thin-controlller-fat-model' refacator of code from chatthreads.store
    public static function findOrCreateChat($sender, $rattrs)
    {
        $cmGroup = null; // the created chat message group, if any (1 only)
        $chatmessages = collect();  // the created chat messages, one or more

        //$chatthreads = DB::transaction( function() use(&$rattrs, &$cmGroup, &$chatmessages, &$sender) {  // breaks sqlite seeder when contains ->attach()! %FIXME

            $chatthreads = collect();
            $originator = User::find($rattrs->originator_id);
            $isMassMessage = count($rattrs->participants) > 1;

            if ($isMassMessage) {
                $cmgroupAttrs = [
                    'mgtype'          => MessagegroupTypeEnum::MASSMSG,
                    'sender_id'       => $sender->id,
                    'cattrs' => [
                        'sender_name'     => $sender->name,
                        'participants'    => $rattrs->participants ?? null,
                        'mcontent'        => $rattrs->mcontent ?? null,
                        'deliver_at'      => $rattrs->deliver_at ?? null, // %TODO: expect this to be an integer, UTC unix ts in s (?)
                        'price'           => $rattrs->price ?? null,
                        'currency'        => $rattrs->currency ?? null,
                        'attachments'     => $rattrs->attachments ?? null,
                    ],
                ];
                $cmGroup = Chatmessagegroup::create($cmgroupAttrs);
            }
    
            foreach ($rattrs->participants as $participantUserId) { // rattrs->participants is an array of [users] primary keys, and should *not* include originator
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
                    ]);
                    $ct->addParticipant($participantUserId);
                }
    
                if ( empty($rattrs->mcontent??null) && empty($rattrs->attachments??null) ) { 
                    // can't send message without text content or media attached
                    throw new Exception('New message requires content or media attached');
                }

                $cm = isset($rattrs->deliver_at)
                    ? $ct->scheduleMessage($sender, $rattrs->mcontent ?? '', $rattrs->deliver_at) // send at scheduled date
                    : $ct->sendMessage($sender, (object)[
                        'mcontent' => $rattrs->mcontent??'',
                        'price' => $rattrs->price??null,
                        'currency' => $rattrs->currency??null,
                        'attachments' => $rattrs->attachments??null,
                    ]); // send now

                if ($isMassMessage) {
                    $cm->chatmessagegroup_id = $cmGroup->id;
                    $cm->save();
                }

                $ct->refresh();
                $chatthreads->push($ct);

                $cm->refresh();
                $chatmessages->push($cm);

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

    // %FIXME %TODO: use transaction (??)
    //public function sendMessage(User $sender, $rattrs, Collection $cattrs = null) : Chatmessage
    public function sendMessage(
        User $sender, 
        string $mcontent = '',
        array $attachments = [],
        $price = null,
        $currency = null,
        Collection $cattrs = null
    ) : Chatmessage
    {
        if (!isset($cattrs)) {
            $cattrs = new Collection();
        }

        $cm = $this->chatmessages()->create([
              'sender_id' => $sender->id,
              'mcontent'  => $mcontent,
              'cattrs'    => $cattrs,
        ]);

        if ( isset($price) ) {
            $cm->setPurchaseOnly($price, $currency); // %FIXME: should pull a default currency from config (?)
        }

        // Create mediafile refs for any attachments
        if ( isset($attachments) && count($attachments) ) {
            foreach ($attachments??[] as $a) {
                if ($a['diskmediafile_id']) {
                    Mediafile::find($a['id'])->diskmediafile->createReference(
                        $cm->getMorphString(), // string   $resourceType
                        $cm->getKey(),         // int      $resourceID
                        $a['mfname'],          // string   $mfname
                        'messages'             // string   $mftype
                    );
                }
            }
        }

        return $cm;
    }

    /*
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
     */

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

<?php
namespace App;

use DB;
use Eloquent as Model;
use App\Enums\PaymentTypeEnum;
use App\Interfaces\Purchaseable;
use Intervention\Image\Facades\Image;
use App\Enums\ShareableAccessLevelEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Timeline extends Model implements Purchaseable
{
    //use SoftDeletes;
    use HasFactory;

    public $table = 'timelines';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $guarded = ['id','created_at','updated_at'];

    protected $casts = [
        'id'             => 'integer',
        'username'       => 'string',
        'name'           => 'string',
        'about'          => 'string',
        'avatar_id'      => 'integer',
        'cover_id'       => 'integer',
        'cover_position' => 'string',
        'type'           => 'string',
        'deleted_at'     => 'datetime',
    ];

    public static $rules = [
    ];

    /**
     * Checking for blank values with saving to DB
     */
    public static function boot() {
        parent::boot();
        self::creating(function($model) {
            $model->checkUsername();
        });
        self::updating(function($model) {
            $model->checkUsername();
        });
        self::saving(function($model) {
            $model->checkUsername();
        });
    }

    /**
     * Makes username a valid random username if it is null or empty.
     */
    public function checkUsername() {
        if (!isset($this->username) || $this->username === '') {
            $this->username = UsernameRule::createRandom();
        }
    }

    public function toArray()
    {
        $array = parent::toArray();

        $cover_url = $this->cover()->get()->toArray();
        $avatar_url = $this->avatar()->get()->toArray();
        $array['cover_url'] = $cover_url;
        $array['avatar_url'] = $avatar_url;

        if ($this->type == 'user') {
            $array['verified'] = $this->user()->first() ? $this->user()->first()->verified : 0;
        } else {
            $array['verified'] = $this->page()->first() ? $this->page()->first()->verified : 0;
        }

        return $array;
    }

    public function followers() {  // includes subscribers (ie premium + default followers)
        return $this->morphToMany('App\User', 'shareable', 'shareables', 'shareable_id', 'sharee_id')->withPivot('access_level', 'shareable_type', 'sharee_id');
    }

    public function ledgersales() {
        return $this->morphMany('App\Fanledger', 'purchaseable');
    }

    public function posts() {
        return $this->hasMany('App\Post');
    }

    public function stories() { 
        return $this->hasMany('App\Story');
    }

    public function user() { // timeline owner/creator
        return $this->hasOne('App\User');
    }

    public function avatar() {
        return $this->belongsTo('App\Mediafile', 'avatar_id');
    }

    public function cover() {
        return $this->belongsTo('App\Mediafile', 'cover_id');
    }

    public function page() {
        return $this->hasOne('App\Page');
    }

    public function groups() {
        return $this->hasOne('App\Group');
    }

    public function usersSaved() {
        return $this->belongsToMany('App\User', 'saved_timelines', 'timeline_id', 'user_id')->withPivot('type');
    }

    public function reports() {
        return $this->belongsToMany('App\User', 'timeline_reports', 'timeline_id', 'reporter_id')->withPivot('status');
    }

    // public function events()
    // {
    //     return $this->belongsTo('App\Event', 'timeline_id');
    // }

    public function event() {
        return $this->hasOne('App\Event');
    }

    function gen_num()
    {
        $rand   = 0;
        for ($i = 0; $i<15; $i++) {
            $rand .= mt_rand(0, 9);
        }
        return $rand;
    }

    public function notifications()
    {
        return $this->hasMany('App\Notification', 'timeline_id', 'id');
    }

    public function albums()
    {
        return $this->hasMany('App\Album');
    }

    public function wallpaper()
    {
        return $this->belongsTo('App\Media', 'background_id');
    } 

    public function saveWallpaper($wallpaper)
    {
        $strippedName = str_replace(' ', '', $wallpaper->getClientOriginalName());
        $photoName = date('Y-m-d-H-i-s').$strippedName;
        $photo = Image::make($wallpaper->getRealPath());
        $photo->save(storage_path().'/uploads/wallpapers/'.$photoName, 60);

        $media = Media::create([
            'title'  => $wallpaper->getClientOriginalName(),
            'type'   => 'image',
            'source' => $photoName,
        ]);

        $result = $this->update(['background_id' => $media->id]);

        $result = $result ? true : false;
        return $result;

    } 

    public function toggleWallpaper($action, $media)
    {
        if($action == 'activate'){

            $result = $this->update(['background_id' => $media->id]) ? 'activate' : false;
            return $result;
        }
        elseif($action == 'deactivate')
        {
            $result = $this->update(['background_id' => null]) ? 'deactivate' : false;
            return $result;
        }

    }

    // %%% --- Implement Purchaseable Interface ---

    public function receivePayment(
        string $ptype, // PaymentTypeEnum
        User $sender,
        int $amountInCents,
        array $cattrs = []
    ) : ?Fanledger
    {

        $result = null;

        switch ($ptype) {
        case PaymentTypeEnum::TIP:
            $result = Fanledger::create([
                'fltype' => $ptype,
                'seller_id' => $this->user->id,
                'purchaser_id' => $sender->id,
                'purchaseable_type' => 'timelines',
                'purchaseable_id' => $this->id,
                'qty' => 1,
                'base_unit_cost_in_cents' => $amountInCents,
                'cattrs' => $cattrs ?? [],
            ]);
            break;
        case PaymentTypeEnum::SUBSCRIPTION:
            $result = Fanledger::create([
                'fltype' => $ptype,
                'seller_id' => $this->user->id,
                'purchaser_id' => $sender->id,
                'purchaseable_type' => 'timelines', // basically a subscription
                'purchaseable_id' => $this->id,
                'qty' => 1,
                'base_unit_cost_in_cents' => $amountInCents,
                'cattrs' => $cattrs ?? [],
            ]);
            //dd($result->toArray());
            break;
        default:
            throw new Exception('Unrecognized payment type : '.$ptype);
        }

        return $result ?? null;
    }

    // Is the user provided following my timeline (includes either premium or default)
    public function isUserFollowing(User $user) : bool
    {
        return $this->followers->contains($user->id);
    }

    // Is the user provided following my timeline (includes either premium or default)
    public function isUserSubscribed(User $user) : bool
    {
        return $this->followers->where('pivot.access_level', ShareableAccessLevelEnum::PREMIUM)->contains($user->id);
    }

    public function isOwnedByUser(User $user) : bool
    {
        return $this->id === $user->timeline->id;
        //return $this->id === $user->timeline_id;
    }
}

/**
 * @SWG\Definition(
 *      definition="Timeline",
 *      required={},
 *      @SWG\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="username",
 *          description="username",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="name",
 *          description="name",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="about",
 *          description="about",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="avatar_id",
 *          description="avatar_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="cover_id",
 *          description="cover_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="cover_position",
 *          description="cover_position",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="type",
 *          description="type",
 *          type="string"
 *      )
 * )
 */

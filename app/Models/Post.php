<?php

namespace App\Models;

use DB;
use Auth;
use Exception;
use App\Models\Fanledger;
use App\Interfaces\UuidId;
use App\Interfaces\Ownable;
use App\Interfaces\Likeable;
use App\Interfaces\Deletable;
use App\Enums\PaymentTypeEnum;
use App\Interfaces\Reportable;
use App\Interfaces\Commentable;
use App\Models\Traits\UsesUuid;
use Illuminate\Support\Collection;
use App\Models\Traits\OwnableTraits;
use App\Models\Traits\LikeableTraits;
use Illuminate\Support\Facades\Storage;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Interfaces\Purchaseable; // was PaymentReceivable
use App\Models\Traits\SluggableTraits;

class Post extends Model implements UuidId, Ownable, Deletable, Purchaseable, Likeable, Reportable, Commentable
{
    use UsesUuid, SoftDeletes, HasFactory, OwnableTraits, LikeableTraits, Sluggable, SluggableTraits;

    //--------------------------------------------
    // Boot
    //--------------------------------------------
    public static function boot()
    {
        parent::boot();
        static::deleting(function ($model) {
            /*
            if (!$model->canBeDeleted()) {
                throw new Exception('Can not delete Post (26)'); // or soft delete and give access to purchasers (?)
            }
             */
            // %TODO: should refernce count these (?), or N/A as we are cloning (ie ref count always 1)?
            foreach ($model->mediafiles as $o) {
                Storage::disk('s3')->delete($o->filename); // Remove from S3
                $o->delete();
            }
            foreach ($model->comments as $o) {
                $o->delete();
            }
            foreach ($model->likes as $o) {
                $o->delete();
            }
        });
    }

    protected $guarded = [ 'id', 'created_at', 'updated_at' ];
    protected $appends = [ 'isLikedByMe', ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => [ 'description' ]
            ]
        ];
    }

    //--------------------------------------------
    // %%% Accessors/Mutators | Casts
    //--------------------------------------------

    public function getIsLikedByMeAttribute($value)
    {
        $sessionUser = Auth::user();
        return $sessionUser ? $this->likes->contains($sessionUser->id) : false;
    }

    protected $casts = [
        'cattrs' => 'array',
        'meta' => 'array',
    ];

    //--------------------------------------------
    // %%% Relationships
    //--------------------------------------------

    // can be shared with many users (via [shareables])
    public function sharees()
    {
        return $this->morphToMany('App\Models\User', 'shareable', 'shareables', 'shareable_id', 'sharee_id')->withTimestamps();
    }

    public function mediafiles()
    {
        return $this->morphMany('App\Models\Mediafile', 'resource');
    }

    public function ledgersales()
    {
        return $this->morphMany('App\Models\Fanledger', 'purchaseable');
    }

    // owner of the post
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function postable()
    {
        return $this->morphTo();
    }

    public function timeline()
    {
        //return $this->belongsTo('App\Models\Timeline', 'postable_id')->where('postable_type', $this->getMorphString('App\Models\Timeline')); // %FIXME: FAILS REGRESSION
        return $this->belongsTo('App\Models\Timeline', 'postable_id');
    }

    /**
     * Direct comments
     */
    public function comments(): MorphMany
    {
        return $this->morphMany('App\Models\Comment', 'commentable')->where('parent_id', null);
    }

    /**
     * Direct comments and all comment replies
     */
    public function allComments(): MorphMany
    {
        return $this->morphMany('App\Models\Comment', 'commentable');
    }

    //--------------------------------------------
    // %%% Methods
    //--------------------------------------------

    // `user()` alias
    public function poster()
    {
        return $this->user();
    }

    public function getOwner(): ?Collection
    {
        return new Collection([$this->user]);
    }

    // %%% --- Implement Purchaseable Interface ---

    public function receivePayment(
        string $fltype, // PaymentTypeEnum
        User $sender,
        int $amountInCents,
        array $cattrs = []
    ): ?Fanledger {
        $result = DB::transaction(function () use ($fltype, $amountInCents, $cattrs, &$sender) {

            switch ($fltype) {
                case PaymentTypeEnum::TIP:
                    $result = Fanledger::create([
                        'fltype' => $fltype,
                        'seller_id' => $this->user->id,
                        'purchaser_id' => $sender->id,
                        'purchaseable_type' => 'posts',
                        'purchaseable_id' => $this->id,
                        'qty' => 1,
                        'base_unit_cost_in_cents' => $amountInCents,
                        'cattrs' => json_encode($cattrs ?? []),
                    ]);
                    break;
                case PaymentTypeEnum::PURCHASE:
                    $result = Fanledger::create([
                        'fltype' => $fltype,
                        'seller_id' => $this->user->id,
                        'purchaser_id' => $sender->id,
                        'purchaseable_type' => 'posts',
                        'purchaseable_id' => $this->id,
                        'qty' => 1,
                        'base_unit_cost_in_cents' => $amountInCents,
                        'cattrs' => json_encode($cattrs ?? []),
                    ]);
                    /*
                    $sender->sharedposts()->attach($this->id, [
                        'cattrs' => json_encode($cattrs ?? []),
                    ]);
                     */
                    break;
                default:
                    throw new Exception('Unrecognized payment type : ' . $fltype);
            }

            return $result;
        });

        return $result ?? null;
    }

    public function canBeDeleted(): bool
    {
        return !($this->ledgersales->count() > 0);
    }
}

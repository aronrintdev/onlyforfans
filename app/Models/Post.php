<?php

namespace App\Models;

use DB;
use Auth;
use Exception;
use App\Models\Fanledger;
use App\Interfaces\Ownable;
use App\Interfaces\Likeable;
use App\Interfaces\Deletable;
use App\Interfaces\ShortUuid;
use App\Enums\PaymentTypeEnum;
use App\Interfaces\Reportable;
use App\Interfaces\Commentable;
use App\Models\Traits\UsesUuid;
use App\Traits\OwnableFunctions;
use Illuminate\Support\Collection;
use App\Models\Traits\UsesShortUuid;
use App\Models\Traits\LikeableTraits;
use Illuminate\Support\Facades\Storage;
use App\Models\Traits\CommentableTraits;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Interfaces\Purchaseable; // was PaymentReceivable

class Post extends Model implements Ownable, Deletable, Purchaseable, Likeable, Reportable, Commentable
{
    use UsesUuid;
    use SoftDeletes;
    use HasFactory;
    use OwnableFunctions;
    use LikeableTraits;
    use CommentableTraits;

    //--------------------------------------------
    // Boot
    //--------------------------------------------
    public static function boot()
    {
        parent::boot();
        static::deleting(function ($model) {
            if (!$model->canBeDeleted()) {
                throw new Exception('Can not delete Post (26)'); // or soft delete and give access to purchasers (?)
            }
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

    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

    protected $appends = [
        'isLikedByMe',
    ];

    //--------------------------------------------
    // %%% Accessors/Mutators | Casts
    //--------------------------------------------

    public function getIsLikedByMeAttribute($value)
    {
        $sessionUser = Auth::user();
        return $this->likes->contains($sessionUser->id);
    }

    //--------------------------------------------
    // %%% Relationships
    //--------------------------------------------

    /**
     * can be shared with many users (via [shareables])
     */
    public function sharees()
    {
        return $this->morphToMany('App\Models\User', 'shareable', 'shareables', 'shareable_id', 'sharee_id')->withTimestamps();
    }

    public function mediafiles()
    {
        return $this->morphMany('App\Models\Mediafile', 'resource');
    }

    public function ledgerSales()
    {
        return $this->morphMany('App\Models\Fanledger', 'purchaseable');
    }

    /**
     * owner of the post
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * `user()` alias
     */
    public function poster()
    {
        return $this->user();
    }

    public function getOwner(): ?Collection
    {
        return new Collection([$this->user]);
    }

    public function postable()
    {
        return $this->morphTo();
    }

    public function timeline()
    {
        return $this->belongsTo('App\Models\Timeline', 'postable_id')
            ->where('postable_type', $this->getMorphString('App\Models\Timeline'));
    }

    //--------------------------------------------
    // %%% Methods
    //--------------------------------------------

    // %%% --- Implement Purchaseable Interface ---

    public function receivePayment(
        string $fltype, // PaymentTypeEnum
        User $sender,
        int $amountInCents,
        array $customAttributes = []
    ): ?Fanledger {
        $result = DB::transaction(function () use ($fltype, $amountInCents, $customAttributes, &$sender) {

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
                        'cattrs' => json_encode($customAttributes ?? []),
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
                        'cattrs' => json_encode($customAttributes ?? []),
                    ]);
                    $sender->sharedposts()->attach($this->id, [
                        'custom_attributes' => json_encode($customAttributes ?? []),
                    ]);
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
        return !($this->ledgerSales->count() > 0);
    }
}
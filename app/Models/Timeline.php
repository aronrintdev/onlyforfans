<?php

namespace App\Models;

use Exception;
use Eloquent as Model;
use App\Interfaces\Ownable;
use App\Interfaces\ShortUuid;
use App\Enums\PaymentTypeEnum;
use App\Interfaces\Reportable;
use App\Models\Traits\UsesUuid;
use App\Interfaces\Purchaseable;
use Illuminate\Support\Collection;
use Cviebrock\EloquentSluggable\Sluggable;
use App\Models\Traits\UsesShortUuid;
use App\Enums\ShareableAccessLevelEnum;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Traits\OwnableTraits;

class Timeline extends Model implements Purchaseable, Ownable, Reportable
{
    use SoftDeletes, HasFactory, OwnableTraits, UsesUuid, Sluggable;

    protected $keyType = 'string';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'name' => 'string',
        'about' => 'string',
        'cattrs' => 'array',
        'meta' => 'array',
    ];

    public function toArray()
    {
        $array = parent::toArray();
        $array['cover_url'] = $this->cover()->get()->toArray();
        $array['avatar_url'] = $this->avatar()->get()->toArray();
        return $array;
    }

    public function sluggable(): array
    {
        return ['slug' => [
            'source' => [ 'user.username' ],
        ]];
    }

    // includes subscribers (ie premium + default followers)
    public function followers()
    {
        return $this->morphToMany('App\Models\User', 'shareable', 'shareables', 'shareable_id', 'sharee_id')
            ->withPivot('access_level', 'shareable_type', 'sharee_id', 'is_approved', 'cattrs')
            ->withTimestamps();
    }

    public function subscribers()
    {
        return $this->morphToMany('App\Models\User', 'shareable', 'shareables', 'shareable_id', 'sharee_id')
            ->withPivot('access_level', 'shareable_type', 'sharee_id', 'is_approved', 'cattrs')
            ->where('access_level', 'premium')
            ->withTimestamps();
    }


    public function ledgersales()
    {
        return $this->morphMany('App\Models\Fanledger', 'purchaseable');
    }

    public function posts()
    {
        return $this->morphMany('App\Models\Post', 'postable');
    }

    public function stories()
    {
        return $this->hasMany('App\Models\Story');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function avatar()
    {
        return $this->belongsTo('App\Models\Mediafile', 'avatar_id');
    }

    public function cover()
    {
        return $this->belongsTo('App\Models\Mediafile', 'cover_id');
    }

    // %%% --- Implement Purchaseable Interface ---

    public function receivePayment(
        string $fltype, // PaymentTypeEnum
        User $sender,
        int $amountInCents,
        array $customAttributes = []
    ): ?Fanledger {

        $result = null;

        switch ($fltype) {
            case PaymentTypeEnum::TIP:
                $result = Fanledger::create([
                    'fltype' => $fltype,
                    'seller_id' => $this->user->id,
                    'purchaser_id' => $sender->id,
                    'purchaseable_type' => 'timelines',
                    'purchaseable_id' => $this->id,
                    'qty' => 1,
                    'base_unit_cost_in_cents' => $amountInCents,
                    'cattrs' => json_encode($customAttributes ?? []),
                ]);
                break;
            case PaymentTypeEnum::SUBSCRIPTION:
                $result = Fanledger::create([
                    'fltype' => $fltype,
                    'seller_id' => $this->user->id,
                    'purchaser_id' => $sender->id,
                    'purchaseable_type' => 'timelines', // basically a subscription
                    'purchaseable_id' => $this->id,
                    'qty' => 1,
                    'base_unit_cost_in_cents' => $amountInCents,
                    'cattrs' => json_encode($customAttributes ?? []),
                ]);
                break;
            default:
                throw new Exception('Unrecognized payment type : ' . $fltype);
        }

        return $result ?? null;
    }

    // Is the user provided following my timeline (includes either premium or default)
    public function isUserFollowing(User $user): bool
    {
        return $this->followers->contains($user->id);
    }

    // Is the user provided following my timeline (includes either premium or default)
    public function isUserSubscribed(User $user): bool
    {
        return $this->followers->where('pivot.access_level', ShareableAccessLevelEnum::PREMIUM)->contains($user->id);
    }

    // Is timeline owned by a user (alias for `isOwner()`)
    public function isOwnedBy(User $user): bool
    {
        return $this->isOwner($user);
    }

    // %%% --- Implement Ownable Interface ---

    public function getOwner(): ?Collection
    {
        return new Collection([ $this->user ]);
    }
}

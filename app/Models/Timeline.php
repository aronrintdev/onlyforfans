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
use App\Traits\OwnableFunctions;
use Illuminate\Support\Collection;
use App\Models\Traits\UsesShortUuid;
use App\Enums\ShareableAccessLevelEnum;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Timeline extends Model implements Purchaseable, Ownable, Reportable, ShortUuid
{
    use SoftDeletes;
    use HasFactory;
    use OwnableFunctions;
    use UsesUuid;
    use UsesShortUuid;

    public $table = 'timelines';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'id'             => 'integer',
        'name'           => 'string',
        'about'          => 'string',
        'avatar_id'      => 'integer',
        'cover_id'       => 'integer',
    ];

    public function toArray()
    {
        $array = parent::toArray();

        $array['cover_url'] = $this->cover()->get()->toArray();
        $array['avatar_url'] = $this->avatar()->get()->toArray();

        return $array;
    }

    /**
     * includes subscribers (ie premium + default followers)
     */
    public function followers()
    {
        return $this->morphToMany('App\User', 'shareable', 'shareables', 'shareable_id', 'shared_with')
            ->withPivot('access_level', 'shareable_type', 'shared_with', 'is_approved', 'custom_attributes')
            ->withTimestamps();
    }

    public function ledgerSales()
    {
        return $this->morphMany('App\Models\FanLedger', 'purchaseable');
    }

    public function posts()
    {
        return $this->hasMany('App\Models\Post');
    }

    public function stories()
    {
        return $this->hasMany('App\Models\Story');
    }

    /**
     * timeline owner/creator
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function avatar()
    {
        return $this->belongsTo('App\Mediafile', 'avatar_id');
    }

    public function cover()
    {
        return $this->belongsTo('App\Mediafile', 'cover_id');
    }

    // %%% --- Implement Purchaseable Interface ---

    public function receivePayment(
        string $ptype, // PaymentTypeEnum
        User $sender,
        int $amountInCents,
        array $cattrs = []
    ): ?FanLedger {

        $result = null;

        switch ($ptype) {
            case PaymentTypeEnum::TIP:
                $result = FanLedger::create([
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
                $result = FanLedger::create([
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
                throw new Exception('Unrecognized payment type : ' . $ptype);
        }

        return $result ?? null;
    }

    /**
     * Is the user provided following my timeline (includes either premium or default)
     */
    public function isUserFollowing(User $user): bool
    {
        return $this->followers->contains($user->id);
    }

    /**
     * Is the user provided following my timeline (includes either premium or default)
     */
    public function isUserSubscribed(User $user): bool
    {
        return $this->followers->where('pivot.access_level', ShareableAccessLevelEnum::PREMIUM)->contains($user->id);
    }

    /**
     * Is timeline owned by a user (alias for `isOwner()`)
     */
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

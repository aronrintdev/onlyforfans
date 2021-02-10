<?php
namespace App\Policies;

use Illuminate\Database\Eloquent\Relations\Relation;
use App\User;
use App\MediaFile;
use App\Policies\Traits\OwnablePolicies;

class MediaFilePolicy extends BasePolicy
{
    use OwnablePolicies;

    protected $policies = [
        'viewAny'     => 'permissionOnly',
        'view'        => 'isOwner:pass isBlockedByOwner:fail',
        'update'      => 'isOwner:pass',
        'delete'      => 'isOwner:pass',
        'restore'     => 'isOwner:pass',
        'forceDelete' => 'isOwner:pass',
        'like'        => 'isBlockedByOwner:fail',
        'tip'         => 'isBlockedByOwner:fail',
    ];

    protected function view(User $user, Mediafile $mediafile)
    {
        switch ($mediafile->resource_type) {
        case 'comments':
        case 'posts':
        case 'stories':
        case 'vaultfolders':
            $alias = $mediafile->resource_type;
            $model = Relation::getMorphedModel($alias);
            $resource = (new $model)->where('id', $mediafile->resource_id)->first();
            return $user->can('view', $resource);
        default:
            return false;
        }
    }

    protected function create(User $user)
    {
        return true; // %TODO
    }
}

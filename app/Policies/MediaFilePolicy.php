<?php
namespace App\Policies;


use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\User;
use App\Models\MediaFile;
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

    protected function view(User $user, MediaFile $mediaFile)
    {
        switch ($mediaFile->resource_type) {
        case 'comments':
        case 'posts':
        case 'stories':
        case 'vaultFolders':
            $alias = $mediaFile->resource_type;
            $model = Relation::getMorphedModel($alias);
            $resource = (new $model)->where('id', $mediaFile->resource_id)->first();
            return $user->can('view', $resource);
        default:
            return false;
        }
    }

    protected function doClone(User $user, MediaFile $mediaFile)
    {
        return $user->isOwner($mediaFile);
    }

    protected function create(User $user)
    {
        return true; // %TODO
    }
}

<?php
namespace App\Policies;


use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\User;
use App\Models\Mediafile;
use App\Models\Vaultfolder;
use App\Policies\Traits\OwnablePolicies;

// %FIXME: correct name is Mediafile (only M is upper)
class MediafilePolicy extends BasePolicy
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
            $alias = $mediafile->resource_type;
            $model = Relation::getMorphedModel($alias);
            $resource = (new $model)->where('id', $mediafile->resource_id)->first();
            return $user->can('view', $resource);

        case 'vaultfolders':
            // if vaultfolder is shared => allowed
            // else, if mediafile is shared => allowed
            // else, not allowed
            $vaultfolder = Vaultfolder::find($mediafile->resource_id);
            if ( !$vaultfolder ) {
                return false;
            }
            $isVaultfolderShared = $vaultfolder->sharees->contains($user->id);
            if ( $isVaultfolderShared ) {
                return true;
            }

            $isMediafileShared = $mediafile->sharees->contains($user->id);
            if ( $isMediafileShared ) {
                return true;
            }
            return false;

        default:
            return false;
        }
    }

    protected function doClone(User $user, Mediafile $mediafile)
    {
        return $user->isOwner($mediafile);
    }

    protected function create(User $user)
    {
        return true; // %TODO
    }
}

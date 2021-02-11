<?php
namespace App\Policies;

use Illuminate\Database\Eloquent\Relations\Relation;
use App\User;
use App\Mediafile;
use App\Vaultfolder;
use App\Policies\Traits\OwnablePolicies;

// %FIXME: correct name is Mediafile (only M is upper)
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
dd('here.Z');
        switch ($mediafile->resource_type) {

        case 'comments':
        case 'posts':
        case 'stories':
            dd('here.A');
            $alias = $mediafile->resource_type;
            $model = Relation::getMorphedModel($alias);
            $resource = (new $model)->where('id', $mediafile->resource_id)->first();
            return $user->can('view', $resource);

        case 'vaultfolders':
            dd('here.0');
            // if vaultfolder is shared => allowed
            // else, if mediafile is shared => allowed
            // else, not allowed
            //dd('view', $mediafile->resource_type);
            $vaultfolder = Vaultfolder::find($mediafile->resource_id);
            $isVaultfolderShared = $vaultfolder->sharees->contains($user->id));
            if ( $isVaultfolderShared ) {
                dd('here.1');
                return true;
            }

            $mediafile = Mediafile::find($mediafile->resource_id);
            $isMediafileShared = $mediafile->sharees->contains($user->id));
            if ( $isMediafileShared ) {
                dd('here.2');
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

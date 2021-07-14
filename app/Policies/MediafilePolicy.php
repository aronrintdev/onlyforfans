<?php
namespace App\Policies;

use App\Models\Chatmessage;
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
        'favorite'    => 'isOwner:pass isBlockedByOwner:fail',
        'delete'      => 'isOwner:pass',
        'restore'     => 'isOwner:pass',
        'forceDelete' => 'isOwner:pass',
        'like'        => 'isBlockedByOwner:fail',
        'tip'         => 'isBlockedByOwner:fail',
    ];

    public static function isViewable(User $user, Mediafile $mediafile)
    {
        switch ($mediafile->resource_type) {

        case 'posts':
            $alias = $mediafile->resource_type;
            $model = Relation::getMorphedModel($alias);
            $resource = (new $model)->where('id', $mediafile->resource_id)->first();
            return $user->can('contentView', $resource); // %NOTE: contentView!

        case 'comments':
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

        case 'chatmessages':
            $message = Chatmessage::find($mediafile->resource_id);
            $isInThread = $message->chatthread->participants->contains($user->id);
            if ( $isInThread ) {
                return true;
            }
            // TODO: Add switch for paid vs free messages.

        default:
            return false;
        }

    }

    protected function view(User $user, Mediafile $mediafile)
    {
        return self::isViewable($user, $mediafile);
    }

    protected function doClone(User $user, Mediafile $mediafile)
    {
        return $user->isOwner($mediafile);
    }

    protected function create(User $user)
    {
        return true; // %TODO
    }

    protected function favorite(User $user, Mediafile $mediafile) {
        return true;
    }
}

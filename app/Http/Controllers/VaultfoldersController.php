<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use App\Http\Resources\VaultfolderCollection;
use App\Models\Invite;
use App\Models\Mediafile;
use App\Models\User;
use App\Models\Vault;
use App\Models\Vaultfolder;

use App\Enums\InviteTypeEnum;
use App\Mail\VaultfolderShared;

// $request->validate([ 'vf_id' => 'required', ]);
class VaultfoldersController extends AppBaseController
{
    public function index(Request $request)
    {
        $request->validate([
            // filters
            'user_id' => 'uuid|exists:users,id',
            'vault_id' => 'uuid|exists:vaults,id',
            'parent_id' => 'exclude_if:parent_id,root|uuid|exists:vaultfolders,id',
        ]);
        $filters = $request->only(['user_id', 'vault_id', 'parent_id']) ?? [];

        if ( !$request->user()->isAdmin() ) {
            do {
                // Check if permission to view the vault
                if ( array_key_exists('vault_id', $filters) ) {
                    $vault = Vault::findOrFail($filters['vault_id']);
                    if ( $request->user()->can('view', $vault) ) {
                        break; // allowed
                    }
                }
                abort(403); // none of the above match, therefore unauthorized
            } while(0);
        }

        // Init query
        $query = Vaultfolder::query()->with(['mediafiles', 'vfparent', 'vfchildren']);

        foreach ($filters as $k => $f) {
            switch ($k) {
            case 'parent_id':
                if ( is_null($f) || ($f==='root') ) {
                    $query->isRoot();
                } else {
                    $query->isChildOf($f);
                }
                break;
            default:
                $query->where($k, $f);
            }
        }
        $data = $query->paginate( $request->input('take', env('MAX_VAULTFOLDERS_PER_REQUEST', 10)) );
        return new VaultfolderCollection($data);
    }

    // %TODO: check session user owner
    public function show(Request $request, Vaultfolder $vaultfolder)
    {
        $this->authorize('view', $vaultfolder);

        $vaultfolder->load('vfchildren', 'vfparent', 'mediafiles');
        $breadcrumb = $vaultfolder->getBreadcrumb();
        $shares = collect();
        $vaultfolder->mediafiles->each( function($vf) use(&$shares) {
            $vf->sharees->each( function($u) use(&$vf, &$shares) {
                $shares->push([
                    'sharee_id' => $u->id,
                    'sharee_name' => $u->name,
                    'sharee_username' => $u->username,
                    'shareable_type' => 'mediafiles', // %FIXME: cleaner way to do this?, ie just get the pivot (?)
                    'shareable_id' => $vf->id,
                ]);
            });
        });
        $vaultfolder->vfchildren->each( function($vf) use(&$shares) {
            $vf->sharees->each( function($u) use(&$vf, &$shares) {
                $shares->push([
                    'sharee_id' => $u->id,
                    'sharee_name' => $u->name,
                    'sharee_username' => $u->username,
                    'shareable_type' => 'vaultfolders', // %FIXME: cleaner way to do this?, ie just get the pivot (?)
                    'shareable_id' => $vf->id,
                ]);
            });
        });

        return response()->json([
            'vaultfolder' => $vaultfolder,
            'breadcrumb' => $breadcrumb,
            'shares' => $shares,
        ]);
    }

    // Creates a new subfolder
    public function store(Request $request)
    {
        $vrules = [
            'vault_id' => 'required|uuid|exists:vaults,id',
            'vfname' => 'required|alpha_dash|min:1',
            // %TODO: prevent new folders on same level with duplicate names
        ];

        // %TODO: refactor to vault or vaultfolder model
        $attrs = [];
        if ( $request->has('parent_id') && !is_null($request->parent_id) ) {
            $vrules['parent_id'] = 'required|uuid|exists:vaultfolders,id';
            $attrs['parent_id'] = $request->parent_id;
        } else {
            $attrs['parent_id'] = null; // parent will be root folder, skip parent_id validation (optional param in effect)
        }
        $request->validate($vrules);

        $vault = Vault::find($request->vault_id);
        $attrs['vault_id'] = $request->vault_id;
        $attrs['vfname'] = $request->vfname;
        $attrs['user_id'] = $request->user()->id;
        $this->authorize('update', $vault);
        /*
        if ( $request->user()->cannot('update', $vault) || $request->user()->cannot('create', Vaultfolder::class) ) {
            abort(403);
        }
         */

        $vaultfolder = Vaultfolder::create($attrs);

        return response()->json([
            'vaultfolder' => $vaultfolder,
        ], 201);
    }


    // Create a new subfolder under *root* of recipient, fill with list of vault files selected by sender
    public function storeByShare(Request $request)
    {
        $vrules = [
            'vault_ids' => 'required|array', // receipient vault (could be more than one)
            'vault_ids.*' => 'required|uuid|exists:vaults,id',
            'mediafile_ids' => 'required|array',
            'mediafile_ids.*' => 'required|uuid|exists:mediafiles,id',
        ];

        $dstVaults = Vault::whereIn($request->vault_ids)->get();
        $mediafiles = Mediafile::whereIn($request->mediafile_ids)->get();

        // %TODO: DB transaction per user (??) 
        $vaultfolderIds = [];
        $dstVaults->each( function($v) use(&$request, &$mediafiles, &$vaultfolderIds) {

            //$this->authorize('update', $vault); // %TODO!

            $vfname = 'shared-from--'.$request->user()->username.'--'.substr(str_shuffle(MD5(microtime())), 0, 6);

            $rf = $v->getRootFolder(); // get root folder

            // create new subfolder
            $vaultfolder = Vaultfolder::create([
                'vault_id' => $v->id,
                'user_id' => $v->user_id,
                'parent_id' => $rf->id,
                'vfname' => $vfname,
            ]);
            $vaultfolderIds[] = $vaultfolder->id;

            // create mediafile references in new subfolder
            $mediafiles->each( function($mf) use(&$vaultfolder) {
                $mf->diskmediafile->createReference(
                    'vaultfolders'             // string   $resourceType
                    $vaultfolder->id,          // int      $resourceID
                    $mf->mfname,               // string   $mfname
                    MediafileTypeEnum::VAULT   // string   $mftype
                );
            });
        });

        return response()->json([
            'vaultfolder_ids' => $vaultfolderIds,
        ], 201);
    }

    public function update(Request $request, Vaultfolder $vaultfolder)
    {
        $this->authorize('update', $vaultfolder);

        $vrules = [
            'vfname' => 'required|sometimes|string',
        ];

        $attrs = [];
        if ( $request->has('parent_id') && !is_null($request->parent_id) ) {
            $vrules['parent_id'] = 'required|integer|min:1'; // add validation rule
            $attrs['parent_id'] = $request->parent_id;
        }
        $request->validate($vrules);
        $attrs['vfname'] = $request->vfname;

        $vaultfolder->fill($attrs);
        $vaultfolder->save();

        return response()->json([
            'vaultfolder' => $vaultfolder,
        ]);
    }

    public function destroy(Request $request, Vaultfolder $vaultfolder)
    {
        $this->authorize('delete', $vaultfolder);
        if ( $vaultfolder->isRootFolder() ) {
            abort(400);
        }
        $vaultfolder->delete();
        return response()->json([]);
    }

    // ---

    public function share(Request $request, Vaultfolder $vaultfolder)
    {
        $vrules = [
            'shareables' => 'array', // things being shared
            'sharees' => 'required|array', // sharee: registered user
            'sharees.*.id' => 'required|exists:users',
        ];

        $this->authorize('update', $vaultfolder);
        $sharees = collect();

        $shareeIDs = $request->input('sharees', []);
        foreach ( $shareeIDs as $_a ) {
            $_sharee = User::find($_a['id']);
            if ($_sharee) {
                $vaultfolder->sharees()->attach($_sharee->id);  
                Mail::to($_sharee->email)->queue( new VaultfolderShared($_sharee) );
                $sharees->push($_sharee);
            } else {
                Log::warning('Could not find user with id '.$_a['id'].' to share vaultfolder');
            }
        }
        return response()->json([
            'sharees' => $sharees,
        ]);
    }

}

                //$request->user()->sharedvaultfolders()->syncWithoutDetaching($vaultfolder->id); // do share %TODO: need to do when they register (!)

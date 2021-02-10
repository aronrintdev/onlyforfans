<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\MediaFile;
use App\Models\Vault;
use App\Models\VaultFolder;

// $request->validate([ 'vf_id' => 'required', ]);
class VaultFoldersController extends AppBaseController
{
    public function index(Request $request)
    {
        $filters = $request->filters ?? [];

        if ( !$request->user()->isAdmin() ) {
            do {
                if ( array_key_exists('vault_id', $filters) ) {
                    $vault = Vault::findOrFail($request->filters['vault_id']);
                    if ( $request->user()->can('view', $vault) ) {
                        break; // allowed
                    }
                }
                abort(403); // none of the above match, therefore unauthorized
            } while(0);
        }

        $query = VaultFolder::query();
        $query->with(['mediaFiles', 'parent', 'children']);

        foreach ( $request->input('filters', []) as $k => $v ) {
            switch ($k) {
            case 'parent_id':
                if ( is_null($v) || ($v==='root') ) {
                    $query->isRoot();
                } else {
                    $query->isChildOf($v);
                }
                break;
            default:
                $query->where($k, $v);
            }
        }
        $vaultFolders = $query->get();

        return response()->json([
            'vaultFolders' => $vaultFolders,
        ]);
    }

    // %TODO: check session user owner
    public function show(Request $request, VaultFolder $vaultFolder)
    {
        if ( $request->user()->cannot('view', $vaultFolder) ) {
            abort(403);
        }

        $vaultFolder->load('children', 'parent', 'mediaFiles');
        $breadcrumb = $vaultFolder->getBreadcrumb();
        $shares = collect();
        $vaultFolder->mediaFiles->each( function($vf) use(&$shares) {
            $vf->sharees->each( function($u) use(&$vf, &$shares) {
                $shares->push([
                    'sharee_id' => $u->id,
                    'sharee_name' => $u->name,
                    'sharee_username' => $u->username,
                    'shareable_type' => 'mediaFiles', // %FIXME: cleaner way to do this?, ie just get the pivot (?)
                    'shareable_id' => $vf->id,
                ]);
            });
        });
        $vaultFolder->children->each( function($vf) use(&$shares) {
            $vf->sharees->each( function($u) use(&$vf, &$shares) {
                $shares->push([
                    'sharee_id' => $u->id,
                    'sharee_name' => $u->name,
                    'sharee_username' => $u->username,
                    'shareable_type' => 'vaultFolders', // %FIXME: cleaner way to do this?, ie just get the pivot (?)
                    'shareable_id' => $vf->id,
                ]);
            });
        });

        return response()->json([
            'sessionUser' => $request->user(),
            'vaultFolder' => $vaultFolder,
            'breadcrumb' => $breadcrumb,
            'shares' => $shares,
        ]);
    }

    // %FIXME: this should be in VaultController, an does the Vault policy (?)
    public function store(Request $request)
    {
        $vrules = [
            'vault_id' => 'required|integer|min:1',
            'vfname' => 'required|string',
        ];

        $attrs = [];
        if ( $request->has('parent_id') && !is_null($request->parent_id) ) {
            $vrules['parent_id'] = 'required|integer|min:1'; // add validation rule
            $attrs['parent_id'] = $request->parent_id;
        } else {
            $attrs['parent_id'] = null; // parent will be root folder, skip parent_id validation (optional param in effect)
        }
        $request->validate($vrules);
        $attrs['vault_id'] = $request->vault_id;
        $attrs['name'] = $request->name;

        $vault = Vault::find($request->vault_id);

        if ( $request->user()->cannot('update', $vault) || $request->user()->cannot('create', VaultFolder::class) ) {
            abort(403);
        }

        $vaultFolder = VaultFolder::create($attrs);

        return response()->json([
            'vaultFolder' => $vaultFolder,
        ], 201);
    }

    public function update(Request $request, VaultFolder $vaultFolder)
    {
        if ( $request->user()->cannot('update', $vaultFolder) ) {
            abort(403);
        }

        $vrules = [
            'name' => 'required|sometimes|string',
        ];

        $attrs = [];
        if ( $request->has('parent_id') && !is_null($request->parent_id) ) {
            $vrules['parent_id'] = 'required|integer|min:1'; // add validation rule
            $attrs['parent_id'] = $request->parent_id;
        }
        $request->validate($vrules);
        $attrs['name'] = $request->name;

        $vaultFolder->fill($attrs);
        $vaultFolder->save();

        return response()->json([
            'vaultFolder' => $vaultFolder,
        ]);
    }

    public function destroy(Request $request, VaultFolder $vaultFolder)
    {
        if ( $request->user()->cannot('delete', $vaultFolder) ) {
            abort(403);
        }
        if ( $vaultFolder->isRootFolder() ) {
            abort(400);
        }
        $vaultFolder->delete();
        return response()->json([]);
    }

    // ---


}

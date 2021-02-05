<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Mediafile;
use App\Vault;
use App\Vaultfolder;

// $request->validate([ 'vf_id' => 'required', ]);
class VaultfoldersController extends AppBaseController
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

        $query = Vaultfolder::query();
        $query->with('mediafiles');
        $query->with('vfparent')->with('vfchildren');

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
        $vaultfolders = $query->get();

        return response()->json([
            'vaultfolders' => $vaultfolders,
        ]);
    }

    // %TODO: check session user owner
    public function show(Request $request, $pkid)
    {
        $sessionUser = Auth::user();
        $vaultfolder = Vaultfolder::where('id', $pkid)->with('vfchildren', 'vfparent', 'mediafiles')->first();
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
            'sessionUser' => $sessionUser,
            'vaultfolder' => $vaultfolder,
            'breadcrumb' => $breadcrumb,
            'shares' => $shares,
        ]);
    }

    // %FIXME: this should be in VaultController, an duse the Vault policy (?)
    public function store(Request $request)
    {
        $request->validate([
            'parent_id' => 'required|integer|min:1',
            'vault_id' => 'required|integer|min:1',
            'vfname' => 'required|string',
        ]);
        $vault = Vault::find($request->vault_id);

        if ( $request->user()->cannot('update', $vault) || $request->user()->cannot('create', Vaultfolder::class) ) {
            abort(403);
        }

        $vaultfolder = Vaultfolder::create( $request->only('parent_id', 'vault_id', 'vfname') );

        return response()->json([
            'vaultfolder' => $vaultfolder,
        ], 201);
    }

}

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

class VaultfoldersController extends AppBaseController
{
    public function index(Request $request)
    {
        $sessionUser = Auth::user();

        //$this->validate($request, [
        $request->validate([
            'vf_id' => 'required',
        ]);
        $vfId = $request->vf_id;

        if ( is_null($vfId) || $vfId==='root' ) {
            $myVault = $sessionUser->vaults()->first(); // %FIXME
            $cwf = $myVault->getRootFolder(); // 'current working folder'
        } else {
            $cwf = Vaultfolder::findOrFail($vfId);
        }

        return response()->json([
            'cwf' => $cwf,
            'parent' => $cwf->vfparent,
            'children' => $cwf->vfchildren,
            'mediafiles' => $cwf->mediafiles,
        ]);
    }

    // %TODO: check session user owner
    public function show(Request $request, $pkid)
    {
        $sessionUser = Auth::user();
        $vaultfolder = Vaultfolder::where('id', $pkid)->with('vfchildren', 'vfparent', 'mediafiles')->first();

        return response()->json([
            'sessionUser' => $sessionUser,
            'vaultfolder' => $vaultfolder,
        ]);
    }

}

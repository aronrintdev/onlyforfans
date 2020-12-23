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
use App\User;

class VaultsController extends AppBaseController
{
    public function dashboard(Request $request)
    {
        $sessionUser = Auth::user();

        $this->_php2jsVars['session'] = [
            'username' => $sessionUser->username,
        ];
        \View::share('g_php2jsVars',$this->_php2jsVars);

        $myVault = $sessionUser->vaults()->first(); // %FIXME

        // If it doesn't exist, create one
        if ( empty($myVault) ) {
            $myVault = DB::transaction(function () use(&$sessionUser) {
                $v = Vault::create([
                    'vname' => 'My Home Vault',
                    'user_id' => $sessionUser->id,
                ]);
                $vf = Vaultfolder::create([
                    'parent_id' => null,
                    'vault_id' => $v->id,
                    'vfname' => 'Root',
                ]);
                $v->refresh();
                return $v;
            });
        }

        $vaultRootFolder = $myVault->getRootFolder();

        return view('vault.dashboard', [
            'sessionUser' => $sessionUser,
            'myVault' => $myVault,
            'vaultRootFolder' => $vaultRootFolder,
        ]);
    }

    public function index(Request $request)
    {
        $sessionUser = Auth::user();
        $vaults = Story::where('user_id', $sessionUser->id)->get();
        return response()->json([
            'sessionUser' => $sessionUser,
            'vaults' => $vaults,
        ]);
    }

    public function show(Request $request, $pkid)
    {
        $sessionUser = Auth::user();
        $vault = Vault::where('id', $pkid)->where('user_id', $sessionUser->id)->first();
        return response()->json([
            'vault' => $vault,
        ]);
    }

    // %TODO: 
    //   ~ use DB transaction (?)
    //   ~ verify resource belongs to the given vault (??)
    public function updateShares(Request $request, $pkid)
    {
        $sessionUser = Auth::user();
        $vault = Vault::where('id', $pkid)->where('user_id', $sessionUser->id)->first();

        $shareables = $request->input('shareables', []);

        // (1) Handle sharees
        $sharees = $request->input('sharees', []);
        foreach ( $sharees as $se ) {
            $user = User::find($se['sharee_id']); // user to share with, ie 'sharee'
            if ( !$user ) {
                continue;
            }
            foreach ( $shareables as $sb ) {
                switch ( $sb['shareable_type'] ) {
                    case 'mediafiles':
                        $user->sharedmediafiles()->syncWithoutDetaching($sb['shareable_id']); // do share
                        break;
                    case 'vaultfolders':
                        $user->sharedvaultfolders()->syncWithoutDetaching($sb['shareable_id']); // do share
                        break;
                }
            }
        }

        // (2) Handle invites/invitees
        $invitees = $request->input('invitees', []);
        foreach ( $invitees as $i ) {
        }

        return response()->json([
            'vault' => $vault,
        ]);
    }

}

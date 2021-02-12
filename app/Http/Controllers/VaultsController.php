<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use View;

use App\Models\User;
use App\Models\Story;
use App\Models\Vault;

use App\Models\Invite;
use App\Models\Mediafile;
use App\Models\Vaultfolder;
use Illuminate\Http\Request;
use App\Enums\InviteTypeEnum;
//use App\Jobs\ProcessVaultInvites;
use App\Mail\ShareableInvited;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VaultsController extends AppBaseController
{
    public function dashboard(Request $request)
    {
        $sessionUser = Auth::user();

        $this->_php2jsVars['session'] = [
            'username' => $sessionUser->username,
        ];
        View::share('g_php2jsVars',$this->_php2jsVars);

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

    // %TODO: DEPRECATE - move to VaultfoldersContoller & MediafilesController (batch)
    //   ~ use DB transaction (?)
    //   ~ verify resource belongs to the given vault (??)
    public function updateShares(Request $request, $pkid)
    {
        throw new \Exception('DEPRECATED - use VaultfoldersContoller or MediafilesController (batch)');
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

        // %TODO: in job, set update_at for invite each time an email for it is sent

        // (2) Handle invites/invitees
        //   ~ %FIXME: use transaction (?)
        $invitees = $request->input('invitees', []);
        foreach ( $invitees as $i ) {

            // (a) create invites
            $invite = Invite::create([
                'inviter_id' => $sessionUser->id,
                'email' => $i['email'],
                'itype' => InviteTypeEnum::VAULT,
                'cattrs' => [
                    'shareables' => $shareables,
                    'vault_id' => $vault->id,
                ],
            ]);

            // (b) queue invite-based email
            //ProcessVaultInvites::dispatch($invite); // %TODO: don't think we need this (!?)
            Mail::to($i['email'])->queue( new ShareableInvited($invite) );

        }

        return response()->json([
            'vault' => $vault,
        ]);
    }

    //public function getRootFolder(Request $request, Vault $vault)
    public function getRootFolder(Request $request, Vault $vault)
    {
        if ( $request->user()->cannot('view', $vault) ) {
            abort(403);
        }
        $vaultfolder = Vaultfolder::with('vfchildren', 'vfparent', 'mediafiles')
            ->where('vault_id', $vault->id)
            ->whereNull('parent_id')
            ->first();

        return response()->json([
            'vaultfolder' => $vaultfolder,
        ]);
    }

}

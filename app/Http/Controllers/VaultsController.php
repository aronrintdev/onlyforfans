<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

use App\Invite;
use App\Mediafile;
use App\Vault;
use App\Vaultfolder;
use App\User;
//use App\Jobs\ProcessVaultInvites;
use App\Mail\ShareableInvited;
use App\Enums\InviteTypeEnum;

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
                'token' => str_random(),
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

}

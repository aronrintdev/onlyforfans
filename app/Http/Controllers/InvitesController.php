<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\Invite;
use App\Models\Mediafile;
use App\Models\User;
use App\Models\Vault;
use App\Models\Vaultfolder;
use App\Enums\InviteTypeEnum;
use App\Mail\ShareableInvited;

class InvitesController extends AppBaseController
{
    public function shareVaultResources(Request $request, Vaultfolder $vaultfolder)
    {
        // [ ] share entire folder contents
        // [ ] OR, share specific files in a single folder
        // [ ] DISALLOW root folder
        $vrules = [
            //'vaultfolder_id' => 'required|integer|exists:vaultfolders',
            'shareables' => 'array', // things being shared
            'invitees' => 'required|array', // invitee: non-registered user
            'invitees.*.email' => 'required,email',
            'invitees.*.name' => 'required,string',
        ];

        // %TODO: verify things being shared belong to the vaultfolder (?)

        // session user must own the vaultfolder
        //$vaultfolder = Vaultfolder::find($request->vaultfolder_id);
        $this->authorize('update', $vaultfolder);
        $invites = collect();

        $invitees = $request->input('invitees', []);
        foreach ( $invitees as $_a ) {
            $_invite = Invite::create([
                'inviter_id' => $request->user()->id,
                'email' => $_a['email'],
                'itype' => InviteTypeEnum::VAULT,
                'cattrs' => [
                    'shareables' => $request->shareables ?? [],
                    'invitee' => $_a,
                    'vaultfolder_id' => $vaultfolder->id,
                ],
            ]);
            Mail::to($_invite->email)->queue( new ShareableInvited($_invite) );
            //$_invite->send();
            $invites->push($_invite);
        }

        return response()->json([
            'invites' => $invites,
        ]);
    }

}

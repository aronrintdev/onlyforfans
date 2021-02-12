<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Invite;
use App\Mediafile;
use App\Vault;
use App\Vaultfolder;
use App\Enums\InviteTypeEnum;
use App\Mail\ShareableInvited;

class InvitesController extends AppBaseController
{
    public function store(Request $request)
    {
        // [ ] share entire folder contents
        // [ ] OR, share specific files in a single folder
        // [ ] DISALLOW root folder
        $vrules = [
            'vaultfolder_id' => 'required|integer|exists:vaultfolders',
            'invitees' => 'required|array',
            'invitees.*.email' => 'required|email',
            'invitees.*.name' => 'string',
            'shareables' => 'array',
        ];

        // session user must own the vaultfolder
        $vaultfolder = Vaultfolder::find($request->vaultfolder_id);
        $this->authorize('update', $vaultfolder);

        $sharees = $request->input('invitees', []);
        $invites = collect();
        foreach ( $sharees as $se ) {
            $i = Invite::create([
                'inviter_id' => $request->user()->id,
                'email' => $se['email'],
                'itype' => InviteTypeEnum::VAULT,
                'cattrs' => [
                    //'shareables' => $request->shareables ? $request->shareables->toArray() : [],
                    'shareables' => $request->shareables ?? [],
                    'vaultfolder_id' => $vaultfolder->id,
                ],
            ]);
            Mail::to($i->email)->queue( new ShareableInvited($i) );
            //$i->send();
            $invites->push($i);
        }

        //$request->user()->sharedvaultfolders()->syncWithoutDetaching($vaultfolder->id); // do share %TODO: need to do when they register (!)

        return response()->json([
            'invites' => $invites,
        ]);
    }

}

<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Mediafile;
use App\Models\Vaultfolder;
use App\Models\Timeline;
use App\Models\User;

class ShareablesController extends AppBaseController
{
    public function index(Request $request)
    {
        $sessionUser = Auth::user();

        $mediafiles = $sessionUser->sharedMediafiles->map( function($mf) {
            $mf->owner = $mf->getOwner()->first()->only('username', 'name', 'avatar');
            return $mf;
        });

        return response()->json([
            'shareables' => [
                'mediafiles' => $mediafiles,
                'vaultfolders' => $sessionUser->sharedVaultfolders,
            ],
        ]);
    }

}

<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Mediafile;
use App\Vaultfolder;
use App\Timeline;
use App\User;

class ShareablesController extends AppBaseController
{
    public function index(Request $request)
    {
        $sessionUser = Auth::user();

        $mediafiles = $sessionUser->sharedmediafiles->map( function($mf) {
            $mf->owner = $mf->getOwner()->first()->only('username', 'name', 'avatar');
            return $mf;
        });

        return response()->json([
            'shareables' => [
                'mediafiles' => $mediafiles,
                'vaultfolders' => $sessionUser->sharedvaultfolders,
            ],
        ]);
    }

}

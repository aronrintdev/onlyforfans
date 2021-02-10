<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\MediaFile;
use App\Models\VaultFolder;
use App\Models\Timeline;
use App\Models\User;

class ShareablesController extends AppBaseController
{
    public function index(Request $request)
    {
        $sessionUser = Auth::user();

        $mediaFiles = $sessionUser->sharedMediaFiles->map( function($mf) {
            $mf->owner = $mf->getOwner()->first()->only('username', 'name', 'avatar');
            return $mf;
        });

        return response()->json([
            'shareables' => [
                'mediaFiles' => $mediaFiles,
                'vaultFolders' => $sessionUser->sharedVaultFolders,
            ],
        ]);
    }

}

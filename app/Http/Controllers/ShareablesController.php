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
            $mf->owner = $mf->getOwner()->only('username', 'name', 'avatar');
            return $mf;
        });

        return response()->json([
            'shareables' => [
                'mediafiles' => $mediafiles,
                'vaultfolders' => $sessionUser->sharedvaultfolders,
            ],
        ]);
    }

    public function followTimeline(Request $request, Timeline $shareable)
    {
        $request->validate([
            'sharee_id' => 'required|numeric|min:1',
        ]);

        $shareable->followers()->syncWithoutDetaching($request->sharee_id, [
            'shareable_type' => 'timelines',
            'shareable_id' => $shareable->id,
            'is_approved' => 1, // %FIXME
            'access_level' => 'default',
            'cattrs' => [],
        ]); //

        return response()->json([
            'shareable' => $shareable,
            'follower_count' => $shareable->followers->count(),
        ]);
    }
}

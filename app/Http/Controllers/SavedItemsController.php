<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use View;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\MediaFile;
use App\Models\Vault;
use App\Models\VaultFolder;

class SavedItemsController extends AppBaseController
{
    public function dashboard(Request $request)
    {
        $sessionUser = Auth::user();

        $this->_php2jsVars['session'] = [
            'username' => $sessionUser->username,
        ];
        View::share('g_php2jsVars',$this->_php2jsVars);

        $myVault = $sessionUser->vaults()->first(); // %FIXME
        $vaultRootFolder = $myVault->getRootFolder();

        return view('saved.dashboard', [
            'sessionUser' => $sessionUser,
            'myVault' => $myVault,
            'vaultRootFolder' => $vaultRootFolder,
        ]);
    }
    public function index(Request $request)
    {
        $sessionUser = Auth::user();
        $saves = $sessionUser->postsSaved()->with('user', 'images')->get();

        return response()->json([
            'saves' => $saves,
        ]);
    }

    // %TODO: check session user owner
    public function show(Request $request, $pkid)
    {
        $sessionUser = Auth::user();
        /*
        $vaultFolder = VaultFolder::where('id', $pkid)->with('children', 'parent', 'mediaFiles')->first();
        $breadcrumb = $vaultFolder->getBreadcrumb();
         */

        return response()->json([
            //'sessionUser' => $sessionUser,
            //'vaultFolder' => $vaultFolder,
            //'breadcrumb' => $breadcrumb,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'parent_id' => 'required|integer|min:1',
            'vault_id' => 'required|integer|min:1',
            'name' => 'required|string',
        ]);

        /*
        $sessionUser = Auth::user();
        $vaultFolder = VaultFolder::create( $request->only('parent_id', 'vault_id', 'name') );
         */

        return response()->json([
            //'vaultFolder' => $vaultFolder,
        ]);
    }

}

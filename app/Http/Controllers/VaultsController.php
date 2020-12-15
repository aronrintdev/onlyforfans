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

    public function create(Request $request)
    {
        /*
        $sessionUser = Auth::user();
        return view('stories.create', [
            'session_user' => $sessionUser,
            'timeline' => $sessionUser->timeline,
            'stories' => $storiesA,
            'dtoUser' => [
                'avatar_url' => $sessionUser->avatar,
                'fullname' => $sessionUser->timeline->name,
                'username' => $sessionUser->timeline->username,
            ],
        ]);
         */
    }

    public function store(Request $request)
    {
        /*
        $request['attrs'] = json_decode($request['attrs'], true); // decode 'complex' data

        $sessionUser = Auth::user();
        $this->validate($request, [
            'attrs' => 'required',
            'attrs.stype' => 'required',
            'mediafile' => 'required_if:attrs.stype,image|file',
        ]);


        try {
            $obj = DB::transaction(function () use(&$sessionUser, &$request) {

                $story = Story::create([
                    'timeline_id' => $sessionUser->timeline_id,
                    'content' => $request->attrs['content'] ?? null,
                    'cattrs' => [
                        'background-color' => array_key_exists('bgcolor', $request->attrs) ? $request->attrs['bgcolor'] : '#fff',
                    ],
                    'stype' => $request->attrs['stype'],
                ]);

                if ( $request->attrs['stype'] === 'image' ) {
                    $file = $request->file('mediafile');
                    $newFilename = $file->store('fans-platform/stories', 's3'); // %FIXME: hardcoded
                    $mediafile = Mediafile::create([
                        'resource_id' => $story->id,
                        'resource_type' => 'stories',
                        'filename' => $newFilename,
                        'mftype' => MediafileTypeEnum::STORY,
                        'meta' => $request->input('attrs.foo') ?? null,
                        'cattrs' => $request->input('attrs.bar') ?? null,
                        'mimetype' => $file->getMimeType(),
                        'orig_filename' => $file->getClientOriginalName(),
                        'orig_ext' => $file->getClientOriginalExtension(),
                    ]);
                }
                return $story;
            });
        } catch (Exception $e) {
            // %TODO: delete file on s3 if it got uploaded
            Log::error(json_encode([
                'msg' => $e->getMessage(),
                'debug' => ['request'=>$request->all()],
            ], JSON_PRETTY_PRINT ));
            throw $e; // %FIXME: report error to user via browser message
        }

        if ( $request->ajax() ) {
            return response()->json([ 'obj' => $obj ]);
        } else {
            return back()->withInput();
        }
         */
    }

}

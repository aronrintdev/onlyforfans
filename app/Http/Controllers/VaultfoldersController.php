<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use App\Models\User;
use App\Models\Vault;
use App\Models\Invite;
use App\Models\Mediafile;

use App\Models\Vaultfolder;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Enums\InviteTypeEnum;
use App\Mail\VaultfolderShared;
use App\Enums\MediafileTypeEnum;
use App\Models\Mediafilesharelog;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Notifications\VaultfileShareSent;
use App\Enums\MediafilesharelogStatusEnum;
use App\Http\Resources\VaultfolderCollection;

// $request->validate([ 'vf_id' => 'required', ]);
class VaultfoldersController extends AppBaseController
{
    public function index(Request $request)
    {
        $request->validate([
            // filters
            'user_id' => 'uuid|exists:users,id',
            'vault_id' => 'uuid|exists:vaults,id',
            'parent_id' => 'exclude_if:parent_id,root|uuid|exists:vaultfolders,id',
        ]);
        $filters = $request->only(['user_id', 'vault_id', 'parent_id']) ?? [];

        if ( !$request->user()->isAdmin() ) {
            do {
                // Check if permission to view the vault
                if ( array_key_exists('vault_id', $filters) ) {
                    $vault = Vault::findOrFail($filters['vault_id']);
                    if ( $request->user()->can('view', $vault) ) {
                        break; // allowed
                    }
                }
                abort(403); // none of the above match, therefore unauthorized
            } while(0);
        }

        // Init query
        $query = Vaultfolder::query()->with(['mediafiles', 'vfparent', 'vfchildren']);

        foreach ($filters as $k => $f) {
            switch ($k) {
            case 'parent_id':
                if ( is_null($f) || ($f==='root') ) {
                    $query->isRoot();
                } else {
                    $query->isChildOf($f);
                }
                break;
            default:
                $query->where($k, $f);
            }
        }
        $data = $query->paginate( $request->input('take', env('MAX_VAULTFOLDERS_PER_REQUEST', 10)) );
        return new VaultfolderCollection($data);
    }

    // %TODO: check session user owner
    public function show(Request $request, Vaultfolder $vaultfolder)
    {
        $this->authorize('view', $vaultfolder);

        $vaultfolder->load(['vfchildren', 'vfparent', 'mediafiles', 'sharees', 'mediafilesharelogs']);
        //$vaultfolder->load(['vfchildren', 'vfparent', 'mediafiles']);

        $breadcrumb = $vaultfolder->getBreadcrumb();
        $shares = collect();
        $vaultfolder->mediafiles->each( function($vf) use(&$shares) {
            $vf->sharees->each( function($u) use(&$vf, &$shares) {
                $shares->push([
                    'sharee_id' => $u->id,
                    'sharee_name' => $u->name,
                    'sharee_username' => $u->username,
                    'shareable_type' => 'mediafiles', // %FIXME: cleaner way to do this?, ie just get the pivot (?)
                    'shareable_id' => $vf->id,
                ]);
            });
        });
        $vaultfolder->vfchildren->each( function($vf) use(&$shares) {
            $vf->sharees->each( function($u) use(&$vf, &$shares) {
                $shares->push([
                    'sharee_id' => $u->id,
                    'sharee_name' => $u->name,
                    'sharee_username' => $u->username,
                    'shareable_type' => 'vaultfolders', // %FIXME: cleaner way to do this?, ie just get the pivot (?)
                    'shareable_id' => $vf->id,
                ]);
            });
        });

        return response()->json([
            'vaultfolder' => $vaultfolder,
            'breadcrumb' => $breadcrumb,
            'shares' => $shares,
        ]);
    }

    // Creates a new subfolder
    public function store(Request $request)
    {
        //dd($request->all());
        $vrules = [
            'vault_id' => 'required|uuid|exists:vaults,id',
            //'vfname' => 'required|string|min:1',
            'vfname' => [
                'required',
                'min:1', 
                'regex:/^[a-zA-Z0-9\s\-]+$/',
            ],
            // %TODO: prevent new folders on same level with duplicate names
        ];

        // %TODO: refactor to vault or vaultfolder model
        $attrs = [];
        if ( $request->has('parent_id') && !is_null($request->parent_id) ) {
            $vrules['parent_id'] = 'required|uuid|exists:vaultfolders,id';
            $attrs['parent_id'] = $request->parent_id;
        } else {
            $attrs['parent_id'] = null; // parent will be root folder, skip parent_id validation (optional param in effect)
        }
        $request->validate($vrules);

        $vault = Vault::find($request->vault_id);
        $attrs['vault_id'] = $request->vault_id;
        $attrs['vfname'] = trim($request->vfname);
        $attrs['user_id'] = $request->user()->id;
        $this->authorize('update', $vault);
        /*
        if ( $request->user()->cannot('update', $vault) || $request->user()->cannot('create', Vaultfolder::class) ) {
            abort(403);
        }
         */

        $vaultfolder = Vaultfolder::create($attrs);

        return response()->json([
            'vaultfolder' => $vaultfolder,
        ], 201);
    }


    // Create a new subfolder under *root* of recipient, fill with list of vault files selected by sender
    public function storeByShare(Request $request)
    {
        $vrules = [
            'vault_ids' => 'required_without:user_ids|array', // receipient vault (could be more than one)
            'vault_ids.*' => 'required_without:user_ids|uuid|exists:vaults,id',
            'user_ids' => 'required_without:vault_ids|array',
            'user_ids.*' => 'required_without:vault_ids|uuid|exists:users,id',
            'mediafile_ids' => 'required|array',
            'mediafile_ids.*' => 'required|uuid|exists:mediafiles,id',
        ];

        if ( $request->has('vault_ids') ) {
            $dstVaults = Vault::whereIn('id', $request->vault_ids)->get();
        } else {
            $dstVaults = Vault::whereIn('user_id', $request->user_ids)->get();
        }
        $mediafiles = Mediafile::whereIn('id', $request->mediafile_ids)->get();

        $vaultfolderIds = [];

        $dstVaults->each( function($v) use(&$request, &$mediafiles, &$vaultfolderIds) {

            //$this->authorize('update', $vault); // %TODO!

            DB::transaction(function() use (&$request, &$mediafiles, &$vaultfolderIds, &$v) {

                $vfname = 'shared-from-'.$request->user()->username.'-'.substr(str_shuffle(MD5(microtime())), 0, 6);

                $rf = $v->getRootFolder(); // get root folder

                // create new subfolder (dst)
                $vaultfolder = Vaultfolder::create([
                    'vault_id' => $v->id,
                    'user_id' => $v->user_id, // receiver
                    'parent_id' => $rf->id,
                    'vfname' => $vfname,
                    'is_pending_approval' => 1, // %NOTE! // %FIXME: redundant? can reference from log table?
                    'cattrs' => [
                        'shared_by' => [
                            'username' => $request->user()->username, // can get from mediafilesharelog (?) %TODO
                            'user_id' => $request->user()->id,
                        ],
                    ],
                ]);
                $vaultfolderIds[] = $vaultfolder->id;

                // Create logs for the share...mediafile refs are created upon receiver approval
                $mediafiles->each( function($mf) use(&$request, &$vaultfolder, &$v) {
                    $mediafilesharelog = Mediafilesharelog::create([
                        'sharer_id' => $request->user()->id,
                        'sharee_id' => $v->user_id,
                        'srcmediafile_id' => $mf->id,
                        'dstmediafile_id' => null, // set when approved by reciever
                        'dstvaultfolder_id' => $vaultfolder->id,
                        'mfsl_status' => MediafilesharelogStatusEnum::PENDING,
                        'is_approved' => false,
                        'cattrs' => [
                            'notes' => '', // %TODO
                        ],
                    ]);
                });

                $v->user->notify( new VaultfileShareSent($vaultfolder, $request->user()) );

            }); // DB::transaction()

        });

        return response()->json([
            'vaultfolder_ids' => $vaultfolderIds,
        ], 201);
    }

    public function approveShare(Request $request, Vaultfolder $vaultfolder)
    {
        // $vaultfolder is the dst of the share...here we populate this folder by creating
        // references to the mediafiles (src) being shared...

        // %TODO: add transaction

        // create mediafile references in dst vaultfolder
        $dstMediafileIds = [];
        $vaultfolder->mediafilesharelogs->each( function($mfsl) use(&$vaultfolder, &$dstMediafileIds) {
            $srcMediafile = $mfsl->srcmediafile;
            if ( !$srcMediafile ) {
                Log::warning( 'vaultfolders.approveShare - could not find srcMediafile: '.json_encode($mfsl) );
                return false;
            }
            $dstMf = $srcMediafile->diskmediafile->createReference(
                'vaultfolders',            // string   $resourceType
                $vaultfolder->id,          // int      $resourceID
                $srcMediafile->mfname,     // string   $mfname
                MediafileTypeEnum::VAULT   // string   $mftype
            );
            $mfsl->mfsl_status = MediafilesharelogStatusEnum::APPROVED;
            $mfsl->dstmediafile_id = $dstMf->id;
            $mfsl->save();
            $dstMediafileIds[] = $dstMf->id;
        });

        $vaultfolder->is_pending_approval = 0;
        $vaultfolder->save();

        return response()->json([
            'vaultfolder' => $vaultfolder,
            'mediafile_ids' => $dstMediafileIds, // dst created post approval
        ], 200);
    }

    public function declineShare(Request $request, Vaultfolder $vaultfolder)
    {
        $srcMediafileIds = [];
        $vaultfolder->mediafilesharelogs->each( function($mfsl) use(&$vaultfolder, &$srcMediafileIds) {
            $srcMediafileIds[] = $srcMediafile->id;
            $mfsl->mfsl_status = MediafilesharelogStatusEnum::DECLINED;
            $mfsl->save();
        });
        $vaultfolder->delete(); // should do soft delete (NOTE we keep the reference [mediafilesharelogs].dstvaultfolder_id ?)
        return response()->json([
            'mediafile_ids' => $srcMediafileIds, // src being declined
        ], 200);
    }

    public function update(Request $request, Vaultfolder $vaultfolder)
    {
        $this->authorize('update', $vaultfolder);

        $vrules = [
            'vfname' => 'required|sometimes|string',
        ];

        $attrs = [];
        if ( $request->has('parent_id') && !is_null($request->parent_id) ) {
            $vrules['parent_id'] = 'required|integer|min:1'; // add validation rule
            $attrs['parent_id'] = $request->parent_id;
        }
        $request->validate($vrules);
        $attrs['vfname'] = $request->vfname;

        $vaultfolder->fill($attrs);
        $vaultfolder->save();

        return response()->json([
            'vaultfolder' => $vaultfolder,
        ]);
    }

    public function destroy(Request $request, Vaultfolder $vaultfolder)
    {
        $this->authorize('delete', $vaultfolder);
        if ( $vaultfolder->isRootFolder() ) {
            abort(400);
        }

        $numberOfItemsDeleted = $vaultfolder->recursiveDelete();
        $vaultfolder->delete();
        $numberOfItemsDeleted += 1; // for top vaultfolder

        return response()->json([
            'number_of_items_deleted' => $numberOfItemsDeleted,
        ], 200);
    }

    // ---

    public function share(Request $request, Vaultfolder $vaultfolder)
    {
        $vrules = [
            'shareables' => 'array', // things being shared
            'sharees' => 'required|array', // sharee: registered user
            'sharees.*.id' => 'required|exists:users',
        ];

        $this->authorize('update', $vaultfolder);
        $sharees = collect();

        $shareeIDs = $request->input('sharees', []);
        foreach ( $shareeIDs as $_a ) {
            $_sharee = User::find($_a['id']);
            if ($_sharee) {
                $vaultfolder->sharees()->attach($_sharee->id);  
                Mail::to($_sharee->email)->queue( new VaultfolderShared($_sharee) );
                $sharees->push($_sharee);
            } else {
                Log::warning('Could not find user with id '.$_a['id'].' to share vaultfolder');
            }
        }
        return response()->json([
            'sharees' => $sharees,
        ]);
    }

    /**
     * Finds or creates and returns the uploads folder for a requested type
     *
     * @param Request $request
     * @return Response
     */
    public function uploadsFolder(Request $request, string $type)
    {
        if ($request->user()->isAdmin() && $request->has('user_id')) {
            $user = User::find($request->user_id);
        } else {
            $user = $request->user();
        }

        $vaultFolder = Vault::primary($user)->first()->getRootFolder()
            ->vfchildren()->where('cattrs->storageFor', $type)
            ->first();

        if (!isset($vaultFolder)) {
            $vault = Vault::primary($user)->first();
            $this->authorize('update', $vault);

            $folderName = Str::ucfirst($type) . ' Uploads';

            $vaultFolder = VaultFolder::create([
                'user_id' => $user->getKey(),
                'parent_id' => $vault->getRootFolder()->getKey(),
                'vault_id' => $vault->getKey(),
                'vfname' => $folderName,
                'cattrs' => [ 'storageFor' => $type ],
            ]);
        }

        return $vaultFolder;
    }

}

//$request->user()->sharedvaultfolders()->syncWithoutDetaching($vaultfolder->id); // do share %TODO: need to do when they register (!)

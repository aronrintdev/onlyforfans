<?php
namespace App\Models;

use Exception;
use Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use App\Interfaces\Ownable;
use App\Interfaces\Guidable;
use App\Models\Traits\UsesUuid;
use App\Traits\OwnableFunctions;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\Services\SlugService;
use App\Models\Traits\SluggableTraits;
use App\Interfaces\Contenttaggable;
use App\Models\Traits\ContenttaggableTraits;

class Vaultfolder extends BaseModel implements Guidable, Ownable, Contenttaggable
{
    use UsesUuid, SluggableTraits, HasFactory, OwnableFunctions, Sluggable, SoftDeletes, ContenttaggableTraits;

    protected $table = 'vaultfolders';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public static $vrules = [];

    protected $appends = [
        'name',
        //'vfparent',
        //'vfchildren',
        //'mediafiles',
        'mediafile_count',
        'vfchildren_count',
    ];

    //--------------------------------------------
    // Boot
    //--------------------------------------------
    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            // subfolder unique name check: use parent_id & user_id
            $slug = SlugService::createSlug(Vaultfolder::class, 'slug', $model->vfname);
            $exists = Vaultfolder::where('user_id', $model->user_id)
                ->where('parent_id', $model->parent_id)
                ->where('vfname', $model->vfname)
                //->where('slug', $slug) // can't use slug for this check as it's uniquified alrady
                ->first();
            if ($exists) {
                throw ValidationException::withMessages(['vfname' => 'A folder with this name already exists, please choose a different name']);
            }
        });
    }

    //--------------------------------------------
    // %%% Relationships
    //--------------------------------------------

    public function vault() {
        return $this->belongsTo('App\Models\Vault');
    }

    public function mediafiles() {
        return $this->morphMany(Mediafile::class, 'resource')->orderByDesc('created_at');
        // %FIXME %PSG 20210915: the orderBy here is a bit of a hack, as a vaultfolder receiveds its files
        // tacked on as a relation to vaultfolders.show api call...instead we should
        // invoke a separate call to mediafiles.index or equvalent for the vaultfolder
    }

    public function vfparent() {
        return $this->belongsTo(Vaultfolder::class, 'parent_id');
    }

    public function vfchildren() {
        return $this->hasMany(Vaultfolder::class, 'parent_id');
    }

    public function sharees() {
        return $this->morphToMany(User::class, 'shareable', 'shareables', 'shareable_id', 'sharee_id');
    }

    // List of mediafilesharelogs associated with this vaultfolder as a *destiation* of a share action 
    //  ~ each share action creates its own new vaultfolder
    //  ~ if this vaultfolder was *not* created as a result of share, then this is NULL or empty collection
    public function mediafilesharelogs() { // %FIXME: suffix with AsDst, like in Mediafile model
        return $this->hasMany(Mediafilesharelog::class, 'dstvaultfolder_id');
    }

    public function isSharePlaceholderFolder() { // this vaultfolder was created as a *result* (dst) of a share
        return $this->mediafilesharelogs->count();
    }

    public function contenttags() {
        return $this->morphToMany(Contenttag::class, 'contenttaggable')->withTimestamps();
    }

    //--------------------------------------------
    // %%% Accessors/Mutators
    //--------------------------------------------

    protected $casts = [
        'cattrs' => 'array',
        'meta' => 'array',
    ];

    public function getBreadcrumb(): array
    {
        $MAX_DEPTH = 15;
        $iter = 0;

        $breadcrumb = [];
        $vf = Vaultfolder::find($this->id);
        while (!empty($vf)) {
            if ($iter++ > $MAX_DEPTH) {
                throw new Exception('Exceeded max sub-folder depth');
            }
            array_unshift($breadcrumb, [
                'pkid' => $vf->id,
                'vfname' => $vf->vfname,
                'slug' => $vf->slug,
                'is_root' => $vf->parent_id == null,
            ]);
            $vf = !empty($vf->parent_id) ? Vaultfolder::find($vf->parent_id) : null;
        }
        return $breadcrumb;
    }

    public function getNameAttribute($value) { return $this->vfname;
    }

    public function getPathAttribute($value) {
        return $this->vfname; // %TODO: get full path back to root
    }

    public function getMediafileCountAttribute($value) {
        return $this->mediafiles->count();
    }

    public function getVfchildrenCountAttribute($value) {
        return $this->vfchildren->count();
    }

    //--------------------------------------------
    // Scopes
    //--------------------------------------------

    public function scopeIsRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeIsChildOf($query, Vaultfolder $vaultfolder)
    {
        return $query->where('parent_id', $vaultfolder->id);
    }

    //--------------------------------------------
    // Methods
    //--------------------------------------------

    // %%% --- Implement Sluggable Interface ---

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => ['name'],
                /*
                'method' => static function(string $string, string $separator): string {
                    return strtolower(preg_replace('/[^a-z]+/i', $separator, $string));
                },
                 */
            ]];

    }

    // %%% --- Overrides in Model Traits (via BaseModel) ---

    public static function _renderFieldKey(string $key): string
    {
        $key = trim($key);
        switch ($key) {
        default:
        $key =  parent::_renderFieldKey($key);
        }
        return $key;
    }

    public function renderField(string $field): ?string
    {
        $key = trim($field);
        switch ($key) {
        default:
            return parent::renderField($field);
        }
    }

    // %%% --- Nameable Interface Overrides (via BaseModel) ---

    public function renderName(): string
    {
        return $this->name;
    }

    // %%% --- Other ---

    public function isRootFolder(): bool
    {
        return empty($this->parent_id);
    }

    public function getSubTree() : ?array
    {
        $subTree = [
            'id' => $this->id,
            'name' => $this->vfname,
            'slug' => $this->slug,
            'parent_id' => $this->parent_id,
            'children' => [],
        ];
        $children = Vaultfolder::where('parent_id', $this->id)->get(); // subfolders
        $children->each( function($vf) use(&$subTree) {
            $subTree['children'][] = $vf->getSubTree();
        });
        return $subTree;
    }

    public function getOwner(): ?Collection
    {
        return $this->vault->getOwner();
    }

    public function getPrimaryOwner(): ?User
    {
        $owners =  $this->getOwner();
        return ( $owners && ($owners->count()>0) ) ? $owners[0] : null;
    }

    public function recursiveDelete()
    {
        // for debug...
        static $level = 0;
        $level += 1; 
        if ($level > 10) {
            //dd("DEBUG recursiveDelete() - max levels reached ".$level);
            throw new \Exception("DEBUG recursiveDelete() - max levels reached ".$level);
        }

        static $numberOfItemsDeleted = 0;

        $sessionUser = Auth::user();

        // Invoke on all sub-folders (recursive)
        $this->vfchildren->each ( function($vfc) use(&$numberOfItemsDeleted, &$sessionUser) {
            $numberOfItemsDeleted += $vfc->recursiveDelete();

            // Clean up the logs..
            $mfShareLogs = Mediafilesharelog::where('dstvaultfolder_id', $vfc->id)->get();
            $mfShareLogs->each( function($mfsl) use(&$sessionUser) {
                $meta = $mfsl->meta;
                if ( !array_key_exists('logs', $meta??[]) ) {
                    $meta['logs'] = []; // init
                }
                $meta['logs'][] = 'Dst vaultfile '.$mfsl->dstvaultfile_id.' soft deleted by user '.$sessionUser->id;
                $mfsl->meta = $meta; 
                // assumes soft vaultfile delete, otherwise we need to set FKID to NULL
                $mfsl->save();
            });

            $vfc->delete();
            $numberOfItemsDeleted += 1; // for vfc
        });

        // Delete all files in this folder
        $this->mediafiles->each( function($mf) use(&$numberOfItemsDeleted) {
            try {
                $mf->diskmediafile->deleteReference($mf->id, false);
                $numberOfItemsDeleted += 1;
            } catch (Exception $e) {
                Log::warning('vaultfolder.model - Could not delete mediafile with pkid = '.$mf->id.' in vaultfolder = '.$this->id. ' : '.$e->getMessage());
            }
        });

        return $numberOfItemsDeleted;
    }

}

<?php
namespace App\Models;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Interfaces\Ownable;
use App\Interfaces\Guidable;
use App\Models\Traits\UsesUuid;
use App\Traits\OwnableFunctions;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\Services\SlugService;
use App\Models\Traits\SluggableTraits;

class Vaultfolder extends BaseModel implements Guidable, Ownable
{
    use UsesUuid, SluggableTraits, HasFactory, OwnableFunctions, Sluggable, SoftDeletes;

    protected $table = 'vaultfolders';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public static $vrules = [];

    protected $appends = [
        'name',
        //'vfparent',
        //'vfchildren',
        //'mediafiles',
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

    public function vault()
    {
        return $this->belongsTo('App\Models\Vault');
    }

    public function mediafiles()
    {
        return $this->morphMany(Mediafile::class, 'resource');
    }

    public function vfparent()
    {
        return $this->belongsTo(Vaultfolder::class, 'parent_id');
    }

    public function vfchildren()
    {
        return $this->hasMany(Vaultfolder::class, 'parent_id');
    }

    public function sharees()
    {
        return $this->morphToMany(User::class, 'shareable', 'shareables', 'shareable_id', 'sharee_id');
    }

    public function getOwner(): ?Collection
    {
        return $this->vault->getOwner();
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
            ]);
            $vf = !empty($vf->parent_id) ? Vaultfolder::find($vf->parent_id) : null;
        }
        return $breadcrumb;
    }

    public function getNameAttribute($value)
    {
        return $this->vfname;
    }

    public function getPathAttribute($value)
    {
        return $this->vfname; // %TODO: get full path back to root
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
}

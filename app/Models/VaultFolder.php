<?php

namespace App\Models;

use Exception;
use App\SluggableTraits;
use App\Interfaces\Ownable;
use App\Interfaces\Guidable;
use App\Interfaces\ShortUuid;
use App\Interfaces\Sluggable;
use App\Models\Traits\UsesShortUuid;
use App\Models\Traits\UsesUuid;
use App\Traits\OwnableFunctions;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VaultFolder extends BaseModel implements Guidable, Sluggable, Ownable, ShortUuid
{
    use UsesUuid;
    use UsesShortUuid;
    use SluggableTraits;
    use HasFactory;
    use OwnableFunctions;

    protected $table = 'vault_folders';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public static $vrules = [];

    protected $appends = [
        'name',
        //'vfparent',
        //'vfchildren',
        //'mediafiles',
    ];

    //--------------------------------------------
    // %%% Relationships
    //--------------------------------------------

    public function vault()
    {
        return $this->belongsTo('App\Models\Vault');
    }

    public function mediaFiles()
    {
        return $this->morphMany('App\Models\MediaFile', 'resource');
    }

    public function parent()
    {
        return $this->belongsTo('App\Models\VaultFolder', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany('App\Models\VaultFolder', 'parent_id');
    }

    public function sharees()
    {
        return $this->morphToMany('App\Models\User', 'shareable', 'shareables', 'shareable_id', 'shared_with');
    }

    public function getOwner(): ?Collection
    {
        return $this->vault->getOwner();
    }

    //--------------------------------------------
    // %%% Accessors/Mutators
    //--------------------------------------------

    protected $casts = [
        'custom_attributes' => 'array',
        'metadata' => 'array',
    ];

    public function getBreadcrumb(): array
    {
        $MAX_DEPTH = 15;
        $iter = 0;

        $breadcrumb = [];
        $vf = VaultFolder::find($this->id);
        while (!empty($vf)) {
            if ($iter++ > $MAX_DEPTH) {
                throw new Exception('Exceeded max sub-folder depth');
            }
            array_unshift($breadcrumb, [
                'pkid' => $vf->id,
                'name' => $vf->name,
                'slug' => $vf->slug,
            ]);
            $vf = !empty($vf->parent_id) ? VaultFolder::find($vf->parent_id) : null;
        }
        return $breadcrumb;
    }

    public function getNameAttribute($value)
    {
        return $this->name;
    }

    public function getPathAttribute($value)
    {
        return $this->name; // %TODO: get full path back to root
    }

    //--------------------------------------------
    // Scopes
    //--------------------------------------------

    public function scopeIsRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeIsChildOf($query, VaultFolder $vaultFolder)
    {
        return $query->where('parent_id', $vaultFolder->id);
    }

    //--------------------------------------------
    // Methods
    //--------------------------------------------

    // %%% --- Implement Sluggable Interface ---

    public function sluggableFields(): array
    {
        return ['name'];
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
}

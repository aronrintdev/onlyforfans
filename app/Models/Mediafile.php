<?php
namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Interfaces\Ownable;
use App\Interfaces\Guidable;
use App\Interfaces\Cloneable;
use App\Models\Traits\UsesUuid;
use App\Enums\MediafileTypeEnum;
use App\Models\Traits\SluggableTraits;
use App\Traits\OwnableFunctions;
use Cviebrock\EloquentSluggable\Sluggable;
use Intervention\Image\Facades\Image;

class Mediafile extends BaseModel implements Guidable, Ownable, Cloneable
{
    use UsesUuid, SoftDeletes, HasFactory, OwnableFunctions, Sluggable, SluggableTraits;

    protected $guarded = [ 'id', 'created_at', 'updated_at' ];
    protected $appends = ['filepath', 'name', 'is_image', 'is_video'];
    public static $vrules = [];

    //--------------------------------------------
    // %%% Relationships
    //--------------------------------------------

    public function resource()
    {
        return $this->morphTo();
    }

    public function sharees()
    {
        return $this->morphToMany(User::class, 'shareable', 'shareables', 'shareable_id', 'sharee_id');
    }

    public function diskmediafile()
    {
        return $this->belongsTo(Diskmediafile::class);
    }

    //--------------------------------------------
    // %%% Accessors/Mutators | Casts
    //--------------------------------------------

    protected $casts = [
        'cattrs'    => 'array',
        'meta'      => 'array',
    ];

    public function getIsImageAttribute($value)
    {
        return $this->diskmediafile->isImage();
    }

    public function getIsVideoAttribute($value)
    {
        return $this->diskmediafile->isVideo();
    }

    public function getGuidAttribute($value)
    {
        return $this->id;
    }

    // %FIXME: this should be consistent with getMidFilename, etc (ie not orig filename)
    public function getNameAttribute($value) {
        return $this->diskmediafile->orig_filename;
    }

    public function getMidFilenameAttribute($value) {
        $subfolder = $this->diskmediafile->owner_id;
        return $subfolder.'/mid/'.$this->basename.'.jpg';
    }

    public function getThumbFilenameAttribute($value) {
        $subfolder = $this->diskmediafile->owner_id;
        return $subfolder.'/thumb/'.$this->basename.'.jpg';
    }

    public function getBlurFilenameAttribute($value) {
        $subfolder = $this->diskmediafile->owner_id;
        return $subfolder.'/blur/'.$this->basename.'.jpg';
    }

    public function getFilepathAttribute($value) {
        return !empty($this->diskmediafile->filename) ? Storage::disk('s3')->url($this->diskmediafile->filename) : null;
        //return !empty($this->filename) ? Storage::disk('s3')->temporaryUrl( $this->filename, now()->addMinutes(5) ) : null;
    }

    public function getMidFilepathAttribute($value) {
        $subfolder = $this->diskmediafile->owner_id;
        $path = $subfolder.'/mid/'.$this->diskmediafile->basename.'.jpg';
        return !empty($path) ? Storage::disk('s3')->url($path) : null;
    }

    public function getThumbFilepathAttribute($value) {
        $subfolder = $this->diskmediafile->owner_id;
        $path = $subfolder.'/thumb/'.$this->diskmediafile->basename.'.jpg';
        return !empty($path) ? Storage::disk('s3')->url($path) : null;
    }

    public function getBlurFilepathAttribute($value) {
        $subfolder = $this->diskmediafile->owner_id;
        $path = $subfolder.'/blur/'.$this->diskmediafile->basename.'.jpg';
        return !empty($path) ? Storage::disk('s3')->url($path) : null;
    }

    //--------------------------------------------
    // %%% Scopes
    //--------------------------------------------

    //--------------------------------------------
    // %%% Methods
    //--------------------------------------------

    // %%% --- Implement Ownable Interface ---

    public function isOwner(User $user): bool {
       // return true;
        switch ($this->resource_type) {
        case 'users':
            //dd('here 0422', $this->resource);
            return $this->resource_id === $user->id;
        default:
            return $this->resource ? $this->resource->isOwner($user) : false;
        }
    }

    public function getOwner(): ?Collection
    {
        //dd('Mediafile', $this->resource->getOwner()); // , $this->toArray());
        switch ($this->resource_type) {
        case 'users':
            //dd('here 0422', $this->resource);
            return new Collection($this->resource->without(['avatar','cover'])); // owner is the user 
        default:
            return $this->resource ? $this->resource->getOwner() : null;
        }
    }

    // %%% --- Implement Sluggable Interface ---

    public function sluggable(): array
    {
        return ['slug' => [
            'source' => ['mfname'],
        ]];
    }

    // %%% --- Overrides in Model Traits (via BaseModel) ---

    public static function _renderFieldKey(string $key): string
    {
        $key = trim($key);
        switch ($key) {
            case 'mftype':
                $key = 'Media File Type';
                break;
            default:
                $key =  parent::_renderFieldKey($key);
        }
        return $key;
    }

    public function renderField(string $field): ?string
    {
        $key = trim($field);
        switch ($key) {
            case 'meta':
            case 'cattrs':
                return json_encode($this->{$key});
            case 'mftype':
                return empty($this->mftype) ? 'N/A' : MediafileTypeEnum::render($this->mftype);
            default:
                return parent::renderField($field);
        }
    }

    // %%% --- Nameable Interface Overrides (via BaseModel) ---

    public function renderName(): string
    {
        return $this->mfname;
    }

    // %%% --- Other ---

    //  Shallow clone: copies/pastes the DB record, not the asset/file
    //  ~ cloning only allowed if new copy is associated with another resource (eg post)
    //  ~ see: https://trello.com/c/0fBcmPjq
    public function doClone(string $resourceType, string $resourceId): ?Model
    {
        $cloned = $this->replicate()->fill([
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
        ]);
        $cloned->save();
        return $cloned;
    }

}

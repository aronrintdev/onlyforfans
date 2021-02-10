<?php

namespace App\Models;

use App\SluggableTraits;
use App\Interfaces\Ownable;
use App\Interfaces\Guidable;
use App\Interfaces\Sluggable;
use App\Enums\MediaFileTypeEnum;
use App\Interfaces\ShortUuid;
use App\Models\Traits\UsesShortUuid;
use App\Models\Traits\UsesUuid;
use App\Traits\OwnableFunctions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MediaFile extends BaseModel implements Guidable, Sluggable, Ownable, ShortUuid
{
    use UsesUuid;
    use UsesShortUuid;
    use SluggableTraits;
    use HasFactory;
    use OwnableFunctions;

    protected $table = 'media_files';
    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];
    protected $appends = ['filepath', 'name'];

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
        return $this->morphToMany('App\User', 'shareable', 'shareables', 'shareable_id', 'shared_with');
    }

    public function getOwner(): ?Collection
    {
        return $this->resource->getOwner();
    }

    //--------------------------------------------
    // %%% Accessors/Mutators | Casts
    //--------------------------------------------

    protected $casts = [
        'custom_attributes' => 'array',
        'metadata'          => 'array',
    ];

    public function getGuidAttribute($value)
    {
        return $this->id;
    }

    public function getFilepathAttribute($value)
    {
        return !empty($this->filename) ? Storage::disk('s3')->url($this->filename) : null;
        //return !empty($this->filename) ? Storage::disk('s3')->temporaryUrl( $this->filename, now()->addMinutes(5) ) : null;
    }

    public function getNameAttribute($value)
    {
        return $this->orig_filename;
    }

    //--------------------------------------------
    // %%% Methods
    //--------------------------------------------

    // %%% --- Implement Sluggable Interface ---

    public function sluggableFields(): array
    {
        return [ 'orig_filename' ];
    }

    // %%% --- Overrides in Model Traits (via BaseModel) ---

    public static function _renderFieldKey(string $key): string
    {
        $key = trim($key);
        switch ($key) {
            case 'type':
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
            case 'metadata':
            case 'custom_attributes':
                return json_encode($this->{$key});
            case 'type':
                return empty($this->type) ? 'N/A' : MediaFileTypeEnum::render($this->type);
            default:
                return parent::renderField($field);
        }
    }

    // %%% --- Nameable Interface Overrides (via BaseModel) ---

    public function renderName(): string
    {
        return $this->orig_filename;
    }

    // %%% --- Other ---

    public function isImage(): bool
    {
        switch ($this->mimetype) {
            case 'image/jpeg':
            case 'image/png':
            case 'image/gif':
                return true;
        }
        return false;
    }
    public function isVideo(): bool
    {
        switch ($this->mimetype) {
            case 'video/mp4':
            case 'video/x-flv':
            case 'video/quicktime':
            case 'video/x-ms-wmv':
                return true;
        }
        return false;
    }
    public function isAudio(): bool
    {
        switch ($this->mimetype) {
            case 'audio/mpeg':
            case 'audio/mp4':
            case 'audio/ogg':
            case 'audio/vnd.wav':
                return true;
        }
        return false;
    }
}

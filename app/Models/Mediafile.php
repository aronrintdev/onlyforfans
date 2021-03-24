<?php

namespace App\Models;

use App\Interfaces\Ownable;
use App\Interfaces\Guidable;
use App\Interfaces\Cloneable;
use App\Models\Traits\UsesUuid;
use App\Enums\MediafileTypeEnum;
use App\Models\Traits\SluggableTraits;
use App\Traits\OwnableFunctions;
use Cviebrock\EloquentSluggable\Sluggable;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Mediafile extends BaseModel implements Guidable, Ownable, Cloneable
{
    use UsesUuid, HasFactory, OwnableFunctions, Sluggable, SluggableTraits;

    protected $table = 'mediafiles';
    protected $guarded = [ 'id', 'created_at', 'updated_at' ];
    protected $appends = ['filepath', 'name', 'is_image', 'is_video'];
    public static $vrules = [];

    //--------------------------------------------
    // Boot
    //--------------------------------------------
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $parsedbase = parse_filebase($model->filepath);
            if ( $parsedbase ) {
                $model->basename = $parsedbase;
            }
        });

        static::deleting(function ($model) {
            // %TODO: move to post-processing script (?)
            Storage::disk('s3')->delete($model->filename); // Remove from S3
            Storage::disk('s3')->delete($model->midFilepath);
            Storage::disk('s3')->delete($model->thumbFilepath);
        });
    }

    //--------------------------------------------
    // %%% Relationships
    //--------------------------------------------

    public function resource()
    {
        return $this->morphTo();
    }

    public function sharees()
    {
        return $this->morphToMany('App\Models\User', 'shareable', 'shareables', 'shareable_id', 'sharee_id');
    }

    //--------------------------------------------
    // %%% Accessors/Mutators | Casts
    //--------------------------------------------

    protected $casts = [
        'cattrs' => 'array',
        'meta'   => 'array',
    ];

    public function getIsImageAttribute($value)
    {
        return $this->isImage();
    }

    public function getIsVideoAttribute($value)
    {
        return $this->isVideo();
    }

    public function getGuidAttribute($value)
    {
        return $this->id;
    }

    // %FIXME: this should be consistent with getMidFilename, etc (ie not orig filename)
    public function getNameAttribute($value) {
        return $this->orig_filename;
    }

    public function getMidFilenameAttribute($value) {
        $subfolder = MediafileTypeEnum::getSubfolder($this->mftype);
        return $subfolder.'/mid/'.$this->basename.'.jpg';
    }

    public function getThumbFilenameAttribute($value) {
        $subfolder = MediafileTypeEnum::getSubfolder($this->mftype);
        return $subfolder.'/thumb/'.$this->basename.'.jpg';
    }

    public function getFilepathAttribute($value) {
        return !empty($this->filename) ? Storage::disk('s3')->url($this->filename) : null;
        //return !empty($this->filename) ? Storage::disk('s3')->temporaryUrl( $this->filename, now()->addMinutes(5) ) : null;
    }

    public function getMidFilepathAttribute($value) {
        $subfolder = MediafileTypeEnum::getSubfolder($this->mftype);
        $path = $subfolder.'/mid/'.$this->basename.'.jpg';
        return !empty($path) ? Storage::disk('s3')->url($path) : null;
    }

    public function getThumbFilepathAttribute($value) {
        $subfolder = MediafileTypeEnum::getSubfolder($this->mftype);
        $path = $subfolder.'/thumb/'.$this->basename.'.jpg';
        return !empty($path) ? Storage::disk('s3')->url($path) : null;
    }

    //--------------------------------------------
    // %%% Methods
    //--------------------------------------------

    // %%% --- Implement Ownable Interface ---

    public function getOwner(): ?Collection
    {
        return $this->resource->getOwner();
    }

    // %%% --- Implement Sluggable Interface ---

    public function sluggable(): array
    {
        return ['slug' => [
            'source' => ['orig_filename'],
        ]];
    }

    public function sluggableFields(): array
    {
        return [ 'orig_filename' ];
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
        return $this->orig_filename;
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

    public function isImage(): bool
    {
        switch ( strtolower($this->mimetype) ) {
            case 'image/jpeg':
            case 'image/png':
            case 'image/gif':
                return true;
        }
        return false;
    }
    public function isVideo(): bool
    {
        switch ( strtolower($this->mimetype) ) {
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
        switch ( strtolower($this->mimetype) ) {
            case 'audio/mpeg':
            case 'audio/mp4':
            case 'audio/ogg':
            case 'audio/vnd.wav':
                return true;
        }
        return false;
    }

    // set width to number and height to null to scale existing
    public function createThumbnail()
    {
        $WIDTH = 320;
        $url = Storage::disk('s3')->temporaryUrl( $this->filename, now()->addMinutes(10) );
        $subFolder = MediafileTypeEnum::getSubfolder($this->mftype);
        $img = Image::make($url);
        $s3Path = "$subFolder/thumb/".$this->basename.".jpg";
        $img->widen($WIDTH)->encode('jpg', 90);
        $contents = $img->stream();
        Storage::disk('s3')->put($s3Path, $contents); //$contents = file_get_contents($json->file);
        $this->has_thumb = true;
        $this->save();
    }

    public function createMid()
    {
        $WIDTH = 1280;
        $url = Storage::disk('s3')->temporaryUrl( $this->filename, now()->addMinutes(10) );
        $subFolder = MediafileTypeEnum::getSubfolder($this->mftype);
        $img = Image::make($url);
        $s3Path = "$subFolder/mid/".$this->basename.".jpg";
        $img->widen($WIDTH)->encode('jpg', 90);
        $contents = $img->stream();
        Storage::disk('s3')->put($s3Path, $contents);
        $this->has_mid = true;
        $this->save();
    }

    public function createBlur()
    {
        $WIDTH = 1280;
        $url = Storage::disk('s3')->temporaryUrl( $this->filename, now()->addMinutes(10) );
        $subFolder = MediafileTypeEnum::getSubfolder($this->mftype);
        $img = Image::make($url);
        $s3Path = "$subFolder/blur/".$this->basename.".jpg";
        $img->widen($WIDTH)->encode('jpg', 90);
        $contents = $img->stream();
        Storage::disk('s3')->put($s3Path, $contents);
        $this->has_blur = true;
        $this->save();
    }
}

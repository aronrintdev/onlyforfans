<?php
namespace App\Models;

use DB;
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

//class DiskMediafile extends BaseModel implements Guidable, Ownable, Cloneable
class DiskMediafile extends BaseModel implements Guidable, Ownable
{
    use UsesUuid, SoftDeletes, HasFactory, OwnableFunctions, Sluggable, SluggableTraits;

    protected $guarded = [ 'id', 'created_at', 'updated_at' ];
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
    }

    //--------------------------------------------
    // %%% Relationships
    //--------------------------------------------

    // The primary owner of the mediafile/content
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function mediafiles()
    {
        return $this->hasMany(Mediafile::class);
    }

    //--------------------------------------------
    // %%% Accessors/Mutators | Casts
    //--------------------------------------------

    protected $casts = [
        'cattrs'    => 'array',
        'meta'      => 'array',
        'has_thumb' => 'bool',
        'has_mid'   => 'bool',
        'has_blur'  => 'bool',
    ];

    //--------------------------------------------
    // %%% Scopes
    //--------------------------------------------

    // %DRY %FIXME: see attribute and appends
    public function scopeIsImage($query)
    {
        return $query->whereIn('mimetype', ['image/jpeg', 'image/jpg', 'image/png']);
    }

    public function scopeIsVideo($query)
    {
        return $query->whereIn('mimetype', ['video/mp4', 'video/mpeg', 'video/ogg', 'video/quicktime']);
    }


    //--------------------------------------------
    // %%% Methods
    //--------------------------------------------

    // %%% --- Implement Ownable Interface ---

    public function isOwner(User $user): bool {
        return $this->owner_id === $user->id;
    }

    public function getOwner(): ?Collection
    {
        return new Collection( $this->owner->without(['avatar','cover']) );
    }

    public function getPrimaryOwner(): User
    {
        return $this->owner;
    }

    // %%% --- Implement Sluggable Interface ---

    public function sluggable(): array
    {
        return ['slug' => [
            'source' => ['orig_filename'],
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
            case 'meta':
            case 'cattrs':
                return json_encode($this->{$key});
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
            case 'video/x-m4v':
            case 'video/x-flv':
            case 'video/quicktime':
            case 'video/x-ms-wmv':
            case 'video/x-matroska':
            case 'video/ogg':
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

    // creates diskmediafile and associated mediafile reference
    public static function doCreate(
        $s3Filepath,
        $mfname, 
        $mftype, 
        $owner, 
        $resourceType=null, 
        $resourceID=null, 
        $mimetype=null, 
        $origFilename=null, 
        $origExt=null, 
        $cattrs=null, 
        $meta=null
    ) 
    {
        $mediafile = DB::transaction(function () use(
            &$mimetype, 
            &$owner, 
            $s3Filepath, 
            $origFilename, 
            $origExt,
            $mfname, 
            $mftype, 
            $resourceType, 
            $resourceID, 
            $cattrs, 
            $meta
        ) {
            $subFolder = $owner->id;
            //$s3Filename = $file->store($subFolder, 's3');
            $diskmediafile = Diskmediafile::create([
                'filename' => $s3Filepath,
                'mimetype' => $mimetype,
                'owner_id' => $owner->id,
                'orig_filename' => $origFilename,
                'orig_ext' => $origExt,
                'cattrs' => $cattrs,
                'meta' => $meta,
            ]);
            $mediafile = Mediafile::create([
                'diskmediafile_id' => $diskmediafile->id,
                'resource_id' => $resourceID,
                'resource_type' => $resourceType,
                'mfname' => $mfname ?? $file->getClientOriginalName(),
                'mftype' => $mftype,
                'cattrs' => $cattrs,
                'meta' => $meta,
            ]);
            return $mediafile;
        });
        return $mediafile;
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
        $WIDTH = 320;
        $BLUR_STRENGTH = 90; // 0 ~ 100 http://image.intervention.io/api/blur
        $url = Storage::disk('s3')->temporaryUrl( $this->filename, now()->addMinutes(10) );
        $subFolder = MediafileTypeEnum::getSubfolder($this->mftype);
        $img = Image::make($url);
        $s3Path = "$subFolder/blur/".$this->basename.".jpg";
        $img->widen($WIDTH)->blur($BLUR_STRENGTH)->encode('jpg', 90);
        $contents = $img->stream();
        Storage::disk('s3')->put($s3Path, $contents);
        $this->has_blur = true;
        $this->save();
    }

    // Deletes all images, videos, etc associated with the mediafile record
    //  ~ Typically there will only be a single video, but a video could have related images such as a preview
    //  ~ An image could have an associated thumb, blur, etc.
    //  ~ Should be called *before* a mediafile is hard-deleted
    public function deleteAssets()
    {
        if ( $this->has_thumb ) {
            Storage::disk('s3')->delete($this->thumbFilename);
            $this->has_thumb = false;
        }
        if ( $this->has_mid ) {
            Storage::disk('s3')->delete($this->midFilename);
            $this->has_mid = false;
        }
        if ( $this->has_blur ) {
            Storage::disk('s3')->delete($this->blurFilename);
            $this->has_blur = false;
        }
        Storage::disk('s3')->delete($this->filename);
        $this->save();
    }
}

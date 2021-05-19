<?php
namespace App\Models;

use DB;
use Auth;
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

//class Diskmediafile extends BaseModel implements Guidable, Ownable, Cloneable
class Diskmediafile extends BaseModel implements Guidable, Ownable
{
    use UsesUuid, SoftDeletes, HasFactory, OwnableFunctions, Sluggable, SluggableTraits;

    protected $guarded = [ 'id', 'created_at', 'updated_at' ];
    public static $vrules = [];

    static public $mimeImageTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
    ];
    static public $mimeVideoTypes = [
            'video/mp4',
            'video/x-m4v',
            'video/x-flv',
            'video/quicktime',
            'video/x-ms-wmv',
            'video/x-matroska',
            'video/ogg',
    ];
    static public $mimeAudioTypes = [
            'audio/mpeg',
            'audio/mp4',
            'audio/ogg',
            'audio/vnd.wav',
    ];

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

    public function getIsImageAttribute($value)
    {
        return in_array($this->mimetype, Diskmediafile::$mimeImageTypes);
    }

    public function getIsVideoAttribute($value)
    {
        return in_array($this->mimetype, Diskmediafile::$mimeVideoTypes);
    }

    public function getIsAudioAttribute($value)
    {
        return in_array($this->mimetype, Diskmediafile::$mimeAudioTypes);
    }

    //--------------------------------------------
    // %%% Scopes
    //--------------------------------------------

    // %DRY %FIXME: see attribute and appends
    public function scopeIsImage($query)
    {
        return $query->whereIn('mimetype', Diskmediafile::$mimeImageTypes);
    }

    public function scopeIsVideo($query)
    {
        return $query->whereIn('mimetype', Diskmediafile::$mimeVideoTypes);
    }

    public function scopeIsAudio($query)
    {
        return $query->whereIn('mimetype', Diskmediafile::$mimeAudioTypes);
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


    // creates diskmediafile and associated mediafile reference
    public static function doCreate(array $attrs) : Mediafile
    {
        $mediafile = DB::transaction( function () use(&$attrs)  {
            $diskmediafile = Diskmediafile::create([
                'filepath' => $attrs['filepath'],
                'mimetype' => $attrs['mimetype'],
                'owner_id' => $attrs['owner_id'],
                'orig_filename' => $attrs['orig_filename'],
                'orig_ext' => $attrs['orig_ext'],
            ]);
            $mediafile = Mediafile::create([
                'diskmediafile_id' => $diskmediafile->id,
                'is_primary' => true, // only place this should be true
                'resource_id' => $attrs['resource_id'],
                'resource_type' => $attrs['resource_type'],
                'mfname' => $attrs['mfname'],
                'mftype' => $attrs['mftype'],
                'cattrs' => $cattrs ?? null, // cattrs param will be applied to mediafile
                'meta' => $meta ?? null, // meta param will be applied to mediafile
            ]);
            return $mediafile;
        });
        return $mediafile; // %NOTE: returns the reference (!)
    }


    // Create a reference from this diskmediafile
    public function createReference(
        string   $resourceType,
        string   $resourceID,
        string   $mfname, 
        string   $mftype,
        array    $cattrs=null,
        array    $meta=null
    ) : ?Mediafile
    {
        $mediafile = Mediafile::create([
            'diskmediafile_id' => $this->id,
            'resource_id' => $resourceID,
            'resource_type' => $resourceType,
            'mfname' => $mfname,
            'mftype' => $mftype,
            'cattrs' => $cattrs,
            'meta' => $meta,
        ]);
        return $mediafile;
    }

    // Delete a reference to this diskmediafile
    // %Caller must check permissions/ownership !
    public function deleteReference($mediafileID, $deleteFromDiskIfLast=false)
    {
        DB::table('mediafiles')->where('id', $mediafileID)->delete();
        if  ( $deleteFromDiskIfLast ) {
            $this->deleteAssets(); // S3, etc
            Mediafile::withTrashed()->where('diskmediafile_id', $this->id)->forceDelete();
            //$this->delete();
            $this->forceDelete();
        }
    }

    // Deletes assets (S3), all references ([mediafiles] records), and finally the [diskmediafiles] record itself
    // USE WITH CAUTION!
    public function forceDeleteAll()
    {
        $this->deleteAssets(); // S3, etc
        Mediafile::withTrashed()->where('diskmediafile_id', $this->id)->forceDelete();
        $this->forceDelete();
    }

    // set width to number and height to null to scale existing
    public function createThumbnail()
    {
        $WIDTH = 320;
        $url = Storage::disk('s3')->temporaryUrl( $this->filepath, now()->addMinutes(10) );
        $img = Image::make($url);
        $subFolder = $this->owner_id;
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
        $url = Storage::disk('s3')->temporaryUrl( $this->filepath, now()->addMinutes(10) );
        $img = Image::make($url);
        $subFolder = $this->owner_id;
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
        $url = Storage::disk('s3')->temporaryUrl( $this->filepath, now()->addMinutes(10) );
        $img = Image::make($url);
        $subFolder = $this->owner_id;
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
    private function deleteAssets()
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
        Storage::disk('s3')->delete($this->filepath);
        $this->save();
    }
}

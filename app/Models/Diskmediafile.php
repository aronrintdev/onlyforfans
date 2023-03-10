<?php
namespace App\Models;

use DB;
use Auth;

use Exception;
use App\Interfaces\Ownable;
use App\Enums\ImageBlurEnum;
use App\Interfaces\Guidable;
use App\Interfaces\Cloneable;
use Illuminate\Support\Carbon;

use App\Models\Traits\UsesUuid;
use App\Enums\MediafileTypeEnum;

use App\Traits\OwnableFunctions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

use Intervention\Image\Facades\Image;

use App\Models\Traits\SluggableTraits;
use Illuminate\Support\Facades\Config;

use Illuminate\Support\Facades\Storage;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Dreamonkey\CloudFrontUrlSigner\Facades\CloudFrontUrlSigner;

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
        'video/webm',
    ];
    static public $mimeAudioTypes = [
        'audio/mpeg',
        'audio/mp4',
        'audio/ogg',
        'audio/vnd.wav',
        'audio/webm',
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

        static::created(function ($model) {
            if ( Storage::disk('s3')->exists($model->filepath) ) {
                $model->orig_size = Storage::disk('s3')->size($model->filepath);
                $model->save();
            }
        });
    }

    //--------------------------------------------
    // %%% Relationships
    //--------------------------------------------

    // The primary owner of the mediafile/content
    public function owner() {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function mediafiles() {
        return $this->hasMany(Mediafile::class);
    }

    // %NOTE: this is applied to the diskmediafile, not the mediafile (!)
    public function contentflags() {
        return $this->morphMany(Contentflag::class, 'flaggable');
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

    // public function flagCount() {
    //     return !empty($this->filepath) ? Storage::disk('s3')->url($this->filepath) : null; // %FIXME?: this doesn't look right
    // }

    public function renderUrl() {
        return !empty($this->filepath) ? $this->getCdnUrl() : null;
    }

    public function renderUrlBlur() {
        $subfolder = $this->owner_id;
        $path = $subfolder.'/blur/'.$this->basename.'.jpg';
        return !empty($path) ? Storage::disk('s3')->url($path) : null;
    }

    public function renderUrlMid() {
        $subfolder = $this->owner_id;
        $path = $subfolder.'/mid/'.$this->basename.'.jpg';
        return !empty($path) ? Storage::disk('s3')->url($path) : null;
    }

    public function renderUrlThumb() {
        $subfolder = $this->owner_id;
        $path = $subfolder.'/thumb/'.$this->basename.'.jpg';
        return !empty($path) ? Storage::disk('s3')->url($path) : null;
    }

    /**
     * Get s3 path from relative storage path
     */
    public function getCdnUrl($path = null)
    {
        if (!isset($path)) {
            $path = $this->filepath;
        }
        if (empty($path)) {
            return null;
        }

        try {
            if (Config::get('filesystems.useSigned', false)) {
                if (Config::get('filesystems.useSignedCloudfront', false)) {
                    return CloudFrontUrlSigner::sign(
                        Storage::disk('cdn')->url($path),
                        Carbon::now()->addMinutes(Config::get('filesystems.availabilityMinutes'))
                    );
                }
                return Storage::disk('cdn')->temporaryUrl(
                    $path,
                    Carbon::now()->addMinutes(Config::get('filesystems.availabilityMinutes'))
                );
            }
            return Storage::disk('cdn')->url($path);
        } catch (Exception $e) {
            Log::error("Issue generating mediafile url", [ 'exception' => $e]);
            return null;
        }
    }

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
        DB::transaction(function () use($mediafileID, $deleteFromDiskIfLast) { 

            $sessionUser = Auth::user();

            $mediafile = Mediafile::find($mediafileID);

            // Clean up the logs as src...
            $mfShareLogs = Mediafilesharelog::where('srcmediafile_id', $mediafile->id)->get();
            $mfShareLogs->each( function($mfsl) use(&$sessionUser) {
                $meta = $mfsl->meta;
                if ( !array_key_exists('logs', $meta??[]) ) {
                    $meta['logs'] = []; // init
                }
                $meta['logs'][] = 'Src mediafile '.$mfsl->srcmediafile_id.' soft deleted by user '.$sessionUser->id;
                $mfsl->meta = $meta; 
                // assumes soft mediafile delete, otherwise we need to set FKID to NULL
                $mfsl->save();
            });

            // Clean up the logs as dst...
            $mfShareLogs = Mediafilesharelog::where('dstmediafile_id', $mediafile->id)->get();
            $mfShareLogs->each( function($mfsl) use(&$sessionUser) {
                $meta = $mfsl->meta;
                if ( !array_key_exists('logs', $meta??[]) ) {
                    $meta['logs'] = []; // init
                }
                $meta['logs'][] = 'Dst mediafile '.$mfsl->dstmediafile_id.' soft deleted by user '.$sessionUser->id;
                $mfsl->meta = $meta;
                // assumes soft mediafile delete, otherwise we need to set FKID to NULL
                $mfsl->save();
            });

            $mediafile->delete();

            // ---

            $refCount = Mediafile::where('diskmediafile_id', $this->id)->count();
            if  ( $deleteFromDiskIfLast && ($refCount > 0) ) {
                $this->deleteAssets(); // S3, etc
                Mediafile::withTrashed()->where('diskmediafile_id', $this->id)->forceDelete();
                //$this->delete();
                $this->forceDelete();
            }
        });
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
        $WIDTH = Config::get('usersettings.image_thumbnail_width');
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
        $WIDTH = Config::get('usersettings.image_mid_width');
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

    public static function mapBlurSettingToStrength(string $setting) : int 
    {
        switch ($setting) {
            case 'strong':
                return 95;
            case 'light':
                return 55;
            case 'off':
                return 0;
            case 'medium':
            default:
                return 90;
        }
    }

    public function createBlur()
    {
        $WIDTH = Config::get('usersettings.image_thumbnail_width');
        $JPEG_QUALITY = Config::get('usersettings.image_jpeg_quality');

        $blurSetting = Config::get('usersettings.image_blur_setting');
        $BLUR_STRENGTH = Diskmediafile::mapBlurSettingToStrength($blurSetting);

        $url = Storage::disk('s3')->temporaryUrl( $this->filepath, now()->addMinutes(10) );
        $img = Image::make($url);
        $subFolder = $this->owner_id;
        $s3Path = "$subFolder/blur/".$this->basename.".jpg";
        $img->widen($WIDTH)->blur($BLUR_STRENGTH)->encode('jpg', $JPEG_QUALITY);
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

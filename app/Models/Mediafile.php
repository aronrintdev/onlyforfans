<?php
namespace App\Models;

use Exception;
use App\Interfaces\Ownable;
use App\Interfaces\Guidable;
use App\Interfaces\Messagable;
use Illuminate\Support\Carbon;
use App\Models\Traits\UsesUuid;
//use App\Interfaces\Cloneable;
use App\Enums\MediafileTypeEnum;
use App\Traits\OwnableFunctions;
use Illuminate\Support\Collection;
use App\Interfaces\Contenttaggable;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use App\Models\Traits\SluggableTraits;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Cviebrock\EloquentSluggable\Sluggable;
use App\Models\Traits\ContenttaggableTraits;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Http\Resources\Mediafile as ResourcesMediafile;
use Dreamonkey\CloudFrontUrlSigner\Facades\CloudFrontUrlSigner;

class Mediafile extends BaseModel implements Guidable, Ownable, Messagable, Contenttaggable
{
    use UsesUuid, SoftDeletes, HasFactory, OwnableFunctions, Sluggable, SluggableTraits, ContenttaggableTraits;

    protected $guarded = [ 'id', 'created_at', 'updated_at' ];
    protected $appends = ['filepath', 'name', 'mimetype', 'has_blur', 'is_image', 'is_audio', 'is_video'];
    public static $vrules = [];

    //--------------------------------------------
    // %%% Relationships
    //--------------------------------------------

    public function resource() {
        return $this->morphTo();
    }

    public function sharees() {
        return $this->morphToMany(User::class, 'shareable', 'shareables', 'shareable_id', 'sharee_id');
    }

    public function diskmediafile() {
        return $this->belongsTo(Diskmediafile::class);
    }

    public function mediafilesharelogsAsSrc() {
        return $this->hasMany(Mediafilesharelog::class, 'srcmediafile_id');
    }

    public function mediafilesharelogsAsDst() {
        return $this->hasMany(Mediafilesharelog::class, 'dstmediafile_id');
    }

    public function contentflags() {
        return $this->morphMany(Contentflag::class, 'flaggable');
    }

    public function contenttags() {
        return $this->morphToMany(Contenttag::class, 'contenttaggable')->withPivot('access_level')->withTimestamps();
    }

    //--------------------------------------------
    // %%% Accessors/Mutators | Casts
    //--------------------------------------------

    protected $casts = [
        'cattrs'    => 'array',
        'meta'      => 'array',
    ];

    public function getMimetypeAttribute($value)
    {
        return $this->diskmediafile->mimetype;
    }

    public function getHasBlurAttribute($value)
    {
        return $this->diskmediafile->has_blur;
    }

    public function getHasThumbAttribute($value)
    {
        return $this->diskmediafile->has_thumb;
    }

    public function getHasMidAttribute($value)
    {
        return $this->diskmediafile->has_mid;
    }

    public function getIsImageAttribute($value)
    {
        return $this->diskmediafile->is_image;
    }

    public function getIsVideoAttribute($value)
    {
        return $this->diskmediafile->is_video;
    }

    public function getIsAudioAttribute($value)
    {
        return $this->diskmediafile->is_audio;
    }

    public function getOrigSizeAttribute($value)
    {
        return $this->diskmediafile->orig_size;
    }

    public function getGuidAttribute($value)
    {
        return $this->id;
    }

    // %FIXME: this should be consistent with getMidFilename, etc (ie not orig filename)
    public function getNameAttribute($value) {
        //return $this->diskmediafile->orig_filename;
        return $this->mfname;
    }

    public function getMidFilenameAttribute($value) {
        $subfolder = $this->diskmediafile->owner_id;
        return $subfolder.'/mid/'.$this->basename.'.jpg'; // %NOTE: these will always be generated as jpg (JPEG)!
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
        return $this->getCdnUrl($this->diskmediafile->filepath);
    }

    public function getMidFilepathAttribute($value) {
        $subfolder = $this->diskmediafile->owner_id;
        $path = $subfolder.'/mid/'.$this->diskmediafile->basename.'.jpg';
        return $this->getCdnUrl($path);
    }

    public function getThumbFilepathAttribute($value) {
        $subfolder = $this->diskmediafile->owner_id;
        $path = $subfolder.'/thumb/'.$this->diskmediafile->basename.'.jpg';
        return $this->getCdnUrl($path);
    }

    public function getBlurFilepathAttribute($value) {
        $subfolder = $this->diskmediafile->owner_id;
        $path = $subfolder.'/blur/'.$this->diskmediafile->basename.'.jpg';
        return $this->getCdnUrl($path);
    }

    /**
     * Get s3 path from relative storage path
     */
    public function getCdnUrl($path = null) {
        if (!isset($path)) {
            $path = $this->diskmediafile->filepath;
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

    //--------------------------------------------
    // %%% Scopes
    //--------------------------------------------

    // %DRY %FIXME: see attribute and appends
    public function scopeIsImage($query)
    {
        return $query->whereHas('diskmediafile', function($q1) {
            //$q1->whereIn('mimetype', ['image/jpeg', 'image/jpg', 'image/png']); // %FIXME DRY
            $q1->whereIn('mimetype', Diskmediafile::$mimeImageTypes);
        });
    }

    public function scopeIsVideo($query)
    {
        return $query->whereHas('diskmediafile', function($q1) {
            //$q1->whereIn('mimetype', ['video/mp4', 'video/mpeg', 'video/ogg', 'video/quicktime']);
            $q1->whereIn('mimetype', Diskmediafile::$mimeVideoTypes);
        });
    }


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

    public function getOwner(): Collection
    {
        if ( !$this->resource ) {
            return new Collection();
        }
        //dd('Mediafile', $this->resource->getOwner()); // , $this->toArray());
        switch ($this->resource_type) {
        case 'users':
            //dd('here 0422', $this->resource);
            return new Collection($this->resource->without(['avatar','cover'])); // owner is the user 
        default:
            return $this->resource->getOwner();
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

    /* ---------------------------------------------------------------------- */
    /*                               Messagable                               */
    /* ---------------------------------------------------------------------- */
    #region Messagable

    public function getMessagableArray()
    {
        return new ResourcesMediafile($this);
    }

    #endregion Messagable
    /* ---------------------------------------------------------------------- */

    // %%% --- Other ---

    /*
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
     */


}

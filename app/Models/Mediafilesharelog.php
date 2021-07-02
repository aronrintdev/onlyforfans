<?php
namespace App\Models;

use DB;
use Auth;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Interfaces\Guidable;
use App\Models\Traits\UsesUuid;
use App\Enums\MediafileTypeEnum;

//class Diskmediafile extends BaseModel implements Guidable, Ownable, Cloneable
class Mediafilesharelog extends BaseModel 
{
    use UsesUuid, HasFactory;

    protected $guarded = [ 'id', 'created_at', 'updated_at' ];
    public static $vrules = [];

    //--------------------------------------------
    // Boot
    //--------------------------------------------
    public static function boot()
    {
        parent::boot();
        static::deleting(function ($model) {
            throw new Exception('Can not delele a log record (mediafilesharelogs)');
        });
    }

    //--------------------------------------------
    // %%% Relationships
    //--------------------------------------------

    // %NOTE: one [mediafilesharelog] record *per* src mediafile shared (vs per src vaultfolder)

    public function sharer() { // user who did the sharing (sender)
        return $this->belongsTo(User::class, 'sharer_id');
    }

    public function sharee() { // user being shared to (receiver)
        return $this->belongsTo(User::class, 'sharee_id');
    }

    public function dstvaultfolder() { // new vaultfolder created for sharee to hold shared content (files)
        return $this->belongsTo(Vaultfolder::class, 'dstvaultfolder_id');
    }

    public function srcmediafile() {
        return $this->belongsTo(Mediafile::class, 'srcmediafile_id');
    }

    public function dstmediafile() {
        return $this->belongsTo(Mediafile::class, 'dstmediafile_id');
    }

    //--------------------------------------------
    // %%% Accessors/Mutators | Casts
    //--------------------------------------------

    protected $casts = [ 'cattrs' => 'array', 'meta' => 'array' ];

    //--------------------------------------------
    // %%% Methods
    //--------------------------------------------

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
        return $this->id;
    }

    // %%% --- Other ---

}

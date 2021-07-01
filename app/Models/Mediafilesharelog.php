<?php
namespace App\Models;

use DB;
use Auth;
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
    // %%% Relationships
    //--------------------------------------------

    public function sharer() { // user who did the sharing (sender)
        return $this->belongsTo(User::class, 'sharer_id');
    }

    public function sharee() { // user being shared to (receiver)
        return $this->belongsTo(User::class, 'sharee_id');
    }

    public function vaultfolder() { // new vaultfolder created for sharee to hold shared content (files)
        return $this->belongsTo(Vaultfolder::class, 'vaultfolder_id');
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

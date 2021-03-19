<?php
namespace App\Models;

use App\Models\Traits\UsesUuid;
use App\Enums\GenderTypeEnum;

class UserSetting extends Model
{
    use UsesUuid;

    protected $guarded = [ 'id', 'created_at', 'updated_at' ];
    protected $table = 'user_settings';
    protected $casts = [
        'cattrs' => 'array',
        'meta' => 'array',
        'custom' => 'array',
    ];

    //--------------------------------------------
    // %%% Boot
    //--------------------------------------------
    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $cattrs = [
                'subscriptions' => array_key_exists('subscriptions',$model->cattrs??[]) ? $model->cattrs['subscriptions'] : null,
                'localization'  => array_key_exists('localization',$model->cattrs??[])  ? $model->cattrs['localization']  : null,
                'weblinks'      => array_key_exists('weblinks',$model->cattrs??[])      ? $model->cattrs['weblinks']      : null,
                'privacy'       => array_key_exists('privacy',$model->cattrs??[])       ? $model->cattrs['privacy']       : null,
                'watermark'     => array_key_exists('watermark',$model->cattrs??[])     ? $model->cattrs['watermark']     : null,
            ];
            $model->cattrs = $cattrs;
        });
    }

    //--------------------------------------------
    // %%% Relationships
    //--------------------------------------------

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}

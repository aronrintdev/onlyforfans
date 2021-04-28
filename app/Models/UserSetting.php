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
                'notifications' => array_key_exists('notifications',$model->cattrs??[]) ? $model->cattrs['notifications'] : null,
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

    //--------------------------------------------
    // %%% Other
    //--------------------------------------------

    // updates settings cattrs 'blocked' key, by batch (ips, countries, etc)
    public static function parseBlockedBatched($requestAttrs, $oldBlocked)
    {
        //$requestAttrs = $request->blocked;
        //$oldBlocked = $cattrs['blocked'];
        $byCountry = [];
        $byIP = [];
        $byUsername = [];
        foreach ( $requestAttrs as $bobj) {
            $slug = trim($bobj['slug'] ?? '');
            $text = trim($bobj['text'] ?? '');
            do {
                // country
                $exists = Country::where('slug', $slug)->first();
                if ( $exists ) { 
                    $byCountry[] = $slug;
                    break;
                }
                // user
                $exists = User::where('username', $slug)->first();
                if ( $exists ) {
                    $byUsername[] = $slug;
                    break;
                }
                // IP
                if ( filter_var($text, FILTER_VALIDATE_IP) ) { // ip
                    $byIP[] = $text;
                    break;
                }
            } while(0);
        }
        $blocked = $oldBlocked ?? [];
        $blocked['ips'] = $blocked['ips'] ?? [];
        $blocked['countries'] = $blocked['countries'] ?? [];
        $blocked['usernames'] = $blocked['usernames'] ?? [];
        array_push($blocked['ips'], ...$byIP);
        array_push($blocked['countries'], ...$byCountry);
        array_push($blocked['usernames'], ...$byUsername);

        $result= array_map('array_unique', $blocked);
        return $result;
        //$cattrs['blocked'] = array_map('array_unique', $blocked);
    }
}

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
            $groups = [
                'notifications',
                'subscriptions',
                'localization',
                'weblinks',
                'privacy',
                'watermark',
            ];

            $cattrs = UserSetting::getTemplate(); // init with empty template

            // batch-fill in any values sent from caller
            foreach ($groups as $g) {
                if ( array_key_exists($g, $model->cattrs??[]) ) {
                    $cattrs[$g] = $model->cattrs[$g];
                }
            }
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

    public static $notifyTypes = [ 'email', 'sms', 'site', 'push' ];

    public function enable(string $group, array $payload) {
        $cattrs = $this->cattrs; // 'pop'

        // apply any set payload from input
        switch ($group) {
        case 'notifications':
            //dd($group, $payload);
            if ( array_key_exists('campaigns', $payload) ) {
                /*
                if ( array_key_exists('goal_acheived', $v['campaigns']) ) {
                    $cattrs['notifications']['campaigns']['goal_achieved'] = $v['campaigns']['goal_achieved'];
                }
                if ( array_key_exists('goal_acheived', $v['campaigns']) ) {
                    $cattrs['notifications']['campaigns']['new_contribution'] = $v['campaigns']['new_contribution'];
                }
                 */
            }
            if ( array_key_exists('income', $payload) ) {
//dd('income', $payload);
                foreach ($payload['income'] as $k => $v) {
                    //dd($k, $v);
                    //if ( in_array( , self::$notifyTypes) ) { }
                    switch ($k) {
                    case 'new_tip':
                        $cattrs['notifications']['income']['new_tip'] = array_unique(array_merge($cattrs['notifications']['income']['new_tip'], $v));
                        break;
                    }
                } // foreach
            }
            break;
        } // switch

        $this->cattrs = $cattrs; // 'push'
        $this->save();
    }

    public function disable(string $group, array $payload) {
        $cattrs = $this->cattrs; // 'pop'

        // apply any set payload from input
        switch ($group) {
        case 'notifications':
            if ( array_key_exists('income', $payload) ) {
                foreach ($payload['income'] as $k => $v) {
                    switch ($k) {
                    case 'new_tip':
                        $cattrs['notifications']['income']['new_tip'] = array_unique(array_diff($cattrs['notifications']['income']['new_tip'], $v));
                        break;
                    }
                } // foreach
            }
            break;
        } // switch

        $this->cattrs = $cattrs; // 'push'
        $this->save();
    }

    public static function getTemplate() {
        $template = [
            'notifications' => [
                // in:email,sms,site,push
                'campaigns' => [
                    'goal_achieved' => [],
                    'new_contribution' => [],
                ],
                'refunds' => [
                    'new_refund' => [],
                ],
                'income' => [
                    'new_tip' => [],
                    //'new_tip' => ['push'], // TEST ONLY
                    'new_subscription' => [],
                    'renewed_subscription' => [],
                ],
            ],
            'subscriptions' => [
            ], 
            'localization' => [
            ], 
            'weblinks' => [
            ], 
            'privacy' => [
            ], 
            'blocked' => [
                'ips' => [],
                'countries' => [],
                'usernames' => [],
            ], 
            'watermark' => [
            ], 
        ];
        return $template;
        /*
        $this->cattrs = $template; // 'push'
        $this->save();
        return $this;
         */
    }
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

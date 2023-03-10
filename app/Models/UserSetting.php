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
        'weblinks' => 'array',
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

            $cattrs = self::$template; // init with empty template

            // batch-fill in any values sent from caller (overwrites existing values per group)
            foreach ($groups as $g) {
                if ( array_key_exists($g, $model->cattrs??[]) ) {
                    $cattrs[$g] = $model->cattrs[$g];
                }
            }
            $model->cattrs = $cattrs;
        });

        static::created(function ($model) {
            $model->enableAllNotifications(); // 20210914 - enable all notfications for new accounts
        });
    }

    //--------------------------------------------
    // %%% Relationships
    //--------------------------------------------

    public function user() {
        return $this->belongsTo(User::class);
    }

    //--------------------------------------------
    // %%% Other
    //--------------------------------------------

    public static $notifyTypes = [ 'email', 'sms', 'site', 'push' ];
    public static $vrules = [
        'notifications' => [
                'campaigns.*' => 'array',
                'campaigns.*.*' => 'string|in:email,sms,site,push',
                'global.*' => 'array',
                'global.*.*' => 'string|in:email,sms,site,push',
                'income.*' => 'array',
                'income.*.*' => 'string|in:email,sms,site,push',
                'posts.*' => 'array',
                'posts.*.*' => 'string|in:email,sms,site,push',
                'refunds.*' => 'array',
                'refunds.*.*' => 'string|in:email,sms,site,push',
        ],
    ];

    public static $template = [
        'notifications' => [ // group
            'campaigns' => [ // subcat
                'goal_achieved' => [],
                'new_contribution' => [],
            ],
            'global' => [
                'enabled' => [],
                'show_full_text' => [],
            ],  // (group) subcat (global override by notifyType)
            'income' => [ // subcat
                'new_tip' => [],
                'new_subscription' => [],
                'new_paid_post_purchase' => [],
                //'renewed_subscription' => [],
                //'returning_subscription' => [],
            ],
            'messages' => [ // subcat
                'new_message' => [],
            ],
            'posts' => [ // subcat
                //'new_post_summary' => [],
                'new_comment' => [],
                'new_like' => [],
            ],
            'timelines' => [ // subcat
                'new_follower' => [],
            ],
            'referrals' => [ // subcat
                'new_referral' => [],
            ],
            'refunds' => [ // subcat
                'new_refund' => [],
            ],
            'subscriptions' => [ // subcat
                'new_payment' => [],
            ],
            'usertags' => [ // subcat
                'new_tag' => [],
            ],
        ],
        'subscriptions' => [ // group
            'price_per_1_months' => null,
        ], 
        'localization' => [ // group
        ], 
        'weblinks' => [ // group
        ], 
        'privacy' => [ // group
        ], 
        'blocked' => [ // group
            'ips' => [],
            'countries' => [],
            'usernames' => [],
        ], 
        'watermark' => [ // group
        ], 
    ];

    public function enableAllNotifications() {
        $this->enable('notifications', [
            'global' => [
                'enabled' => [ 'email', 'site' ],
            ],
            'income' => [
                'new_tip' => [ 'email', 'site' ],
                'new_subscription' => [ 'email', 'site' ],
                'new_paid_post_purchase' => [ 'email', 'site' ],
            ],
            'posts' => [
                'new_like' => [ 'email', 'site' ],
                'new_comment' => [ 'email', 'site' ],
            ],
            'messages' => [
                'new_message' => [ 'email', 'site' ],
            ],
            'timelines' => [
                'new_follower' => [ 'email', 'site' ],
            ],
            'usertags' => [
                'new_tag' => [ 'email', 'site' ],
            ],
        ]);
    }

    public function setValues(string $group, array $payload) {
        //dump($payload);
        $cattrs = $this->cattrs; // 'pop'

        // apply any set payload from input
        switch ($group) {
        case 'subscriptions': // 1-level deep
            foreach ($payload as $k => $v) {
                if ( array_key_exists( $k, self::$template[$group]) ) {
                    $cattrs[$group][$k] = $v;
                }
            }
            break;
        } // switch
        $this->cattrs = $cattrs; // 'push'
        $this->save();
    }

    public function enable(string $group, array $payload) {
        //dump($payload);
        $cattrs = $this->cattrs; // 'pop'

        // apply any set payload from input
        switch ($group) {
        case 'notifications':
            foreach (self::$template[$group] as $gsubcat => $tsubobj) {
                foreach ($payload[$gsubcat]??[] as $k => $v) {
                    if ( array_key_exists( $k, self::$template[$group][$gsubcat]) ) {
                        $cattrs[$group][$gsubcat][$k] = array_unique(array_merge($cattrs[$group][$gsubcat][$k]??[], $v));
                    }
                }
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
            foreach (self::$template[$group] as $gsubcat => $tsubobj) {
                foreach ($payload[$gsubcat]??[] as $k => $v) {
                    if ( array_key_exists( $k, self::$template[$group][$gsubcat]) ) {
                        $cattrs[$group][$gsubcat][$k] = array_unique(array_diff($cattrs[$group][$gsubcat][$k]??[], $v));
                    }
                }
            }
            break;
        } // switch

        $this->cattrs = $cattrs; // 'push'
        $this->save();
    }

    public static function getTemplate() {
        return self::$template;
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

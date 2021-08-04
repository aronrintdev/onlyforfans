<?php
namespace App\Console\Commands;

use DB;
use App;
use Exception;
use App\Models\Mediafile;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class TruncateData extends Command
{
    protected $signature = 'truncate:data {--list=all}';

    protected $description = 'Development-only script to truncate selected DB tables pre-seeding';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        //$isEnvLocal = App::environment(['local']);
        //$isEnvTesting = App::environment(['testing']);

        $whitelistedEnvs = ['testing', 'local',];
        $thisEnv = App::environment();
        $dbName = Config::get('database.connections.primary.database');

        $this->info( '%%% DB Name: ' . $dbName);
        $this->info( '%%% Env: ' . $thisEnv);
        if ( $dbName !== 'fansplat_dev_test' && !in_array($thisEnv, $whitelistedEnvs) ) {
            throw new Exception('Environment not in whitelist: ' . App::environment());
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // disable
        $list = [];
        switch(Str::lower($this->option('list'))) {
            case 'all':
                $list = self::$truncateList;
                break;
            case 'shareables':
                $list = self::$shareablesList;
                break;
        }

        foreach ( $list['tables'] as $t ) {
            $this->info( ' - Truncate "'.$t.'"...');
            switch ($t) {
                case 'mediafiles':
                    Mediafile::cursor()->each(function($mf) {
                        $this->removeMediafile($mf);
                    });
                    break;
                default:
                    //DB::table($t)->truncate();
                    DB::table($t)->delete();
            }
        }
        foreach($list['models'] as $m) {
            $this->info(" - Truncate '$m' ..."  );
            $m::truncate();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // enable

        /*
         */
    }

    private function removeMediafile($mf) {
        Storage::disk('s3')->delete($mf->filename); // Remove from S3
        $mf->delete();
    }

    private static $shareablesList = [
        'models' => [
            \App\Models\Financial\Transaction::class,
            \App\Models\Financial\TransactionSummary::class,
            \App\Models\Financial\Flag::class,
        ],
        'tables' => [
            'shareables',
            'subscriptions',
        ],

    ];

    private static $truncateList = [
        'models' => [
            \App\Models\Financial\Account::class,
            \App\Models\Financial\AchAccount::class,
            \App\Models\Financial\Flag::class,
            \App\Models\Financial\PayoutBatch::class,
            \App\Models\Financial\SegpayCall::class,
            \App\Models\Financial\SegpayCard::class,
            \App\Models\Financial\SystemOwner::class,
            \App\Models\Financial\Transaction::class,
            \App\Models\Financial\TransactionSummary::class,
        ],
        'tables' => [
            'websockets_statistics_entries',
            'favorites',
            'mycontacts',
            'notifications',
            'purchasable_price_points',
            'subscriptions',
            'model_has_permissions',
            'model_has_roles',
            'password_resets',
            'role_has_permissions',
            'sessions',
            'settings',
            'user_settings',
            'websockets_statistics_entries',
            // 'migrations',
            'invites',
            'jobs',
            'links',
            'locations',
            'blockables',
            'fanledgers',
            'shareables',
            'likeables',
            'comments',
            'diskmediafiles',
            'mediafiles',
            'stories',

            'user_settings',
            'permissions',
            'roles',

            'vaultfolders',
            'vaults',

            'posts',
            'timelines',
            'users',

            'username_rules',
            'countries',
            'usstates',
        ],
    ];

}


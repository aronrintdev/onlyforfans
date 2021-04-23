<?php
namespace App\Console\Commands;

use DB;
use App;
use Exception;
use App\Models\Mediafile;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
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
        $dbName = env('DB_DATABASE');

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

        foreach ( $list as $t ) {
            $this->info( ' - Truncate "'.$t.'"...');
            switch ($t) {
                case 'mediafiles':
                    Mediafile::cursor()->each(function($mf) {
                        $this->removeMediafile($mf);
                    });
                    break;
                default:
                    DB::table($t)->truncate();
            }
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
        'financial_transactions',
        'financial_transaction_summaries',
        'financial_currency_exchange_transactions',
        'financial_flags',
        'shareables',
        'subscriptions',
    ];

    private static $truncateList = [
        'bookmarks',
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
        'financial_accounts',
        'financial_currency_exchange_transactions',
        'financial_transactions',
        'fanledgers',
        'shareables',
        'likeables',
        'comments',
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
    ];

}


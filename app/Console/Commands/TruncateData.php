<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App;
use DB;
use Exception;
use App\Models\MediaFile;

class TruncateData extends Command
{
    protected $signature = 'truncate:data';

    protected $description = 'Development-only script to truncate selected DB tables pre-seeding';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $isEnvLocal = App::environment(['local']);
        $isEnvTesting = App::environment(['testing']);
        $dbName = env('DB_DATABASE');
        $this->info( '%%% DB Name: '.$dbName);
        $this->info( '%%% Is env local?: '.($isEnvLocal?'true':'false') );
        $this->info( '%%% Is env testing?: '.($isEnvTesting?'true':'false') );
        if ( $dbName !== 'fansplat_dev_test' && !$isEnvTesting ) {
            throw new Exception('Environment not in whitelist: '.App::environment());
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // disable
        foreach ( self::$truncateList as $t ) {
            $this->info( ' - Truncate "'.$t.'"...');
            switch ($t) {
                case 'media_files':
                    $mediaFiles = MediaFile::get();
                    $mediaFiles->each( function($mf) {
                        $this->removeMediaFile($mf);
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

    private function removeMediaFile($mf) {
        Storage::disk('s3')->delete($mf->filename); // Remove from S3
        $mf->delete();
    }

    private static $truncateList = [
        'role_user',
        'permission_role',
        'fanledgers',
        'shareables',
        'likeables',
        'comments',
        'media_files',
        'stories',
        //'subscriptions',

        'model_has_permissions',
        'model_has_roles',
        'role_has_permissions',
        'user_settings',
        'permissions',
        'roles',

        'vault_folders',
        'vaults',

        'posts',
        'timelines',
        'users',

        'username_rules',

    ];
}

<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App;
use DB;
use Exception;
use App\Mediafile;

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
        $dbName = env('DB_DATABASE');
        $this->info( '%%% DB Name: '.$dbName.', Is env local?: '.($isEnvLocal?'true':'false') );
        if ( $dbName !== 'fansplat_dev' || !$isEnvLocal ) {
            throw new Exception('Environment not in whitelist');
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // disable
        foreach ( self::$truncateList as $t ) {
            $this->info( ' - Truncate "'.$t.'"...');
            switch ($t) {
                case 'mediafiles':
                    $mediafiles = Mediafile::get();
                    $mediafiles->each( function($mf) {
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

    private static $truncateList = [
        'album_media',
        'post_media',
        'post_likes',
        'post_shares',
        'post_follows',
        'saved_posts',
        'comments',
        'media',
        'albums',

        'mediafiles',
        'shareables',
        'stories',
        'subscriptions',

        'role_user',
        'user_settings',
        'user_lists',
        'users_tips',
        'event_user',
        'favourite_users',
        'group_user',
        'announcement_user',

        'bank_account_details',
        'comment_likes',
        'followers',

        'threads',
        'messages',

        'vaultfolders',
        'vaults',

        'saved_timelines',
        'timelines',
        'users',

        'purchased_posts',
        'posts',
        /*
         */
    ];
}

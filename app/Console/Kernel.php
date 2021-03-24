<?php
namespace App\Console;

use App\Console\Commands\ExpirePostCommand;
use App\Console\Commands\PublishPostCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        PublishPostCommand::class,
        ExpirePostCommand::class,
        \App\Console\Commands\TruncateData::class,
        \App\Console\Commands\UpdateSlugs::class,
        \App\Console\Commands\MakeThumbnails::class,
        \App\Console\Commands\SetMediafileBasename::class,
        \App\Console\Commands\DeleteMediafileAssets::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
         $schedule->command('publish:posts')
                  ->everyMinute()->appendOutputTo(storage_path('logs/publish_posts.log'));
        $schedule->command('expire:post')
            ->everyMinute()->appendOutputTo(storage_path('logs/expire_posts.log'));
    }
    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}

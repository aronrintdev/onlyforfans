<?php
namespace App\Console;


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
        \App\Console\Commands\DeleteMediafileAssets::class,
        \App\Console\Commands\MakeBlurs::class,
        \App\Console\Commands\MakeThumbnails::class,
        \App\Console\Commands\SetMediafileBasename::class,
        \App\Console\Commands\TruncateData::class,
        \App\Console\Commands\UpdateCanceledSubscriptions::class,
        \App\Console\Commands\UpdateMediafilesNullResource::class,
        \App\Console\Commands\UpdateSlugs::class,
        \App\Console\Commands\SendScheduleMessages::class,
        \App\Console\Commands\WebhooksDispatch::class,
        \App\Console\Commands\WebhooksRetry::class,
        \App\Console\Commands\PublishScheduledPosts::class,
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
        // $schedule->command('subscription:update-canceled')->everyHour();
        $schedule->command('send:schdule-messages')->everyMinute()->appendOutputTo(storage_path('logs/publish_posts.log'))->runInBackground();
        $schedule->command('publish:schduled-posts')->everyMinute()->appendOutputTo(storage_path('logs/publish_posts.log'))->runInBackground();
        // $schedule->command('publish:posts')
        //           ->everyMinute()->appendOutputTo(storage_path('logs/publish_posts.log'));
        // $schedule->command('expire:post')
        //     ->everyMinute()->appendOutputTo(storage_path('logs/expire_posts.log'));
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

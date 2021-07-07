<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Carbon\Carbon;

class PublishScheduledPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'publish:schduled-posts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish scheduled posts';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $posts = POST::whereDate('schedule_datetime', '=', Carbon::now('UTC')->toDateString())
                    ->whereTime('schedule_datetime', '=', Carbon::now('UTC')->format('H:i').':00')
                    ->get();
        if ($posts !== null) {
            $posts->each(function($post) {
                $post->schedule_datetime = null;
                $post->created_at = Carbon::now('UTC');
                
                $post->save();
            });
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Models\POST;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\User;
use App\Events\MessageSentEvent;
use App\Events\MessagePublishedEvent;

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
        $now = Carbon::now('UTC')->timestamp;

        $posts = POST::where('schedule_datetime', $now)->get();
        if ($posts !== null) {
            $posts->each(function($post) {
                $post->schedule_datetime = null;
                $post->created_at = date("Y-m-d H:i:s", strtotime(Carbon::now('UTC')));
                
                $post->save();
            });
        }
    }
}

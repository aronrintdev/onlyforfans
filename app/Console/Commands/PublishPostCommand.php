<?php

namespace App\Console\Commands;

use App\Post;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PublishPostCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'publish:posts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will publish post';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Publishing Command....');
        
        $today = Carbon::now()->toDateString();
        $posts = Post::whereActive(0)->where('publish_date', $today)->get();
        /** @var Post $post */
        foreach ($posts as $post) {
            $timezone = ($post->user && !empty($post->user->timezone)) ? $post->user->timezone : 'UTC';
            $time = Carbon::now($timezone)->toTimeString();
            if ($post->publish_time <= $time) {
                $post->update([
                    'active' => 1,
                    'created_at' => Carbon::now($timezone)
                ]);
            }
            
        }
        $now = Carbon::now();
        
        $this->info('Post Publishing complete');
    }
}

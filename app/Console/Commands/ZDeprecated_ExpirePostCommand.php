<?php

namespace App\Console\Commands;

use App\Post;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ZDeprecated_ExpirePostCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expire:post';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will expire the post.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Expiring post...');
        $today = Carbon::now()->toDateString();
        $posts = Post::whereActive(1)->where('expiration_date', $today)->get();
        /** @var Post $post */
        foreach ($posts as $post) {
            $timezone = ($post->user && !empty($post->user->timezone)) ? $post->user->timezone : 'UTC';
            $time = Carbon::now($timezone)->toTimeString();
            if ($post->expiration_time <= $time) {
                $post->update([
                    'active' => 0,
                ]);
            }

        }
        $this->info('Post expired successfully.');
    }
}

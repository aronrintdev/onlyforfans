<?php

namespace App\Console\Commands;

use App\Models\ChatThread;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\User;
use App\Events\MessageSentEvent;
use App\Events\MessagePublishedEvent;

class SendScheduleMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:schdule-messages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send scheduled messages to users';

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
        $now = date("Y-m-d H:i", strtotime(Carbon::now('UTC')));

        $chatthreads = ChatThread::where('schedule_datetime', $now)->get();
        if ($chatthreads !== null) {
            $chatthreads->each(function($chatthread) {
                $sender = User::where('id', $chatthread->sender_id)->first();
                $receiver = User::where('id', $chatthread->receiver_id)->first();
                $chatthread->schedule_datetime = null;
                $chatthread->created_at = date("Y-m-d H:i:s", strtotime(Carbon::now()));
                $chatthread->messages = $chatthread->messages()->with('mediafile')->orderBy('mcounter', 'asc')->get();
                
                broadcast(new MessageSentEvent($chatthread, $sender, $receiver))->toOthers();
                broadcast(new MessagePublishedEvent($chatthread, $sender))->toOthers();
                
                $chatthread->save();
            });
        }
    }
}

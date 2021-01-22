<?php

namespace App\Jobs;

use App\Webhook;
use App\Enums\WebhookStatusEnum as Status;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessPusherWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HandlesSlot;

    protected $webhook;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Webhook $webhook)
    {
        $this->webhook = $webhook;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Lock webhook so other jobs don't interfere
        DB::transaction(function() {
            $webhook = Webhook::lockForUpdate()->find($this->webhook->id);
            if ($webhook->status != Status::UNHANDLED) {
                return;
            }
            try {
                $events = $webhook->body['events'];
                foreach($events as $index => $event) {
                    /**
                     * Pusher webhook events
                     * https://pusher.com/docs/channels/server_api/webhooks#events
                     */
                    switch($event['name']) {
                        // private channel gains first subscriber
                        case 'channel_occupied':
                            $this->channelOccupied($event, $webhook->body['time_ms']);
                        break;
                        // private channel loses last subscriber
                        case 'channel_vacated':
                            $this->channelVacated($event, $webhook->body['time_ms']);
                        break;
                        // new user subscribes to presence channel
                        case 'member_added':
                            $this->memberAdded($event, $webhook->body['time_ms']);
                        break;
                        // member leaves a presence channel
                        case 'member_removed':
                            $this->memberRemoved($event, $webhook->body['time_ms']);
                        break;
                        // A custom client even happened.
                        case 'client_event':
                            $this->clientEvent($event, $webhook->body['time_ms']);
                        break;
                    }
                }
            } catch(Exception $e) {
                $webhook->status = Status::ERROR;
                $webhook->notes = 'Error on execution: ' . $e->getMessage();
                $webhook->save();
                return;
            }
            $webhook->status = Status::HANDLED;
            $webhook->save();
        });
    }

    /**
     * Handle `channel_occupied` event
     */
    private function channelOccupied($event, $time) {
        //
    }

    /**
     * Handle `channel_vacated` event
     */
    private function channelVacated($event, $time) {
        //
    }

    /**
     * Handle `client_event` event
     */
    private function clientEvent($event, $time) {
        //
    }

    /**
     * Handle `member_added` event
     */
    private function memberAdded($event, $time) {
        // TODO: Update User to online if this is their status channel
    }

    /**
     * Handle `member_removed` event
     */
    private function memberRemoved($event, $time) {
        // TODO: Update user to offline if this is their status channel
    }
}

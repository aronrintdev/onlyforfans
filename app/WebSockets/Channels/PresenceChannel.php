<?php

namespace App\Websockets\Channels;

use BeyondCode\LaravelWebSockets\WebSockets\Channels\PresenceChannel as ParentClass;

use Illuminate\Support\Facades\Broadcast;
use Ratchet\ConnectionInterface;
use stdClass;
use Illuminate\Support\Str;

class PresenceChannel extends ParentClass
{
    /**
     * @link https://pusher.com/docs/pusher_protocol#presence-channel-events
     */
    public function subscribe(ConnectionInterface $connection, stdClass $payload)
    {
        parent::subscribe($connection, $payload);

        // Attempt to call subscribe on channel implementation.
        Broadcast::raiseChannelEvent('subscribe', $this->channelName, $this->users[$connection->socketId]);
    }

    public function unsubscribe(ConnectionInterface $connection)
    {
        if (! isset($this->users[$connection->socketId])) {
            return;
        }

        // Attempt to call unsubscribe on channel implementation
        Broadcast::raiseChannelEvent('unsubscribe', $this->channelName, $this->users[$connection->socketId]);

        parent::unsubscribe($connection);
    }

    public function broadcastToOthers(ConnectionInterface $connection, $payload)
    {
        parent::broadcastToOthers($connection, $payload);
        $payload = gettype($payload) == 'string' ? json_decode($payload) : (object) $payload;
        if (Str::startsWith($payload->event, 'client-')) {
            Broadcast::raiseClientEvent(
                $payload->event,
                $this->channelName,
                $this->users[$connection->socketId],
                $payload->data
            );
        }
    }
}


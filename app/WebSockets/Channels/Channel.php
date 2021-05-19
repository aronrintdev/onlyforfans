<?php

namespace App\WebSockets\Channels;

use Illuminate\Support\Str;

use Ratchet\ConnectionInterface;
use Illuminate\Support\Facades\Broadcast;
use BeyondCode\LaravelWebSockets\WebSockets\Channels\Channel as ParentClass;

class Channel extends ParentClass
{
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


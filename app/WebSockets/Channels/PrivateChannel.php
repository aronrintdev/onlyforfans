<?php

namespace App\WebSockets\Channels;

use BeyondCode\LaravelWebSockets\WebSockets\Channels\PrivateChannel as ParentClass;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Str;
use Ratchet\ConnectionInterface;

class PrivateChannel extends ParentClass
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


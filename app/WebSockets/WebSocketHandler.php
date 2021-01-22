<?php

namespace App\WebSockets;

use BeyondCode\LaravelWebSockets\WebSockets\WebSocketHandler as ParentHandler;

use BeyondCode\LaravelWebSockets\Facades\StatisticsLogger;
use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;

class WebSocketHandler extends ParentHandler
{

    public function onOpen(ConnectionInterface $connection)
    {
        parent::onOpen($connection);
    }

    public function onMessage(ConnectionInterface $connection, MessageInterface $message)
    {
        $message = PusherMessageFactory::createForMessage($message, $connection, $this->channelManager);
        $message->respond();
        StatisticsLogger::webSocketMessage($connection);
    }

    public function onClose(ConnectionInterface $connection)
    {
        parent::onClose($connection);
    }

}

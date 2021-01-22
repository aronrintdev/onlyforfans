<?php

namespace App\WebSockets\Channels;

use Illuminate\Support\Str;
use BeyondCode\LaravelWebSockets\WebSockets\Channels\ChannelManagers\ArrayChannelManager;


class Manager extends ArrayChannelManager
{
    /**
     * Substituting default channels for this apps' channels
     */
    protected function determineChannelClass(string $channelName): string
    {
        if (Str::startsWith($channelName, 'private-')) {
            return PrivateChannel::class;
        }

        if (Str::startsWith($channelName, 'presence-')) {
            return PresenceChannel::class;
        }

        return Channel::class;
    }
}
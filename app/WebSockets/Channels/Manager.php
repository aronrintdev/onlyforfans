<?php

namespace App\WebSockets\Channels;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Ratchet\ConnectionInterface;
use BeyondCode\LaravelWebSockets\WebSockets\Channels\ChannelManager;
use BeyondCode\LaravelWebSockets\WebSockets\Channels\Channel as WSChannel;

class Manager implements ChannelManager
{
    /** @var string */
    protected $appId;

    /** @var array */
    protected $channels = [];

    public function findOrCreate(string $appId, string $channelName): WSChannel
    {
        if (!isset($this->channels[$appId][$channelName])) {
            $channelClass = $this->determineChannelClass($channelName);

            $this->channels[$appId][$channelName] = new $channelClass($channelName);
        }

        return $this->channels[$appId][$channelName];
    }

    public function find(string $appId, string $channelName): ?WSChannel
    {
        return $this->channels[$appId][$channelName] ?? null;
    }

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

    public function getChannels(string $appId): array
    {
        return $this->channels[$appId] ?? [];
    }

    public function getConnectionCount(string $appId): int
    {
        return collect($this->getChannels($appId))
            ->flatMap(function (WSChannel $channel) {
                return collect($channel->getSubscribedConnections())->pluck('socketId');
            })
            ->unique()
            ->count();
    }

    public function removeFromAllChannels(ConnectionInterface $connection)
    {
        if (!isset($connection->app)) {
            return;
        }

        /*
         * Remove the connection from all channels.
         */
        collect(Arr::get($this->channels, $connection->app->id, []))->each->unsubscribe($connection);

        /*
         * Unset all channels that have no connections so we don't leak memory.
         */
        collect(Arr::get($this->channels, $connection->app->id, []))
            ->reject->hasConnections()
            ->each(function (WSChannel $channel, string $channelName) use ($connection) {
                unset($this->channels[$connection->app->id][$channelName]);
            });

        if (count(Arr::get($this->channels, $connection->app->id, [])) === 0) {
            unset($this->channels[$connection->app->id]);
        }
    }

}
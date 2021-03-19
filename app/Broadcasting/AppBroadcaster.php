<?php

namespace App\Broadcasting;

use App\Models\User;
use Illuminate\Broadcasting\Broadcasters\PusherBroadcaster;
use Illuminate\Container\Container;
use stdClass;
use Illuminate\Support\Str;

/**
 * Custom Pusher Broadcasting implementation to expose routing to websocket server
 */

class AppBroadcaster extends PusherBroadcaster
{
    /**
     * Raise channel event to the channel class.
     *
     * @param  string  $eventName
     * @param  string  $channelName
     * @param  stdClass  $data
     */
    public function raiseChannelEvent( string $eventName, string $channelName, stdClass $data ) {
        $channelName = $this->normalizeChannelName($channelName);
        foreach ($this->channels as $pattern => $callback) {
            if (! $this->channelNameMatchesPattern($channelName, $pattern)) {
                continue;
            }

            $parameters = $this->extractAuthParameters($pattern, $channelName, $callback);

            if ($handler = $this->makeEventHandler($callback, $eventName) ) {
                call_user_func(
                    [$handler, $eventName],
                    $data ?? new stdClass(),
                    isset($data->user_id) ? User::find($data->user_id) : null,
                    ...$parameters
                );
            }
        }
    }

    /**
     * Raises a client event to the channel's clientEvent method
     *
     * @param  string  $eventName
     * @param  string  $channelName
     * @param  stdClass  $userData
     * @param  stdClass  $data
     */
    public function raiseClientEvent(string $eventName, string $channelName, stdClass $userData, stdClass $data)
    {
        $channelName = $this->normalizeChannelName($channelName);
        foreach ($this->channels as $pattern => $callback) {
            if (! $this->channelNameMatchesPattern($channelName, $pattern)) {
                continue;
            }

            $parameters = $this->extractAuthParameters($pattern, $channelName, $callback);

            if ($handler = $this->makeEventHandler($callback, 'clientEvent') ) {
                $handler->clientEvent(
                    Str::startsWith($eventName, 'client-') ? Str::replaceFirst('client-', '', $eventName) : $eventName,
                    $data ?? new stdClass(),
                    isset($userData->user_id) ? User::find($userData->user_id) : null,
                    ...$parameters
                );
            }
        }

    }

    /**
     * Return callback container if it has the event as a method
     *
     * @param  mixed  $callback
     * @param  string $eventName
     * @return Container
     */
    protected function makeEventHandler($callback, $eventName)
    {
        if (is_callable($callback)) {
            // Only auth function, not class
            return false;
        }
        if (method_exists($callback, $eventName)) {
            return Container::getInstance()->make($callback);
        }
    }

}

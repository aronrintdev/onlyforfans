<?php

namespace App\WebSockets;

use Ratchet\WebSocket\WsServer;

use Illuminate\Support\Facades\Config;
use BeyondCode\LaravelWebSockets\WebSockets\WebSocketHandler;
use BeyondCode\LaravelWebSockets\Server\Router as ParentRouter;
use BeyondCode\LaravelWebSockets\Server\Logger\WebsocketsLogger;
use BeyondCode\LaravelWebSockets\HttpApi\Controllers\FetchUsersController;
use BeyondCode\LaravelWebSockets\HttpApi\Controllers\FetchChannelController;
use BeyondCode\LaravelWebSockets\HttpApi\Controllers\TriggerEventController;
use BeyondCode\LaravelWebSockets\HttpApi\Controllers\FetchChannelsController;

class Router extends ParentRouter {

    public function echo()
    {
        $this->get('/app/{appKey}', \App\WebSockets\WebSocketHandler::class);
        // $this->get('/app/{appKey}', WebSocketHandler::class);

        $this->post('/apps/{appId}/events', TriggerEventController::class);
        $this->get('/apps/{appId}/channels', FetchChannelsController::class);
        $this->get('/apps/{appId}/channels/{channelName}', FetchChannelController::class);
        $this->get('/apps/{appId}/channels/{channelName}/users', FetchUsersController::class);
    }


    /**
     * @param string $action
     * @return WsServer
     */
    protected function createWebSocketsServer(string $action): WsServer
    {
        $app = app($action);

        // if (WebsocketsLogger::isEnabled() && !$this->isCustomAction($action)) {
        if(Config::get('app.debug')) {
            $app = WebsocketsLogger::decorate($app);
        }
        // }

        return new WsServer($app);
    }

    /**
     * @param string $action
     * @return bool
     */
    private function isCustomAction(string $action): bool
    {
        return false !== array_search($action, $this->customRoutes->all());
    }
}
<?php

namespace App\Http\Middleware;

use App\Http\Controllers\SpaController;
use Closure;

/**
 * Middleware to handle routes that have mixed SPA and API endpoints
 */
class SpaMixed
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string|null              $guard
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if ($request->wantsJson()) {
            return $next($request);
        }
        // Use SpaController index instead
        $route = $request->route();
        $routeAction = array_merge($route->getAction(), [
            'uses'       => '\App\Http\Controllers\SpaController@index',
            'controller' => '\App\Http\Controllers\SpaController@index',
        ]);
        $route->setAction($routeAction);
        $route->controller = false;

        return $next($request);
    }
}

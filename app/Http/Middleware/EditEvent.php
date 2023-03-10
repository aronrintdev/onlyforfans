<?php

namespace App\Http\Middleware;

use App\Models\Timeline;
use Closure;
use Illuminate\Support\Facades\Auth;

class EditEvent
{
    /**
      * Handle an incoming request.
      *
      * @param  \Illuminate\Http\Request  $request
      * @param  \Closure  $next
      *
      * @return mixed
      */
    public function handle($request, Closure $next)
    {
        $timeline = Timeline::where('username', $request->username)->first();

        $event = $timeline->event()->first();

        if (!$event->is_admin(Auth::user()->id)) {
            return redirect($request->username);
        }


        return $next($request);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Gets what user is being targeted from a request, allows admins to change
     *   user to a different user from the request parameters.
     *
     * Putting this here to prevent this code from being repeated in many
     *   controllers
     *
     * @param Request $request
     * @return User
     */
    public function getUserFromRequest(Request $request)
    {
        if ($request->user()->isAdmin()) {
            $request->validate([
                'user_id' => 'uuid|exists:users,id',
            ]);

            if ($request->has('user_id')) {
                return User::find($request->user_id);
            }
        }

        return $request->user();
    }
}

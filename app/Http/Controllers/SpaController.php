<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class SpaController extends Controller
{
    /**
     * Index of all unknown routes. Load up appropriate app
     */
    public function index(Request $request)
    {
        // If the request want JSON, then it's a 404 when reaching here
        if (Request::wantsJson()) {
            abort(404, 'Route wanted JSON response');
        }

        // Send to app spa if logged in.
        if (Auth::user()) {
            return view('app');
        }
        // Send to guest spa if not logged in.
        return view('guest');
    }

    /**
     * Attempting to get to admin section of site
     */
    public function admin(Request $request)
    {
        // Verify admin. Then return admin spa
    }
}

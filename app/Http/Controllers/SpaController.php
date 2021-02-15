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
        // Send to app if logged in.
        if (Auth::user()) {
            return view('app');
        }
        // Send to guest if not logged in.
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

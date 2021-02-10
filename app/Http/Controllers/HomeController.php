<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
    }

    public function getLocation(Request $request)
    {
        $location = str_replace(' ', '+', $request->location);

        $map_url = 'http://www.google.com/maps/place/'.$location;

        return redirect($map_url);
    }
}

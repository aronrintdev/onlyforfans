<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->middleware('auth');
    }

}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;

class MessageController extends Controller
{
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->middleware('auth');
    }
    public function index()
    {
        return Message::all();
    }
}

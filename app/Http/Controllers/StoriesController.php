<?php
namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Setting;
use App\Story;

class StoriesController extends AppBaseController
{
    public function create(Request $request, $username)
    {
        $sessionUser = Auth::user();
        return view('stories.create', [
            'session_user' => $sessionUser,
            'timeline' => $sessionUser->timeline,
        ]);
    }

    public function store(Request $request, $username)
    {
        $sessionUser = Auth::user();
        $obj = Story::create([
            'timeline_id' => $sessionUser->timeline_id,
            'content' => $request->content,
            'cattrs' => [
                'background-color' => $request->bgcolor ?? '#fff',
            ],
            'stype' => $request->stype,
        ]);
        return response()->json([
            'data' => $obj,
        ]);
    }

}

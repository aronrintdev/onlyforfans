<?php

namespace App\Http\Controllers;

use App\Http\Resources\TimelineCollection;
use App\Models\Timeline;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Handle search requests
 */
class SearchController extends Controller
{
    /**
     * Basic search endpoint
     */
    public function search(Request $request)
    {
        //

        // TODO: Dummy Data | Replace with real search
        $data = Timeline::latest()->paginate(10);
        return new TimelineCollection($data);
    }
}

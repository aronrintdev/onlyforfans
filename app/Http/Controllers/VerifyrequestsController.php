<?php
namespace App\Http\Controllers;

use DB;
use Exception;
use Throwable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
//use App\Notifications\CommentReceived;
use App\Http\Resources\VerifyrequestCollection;
use App\Http\Resources\Verifyrequest as VerifyrequestResource;
use App\Models\Verifyrequest;

class VerifyrequestsController extends AppBaseController
{
    public function index(Request $request)
    {
        $request->validate([
            // filters
            'service' => 'string|in:idmerit',
        ]);
        $filters = $request->only(['service', ]) ?? [];

        // Init query
        $query = Verifyrequest::query();

        // Check permissions
        if ( !$request->user()->isAdmin() ) {
            abort(403);
        }

        // Apply filters
        foreach ($filters as $key => $f) {
            switch ($key) {
            default:
                $query->where($key, $f);
            }
        }

        $data = $query->paginate( $request->input('take', Config::get('collections.max.comments', 10)) );
        return new VerifyrequestCollection($data);
    }

    public function show(Request $request, Comment $comment)
    {
        $this->authorize('view', $comment);
        return new CommentResource($comment);
    }

}

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
use App\Enums\VerifyStatusTypeEnum;
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

    public function show(Request $request, Verifyrequest $verifyrequest)
    {
        $vr = $verifyrequest;
        //$vr->load('requester');
        //dd($vr->toArray());
        //$this->authorize('view', $comment);
        return new VerifyrequestResource($vr);
    }

    // Manually check status of a request by GUID
    // %TODO: put on queue (?)
    public function checkStatus(Verifyrequest $vr)
    {
        $vr = Verifyrequest::checkStatusByGUID($vr->guid);
        /* disable for now as this is done by admin...tho we could notify if status changes %TODO
        if ( $vr->vstatus ===  VerifyStatusTypeEnum::VERIFIED ) {
            $vr->requester->notify( new IdentityVerificationVerified($vr, $user) );
        } else if ( $vr->vstatus ===  VerifyStatusTypeEnum::REJECTED ) {
            $vr->requester->notify( new IdentityVerificationRejected($vr, $user) );
        }
         */
        return new VerifyrequestResource($vr);
        //return response()->json( $vr );
    }

}

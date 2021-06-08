<?php
namespace App\Http\Controllers;

use DB;
use Exception;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Http\Resources\MycontactCollection;
use App\Http\Resources\Mycontact as MycontactResource;
use App\Models\Mycontact;
use App\Models\User;

class MycontactsController extends AppBaseController
{
    public function index(Request $request)
    {
        $request->validate([
            // filters
            'owner_id' => 'uuid|exists:users,id',
        ]);
        $filters = $request->only(['owner_id']) ?? [];

        $query = Mycontact::query(); // Init query

        // Check permissions
        if ( !$request->user()->isAdmin() ) {

            $query->where('owner_id', $request->user()->id); // limit to my own
            unset($filters['owner_id']);

        }

        // Apply filters
        foreach ($filters as $key => $v) {
            switch ($key) {
            default:
                $query->where($key, $v);
            }
        }

        $data = $query->latest()->paginate( $request->input('take', env('MAX_DEFAULT_PER_REQUEST', 10)) );
        return new MycontactCollection($data);
    }

}

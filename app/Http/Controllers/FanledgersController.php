<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Fanledger;
use App\Models\User;
//use App\Models\Timeline;
use App\Enums\PaymentTypeEnum;
use App\Http\Resources\FanledgerCollection;
use App\Http\Resources\FanledgerResource;
;

class FanledgersController extends AppBaseController
{
    public function index(Request $request)
    {
        $request->validate([
            'seller_id' => 'uuid|exists:users,id', // if admin only
            //'post_id' => 'uuid|exists:posts,id', // if admin or post-owner only (per-post comments by fan use posts controller)
        ]);

        $filters = $request->only('seller_id');

        $query = Fanledger::query();
        // Check permissions
        if ( !$request->user()->isAdmin() ) {

            // non-admin: only view own resources
            $query->where('seller_id', $request->user()->id); 
            unset($filters['seller_id']);

            /*
            if ( array_key_exists('post_id', $filters) ) {
                $post = Post::find($filters['post_id']);
                $this->authorize('update', $post); // non-admin must own post filtered on
            }
             */
        }

        // Apply any filters
        foreach ($filters as $key => $f) {
            // %TODO: subgroup under 'filters' (need to update axios.GET calls as well in Vue)
            switch ($key) {
                case 'seller_id':
                    $query->where($key, $f);
                    break;
            }
        }

        $data = $query->paginate( $request->input('take', env('MAX_DEFAULT_PER_REQUEST', 10)) );
        return new FanledgerCollection($data);
    }

    public function showEarnings(Request $request, User $user)
    {
        return response()->json([
            'foo' => 'bar',
        ]);
    }
}

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
            'seller_id' => 'uuid|exists:users,id',
            'purchaser_id' => 'uuid|exists:users,id',
        ]);

        // Filters

        if ( !$request->user()->isAdmin() ) { // Check permissions
            // non-admin can only view own resources, option to filter by seller and/or purchaser, defaults to seller
            $filters = [];
            if ( $request->has('seller_id') && ($request->seller_id === $request->user()->id) ) {
                $filters['seller_id'] = $request->seller_id;
            }
            if ( $request->has('purchaser_id') && ($request->purchaser_id === $request->user()->id) ) {
                $filters['purchaser_id'] = $request->purchaser_id;
            }
            if ( !count($filters) ) { // if none set, default to seller...
                $filters['seller_id'] = $request->user()->id;
            }
        } else {
            $filters = $request->only(['seller_id', 'purchaser_id']);
        }

        // Query 

        $query = Fanledger::query()->with(['seller', 'purchaser']);
        foreach ($filters as $key => $f) { // Apply any filters
            // %TODO: subgroup under 'filters' (need to update axios.GET calls as well in Vue)
            switch ($key) {
            case 'seller_id':
            case 'purchaser_id':
                $query->where($key, $f);
                break;
            }
        }

        $data = $query->latest()->paginate( $request->input('take', env('MAX_DEFAULT_PER_REQUEST', 10)) );
        return new FanledgerCollection($data);
    }

    public function showEarnings(Request $request, User $user)
    {
        $this->authorize('view', $user);
        $sums = [];
        $sums['subscriptions'] = DB::table('fanledgers')
            ->where('seller_id', $user->id)
            ->where('fltype', PaymentTypeEnum::SUBSCRIPTION)->where('purchaseable_type', 'timelines')
            ->sum('total_amount');
        $sums['tips'] = DB::table('fanledgers')
            ->where('seller_id', $user->id)
            ->where('fltype', PaymentTypeEnum::TIP)->where('purchaseable_type', 'timelines')
            ->sum('total_amount');
        $sums['posts'] = DB::table('fanledgers')
            ->where('seller_id', $user->id)
            ->where('fltype', PaymentTypeEnum::PURCHASE)->where('purchaseable_type', 'posts')
            ->sum('total_amount');
        return response()->json([
            'earnings' => [
                'sums' => $sums,
            ],
        ]);
    }

    public function showDebits(Request $request, User $user)
    {
        $this->authorize('view', $user);
        $sums = [];
        $sums['subscriptions'] = DB::table('fanledgers')
            ->where('purchaser_id', $user->id)
            ->where('fltype', PaymentTypeEnum::SUBSCRIPTION)->where('purchaseable_type', 'timelines')
            ->sum('total_amount');
        $sums['tips'] = DB::table('fanledgers')
            ->where('purchaser_id', $user->id)
            ->where('fltype', PaymentTypeEnum::TIP)->where('purchaseable_type', 'timelines')
            ->sum('total_amount');
        $sums['posts'] = DB::table('fanledgers')
            ->where('purchaser_id', $user->id)
            ->where('fltype', PaymentTypeEnum::PURCHASE)->where('purchaseable_type', 'posts')
            ->sum('total_amount');
        return response()->json([
            'debits' => [
                'sums' => $sums,
            ],
        ]);
    }
}

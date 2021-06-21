<?php
namespace App\Http\Controllers;

use App\Models\Mycontact;
use Illuminate\Http\Request;
use App\Http\Resources\MycontactCollection;
use App\Http\Resources\Mycontact as MycontactResource;

/**
 * Mycontacts Resource Controller
 * @package App\Http\Controllers
 */
class MycontactsController extends AppBaseController
{
    /**
     * Fetch list of contacts with filter
     *
     * @param Request $request
     * @return MycontactCollection
     */
    public function index(Request $request)
    {
        $request->validate([
            // filters
            'owner_id'                => 'uuid|exists:users,id',
            'is_subscriber'           => 'boolean',
            'is_follower'             => 'boolean',
            'is_cancelled_subscriber' => 'boolean',
            'is_expired_subscriber'   => 'boolean',
            'has_purchased_post'      => 'boolean',
            'has_tipped'              => 'boolean',
        ]);
        $filters = $request->only([
            'owner_id',
            'is_subscriber',
            'is_follower',
            'is_cancelled_subscriber',
            'is_expired_subscriber',
            'has_purchased_post',
            'has_tipped',
        ]) ?? [];

        $sessionUser = $request->user();

        $query = Mycontact::query(); // Init query

        // Check permissions
        if ( !$request->user()->isAdmin() ) {

            $query->where('owner_id', $request->user()->id); // limit to my own
            unset($filters['owner_id']);

        }

        // Apply filters
        foreach ($filters as $key => $v) {
            switch ($key) {
                case 'is_subscriber':
                    $query->whereHas('contact', function ($q1) use (&$sessionUser) {
                        $q1->whereHas('subscribedtimelines', function ($q2) use (&$sessionUser) {
                            $q2->where('timelines.id', $sessionUser->timeline->id);
                        });
                    });
                    break;
                case 'is_follower':
                    $query->whereHas('contact', function ($q1) use (&$sessionUser) {
                        $q1->whereHas('followedForFreeTimelines', function ($q2) use (&$sessionUser) {
                            $q2->where('timelines.id', $sessionUser->timeline->id);
                        });
                    });
                    break;
                case 'is_cancelled_subscriber':
                    $query->whereHas('contact', function ($qContact) use (&$sessionUser) {
                        $qContact->whereHas('timeline', function ($qTimeline) use (&$sessionUser) {
                            $qTimeline->whereHas('subscriptions', function ($qSubscriptions) use (&$sessionUser) {
                                $qSubscriptions->where('user_id', $sessionUser->id)->canceled()
                                    ->where('created_at', function($q2) use (&$sessionUser) {
                                        $q2->select('created_at')->where('user_id', $sessionUser->id)
                                            ->orderByDesc('created_at')->limit(1);
                                    });
                            });
                        });
                    });
                    break;
                case 'is_expired_subscriber':
                    $query->whereHas('contact', function ($qContact) use (&$sessionUser) {
                        $qContact->whereHas('timeline', function ($qTimeline) use (&$sessionUser) {
                            $qTimeline->whereHas('subscriptions', function ($qSubscriptions) use (&$sessionUser) {
                                $qSubscriptions->where('user_id', $sessionUser->id)->expired()
                                    ->where('created_at', function ($q2) use (&$sessionUser) {
                                        $q2->select('created_at')->where('user_id', $sessionUser->id)
                                        ->orderByDesc('created_at')->limit(1);
                                    });
                            });
                        });
                    });
                    break;

                case 'has_purchased_post':
                    $query->whereHas('contact', function ($qContact) use (&$sessionUser) {
                        $qContact->whereHas('purchasedPosts', function ($qPurchasedPosts) use (&$sessionUser) {
                            $qPurchasedPosts->where('user_id', $sessionUser->id);
                        });
                    });
                    break;

                case 'has_tipped':
                    $query->whereHas('contact', function ($qContact) use (&$sessionUser) {
                        $qContact->whereHas('financialAccounts', function ($qFinancialAccounts) use (&$sessionUser) {
                            $qFinancialAccounts->isInternal()->whereHas('transactions', function ($qTransactions) use (&$sessionUser) {
                                $qTransactions->isTip()->isDebit()->whereHas('reference', function ($qReference) use(&$sessionUser) {
                                    $qReference->whereHas('account', function ($qAccount) use(&$sessionUser) {
                                        $qAccount->where('owner_id', $sessionUser->id);
                                    });
                                });
                            });
                        });
                    });
                    break;

                default:
                    $query->where($key, $v);
            }
        }

        $data = $query->latest()->paginate( $request->input('take', env('MAX_DEFAULT_PER_REQUEST', 10)) );
        return new MycontactCollection($data);
    }

    /**
     * Simple search
     *
     * @param Request $request
     * @return MycontactCollection
     */
    public function search(Request $request)
    {
        $searchQuery = $request->input('query') ?? $request->input('q');

        $data = Mycontact::search($searchQuery)->where('owner_id', $request->user()->getKey())
            ->paginate($request->input('take', env('MAX_DEFAULT_PER_REQUEST', 10)));

        return new MycontactCollection($data);
    }

    /**
     * Store new Mycontact
     *
     * @param Request $request
     * @return MycontactResource
     */
    public function store(Request $request)
    {
        $this->authorize('store', Mycontact::class);

        $request->validate([
            'contact_id' => 'required|uuid|exists:users,id',
            'alias'      => 'nullable|string|max:255',
            'cattrs'     => 'nullable|json',
            'meta'       => 'nullable|json',
        ]);

        $mycontact = Mycontact::create(array_merge(
            [ 'owner_id' => $request->user()->getKey() ],
            $request->all(),
        ));

        return new MycontactResource($mycontact);
    }

    /**
     * Update existing Mycontact
     *
     * @param Request   $request
     * @param Mycontact $mycontact
     * @return MycontactResource
     */
    public function update(Request $request, Mycontact $mycontact)
    {
        $this->authorize('update', $mycontact);

        $request->validate([
            'alias'  => 'nullable|string|max:255',
            'cattrs' => 'nullable|json',
            'meta'   => 'nullable|json',
        ]);

        $fields = ['alias', 'cattrs', 'meta'];

        foreach($fields as $field) {
            if ($request->has($field)) {
                $mycontact->{$field} = $request->input($field);
            }
        }

        $mycontact->save();

        return new MycontactResource($mycontact);
    }

    /**
     * Show existing Mycontact
     *
     * @param Mycontact $mycontact
     */
    public function show(Mycontact $mycontact)
    {
        $this->authorize('view', $mycontact);
        return new MycontactResource($mycontact);
    }

    /**
     * Delete existing Mycontact
     *
     * @param Mycontact $mycontact
     */
    public function destroy(Mycontact $mycontact)
    {
        $this->authorize('delete', $mycontact);
        $mycontact->delete();
        return;
    }

}

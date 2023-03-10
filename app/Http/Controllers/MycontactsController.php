<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Mycontact;
use Illuminate\Http\Request;
use App\Http\Resources\MycontactCollection;
use App\Http\Resources\Mycontact as MycontactResource;
use App\Http\Resources\MycontactUser;
use App\Http\Resources\MycontactUserCollection;
use Illuminate\Support\Facades\Config;

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
            'include_non_contacts'    => 'boolean',
            'is_offline'              => 'boolean',
            'is_online'               => 'boolean',
        ]);
        $filters = $request->only([
            'owner_id',
            'is_subscriber',
            'is_follower',
            'is_cancelled_subscriber',
            'is_expired_subscriber',
            'has_purchased_post',
            'has_tipped',
            'is_offline',
            'is_online',
        ]) ?? [];

        $sessionUser = $request->user();

        $query = Mycontact::query(); // Init query

        $all = $request->include_non_contacts || false;
        $usersQuery = User::query();

        // Check permissions
        if ( !($request->user()->isAdmin() && $request->has('owner_id')) ) {
            $query->where('owner_id', $request->user()->id); // limit to my own
            unset($filters['owner_id']);
        }

        // Apply filters
        foreach ($filters as $key => $v) {
            switch ($key) {
                case 'is_subscriber':
                    if ($all) {
                        $usersQuery->whereHas('subscribedtimelines', function ($q2) use (&$sessionUser) {
                            $q2->where('timelines.id', $sessionUser->timeline->id);
                        });
                    } else {
                        $query->whereHas('contact', function ($q1) use (&$sessionUser) {
                            $q1->whereHas('subscribedtimelines', function ($q2) use (&$sessionUser) {
                                $q2->where('timelines.id', $sessionUser->timeline->id);
                            });
                        });
                    }
                    break;

                case 'is_follower':
                    if ($all) {
                        $usersQuery->whereHas('followedForFreeTimelines', function ($q2) use (&$sessionUser) {
                            $q2->where('timelines.id', $sessionUser->timeline->id);
                        });
                    } else {
                        $query->whereHas('contact', function ($q1) use (&$sessionUser) {
                            $q1->whereHas('followedForFreeTimelines', function ($q2) use (&$sessionUser) {
                                $q2->where('timelines.id', $sessionUser->timeline->id);
                            });
                        });
                    }

                    break;
                case 'is_cancelled_subscriber':
                    if ($all) {
                        $usersQuery->whereHas('timeline', function ($qTimeline) use (&$sessionUser) {
                            $qTimeline->whereHas('subscriptions', function ($qSubscriptions) use (&$sessionUser) {
                                $qSubscriptions->where('user_id', $sessionUser->id)->canceled()
                                    ->where('created_at', function ($q2) use (&$sessionUser) {
                                        $q2->select('created_at')->where('user_id', $sessionUser->id)
                                        ->orderByDesc('created_at')->limit(1);
                                    });
                            });
                        });
                    } else {
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
                    }

                    break;
                case 'is_expired_subscriber':
                    if ($all) {
                        $usersQuery->whereHas('timeline', function ($qTimeline) use (&$sessionUser) {
                            $qTimeline->whereHas('subscriptions', function ($qSubscriptions) use (&$sessionUser) {
                                $qSubscriptions->where('user_id', $sessionUser->id)->expired()
                                    ->where('created_at', function ($q2) use (&$sessionUser) {
                                        $q2->select('created_at')->where('user_id', $sessionUser->id)
                                        ->orderByDesc('created_at')->limit(1);
                                    });
                            });
                        });
                    } else {
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
                    }

                    break;

                case 'has_purchased_post':
                    if ($all) {
                        $usersQuery->whereHas('purchasedPosts', function ($qPurchasedPosts) use (&$sessionUser) {
                            $qPurchasedPosts->where('user_id', $sessionUser->id);
                        });
                    } else {
                        $query->whereHas('contact', function ($qContact) use (&$sessionUser) {
                            $qContact->whereHas('purchasedPosts', function ($qPurchasedPosts) use (&$sessionUser) {
                                $qPurchasedPosts->where('user_id', $sessionUser->id);
                            });
                        });
                    }

                    break;

                case 'has_tipped':
                    // TODO: Fix with tips table
                    if ($all) {
                        $usersQuery->whereHas('financialAccounts', function ($qFinancialAccounts) use (&$sessionUser) {
                            $qFinancialAccounts->isInternal()->whereHas('transactions', function ($qTransactions) use (&$sessionUser) {
                                $qTransactions->isTip()->isDebit()->whereHas('reference', function ($qReference) use (&$sessionUser) {
                                    $qReference->whereHas('account', function ($qAccount) use (&$sessionUser) {
                                        $qAccount->where('owner_id', $sessionUser->id);
                                    });
                                });
                            });
                        });
                    } else {
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
                    }
                    break;

                case 'is_offline':
                    if ($all) {
                        $usersQuery->where('is_online', 0)->orWhereNull('is_online');
                    } else {
                        $query->whereHas('contact', function ($q1) {
                            $q1->where('is_online', 0)->orWhereNull('is_online');
                        });
                    }
                    break;

                case 'is_online':
                    if ($all) {
                        $usersQuery->where('is_online', 1)->where('id', '<>', $sessionUser->id);
                    } else {
                        $query->whereHas('contact', function ($q1) {
                            $q1->where('is_online', 1);
                        });
                    }
                    break;

                default:
                    if ($all) {
                        //
                    } else {
                        $query->where($key, $v);
                    }

            }
        }

        if ($request->has('sortBy')) {
            switch($request->input('sortBy')) {
                // Oldest first
                case 'oldest':
                    $query->oldest();
                    $usersQuery->oldest();
                    break;
                // Newest first
                case 'recent':
                default:
                    $query->latest();
                    $usersQuery->latest();
                    break;
            }
        }

        if ($all) {
            $data = $usersQuery->groupBy('id')->paginate( $request->input('take', Config::get('collections.defaultMax', 10)));

            return new MycontactUserCollection($data);
        }

        $data = $query->paginate( $request->input('take', Config::get('collections.defaultMax', 10)) );
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
            ->paginate( $request->input('take', Config::get('collections.defaultMax', 10)) );

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

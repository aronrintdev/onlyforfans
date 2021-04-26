<?php

namespace App\Http\Controllers;

use App\Http\Resources\Subscription as SubscriptionResource;
use App\Http\Resources\SubscriptionCollection;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class SubscriptionsController extends Controller
{

    /**
     * Helper for pagination takes
     *
     * @param Request $request
     * @return mixed
     */
    private function take(Request $request)
    {
        return $request->input('take', Config::get('collections.max.subscriptions', 50));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = $user->subscriptions()->orderBy('created_at', 'desc');
        if ($request->inactive) {
            $data = $query->inactive();
        } else {
            $data = $query->active();
        }
        $data = $query->paginate($this->take($request));

        return new SubscriptionCollection($data);
    }

    /**
     * Retrieve the count of a user's subscriptions
     *
     * @return \Illuminate\Http\Response
     */
    public function count()
    {
        $user = Auth::user();

        return [
            'active' => $user->subscriptions()->active()->count(),
            'inactive' => $user->subscriptions()->inactive()->count(),
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Subscription  $subscription
     * @return \Illuminate\Http\Response
     */
    public function show(Subscription $subscription)
    {
        $this->authorize('view', $subscription);

        return new SubscriptionResource($subscription);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Subscription  $subscription
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Subscription $subscription)
    {
        $this->authorize('update', $subscription);
        //

    }

    /**
     * Cancels an active subscription
     *
     * @param Request $request
     * @param Subscription $subscription
     * @return \Illuminate\Http\Response
     */
    public function cancel(Request $request, Subscription $subscription)
    {
        $this->authorize('cancel', $subscription);
        $subscription->cancel();
        return new SubscriptionResource($subscription);
    }

    /**
     * Soft remove the specified resource from storage.
     *
     * @param  \App\Models\Subscription  $subscription
     * @return \Illuminate\Http\Response
     */
    public function destroy(Subscription $subscription)
    {
        $this->authorize('delete', $subscription);
        //
    }

    /**
     * Restore the specified resource from deleted status.
     *
     * @param  \App\Models\Subscription  $subscription
     * @return \Illuminate\Http\Response
     */
    public function restore(Subscription $subscription)
    {
        $this->authorize('restore', $subscription);
        //
    }

    /**
     * Force remove the specified resource from storage.
     *
     * @param  \App\Models\Subscription  $subscription
     * @return \Illuminate\Http\Response
     */
    public function forceDelete(Subscription $subscription)
    {
        $this->authorize('forceDelete', $subscription);
        //
    }

}

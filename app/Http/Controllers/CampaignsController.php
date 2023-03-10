<?php
namespace App\Http\Controllers;

use Log;
use Exception;
use Money\Money;
use App\Models\User;
use App\Rules\InEnum;

use App\Models\Campaign;
use Illuminate\Http\Request;
use App\Enums\CampaignTypeEnum;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\Campaign as CampaignResource;

class CampaignsController extends AppBaseController
{
    // Gets the active campaign for the session user
    public function active(Request $request)
    {
        $campaign = Campaign::where('creator_id', $request->user()->id)->where('active', true)->first();
        if (!$campaign) {
            return response()->json(null, 200);
        }
        return new CampaignResource($campaign);
    }

    // Gets the active campaign for the specified user
    public function showActive(Request $request, User $user)
    {
        $campaign = Campaign::where('creator_id', $user->id)->where('active', true)->first();
        if (!$campaign) {
            return response()->json(null, 200);
        }
        return new CampaignResource($campaign);
    }

    // Stops the active campaign for the current user
    public function stop(Request $request)
    {
        Campaign::deactivateAll($request->user());
        return response()->json([], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => ['required', new InEnum(new CampaignTypeEnum())],
            'has_new' => 'nullable|boolean',
            'has_expired' => 'nullable|boolean',
            'subscriber_count' => 'nullable|integer|min:0',
            'offer_days' => 'required|integer|min:0',
            'discount_percent' => 'nullable|integer|min:1',
            'trial_days' => 'nullable|integer|min:1',
            'message' => 'nullable|string',
        ]);

        if ( !$request->has_expired && !$request->has_new ) {
            // at least 1 of 3 options must be set
            throw ValidationException::withMessages([
                'has_new' => ['Please select for new or expired subscribers'],
            ]);
        }
        // $this->authorize('create', Campaign::class);

        Campaign::deactivateAll($request->user()); // %TODO: move to boot observer?

        $attrs = $request->only([ 'type', 'has_new', 'has_expired', 'offer_days', 'discount_percent', 'trial_days' ]);
        $attrs['creator_id'] = $request->user()->id;
        $attrs['subscriber_count'] = ($request->has('subscriber_count') && !empty($request->subscriber_count)) ?  $request->subscriber_count : -1;
        if ( $request->has('message') ) {
            $attrs['message'] = $request->message;
        }

        // Check that discount keeps monthly price above 300 cents ($3.00) %FIXME: unhardcode
        $userStats = $request->user()->getStats(); // call to get subscription price & other info
        if ( empty($userStats['prices']) ) {
            throw new Exception('Subscription price is required before creating a promotion');
        }
        $subMonthlyPrice = $userStats['prices']['1_month'] ?? 0;
        $minPrice = Money::USD(Config::get('subscriptions.minPriceInCents', 300));
        $discounted = $subMonthlyPrice->multiply((100 - $attrs['discount_percent']) / 100);
        if ( $discounted->lessThan($minPrice) ) {
            //abort(422, 'Discounted price must be '.nice_currency($discounted).' or higher');
            throw ValidationException::withMessages([
                'discount_percent' => ['Minimum net price required'],
            ]);
        }

        $campaign = Campaign::create($attrs);
        return new CampaignResource($campaign);
    }
}

<?php
namespace App\Http\Controllers;

use Log;
use Illuminate\Http\Request;

use App\Enums\CampaignTypeEnum;
use App\Http\Resources\Campaign as CampaignResource;
use App\Models\Campaign;
use App\Models\User;
use App\Rules\InEnum;

class CampaignsController extends AppBaseController
{
    // Gets the active campaign for the session user
    public function active(Request $request)
    {
        $campaign = Campaign::where('creator_id', $request->user()->id)->where('active', true)->first();
        if (!$campaign) {
            abort(404, 'Could not find an active campaign for the user');
        }
        return new CampaignResource($campaign);
    }

    // Gets the active campaign for the specified user
    public function showActive(Request $request, User $user)
    {
        $campaign = Campaign::where('creator_id', $user->id)->where('active', true)->first();
        if (!$campaign) {
            abort(404, 'Could not find an active campaign for the user');
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
            'offer_days' => 'required|integer|min:1',
            'discount_percent' => 'nullable|integer|min:1',
            'trial_days' => 'nullable|integer|min:1',
            'message' => 'nullable|string',
        ]);

        if ( !$request->has_expired && !$request->has_new ) {
            abort(422); // at least 1 of 3 options must be set
        }

        // $this->authorize('create', Campaign::class);

        Campaign::deactivateAll($request->user());

        $attrs = $request->only([ 'type', 'has_new', 'has_expired', 'offer_days', 'discount_percent', 'trial_days' ]);
        $attrs['creator_id'] = $request->user()->id;
        $attrs['subscriber_count'] = ($request->has('subscriber_count') && !empty($request->subscriber_count)) ?  $request->subscriber_count : -1;
        if ( $request->has('message') ) {
            $attrs['message'] = $request->message;
        }
        $campaign = Campaign::create($attrs);
        return new CampaignResource($campaign);
    }
}

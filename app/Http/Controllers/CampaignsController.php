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
    // Gets the active campaign for the current user
    public function active(Request $request)
    {
        $campaign = Campaign::where([
            ['creator_id', '=', $request->user()->id],
            ['active', '=', 1],
        ])->first();

        if (!$campaign) {
            return null;
        }

        return new CampaignResource($campaign);
    }

    // Gets the active campaign for the specified user
    public function showActive(Request $request, User $creator)
    {
        $campaign = Campaign::where([
            ['creator_id', '=', $creator->id],
            ['active', '=', 1],
        ])->first();

        if (!$campaign) {
            return null;
        }

        return new CampaignResource($campaign);
    }

    // Stops the active campaign for the current user
    public function stop(Request $request)
    {
        Campaign::where([
            ['creator_id', '=', $request->user()->id],
            ['active', '=', 1],
        ])->update(['active' => 0]);

        http_response_code(200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => ['required', new InEnum(new CampaignTypeEnum())],
            'has_new' => 'nullable|boolean',
            'has_expired' => 'nullable|boolean',
            'subscriber_count' => 'required|integer|min:-1',
            'offer_days' => 'required|integer|min:1',
            'discount_percent' => 'nullable|integer|min:1',
            'trial_days' => 'nullable|integer|min:1',
            'message' => 'nullable|string',
        ]);

        if ( !$request->has_expired && !$request->has_new ) {
            abort(422); // at least 1 of 3 options must be set
        }

        // $this->authorize('create', Campaign::class);

        $sessionUser = $request->user();

        // de-active all existing active campaigns for the user
        Campaign::where([
            ['creator_id', '=', $request->user()->id],
            ['active', '=', 1],
        ])->update(['active' => 0]);

        $campaign = Campaign::create(array_merge(
            [ 'creator_id' => $sessionUser->id ],
            $request->all(),
        ));

        return new CampaignResource($campaign);
    }
}

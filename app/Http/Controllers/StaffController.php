<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Staff;
use App\Models\Timeline;
use App\Http\Resources\StaffCollection;
use App\Http\Resources\UserCollection;
use App\Models\Permission;
use App\Apis\Sendgrid\Api as SendgridApi;


class StaffController extends Controller
{
    /**
     * Retrieves the logged in user's staff accounts
     *
     * @param Request $request
     * @return array
     */
    public function indexManagers(Request $request)
    {
        $sessionUser = $request->user();

        $accounts = $sessionUser->staffMembers()
            ->with('user')
            ->where('role', 'manager')
            ->paginate($request->input('take', 10));

        return new StaffCollection($accounts);
    }

    /**
     * Retrieves the logged in user's staff accounts
     *
     * @param Request $request
     * @return array
     */
    public function indexStaffMembers(Request $request)
    {
        $sessionUser = $request->user();

        $teams = Staff::where('user_id', $sessionUser->id)->where('role', 'manager')->get();

        foreach( $teams as $team) {
            $team->owner = User::where('id', $team->owner_id)->first();
            $members = Staff::with(['user'])->where('creator_id', $team->owner_id)->where('role', 'staff')->get()->makeVisible(['user']);
            foreach( $members as $member) {
                $member->permissions = $member->permissions()->get();
                $member->name = $member->first_name.' '.$member->last_name;
            }
            $team->members = $members;
        }

        return response()->json($teams);
    }


    /**
     * Retrieve staff account
     *
     * @param Request $request
     * @return array
     */
    public function getManager(Request $request, $id)
    {
        $manager = Staff::where('id', $id)->where('role', 'manager')->first();
        if (!$manager->settings) {
            $manager->settings = [
                'earnings' => [
                    'value' => null
                ]
            ];
        } else {
            $manager->settings = json_decode($manager->settings);
        }
        return response()->json($manager);
    }

    /**
     * Remove staff account
     *
     * @param Request $request
     * @return void
     */
    public function remove(Request $request, $id)
    {
        $staff = Staff::find($id);
        $staff->delete();
    }

    /**
     * Accept staff invitation
     *
     * @param Request $request
     * @return success
     */
    public function acceptInvite(Request $request)
    {
        $sessionUser = $request->user();

        $request->validate([
            'email' => 'required|string',
            'token' => 'required|string',
        ]);

        $staff = Staff::where('email', $request->email)
            ->where('token', $request->token)
            ->first();
        
        if ($staff) {
            $staff->active = true;
            $staff->pending = false;
            $staff->user_id = $sessionUser->id;
            $staff->settings = [];
            $staff->save();

            return response()->json([
                'status' => 200,
            ], 200);
        }

        return response()->json([
            'error' => 'Invalid email or token'
        ], 400);
    }

    /**
     * Update account active/inactive status
     *
     * @param Request $request
     * @return success
     */
    public function changeStatus(Request $request, $id)
    {
        $staff = Staff::find($id);
        $staff->active = !$staff->active;
        $staff->save();

        return response()->json([ 'data' => $staff ]);
    }

    /**
     * Return a list of staff permissions
     *
     * @param Request $request
     * @return array
     */
    public function listPermissions(Request $request)
    {
        $permissions = Permission::where('guard_name', 'staff')->get();

        return response()->json($permissions);
    }

    /**
     * Return updated settings
     *
     * @param Request $request
     * @return array
     */
    public function updateManagerSettings(Request $request, $id)
    {
        $request->validate([
            'settings' => 'required',
            'settings.earnings' => 'required|numeric',
        ]);

        $manager = Staff::where('id', $id)->where('active', true)->where('role', 'manager')->first();
        $settings = $request->input('settings');
        foreach($settings as $key => $value) {
            $settings[$key] = [
                'value' => $value,
                'pending' => true,
            ];
        }
        $manager->settings = $settings;
        $token = str_random(60);
        $manager->token = $token;
        $manager->save();
        
        // Send email notification
        $sessionUser = $request->user();
        $accept_link = url('/staff/managers/'.$manager->id.'/settings/earnings?agreed=true&token='.$token);
        $cancel_link = url('/staff/managers/'.$manager->id.'/settings/earnings?agreed=false&token='.$token);

        SendgridApi::send('change-percentage-of-gross-earnings', [
            'to' => [
                'email' => $manager->email,
            ],
            'dtdata' => [
                'manager_name' => $manager->first_name.' '.$manager->last_name,
                'username' => $sessionUser->name,
                'accept_link' => $accept_link,
                'cancel_link' => $cancel_link,
                'percent' => $settings['earnings']['value'],
                'home_url' => url('/'),
                'referral_url' => url('/referrals'),
                'privacy_url' => url('/privacy'),
            ],
        ]);

        return $manager->settings;
    }

    /**
     * Return updated settings
     *
     * @param Request $request
     * @return array
     */
    public function changeSettings(Request $request, $id)
    {
        $request->validate([
            'agreed' => 'required|boolean',
            'token' => 'required|string'
        ]);

        $manager = Staff::where('id', $id)->where('active', true)->where('role', 'manager')->first();
        
        if ($manager->token !== $request->input('token')) {
            abort(400);
        }
        
        $settings = json_decode($manager->settings);
        $settings->earnings->pending = false;
        $settings->earnings->agreed = $request->input('agreed');
        $manager->settings = json_encode($settings);
        $manager->token = null;
        $manager->save();


        // SendgridApi::send('notify-percentage-of-gross-earnings-acceptance', [
        //     'to' => [
        //         'email' => $creator->email,
        //     ],
        //     'dtdata' => [
        //         'manager_name' => $manager->first_name.' '.$manager->last_name,
        //         'username' => $sessionUser->name,
        //         'accept_link' => $accept_link,
        //         'cancel_link' => $cancel_link,
        //         'percent' => $settings['earnings']['value'],
        //         'home_url' => url('/'),
        //         'referral_url' => url('/referrals'),
        //         'privacy_url' => url('/privacy'),
        //     ],
        // ]);
        return ['status' => 'success'];
    }

}

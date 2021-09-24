<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

use App\Models\Permission;
use App\Models\Staff;
use App\Models\Timeline;
use App\Models\User;

use App\Http\Resources\StaffCollection;
use App\Http\Resources\Staff as StaffResource;
use App\Http\Resources\UserCollection;

use App\Apis\Sendgrid\Api as SendgridApi;

use App\Notifications\StaffSettingsChanged;
use App\Notifications\InviteStaffManager;
use App\Notifications\InviteStaffMember;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            // filters
            'role' => 'string|in:member,manager',
        ]);

        $query = Staff::with('user');
        $query->where('owner_id', $request->user()->id);
        if ( $request->has('role') ) {
            $query->where('role', $request->role);
        }

        $data = $query->paginate( $request->input('take', Config::get('collections.max.default', 10)) );
        return new StaffCollection($data);
    }

    // Add new staff account and send invitation email
    // sendStaffInvite
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email',
            'role' => 'required|string|in:manager,member',
        ]);

        // Add new staff user
        $inviteeEmail = $request->input('email'); // invitee's email
        $invitee = User::where('email', $inviteeEmail)->first(); // ->makeVisible('email'); // invitee: may or may not exist
        $inviter = $request->user(); // ->makeVisible('email');

        // Check if the same invite exists
        $existingStaff = Staff::where('role', $request->input('role'))->where('email', $inviteeEmail)->where('owner_id', $inviter->id)->get();
        if (count($existingStaff) > 0) {
            return response()->json( [ 'message' => 'This user was already invited as a '.$request->input('role') ], 400);
        }

        $attrs = [
            'first_name' => $request->input('first_name'), // invitee
            'last_name' => $request->input('last_name'), // invitee
            'email' => $inviteeEmail, // invitee
            'role' => $request->input('role'),
            'owner_id' => $inviter->id,
            'creator_id' => $request->input('creator_id'), // inviter (?)
        ];
        if ( !empty($invitee->id??null) ) {
            $attrs['user_id'] = $invitee->id;
        }
        $staff = Staff::create($attrs);

        if ($request->has('permissions')) {
            $permissions = $request->input('permissions');
            $staff->permissions()->attach($permissions);
        }

        // Send Invitation email
        switch ( $request->input('role', 'none') ) {
            case 'manager':
                if ( !empty($invitee) ) {
                    $invitee->notify(new InviteStaffManager($staff, $inviter, $invitee));
                } else {
                    InviteStaffManager::sendGuestInvite($staff, $inviter);
                }
                break;
            case 'member':
                if ( !empty($invitee) ) {
                    $invitee->notify(new InviteStaffMember($staff, $inviter, $invitee));
                } else {
                    InviteStaffMember::sendGuestInvite($staff, $inviter);
                }
                break;
        }

        return new StaffResource($staff);
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
            $staff->settings = null;
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
            ];
        }
        $manager->settings = $settings;
        $manager->save();
        
        $manager->user->notify( new StaffSettingsChanged($manager->user, $request->user(), $settings) );

        return $manager->settings;
    }

}

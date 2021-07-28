<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Staff;
use App\Http\Resources\StaffCollection;
use App\Models\Permission;


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
            $team->members = new StaffCollection($members);
        }

        return response()->json($teams);
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
}

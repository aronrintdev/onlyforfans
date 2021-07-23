<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Staff;
use App\Http\Resources\StaffCollection;


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

        $accounts = $sessionUser->staffMembers()
            ->where('role', 'staff')
            ->paginate($request->input('take', 10));

        return new StaffCollection($accounts);
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

}

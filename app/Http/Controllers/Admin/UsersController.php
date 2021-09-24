<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\Admin\User as UserAdminResource;
use App\Http\Resources\Admin\UserCollection;
use App\Models\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    //

    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::query();
        if ($request->has('sortBy')) {
            $query->orderBy($request->sortBy, $request->input('sortDir', 'desc'));
        } else {
            $query->latest();
        }

        return new UserCollection($query->paginate($this->request->input('take', 10)));
    }

    public function show(Request $request, User $user)
    {
        $this->authorize('view', $user);
        $user->load('settings');
        return new UserAdminResource($user);
    }

    public function updateDisablePayments(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $request->validate([
            'disable' => 'required|bool',
        ]);

        $settings = $user->settings;
        $customAttributes = $settings->cattrs;

        if ($request->disable) {
            $customAttributes['disable_payments'] = true;
        } else {
            unset($customAttributes['disable_payments']);
        }
        $settings->update(['cattrs' => $customAttributes]);

        return new UserAdminResource($user);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class RolesController extends Controller
{

    /* --------------------------- Resource Action -------------------------- */

    /**
     * Roles listing.
     * - Paginated
     */
    public function index()
    {
        $this->authorize('viewAny', Role::class);

        $perPage = $this->request->perPage ?? 15;
        return Role::paginate($perPage)->withQueryString();
    }

    /**
     * View a role
     */
    public function show(Role $role)
    {
        $this->authorize('view', $role);
        $role->permissions;
        return $role;
    }

    public function store()
    {
        $this->authorize('create', Role::class);
        $this->request->validate([
            'name'         => 'required|unique:roles,name',
            'display_name' => 'required|unique:roles,display_name',
            'description'  => 'required',
        ]);
        return [
            'created' => Role::create($this->request->all()),
        ];
    }

    public function update(Role $role)
    {
        $this->authorize('update', $role);
        $this->request->validate([
            'name'         => 'required',
            'display_name' => 'required',
            'description'  => 'required',
        ]);
        if ( $role->update($this->request->all()) ) {
            return [ 'updated' => $role ];
        }
        return [
            'message' => 'Updated failed',
            'updated' => false,
        ];
    }

    public function destroy(Role $role)
    {
        $this->authorize('delete', $role);

        $role->delete();
    }

    /* ----------------------------- Permissions ---------------------------- */
    public function getPermissions(Role $role) {
        $this->authorize('view', $role);
        return $role->getAllPermissions();
    }

    /**
     * Add Set of Permissions to role.
     * Any new permissions will use same guard_name as role.
     */
    public function assignPermissions(Role $role)
    {
        $this->authorize('addPermissions', $role);
        $this->request->validate([
            'permissions.*.name' => 'required'
        ]);
        // Check if creating or editing new permissions. Authorize actions if so
        $permissions = collect([]);
        foreach($this->request->permissions as $permission) {
            if (isset($permission->guard_name) && $permission['guard_name'] !== $role->guard_name) {
                throw ValidationException::withMessages([
                    'guard_name' => ['Guard name mismatch, do not attempt to edit the guard name when adding permissions'],
                ]);
            }
            $permissionModel = Permission::where('name', $permission['name'])
                ->where('guard_name', $role->guard_name)
                ->first();
            if (!isset($permissionModel)) {
                // Creating new Permission
                $this->authorize('create', Permission::class);

                $permissionModel = Permission::create($permission);
                // Guarantee same guard as role.
                $permissionModel->guard_name = $role->guard_name;
                $permissionModel->save();

            } else if ($permissionModel->willBeEditedBy(collect($permission))) {
                // Editing Existing Permission
                $this->authorize('edit', $permissionModel);

                $permissionModel->update($permission);
            }
            $permissions->push($permissionModel);
        }
        $role->addPermissions($permissions);
        $role->permissions;
        return $role;
    }

    /**
     * Remove a set of permissions from a role.
     */
    public function removePermissions(Role $role)
    {
        $this->authorize('removePermissions', $role);
        $this->request->validate([
            'permissions.*.name' => 'required'
        ]);
        $role->removePermissions(collect($this->request->permissions));
        $role->permissions;
        return $role;
    }


    /* -------------------------------- Users ------------------------------- */
    /**
     * Get the users that have a role
     * - Paginated
     */
    public function getUsers(Role $role)
    {
        $this->authorize('viewUsers', $role);
        $perPage = $this->request->perPage ?? 15;
        $query = User::role($role->name)->with('roles');
        $users = $query->paginate($perPage);
        $users->setCollection($users->getCollection()->makeVisible('email'));
        return $users;
    }

    /**
     * Get a user's roles
     */
    public function getUserRoles(User $user)
    {
        $this->authorize('viewUserRoles', Role::class);
        return $user->getRoleNames();
    }

    /**
     * Assigns a role to a user
     */
    public function assignUserRole()
    {
        $this->request->validate([
            'user.id' => 'required|exists:users,id',
            'role.name' => 'required|exists:role,name',
        ]);
        $role = Role::findByName($this->request->role->name);
        $user = User::find($this->request->user->id);

        $this->authorize('giveUserRole', $role);

        $user->assignRole($role->name);
        return [
            'userRoles' => $user->getRoleNames(),
        ];
    }

    /**
     * Removes a role from a user
     */
    public function removeUserRole()
    {
        $this->request->validate([
            'user.id' => 'required|exists:users,id',
            'role.name' => 'required|exists:role,name',
        ]);
        $role = Role::findByName($this->request->role->name);
        $user = User::find($this->request->user->id);

        $this->authorize('removeUserRole', $role);

        $user->removeRole($role->name);
        return [
            'userRoles' => $user->getRoleNames(),
        ];
    }

}

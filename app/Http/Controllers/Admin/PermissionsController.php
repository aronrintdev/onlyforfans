<?php

namespace App\Http\Controllers\Admin;

use App\Permission;

class PermissionsController extends Controller
{

    public function index()
    {
        $this->authorize('viewAny', Permission::class);
    }

    public function show(Permission $permission)
    {
        $this->authorize('view', $permission);
    }

    public function store()
    {
        $this->authorize('create', Permission::class);
    }

    public function update(Permission $permission)
    {
        $this->authorize('update', $permission);
    }

    public function destroy(Permission $permission)
    {
        $this->authorize('delete', $permission);
    }

}

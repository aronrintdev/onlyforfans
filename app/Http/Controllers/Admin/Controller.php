<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;
use Spatie\Permission\Exceptions\UnauthorizedException;

class Controller extends BaseController
{

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected $requiredRoles = ['admin', 'super-admin'];
    protected $permissionPath = 'admin.';

    /**
     * Authorize ability, verifies user has required roles.
     */
    public function authorize($ability = null, $arguments = [])
    {
        // Must have required Role
        if (!Auth::user()->hasAnyRole($this->requiredRoles)) {
            throw UnauthorizedException::forRoles($this->roles);
        }
        if (isset($ability)) {
            parent::authorize($ability, $arguments);
        }
    }

    /**
     * Authorize a specific permission.
     */
    public function authorizePermission($action)
    {
        $this->authorize();
        if (!Auth::user()->can($this->permissionPath . $action)) {
            throw UnauthorizedException::forPermissions([$this->permissionPath . $action]);
        }
    }


}

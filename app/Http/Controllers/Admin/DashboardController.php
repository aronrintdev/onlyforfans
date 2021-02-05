<?php

namespace App\Http\Controllers\Admin;

class DashboardController extends Controller
{
    protected $permissionPath = 'admin.dashboard.';

    public function index()
    {
        $this->authorizePermission('view');
        return view('admin.dashboard');
    }
}

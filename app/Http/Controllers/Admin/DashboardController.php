<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    protected $permissionPath = 'admin.dashboard.';

    public function index()
    {
        $this->authorizePermission('view');
        return view('admin.dashboard');
    }

    public function analyzerReport()
    {
        $this->authorizePermission('analyzer-report');

        return Storage::disk('local')->get('analyzerReport.html');
    }
}

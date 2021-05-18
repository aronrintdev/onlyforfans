<?php

namespace App\Http\Controllers\Admin;

use App\Models\Financial\SegpayCall;

class SegpayCallsController extends Controller
{
    //

    public function index()
    {
        $this->authorize('viewAny', SegpayCall::class);

        return SegpayCall::orderby('created_at', 'desc')->paginate($this->request->input('take', 10));
    }
}

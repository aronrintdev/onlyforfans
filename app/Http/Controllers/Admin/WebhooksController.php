<?php

namespace App\Http\Controllers\Admin;

use App\Models\Webhook;

class WebhooksController extends Controller
{
    //

    public function index()
    {
        $this->authorize('viewAny', Webhook::class);

        return Webhook::orderby('created_at', 'desc')->paginate($this->request->input('take', 10));
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Helps determine if a response should be a json return or a view return based on the type of request
     *
     * @param  Illuminate\Http\Request  $request
     * @param  array   $data
     * @param  string  $view
     * @param  string  $status
     * @param  array  $headers
     * @param  int  $options
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function viewOrJson(Request $request, $data, $view, $status = 200, array $headers = [], $options = 0)
    {
        // Check if the item needs to be returned as json.
        // - Is ajax
        // - Requests header wants json (Accept: application/json)
        if ($request->ajax() || $request->header('Accept') === 'application/json') {
            return response()->json($data, $status, $headers, $options);
        }
        return response()->view($view, $data, $status, $headers);
    }
}

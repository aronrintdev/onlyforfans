<?php
namespace App\Http\Controllers;

use App;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

//use App\Http\Resources\Session as SessionResource;
use App\Http\Resources\SessionCollection;
use App\Models\User;
use App\Models\Session;

class SessionsController extends AppBaseController
{
    public function index(Request $request)
    {
        $this->authorize('view', $request->user());
        $query = Session::query()->with('user');
        $query->where('user_id', $request->user()->id);
        $data = $query->paginate( $request->input('take', Config::get('collections.defaultMax', 10)) );
        return new SessionCollection($data);
    }

}

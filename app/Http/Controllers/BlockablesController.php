<?php
namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
//use App\Http\Resources\Post as PostResource;
//use App\Http\Resources\PostCollection;
use App\Models\User;
use App\Models\Country;

class BlockablesController extends AppBaseController
{
    public function match(Request $request)
    {
        $term = $request->term;
        $first = DB::table('countries')->select('slug', 'country_name as display')->where('country_name', 'LIKE', $term.'%');
        $results = DB::table('users')->select('username as slug', 'username as display')->where('username', 'LIKE', $term.'%')->union($first)->get();
        return response()->json([
            'results' => $results,
        ]);
    }
}

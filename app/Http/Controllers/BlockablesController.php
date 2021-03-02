<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
//use App\Http\Resources\Post as PostResource;
//use App\Http\Resources\PostCollection;
use App\Models\User;
use App\Models\Mediafile;
use App\Enums\CountryTypeEnum;

class BlockablesController extends AppBaseController
{

    public function match(Request $request)
    {
        $term = $request->input('term',null);

        $autocompleteItems = [
            [ 'slug' => 'spain', 'display' => 'Spain', ], 
            [ 'slug' => 'france', 'display' => 'France', ], 
            [ 'slug' => 'usa', 'display' => 'USA', ], 
            [ 'slug' => 'germany', 'display' => 'Germany', ], 
            [ 'slug' => 'china', 'display' => 'China', ]
        ];

        return response()->json([
            'results' => $autocompleteItems,
        ]);
    }
}

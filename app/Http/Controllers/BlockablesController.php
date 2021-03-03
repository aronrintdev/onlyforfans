<?php
namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
//use App\Http\Resources\Post as PostResource;
//use App\Http\Resources\PostCollection;
use App\Models\User;
use App\Models\UserSetting;
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

    public function unblock(Request $request, User $user, string $slug)
    {
        $userSetting = $user->settings;
        $cattrs = $userSetting->cattrs; // pop
        if ( !array_key_exists('blocked', $cattrs) ) {
            return;
        }

        $blocked = $cattrs['blocked']; // pop
        foreach ( $blocked as $key => $items ) {
            $index = array_search($slug, $items);
            if ( $index !== false ) {
                array_splice($blocked[$key], $index, 1);
            }
        }
        $cattrs['blocked'] = $blocked; // push
        $userSetting->cattrs = $cattrs; // push
        $userSetting->save();
        
        //{"ips": ["33.33.33.33"], "countries": ["united-states", "germany"], "usernames": ["jonas.cronin", "lillie.kirlin"]}, "privacy": null, "weblinks": null, "watermark": null, "localization": null, "subscriptions": null}
    }
}

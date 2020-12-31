<?php

use App\BlockedProfile;
use App\Setting;
use App\Timeline;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

function trendingTags()
{
    $trending_tags = App\Hashtag::orderBy('count', 'desc')->get();

    if (count($trending_tags) > 0) {
        $min_item_page = Setting::get('min_items_page', 3);
        if (count($trending_tags) > (int) $min_item_page) {
            $trending_tags = $trending_tags->random((int) $min_item_page);
        }
    } else {
        $trending_tags = '';
    }

    return $trending_tags;
}

function suggestedUsers()
{
    $sessionUser = Auth::user();
    $admin_role = App\Role::where('name', 'admin')->get()->first();
    $admin_users = NULL;
    if ($admin_role != NULL) {
        $admin_users = DB::table('role_user')->where('role_id', $admin_role->id)->get();
    }

    $blockProfiles = BlockedProfile::where('blocked_by', $sessionUser->id)->get();
    $blockCountry = $blockProfiles->pluck('country')->toArray();
    $blockedUsers = User::whereIn('country', $blockCountry)->pluck('id')->toArray();

    $suggested_users = '';
    $followingUsers = $sessionUser->following()->get()->pluck('id')->toArray();

    if ($admin_users != NULL) {
        $blockedUsers = array_merge($blockedUsers, $admin_users->pluck('user_id')->toArray());
        $blockedUsers[] = $sessionUser->id;
        $suggested_users = App\User::with('blockedProfiles')
            ->whereDoesntHave('blockedProfiles', function (Builder $q) use ($sessionUser) {
                $q->where('country', 'like', '%'.$sessionUser->country.'%');
            })
            ->whereNotIn('id', $followingUsers)
            ->whereNotIn('id', $blockedUsers)
            ->get();
    }
    else {
        $blockedUsers = array_merge($blockedUsers, $followingUsers);
        $blockedUsers[] = $sessionUser->id; // session user

        $suggested_users = App\User::whereNotIn('id', $blockedUsers)
            ->whereDoesntHave('blockedProfiles', function (Builder $q) use ($sessionUser) {
                $q->where('country', 'like', '%'.$sessionUser->country.'%');
            })->get();
    }

    if (count($suggested_users) > 0) {
        $min_item_page = Setting::get('min_items_page', 3);
        if (count($suggested_users) > (int) $min_item_page) {
            $suggested_users = $suggested_users->random((int) $min_item_page);
        }
    } else {
        $suggested_users = '';
    }

    return $suggested_users;
}

function suggestedGroups()
{
    $suggested_groups = '';
    $suggested_groups = App\Group::whereNotIn('id', Auth::user()->groups()->pluck('group_id'))->where('type', 'open')->get();

    if (count($suggested_groups) > 0) {
        $min_item_page = Setting::get('min_items_page', 3);
        if (count($suggested_groups) > (int) $min_item_page) {
            $suggested_groups = $suggested_groups->random((int) $min_item_page);
        }
    } else {
        $suggested_groups = '';
    }

    return $suggested_groups;
}

function suggestedPages()
{
    $suggested_pages = '';
    $suggested_pages = App\Page::whereNotIn('id', Auth::user()->pageLikes()->pluck('page_id'))->whereNotIn('id', Auth::user()->pages()->pluck('page_id'))->get();

    if (count($suggested_pages) > 0) {
        $min_item_page = Setting::get('min_items_page', 3);
        if (count($suggested_pages) > (int) $min_item_page) {
            $suggested_pages = $suggested_pages->random((int) $min_item_page);
        }
    } else {
        $suggested_pages = '';
    }

    return $suggested_pages;
}

function verifiedBadge($timeline)
{
    $code = '<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-patch-check-fll" fill="#298ad3" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M10.067.87a2.89 2.89 0 0 0-4.134 0l-.622.638-.89-.011a2.89 2.89 0 0 0-2.924 2.924l.01.89-.636.622a2.89 2.89 0 0 0 0 4.134l.637.622-.011.89a2.89 2.89 0 0 0 2.924 2.924l.89-.01.622.636a2.89 2.89 0 0 0 4.134 0l.622-.637.89.011a2.89 2.89 0 0 0 2.924-2.924l-.01-.89.636-.622a2.89 2.89 0 0 0 0-4.134l-.637-.622.011-.89a2.89 2.89 0 0 0-2.924-2.924l-.89.01-.622-.636zm.287 5.984a.5.5 0 0 0-.708-.708L7 8.793 5.854 7.646a.5.5 0 1 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0l3-3z"></path>
             </svg>';
    if($timeline->type == 'user')
    {
        if($timeline->user->verified)
        {
            $result = $code;
        }
        else
        {
            $result = false;
        }
    }
    elseif($timeline->type == 'page')
    {
        if($timeline->page->verified)
        {
            $result = $code;
        }
        else
        {
            $result = false;
        }
    }
    else
    {
        $result = false;
    }
    return $result;
}

function getOS() {

    global $user_agent;

    $os_platform  = "Unknown OS Platform";

    $os_array     = array(
        '/windows nt 10/i'      =>  'Windows 10',
        '/windows nt 6.3/i'     =>  'Windows 8.1',
        '/windows nt 6.2/i'     =>  'Windows 8',
        '/windows nt 6.1/i'     =>  'Windows 7',
        '/windows nt 6.0/i'     =>  'Windows Vista',
        '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
        '/windows nt 5.1/i'     =>  'Windows XP',
        '/windows xp/i'         =>  'Windows XP',
        '/windows nt 5.0/i'     =>  'Windows 2000',
        '/windows me/i'         =>  'Windows ME',
        '/win98/i'              =>  'Windows 98',
        '/win95/i'              =>  'Windows 95',
        '/win16/i'              =>  'Windows 3.11',
        '/macintosh|mac os x/i' =>  'Mac OS X',
        '/mac_powerpc/i'        =>  'Mac OS 9',
        '/linux/i'              =>  'Linux',
        '/ubuntu/i'             =>  'Ubuntu',
        '/iphone/i'             =>  'iPhone',
        '/ipod/i'               =>  'iPod',
        '/ipad/i'               =>  'iPad',
        '/android/i'            =>  'Android',
        '/blackberry/i'         =>  'BlackBerry',
        '/webos/i'              =>  'Mobile'
    );

    foreach ($os_array as $regex => $value)
        if (preg_match($regex, $user_agent))
            $os_platform = $value;

    return $os_platform;
}

function getBrowser() {

    global $user_agent;

    $browser        = "Unknown Browser";

    $browser_array = array(
        '/msie/i'      => 'Internet Explorer',
        '/firefox/i'   => 'Firefox',
        '/safari/i'    => 'Safari',
        '/chrome/i'    => 'Chrome',
        '/edge/i'      => 'Edge',
        '/opera/i'     => 'Opera',
        '/netscape/i'  => 'Netscape',
        '/maxthon/i'   => 'Maxthon',
        '/konqueror/i' => 'Konqueror',
        '/mobile/i'    => 'Handheld Browser'
    );

    foreach ($browser_array as $regex => $value)
        if (preg_match($regex, $user_agent))
            $browser = $value;

    return $browser;
}

function get_client_ip()
{
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    } else if (isset($_SERVER['HTTP_FORWARDED'])) {
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    } else if (isset($_SERVER['REMOTE_ADDR'])) {
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    } else {
        $ipaddress = 'UNKNOWN';
    }

    return $ipaddress;
}

function test_null($var)
{
    if ($var != "") {
        return ($var);
    }
}

function checkBlockedProfiles($username)
{
    $timeline = Timeline::where('username', $username)->first();
    if($timeline && Auth::check()) {
        $user = User::with('blockedProfiles')->where('timeline_id', $timeline['id'])->first();
        if (Auth::user()->id != $user->id && $user->blockedProfiles->count()) {
            $countries = array_filter($user->blockedProfiles->pluck('country')->toArray(), 'test_null');
            $ipAddresses = array_filter($user->blockedProfiles->pluck('ip_address')->toArray(), 'test_null');

            $authUser = Auth::user();
            $clientIp = get_client_ip();
            if ( 
                 in_array(strtolower($authUser->country), array_map('strtolower', $countries)) 
                 || in_array($clientIp, $ipAddresses)
               ) 
            {
                return false;
            }
        }
    }

    return true;
}

function isBlockByMe($username)
{
    $timeline = Timeline::where('username', $username)->first();
    $sessionUser = Auth::user();

    if($timeline && Auth::check()) {
        $timelineUser = User::with('blockedProfiles')->where('timeline_id', $timeline['id'])->first();
        if (Auth::user()->id != $timelineUser->id && $sessionUser->blockedProfiles->count()) {
            $countries = array_filter($sessionUser->blockedProfiles->pluck('country')->toArray(), 'test_null');
            $ipAddresses = array_filter($sessionUser->blockedProfiles->pluck('ip_address')->toArray(), 'test_null');
            $clientIp = get_client_ip();
            if (in_array(strtolower($timelineUser->country), array_map('strtolower', $countries)) || in_array($clientIp, $ipAddresses)) {
                return true;
            }
        }
    }

    // %PSG: if the above if false (not blocked), test for direct block...
    $userToTest = $timeline->user;
    if ($userToTest) {
        $exists = BlockedProfile::where('blocked_by', $sessionUser->id)
                    ->where('blockee_id', $userToTest->id)
                    ->first();
        if ($exists) {
            return true; // blocked
        }
    }

    return false; // not blocked
}

/**
 * @return array
 */
function filterByBlockedFollowings()
{
    $id = Auth::id();
    $user = User::with('following')->find($id);
    $followings = $user->following()->pluck('leader_id')->toArray();
    $clientIp = get_client_ip();
    $blockedProfiles = BlockedProfile::where('country', '=', $user->country)
        ->orWhere('ip_address', '=', $clientIp)
        ->pluck('blocked_by')->toArray();
    $followingIds = array_diff($followings, $blockedProfiles);

    return $followingIds;
}

/**
 * @return array
 */
function get_image_insert_location()
{
    return [
        'top-left' => trans('common.top-left'),
        'top' => trans('common.top'),
        'top-right' => trans('common.top-right'),
        'left' => trans('common.left'),
        'center' => trans('common.center'),
        'right' => trans('common.right'),
        'bottom-left' => trans('common.bottom-left'),
        'bottom' => trans('common.bottom'),
        'bottom-right' => trans('common.bottom-right'),
    ];
}

/**
 * @param $login_id
 * @param $post_user_id
 * @param $timeline_id
 *
 * @return bool
 */
function canUserSeePost($login_id, $post_user_id, $timeline_id = null)
{
    $user = User::findOrFail($post_user_id);
    $userSettings = DB::table('user_settings')->where('user_id', $user->id)->first();
    $result = ($userSettings && !empty($userSettings->post_privacy)) ? $userSettings->post_privacy : 'everyone';
    $followingThisUser = $user->following->contains($login_id);
    $canSee = false;
    if ($result == 'only_follow' && $followingThisUser) {
        $canSee = true;
    } elseif ($result == 'everyone') {
        $canSee = true;
    } elseif ($result == 'only_follow' && !$followingThisUser && $login_id != $timeline_id) {
        $canSee = false;
    }

    if ($login_id == $post_user_id || $login_id == $timeline_id) {
        $canSee = true;
    }
    
    return $canSee;
}

function canMessageToUser($loginUser, $user)
{
    $settings = $user->getUserSettings($user->id);
    $canMessage = false;
    if ($settings->message_privacy == 'everyone' || $settings->message_privacy == '') {
        $canMessage = true;
    } else if ($settings->message_privacy == 'only_follow') {
        $canMessage = $loginUser->followers->contains($user->id);
    }
    
    return $canMessage; 
}

function canCreatePost ($loginUser, $user)
{
    $settings = $user->getUserSettings($user->id);
    $canCreatePost = true;
    if ($settings->timeline_post_privacy == 'everyone' || $settings->timeline_post_privacy == '') {
        $canCreatePost = true;
    } else if ($settings->timeline_post_privacy == 'only_follow') {
        $canCreatePost = $loginUser->followers->contains($user->id);
    } else if ($settings->timeline_post_privacy == 'nobody')  {
        $canCreatePost = false;
    }
    
    return $canCreatePost;
}
